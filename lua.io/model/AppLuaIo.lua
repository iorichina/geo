
-- require("lib.serialize")
-- local unserialize           = unserialize

local cjson                 = require('cjson')

-- local neturl                = require('lib.neturl')
-- local memd                  = require("lib.memd")
local mysql                 = require("lib.mysql")
-- local redis                 = require("lib.redis")
-- local http                  = require("lib.http")
local g                     = require("lib.g")

local table                 = table
local string                = string
local find                  = string.find
local max                   = math.max

local setmetatable          = setmetatable
local type                  = type
local pairs                 = pairs
local ipairs                = ipairs
local tostring              = tostring
local tonumber              = tonumber
local next                  = next

local print                 = ngx.print
-- local header                = ngx.header
-- local md5                   = ngx.md5
-- local quote_sql_str         = ngx.quote_sql_str
-- local crc32_short           = ngx.crc32_short
-- local escape_uri            = ngx.escape_uri
-- local unescape_uri          = ngx.unescape_uri
local null                  = ngx.null

--[[ error logging --]]
local log                   = g.log

--[[ init module --]]
module(...)

--[[ indexed by current module env. --]]
local mt = {__index = _M}

--[[
instantiation
@return table
--]]
function new(self, utils)
    if not utils.config then
        log("utils' configs missing!");
        return;
    end
    return setmetatable({
        config = utils.config
    }, mt);
end

--[[
get dbconn mysql object
@return table
--]]
function _getDb(self, isMaster)
    if not self.dbconn then
        local config = self.config;
        if config then
            if isMaster then
                self.dbconn = mysql:new(config);
            else
                self.dbconn = mysql:new(config);
            end
        else
            log("dbconn config missing!");
        end
    end
    return self.dbconn;
end

--[[
get video informations from db
@param int id
@param bool fresh - true表示不从缓存取数据，直接从数据库里面取
@return table|nil
--]]
function getUserInfo(self)
    local info = nil;
    if self.id then
        local id = self.id;

        local sql = {
            "SELECT id,name,pass",
            "FROM test WHERE ",
            "`id` = :id ",
            "LIMIT 1"
        };
        sql = table.concat( sql, " " );
        -- print (sql);
        info = self:_getDb():findOne(sql, {id = id});
        --print("result: ", cjson.encode(res));
        if not info then
            info = {};
            return info;
        end
    elseif self.name then
        local name = self.name;

        local sql = {
            "SELECT id,name,pass",
            "FROM test WHERE ",
            "`name` = :name ",
            "LIMIT 1"
        };
        sql = table.concat( sql, " " );
        -- print (sql);
        info = self:_getDb():findOne(sql, {name = name});
        --print("result: ", cjson.encode(res));
        if not info then
            info = {};
            return info;
        end
    else
        log("user id/name missing!");
    end
    return info;
end

--[[
destory
确保  dbconn 关闭
--]]
function destory(self)
    self:_getDb():close();
end

--[[ to prevent use of casual module global variables --]]
-- setmetatable(_M, {
--     __newindex = function (table, key, val)
--         log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME);
--     end
-- })


