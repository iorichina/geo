local ngx = ngx;
ngx.header['Content-Type']='text/plain; charset=utf-8';

local var = ngx.var;
---[[
-- set root dir for finding lib & code file in a directory
local ROOT_DIR = ngx.ctx.ROOT_DIR or var.document_root;
-- empty string is treated as true, only nil and false will be treated as false
ROOT_DIR = ROOT_DIR .. (string.match (ROOT_DIR, '/', -1) and '' or '/');
package.path = ROOT_DIR .. "?.lua;" .. package.path;
-- ctx will set a shared var in a request
-- ngx.ctx.ROOT_DIR = ROOT_DIR;
-- ]]
-- require ('common');

-- local say = ngx.say;
local exit = ngx.exit;
local action = var.arg_a;
if not action then
    action = var.arg_action;
end
if not action then
    ngx.header['Api-Info'] = "action doesn't exists";
    exit ( ngx.HTTP_BAD_REQUEST );
end

local app, err = require ("app." .. action);
if not app then
    ngx.header['Api-Info'] = err;
    exit (ngx.HTTP_NOT_FOUND);
end

app.test ();