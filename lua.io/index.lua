ngx.header['Content-Type']='text/plain; charset=utf-8';

ngx.say('hello Tina!');

local abc = ',,,,fddsd,,,,fsdfi939,lsdlf00kdk,jll,,,';
local pros = {}
for v in ngx.re.gmatch (abc, '([^,]+),??') do 
    table.insert (pros, type (v)== 'table' and v[1] or v);
end

---[[ check comment <!--
ngx.say (table.concat( pros, "&" ));
ngx.say (string.gsub("h;el   lo wor ld", "%s*", ""));
ngx.say (string.gsub("h;el   lo wor ld", "wor*", ""));
ngx.say (string.gsub("h;el   lo wor ld", ";*", ""));
ngx.say (string.gsub("hello world", "%w+", "%0-%0"));
--]] check comment -->

for k,v in string.gmatch ('http://uface.56img.com/photo/46/42/iorichina_b_56.com_.jpg', "%d+/%d+/(.-)_?([bm]?)_56%.com_%.jpg") do
    ngx.say (k, '-', v);
end
local str, str1 = string.match ('http://uface.56img.com/photo/46/42/iorichina_b_56.com_.jpg', "%d+/%d+/(.-)_?([bm]?)_56%.com_%.jpg");
ngx.say (str, '-', str1)
-- ngx.say (ngx.crc32_short (str)%10);

ngx.say ('tonumber(false)')
ngx.say (tonumber(false))
ngx.say ('tonumber(ngx.now())')
ngx.say (tonumber(ngx.now()))
ngx.say ('tonumber("var")')
ngx.say (tonumber("var"))
ngx.say ('string.format ("%f", "var")')
-- ngx.say (string.format ("%f", "var"))