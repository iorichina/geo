--[[
common global functions

@author kim 2013
@version 1.0.0
]]

local table                 = table
local string                = string
local type                  = type
local next                  = next
local pairs                 = pairs
local ipairs                = ipairs
local tonumber              = tonumber
local setmetatable          = setmetatable

local ngx                   = ngx
local print                 = ngx.print
local decode_base64         = ngx.decode_base64

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[-------------------------------------------------------------------------]]

--[[ error logging ]]
local logsUniquify = {};
function log(message, level)
    if ngx and type(ngx.log) == 'function' then
        local level = level or ngx.EMERG;
        if not logsUniquify[level .. message] then
            ngx.log(level, message);
            logsUniquify[level .. message] = true;            
        end
    end
    return nil;
end

--[[
string trim
@param string s
@return string
--]]
function trim(s)
    local match = string.match;
    return match(s,'^()%s*$') and '' or match(s,'^%s*(.*%S)');
end

--[[
check if obj is empty
@return bool
]]
function empty(obj)
    if not obj or obj == '' or obj == 0 then
        return true;
    elseif type(obj) == 'table' and next(obj) == nil then
        return true;
    else
        return false;
    end
end

--[[
get length of table
@return int
]]
function length(tbl)
    local count = 0;
    if type(tbl) == 'table' then
        for _ in pairs(tbl) do count = count + 1 end
    end
    return count;
end

--[[
explode string by delimiter, with result uniquify
@return table|nil
]]
function explode(delimiter, str, filter)
    local rs = {};
    if delimiter and type(delimiter) == 'string' and str and type(str) == 'string' then
        --local tb = {};
        local callback = function(w)
            if filter and type(filter) == 'function' then
                w = filter(w);
            end
            if w then
                --print(w .. '---<br />')
                --tb[w] = true
                table.insert(rs, w);
            end
        end
        string.gsub(str, delimiter, callback);
        --[[for _, v in pairs(tb) do
            table.insert(rs, v);
        end]]
    end
    return rs;
end

--[[
check if two tables are the same
@return bool
]]
function deepcompare(t1, t2)
    local ty1 = type(t1);
    local ty2 = type(t2);
    if ty1 ~= ty2 then return false end
    -- non-table types can be directly compared
    if ty1 ~= 'table' and ty2 ~= 'table' then return t1 == t2 end
    -- as well as tables which have the metamethod __eq
    for k1,v1 in pairs(t1) do
        local v2 = t2[k1];
        if v2 == nil or not deepcompare(v1,v2) then return false end
    end
    for k2,v2 in pairs(t2) do
        local v1 = t1[k2];
        if v1 == nil or not deepcompare(v1,v2) then return false end
    end
    return true;
end

--[[
create table sorting iterator
@return function
]]
function pairsByKeys(tb, f)
    local a = {};
    for n in pairs(tb) do table.insert(a, n) end
    table.sort(a, f);
    local i = 0; -- iterator variable
    local iter = function() -- iterator function
        i = i + 1;
        if a[i] == nil then return nil;
        else return a[i], tb[a[i]];
        end
    end
    return iter;
end

--[[
sort table by value
@example {aa = 1, bb = 3, cc = 2} => {{aa, 1}, {cc, 2}, {bb, 3}}
@return nil|table
]]
function sortAssoc(tb, order, limit)
    local rs = nil;
    if type(tb) == 'table' then
        rs = {};
        local tmp = {}
        for k,v in pairs(tb) do
            table.insert(tmp, {key = k, val = tonumber(v)})
        end
        if next(tmp) ~= nil then
            if order and order == 'DESC' then
                table.sort(tmp, function(a, b) return b.val < a.val end);
            else
                table.sort(tmp, function(a, b) return b.val > a.val end);
            end
        end
        for i,v in ipairs(tmp) do
            table.insert(rs, {v['key'], v['val']});
            if limit and (i >= limit) then break end
        end
    end
    return rs
end

--[[
Convert special characters to HTML entities
'&' (ampersand) becomes '&amp;'
'"' (double quote) becomes '&quot;'
"'" (single quote) becomes '&#039;' (or &apos;)
'<' (less than) becomes '&lt;'
'>' (greater than) becomes '&gt;'
@return string
]]
function htmlspecialchars(str)
    local rs = str or nil;
    if rs and type(rs) == 'string' then
        rs = string.gsub(rs, '&', '&amp;');
        rs = string.gsub(rs, '"', '&quot;');
        rs = string.gsub(rs, "'", '&#039;');
        rs = string.gsub(rs, '<', '&lt;');
        rs = string.gsub(rs, '>', '&gt;');
    end
    return rs;
end

