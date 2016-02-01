local ngx = ngx;
local exit = ngx.exit;
local print = ngx.print;
local say = ngx.say;
local pairs = pairs;
local next = next;
local cjson = require "cjson";
local table = require("table")
local string = require("string")

ngx.header['Content-Type']='text/plain; charset=utf-8';

-- get http vars
local client_ip = nil;
local args = ngx.req.get_uri_args();
if not args.ip then
    client_ip = ngx.req.get_headers()["X-Real-IP"];
else
    client_ip = args.ip;
    client_ip = string.gsub (client_ip, "^%s*(.-)%s*$", "%1");
    -- client_ip = string.gsub (client_ip, "%s$", "");
end
if client_ip == nil then
    client_ip = ngx.req.get_headers()["x_forworded_for"];
end
if client_ip == nil then
    client_ip = ngx.var.remote_addr;
end
if not client_ip then
    exit(400);
    exit(ngx.HTTP_BAD_REQUEST);
end

function urlencode(str)
    if (str) then
        str = string.gsub (str, "\n", "\r\n");
        str = string.gsub (str, "([^%w ])",function (c) return string.format ("%%%02X", string.byte(c)) end);
        str = string.gsub (str, " ", "+");
    end
    return str;
end

local function callback (nstr) 
    local loc =  {["geo"]=nstr, ["ip"]=client_ip};
    ngx.header['Set-Cookie'] = 'geoinfo='.. table.concat( {urlencode (loc["geo"]), loc["ip"]}, "-" ) ..'; expires='.. ngx.cookie_time(ngx.now()+1800) ..'; path=/; domain=.56.com';
    ngx.header['Geo-Gen'] = ngx.now();
    
    -- check callback func 
    local callback_func = nil;
    if args.callback then
        callback_func = ngx.re.gsub (args.callback, "(cookie|alert)", "", "i");
        callback_func = ngx.re.match (callback_func, "[a-z0-9%_%.]+", "i");
        if callback_func and next (callback_func) then
            callback_func = callback_func[0];
            print (callback_func, "(");
        end
    end

    -- check json type data
    if args.type and args.type == 'json' then
        print (cjson.encode (loc));
    elseif args.type and args.type == 'json_divide' then
        local divide    = {};
        local geo       = string.match (loc.geo, "(.-)市-");
        if geo then
            local prov, city= string.match (geo,  "(.-)省+(.+)");
            if prov then
                table.insert (divide, prov);
            end
            if city then
                table.insert (divide, city);
            end
        else
            geo = loc.geo;
        end
        if table.maxn (divide) == 0 then
            table.insert (divide, geo);
        end
        print (cjson.encode (divide));
    else
        if callback_func then
            print ("\"");
        end
        print (loc["geo"], "-", loc["ip"]);
        if callback_func then
            print ("\"");
        end
    end

    if callback_func then
        print (")");
    end
end

-- user defined ip area
local ud_ip = {
    ['113.107.234.109'] = '广东省广州市'
};
local location = nil;
for k,v in pairs(ud_ip) do
    if k == client_ip then
        callback (v);
        exit(200);
    end
end

local math = require("math")
local io = require("io")
local iconv = require("iconv");

local ipdat_path0 = "/dev/shm/"
local ipdat_path = ngx.var.document_root .."/ip_data/pure/"
local qqwry = nil

-- binary string to number big-endian
function s2nBE(s)
    if s == nil then return nil end
    local r = 0
    for j = s:len(), 1, -1 do
        r = r + s:sub(j, j):byte() * 256 ^ (j - 1)
    end
    return r
end

function ip2long(s)
    if s == nil then return nil end
    local r = 0
    local i = 3
    for d in s:gmatch("%d+") do
        r = r + d * 256 ^ i
        i = i - 1
        if i < 0 then break end
    end
    return r
end

function long2ip(i)
    if i == nil then return nil end
    local r = ""
    for j = 0, 3, 1 do
        r = i % 256 .. "." .. r
        i = math.floor(i / 256)
    end
    return r:sub(1, -2)
end

-- locate absolute ip info offset from index area
local function locateIpIndex(ip, offset1, offset2)
    local curIp, offset, nextIp
    local m = math.floor((offset2 - offset1) / 7 / 2) * 7 + offset1
    qqwry:seek("set", m)
    local count = 0
    while offset == nil do
        curIp = s2nBE(qqwry:read(4))
        offset = s2nBE(qqwry:read(3))
        nextIp = s2nBE(qqwry:read(4))
        if nextIp == nil then nextIp = 2 ^ 32 end
        if curIp <= ip and ip < nextIp then
            break
        elseif ip < curIp then
            offset2 = m
        else
            offset1 = m + 7
        end
        m = math.floor((offset2 - offset1) / 7 / 2) * 7 + offset1
        qqwry:seek("set", m)
        offset = nil
        count = count + 1
        if count > 200 then break end
    end
    if count > 200 then return nil end
    return offset
end

-- get location info from given offset
-- param  offset, offset for return (if not set offsetR, the function will return current pos)
-- return location offset, next location info offset
local function getOffsetLoc(offset, offsetR)
    local loc = ""
    qqwry:seek("set", offset)
    local form = qqwry:read(1)

    if form ~= "\1" and form ~= "\2" then
        qqwry:seek("set", offset)
        local b = qqwry:read(1)
        while b ~= nil and b ~= "\0" do
            loc = loc .. b
            b = qqwry:read(1)
        end
        if offsetR ~= nil then
            return loc, offsetR
        else
            return loc, qqwry:seek()
        end

    else
        local offsetNew = s2nBE(qqwry:read(3))
        if form == "\2" then
            return getOffsetLoc(offsetNew, offset + 4)
        else
            return getOffsetLoc(offsetNew)
        end
    end
end

function query(ip)
    qqwry = io.open(ipdat_path0 .. "qqwry.dat", "r")
    if qqwry == nil then 
        qqwry = io.open(ipdat_path .. "qqwry.dat", "r")
        if not qqwry then
            return nil, "ip data not found" 
        end
    end

    local offset = locateIpIndex(ip2long(ip), s2nBE(qqwry:read(4)), s2nBE(qqwry:read(4)))
    local loc1, loc2
    loc1,offset = getOffsetLoc(offset + 4)
    loc2 = getOffsetLoc(offset)
    qqwry:close()
    return {loc1, loc2}
end

-- do get location by ip
location = location or query (client_ip);
if type (location) == 'table' and next(location) then
    local cd = iconv.new("utf-8", "gbk");
    local nstr, err = cd:iconv(location[1]);
    -- print (nstr, err);
    -- exit(200);

    callback (nstr);
end