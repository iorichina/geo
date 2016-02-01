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

local code_test = require ('code.test')

ngx.say (code_test.public_check ())
ngx.say (code_test.public_check_with_inner_check ())
ngx.say (code_test.public_check_with_private_check_before_module ())
ngx.say (code_test.public_check_param ())
ngx.say (code_test.public_set_param ())
ngx.say (code_test.public_check_param ())
if code_test.private_check then
    ngx.say ('code_test.private_check')
    ngx.say (code_test.private_check ())
end

if code_test.param then
    ngx.say ('code_test.param')
    ngx.say (code_test.param)
end

ngx.say (code_test.public_check_private_local_func ())

if code_test.private_local_before_check then
    ngx.say ('code_test.private_local_before_check')
    ngx.say (code_test.private_local_before_check ())
end
