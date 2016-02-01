--[[
lua-resty-redis proxy module

@see https://github.com/agentzh/lua-resty-redis
@author kim 2013
@version 1.0.0
--]]

local redis                 = require('resty.redis')
local cjson                 = cjson
local table                 = table
local type                  = type
local tonumber              = tonumber
local setmetatable          = setmetatable
local ipairs                = ipairs
local print                 = ngx.print
local null                  = ngx.null

--[[ error logging --]]
local g                     = require('lib.g')
local log                   = g.log

--[[ init module --]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. ]]
local mt = {__index = _M}

--[[ instantiation ]]
function new(self, cfg)
    local rds, err = redis:new()
    --rds = nil;
    if not rds then
        rds = log('failed to instantiate redis: ' .. (err and err or ''))
    end
    return setmetatable({
        rds = rds,
        cfg = cfg,
    }, mt)
end

--[[
lazying connecting
@return 1|nil
]]
function connect(self)
    if not self.connected then
        local rds = self.rds
        if rds and not self.connect_refused then
            local cfg = self.cfg
            --[[ connect timeout --]]
            if cfg.connect_timeout then
                rds:set_timeout(cfg.connect_timeout)
            end
            local ok, err = rds:connect(cfg.host, cfg.port)
            if not ok then
                self.connect_refused = true
                log('failed connecting redis: ' .. cfg.host 
                    .. ':' .. cfg.port .. ' - ' .. (err and err or ''))
            end
            self.connected = ok
        end
    end
    return self.connected
end

--[[ 华丽分割 ---------------------------------------------------------------]]

--[[
get value
@return string|nil
]]
function get(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:get(...)
            if res == null then
                res = nil
            end
        end
    end
    return res
end

--[[
set value
@return true|nil
]]
function set(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:set(...)
            if res and res == 'OK' then
                res = true
            end
        end
    end
    return res
end

--[[
set value
@return true|nil
]]
function setex(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:setex(...)
            if res and res == 'OK' then
                res = true
            end
        end
    end
    return res
end

--[[ 华丽分割 ---------------------------------------------------------------]]

--[[
below boths are right:
rds:hmset(myhash, field1, value1, field2, value2, ...)
rds:hmset(myhash, { field1 = value1, field2 = value2, ... })

@see https://github.com/agentzh/lua-resty-redis#hmset
@return true|nil
]]
function hmset(self, myhash, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:hmset(myhash, ...)
            if res and res == 'OK' then
                res = true
            else
                res = nil
            end
        end
    end
    return res
end

--[[
@see https://github.com/agentzh/lua-resty-redis#hmget
@return table|nil
]]
function hmget(self, myhash, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:hmget(myhash, ...)
        end
    end
    return res
end

--[[
@return table|nil
]]
function hgetall(self, myhash)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:hgetall(myhash)
            if res == null then
                res = nil
            end
        end
    end
    return res
end

--[[ 华丽分割 ---------------------------------------------------------------]]

--[[
add zset values
@return true|nil
]]
function zadd(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:zadd(...)
            if res then
                res = true
            end
        end
    end
    return res
end

--[[
get zset size
@return int|nil
--]]
function zcard(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:zcard(...)
            if res == null then
                res = nil
            end
        end
    end
    return res
end

--[[
get zset score by key
@return int
]]
function zscore(self, key, member)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:zscore(key, member)
            if res == null then
                res = 0
            end
            res = tonumber(res)
        end
    end
    return res
end

--[[
zadd values
@return table|nil - withscores: {{member1, member2, ...}, {score1, score2, ...}}
]]
function zrevrange(self, key, zStart, zEnd, isWithScores)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            isWithScores = isWithScores and 'WITHSCORES' or false
            if not zStart or not zEnd or zStart > zEnd then
                log("invalid redis zset range")
            else
                res = rds:zrevrange(key, zStart, zEnd, isWithScores)
                if res then
                    if res == null or (type(res) == 'table' and #res == 0) then
                        res = nil
                    else
                        if isWithScores then
                            local members = {}
                            local scores = {}
                            for i,v in ipairs(res) do
                                if i % 2 == 1 then
                                    table.insert(members, res[i])
                                    local j = i + 1
                                    table.insert(scores, res[j])
                                end
                            end
                            res = {members = members, scores = scores}
                        end
                    end
                end
            end
        end
    end
    return res
end

--[[ 华丽分割 ---------------------------------------------------------------]]

--[[
Returns the remaining time to live of a key that has a timeout
The command returns -2 if the key does not exist.
The command returns -1 if the key exists but has no associated expire.
@return int|nil
]]
function ttl(self, key)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:ttl(key)
            if res == null or res < 0 then
                res = nil
            end
        end
    end
    return res
end

--[[
1 if the key exists.
0 if the key does not exist.
@return bool|nil
]]
function exists(self, key)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:exists(key)
            if res and res == 1 then
                res = true
            else
                res = false
            end
        end
    end
    return res
end

--[[
set expire of a key
1 if the timeout was set.
0 if key does not exist or the timeout could not be set.
@return 1|nil
]]
function expire(self, key, seconds)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            seconds = tonumber(seconds)
            if not seconds then
                log("unable to set redis expire by invalid seconds")
            else
                res = rds:expire(key, seconds)
                if res == 0 then
                    res = nil
                    log("unable to set redis expire: " .. key)
                end
            end
        end
    end
    return res
end

--[[
delete
@return 1|nil
]]
function del(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:del(...)
        end
    end
    return res
end

--[[
close connection
]]
function close(self)
    local rds = self.rds
    local res = nil
    local err = ''
    if rds and self.connected then
        local cfg = self.cfg
        --[[ keepalive timeout and connection pool size --]]
        if cfg.max_idle_timeout and cfg.pool_size then
            res, err = rds:set_keepalive(cfg.max_idle_timeout, cfg.pool_size)
        else
            res, err = rds:close()
        end
        if res then
            self.connected = nil
        else
            log("unable to close redis: " .. err)
        end
    end
    return res
end

--[[
Returns server statistics information
@return table|nil
]]
function info(self, ...)
    local rds, res = self.rds
    if rds and self.connected then
        res = rds:info(...)
    end
    return res
end

--[[ 华丽分割 ---------------------------------------------------------------]]

--[[
init pipeline
@return true|nil
]]
function init_pipeline(self, ...)
    local rds, res = self.rds
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = rds:init_pipeline()
        end
    end
    return res
end

--[[
commit pipeline
@return true|nil, string
]]
function commit_pipeline(self, ...)
    local rds = self.rds
    local res, err
    if rds then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res, err = rds:commit_pipeline()
            if not res then
                log("unable to commit redis pipeline: " .. (err and err or ''))
            end
        end
    end
    return res
end

--[[ 华丽分割 ---------------------------------------------------------------]]

--[[ to prevent use of casual module global variables --]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


