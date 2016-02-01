local print = ngx.print;
local say = ngx.say;
ngx.header['Content-Type']='text/plain; charset=utf-8';

local args = ngx.req.get_uri_args();
if not args.ip then
    ngx.exit(400);
    ngx.exit(ngx.HTTP_BAD_REQUEST);
end

say('ip:', args.ip);

local ipdata = io.open('/usr/local/openresty/nginx/html/www/lua.io/ip_data/pure/ip_utf8.txt', 'r');
if not ipdata then
    ngx.exit(503);
    ngx.exit(ngx.HTTP_SERVICE_UNAVAILABLE);
end

local ip2long = function (ip) 
    if not ip then
        return nil;
    end
    local ipa, ipb, ipc, ipd = string.match(ip, "(%d+)%.(%d+)%.(%d+)%.(%d+)");
    if not ipa or not ipb or not ipc or not ipd then
        return nil;
    end

    return tonumber(ipa)*16777216 + tonumber(ipb)*65536 + tonumber(ipc)*256 + tonumber(ipd);
end

local ipnums = ip2long (args.ip);
local ipmode = '%d+%.%d+%.%d+%.%d+';
local match_times = 0;
local region = '';
while true do
    local line = ipdata:read('*line');
    if not line then
        break;
    end
    if match_times > 445449 then
        break;
    end
    -- say (line);
    local ips, ipe, area, detail = string.match(line, "("..ipmode..")%s+("..ipmode..")%s+([^%s]+)%s+([^%s]+)");
    match_times = match_times + 1;
    -- say(ips, ':', ipe, ':', area, ":", detail);

    local sipnum, eipnum = ip2long(ips), ip2long(ipe);

    if sipnum and eipnum and sipnum <= ipnums and eipnum >= ipnums then
        region = area ..'-'..args.ip;
        break;
    end
end

say('');
say('region:', region);