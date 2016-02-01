local ngx = ngx;
local type = type;
local pairs = pairs;
local tostring = tostring;
local model = require ('model.AppLuaIo');
-- local table = table;
-- local setmetatable = setmetatable;

--[[ init module --]]
module(...);
--[[ indexed by current module env. --]]
-- local mt = {__index = _M};

--[[ instantiation ]]
-- function new(self)
--     local set = {};
--     setmetatable(set, mt);
--     return set;
-- end

--[[ for debuging --]]
local function debug_print(tbData)
    if (type(tbData) == 'string') then
        ngx.print(tbData, "\n");
    elseif (type(tbData) == 'table') then
        for k,v in pairs(tbData) do
            ngx.print(k .. ' : ' .. tostring(v), "\n");
        end
    end
end

local mydb = {
    ['database'] = 'app_lua_io',
    ['host'] = '127.0.0.1',
    ['port'] = 3306,
    ['charset'] = "utf8",
    ['user'] = 'root',
    ['password'] = ''
};

local tb_test = model:new({
    ['config'] = mydb
    });
function test ()
    -- debug_print (mydb);
    -- debug_print (tb_test.config);
    -- debug_print (type (tb_test.config));
    tb_test.name = 'iorichina';
    debug_print (tb_test);
    debug_print (type (tb_test));
    local info = tb_test:getUserInfo();
    debug_print (info);
    debug_print (type (info));
end


--[[ to prevent use of casual module global variables --]]
-- setmetatable(_M, {
--     __newindex = function (table, key, val)
--         log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME);
--     end
-- });
