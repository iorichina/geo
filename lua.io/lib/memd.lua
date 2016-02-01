--[[
lua-resty-memcached proxy module

@see https://github.com/agentzh/lua-resty-memcached
@author kim 2013
--]]

--[[ error logging --]]
local g                     = require('lib.g')
local log                   = g.log

local memcached             = require('resty.memcached')
local setmetatable          = setmetatable

local print                 = ngx.print
local header                = ngx.header
local null                  = ngx.null

--[[ init module --]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. --]]
local mt = {__index = _M};

--[[ instantiation --]]
function new(self, cfg)
    local memd, err = memcached:new();
    --memd = nil;
    if not memd then
        memd = log('failed to instantiate memcached: ' .. (err and err or ''));
    end
    return setmetatable({
        memd = memd,
        cfg = cfg,
    }, mt);
end

--[[
lazying connecting
@return 1|nil
--]]
function connect(self)
    if not self.connected then
        local memd = self.memd;
        local cfg = self.cfg;
        --[[ no need to reconnect if refused ever once --]]
        if memd and not self.connect_refused then
            --[[ connect timeout --]]
            if cfg.connect_timeout then
                memd:set_timeout(cfg.connect_timeout);
            end

            local ok, err = memd:connect(cfg.host, cfg.port);
            if not ok then
                self.connect_refused = true;
                log('failed connecting memcached: ' .. cfg.host 
                    .. ':' .. cfg.port .. ' - ' .. (err and err or ''));
            end
            self.connected = ok;
            --header['-mh-' .. cfg.host .. '-' .. cfg.port] = 1;
        end
    end
    return self.connected;
end

--[[
get
@return string|nil
--]]
function get(self, key)
    local memd, res = self.memd;
    if memd then
        if not self.connected then
            self:connect();
        end
        if self.connected then
            res = memd:get(key);
            if res == null then
                res = nil;
            end
        end
    end
    return res;
end

--[[ set --]]
function set(self, ...)
    local memd, res = self.memd;
    if memd then
        if not self.connected then
            self:connect();
        end
        if self.connected then
            res = memd:set(...);
        end
    end
    return res;
end

--[[ delete --]]
function delete(self, key)
    local memd, res = self.memd;
    if memd then
        if not self.connected then
            self:connect();
        end
        if self.connected then
            res, self.lasterr = memd:delete(key);
        end
    end
    return res;
end

--[[ keep some conns when try to close connection --]]
function close(self)
    local memd = self.memd;
    local res = nil;
    local err = '';
    if memd and self.connected then
        local cfg = self.cfg;
        --[[ keepalive timeout and connection pool size --]]
        if cfg.max_idle_timeout and cfg.pool_size then
            res, err = memd:set_keepalive(cfg.max_idle_timeout, cfg.pool_size);
        else
            res, err = memd:close();
        end
        if res then
            self.connected = nil;
        else
            log("unable to close memcached: " .. (err and err or ''));
        end
    end
    return res;
end

--[[ Returns memcached server statistics information --]]
function stats(self, ...)
    local memd, res = self.memd;
    if memd and self.connected then
        res = memd:stats(...);
    end
    return res;
end

--[[ to prevent use of casual module global variables --]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME);
    end
})


