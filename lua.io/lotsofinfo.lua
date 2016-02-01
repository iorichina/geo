ngx.header['Content-Type']='text/plain; charset=utf-8';

local print = ngx.print;
local say = ngx.say;

say('ngx.var.cookie_ab', '=', ngx.var.cookie_ab);

say("ngx.header:");
if (ngx.header) then
    for i,v in pairs(ngx.header) do
        print(i,':=',v,"\n");
    end
end
say('');

say("ngx.arg:");
if (ngx.arg) then
    for i,v in pairs(ngx.arg) do
        print(i,':=',v,"\n");
    end
end
say('');

local args = ngx.req.get_uri_args();
say('args='..(args and 1 or 0)..'&type='..type(args));
say('ngx.req.get_uri_args():');
if (args) then
    for i,v in pairs(args) do
        print(i,':=',v,"\n");
    end
end
say('');

local headers = ngx.req.get_headers();
say('headers='..(headers and 1 or 0)..'&type='..type(headers));
say('ngx.req.get_headers():');
for i,v in pairs(headers) do
        print(i,':=',v,"\n");
end
say('');

say('ngx.req.get_headers()["X-Real-IP"]', ngx.req.get_headers()["X-Real-IP"]);
say('ngx.req.get_headers()["x_forworded_for"]', ngx.req.get_headers()["x_forworded_for"]);
say('ngx.req.get_headers()["X-Forwarded-For"]', ngx.req.get_headers()["X-Forwarded-For"]);
say('ngx.req.get_headers()["x-forwarded-for"]', ngx.req.get_headers()["x-forwarded-for"]);

function pairsByKeys(tb, f)
    local a = {}
    for n in pairs(tb) do table.insert(a, n) end
    table.sort(a, f)
    local i = 0 -- iterator variable
    local iter = function() -- iterator function
        i = i + 1
        if a[i] == nil then return nil
        else return a[i], tb[a[i]]
        end
    end
    return iter
end
say('ngx.var='..(ngx.var and 1 or 0)..'&type='..type(ngx.var));
say('ngx.var:');
if ngx.var then
    say(ngx.var.remote_addr);
    for i,v in pairsByKeys(ngx.var) do
        say(i, type(v), v);
        if type (v) ~= 'table' then
            print(i,':=',v,"\n");
        end
    end
    for i,v in ipairs(ngx.var) do
        say(i, type(v), v);
        if type (v) ~= 'table' then
            print(i,':=',v,"\n");
        end
    end
end
say('');

-- ngx.sleep(1);
local request_time = ngx.now() - ngx.req.start_time()
say('End@', request_time);

say("ngx.now=", ngx.now());

say("ngx.time=", ngx.time());

say("ngx.today=", ngx.today());

say("ngx.localtime=", ngx.localtime());

say("ngx.utctime=", ngx.utctime());

say("ngx.http_time=", ngx.http_time(ngx.time()));

say("ngx.quote_sql_str=", ngx.quote_sql_str('"ff\'xx'));

say('');

local m, err = ngx.re.match("hello, 1234", "([0-9])(?<remaining>[0-9]+)");
say('ngx.re.match("hello, 1234", "([0-9])(?<remaining>[0-9]+)")::');
if (type(m) == 'table') then
    for i,v in pairs(m) do
        print(i,':=',v,"\n");
    end
end
say('');

m, err = ngx.re.match("hello, 美好生活", "HELLO, (.{2})", "i");
say('ngx.re.match("hello, 美好生活", "HELLO, (.{2})", "i")::');
if (type(m) == 'table') then
    for i,v in pairs(m) do
        print(i,':=',v,"\n");
    end
end
say('');

m, err = ngx.re.match("hello, 美好生活", "HELLO, (.{2})", "iu");
say('ngx.re.match("hello, 美好生活", "HELLO, (.{2})", "iu")::');
if (type(m) == 'table') then
    for i,v in pairs(m) do
        print(i,':=',v,"\n");
    end
end