--[[
all characters which have HTML character entity equivalents
are translated into these entities.

@return string
]]
function htmlentities(str)
    if type(str) ~= "string" then
        return nil
    end

    local entities = {
        [' '] = '&nbsp;' ,
        ['¡'] = '&iexcl;' ,
        ['¢'] = '&cent;' ,
        ['£'] = '&pound;' ,
        ['¤'] = '&curren;',
        ['¥'] = '&yen;' ,
        ['¦'] = '&brvbar;' ,
        ['§'] = '&sect;' ,
        ['¨'] = '&uml;' ,
        ['©'] = '&copy;' ,
        ['ª'] = '&ordf;' ,
        ['«'] = '&laquo;' ,
        ['¬'] = '&not;' ,
        ['­'] = '&shy;' ,
        ['®'] = '&reg;' ,
        ['¯'] = '&macr;' ,
        ['°'] = '&deg;' ,
        ['±'] = '&plusmn;' ,
        ['²'] = '&sup2;' ,
        ['³'] = '&sup3;' ,
        ['´'] = '&acute;' ,
        ['µ'] = '&micro;' ,
        ['¶'] = '&para;' ,
        ['·'] = '&middot;' ,
        ['¸'] = '&cedil;' ,
        ['¹'] = '&sup1;' ,
        ['º'] = '&ordm;' ,
        ['»'] = '&raquo;' ,
        ['¼'] = '&frac14;' ,
        ['½'] = '&frac12;' ,
        ['¾'] = '&frac34;' ,
        ['¿'] = '&iquest;' ,
        ['À'] = '&Agrave;' ,
        ['Á'] = '&Aacute;' ,
        ['Â'] = '&Acirc;' ,
        ['Ã'] = '&Atilde;' ,
        ['Ä'] = '&Auml;' ,
        ['Å'] = '&Aring;' ,
        ['Æ'] = '&AElig;' ,
        ['Ç'] = '&Ccedil;' ,
        ['È'] = '&Egrave;' ,
        ['É'] = '&Eacute;' ,
        ['Ê'] = '&Ecirc;' ,
        ['Ë'] = '&Euml;' ,
        ['Ì'] = '&Igrave;' ,
        ['Í'] = '&Iacute;' ,
        ['Î'] = '&Icirc;' ,
        ['Ï'] = '&Iuml;' ,
        ['Ð'] = '&ETH;' ,
        ['Ñ'] = '&Ntilde;' ,
        ['Ò'] = '&Ograve;' ,
        ['Ó'] = '&Oacute;' ,
        ['Ô'] = '&Ocirc;' ,
        ['Õ'] = '&Otilde;' ,
        ['Ö'] = '&Ouml;' ,
        ['×'] = '&times;' ,
        ['Ø'] = '&Oslash;' ,
        ['Ù'] = '&Ugrave;' ,
        ['Ú'] = '&Uacute;' ,
        ['Û'] = '&Ucirc;' ,
        ['Ü'] = '&Uuml;' ,
        ['Ý'] = '&Yacute;' ,
        ['Þ'] = '&THORN;' ,
        ['ß'] = '&szlig;' ,
        ['à'] = '&agrave;' ,
        ['á'] = '&aacute;' ,
        ['â'] = '&acirc;' ,
        ['ã'] = '&atilde;' ,
        ['ä'] = '&auml;' ,
        ['å'] = '&aring;' ,
        ['æ'] = '&aelig;' ,
        ['ç'] = '&ccedil;' ,
        ['è'] = '&egrave;' ,
        ['é'] = '&eacute;' ,
        ['ê'] = '&ecirc;' ,
        ['ë'] = '&euml;' ,
        ['ì'] = '&igrave;' ,
        ['í'] = '&iacute;' ,
        ['î'] = '&icirc;' ,
        ['ï'] = '&iuml;' ,
        ['ð'] = '&eth;' ,
        ['ñ'] = '&ntilde;' ,
        ['ò'] = '&ograve;' ,
        ['ó'] = '&oacute;' ,
        ['ô'] = '&ocirc;' ,
        ['õ'] = '&otilde;' ,
        ['ö'] = '&ouml;' ,
        ['÷'] = '&divide;' ,
        ['ø'] = '&oslash;' ,
        ['ù'] = '&ugrave;' ,
        ['ú'] = '&uacute;' ,
        ['û'] = '&ucirc;' ,
        ['ü'] = '&uuml;' ,
        ['ý'] = '&yacute;' ,
        ['þ'] = '&thorn;' ,
        ['ÿ'] = '&yuml;' ,
        ['"'] = '&quot;' ,
        ["'"] = '&#39;' ,
        ['<'] = '&lt;' ,
        ['>'] = '&gt;' ,
        ['&'] = '&amp;'
    }

    local ret = str:gsub("(.)", entities)
    return ret
end

--[[-------------------------------------------------------------------------]]
--[[ 56特殊函数定义在下面 --]]
--[[-------------------------------------------------------------------------]]

--[[
decode flv id
@return int|string
]]
function flvDeId(id)
    local vid = tonumber(id);
    if (vid and vid ~= 0) then
        return vid;
    else
        vid = tonumber(decode_base64(id));
        if vid then
            return vid;
        else
            return id;
        end
    end
end

--[[
explode & decode ids string into table array
@example "20938015,MjE1NjcwMjQ=" => {20938015, 21567024}
@return table
]]
function eplFlvIds(idStr)
    local rs = {};
    if type(idStr) == 'string' then
        rs = explode('[^,]+', idStr, flvDeId);
    end
    return rs;
end

--[[
replace normal img host to cdn host
@param string imgHost
@return string
]]
function imgHostCdnFast(imgHost)
    local res = imgHost;
    if type(imgHost) == 'string' then
        local imgHostNum;
        string.gsub(imgHost, '(%d+)', function(n) imgHostNum = n end, 1);
        --print(type(imgHostNum));
        if imgHostNum then
            imgHostNum = tonumber(imgHostNum);
            if imgHostNum > 16 or imgHostNum == 15 then
                res = string.gsub(imgHost, 'v' .. imgHostNum .. '%.56%.com', 
                                           'v' .. imgHostNum .. '%.56img%.com');
            end
        end
    end
    return res;
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables ]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME);
    end
})


