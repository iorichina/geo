local ngx       = ngx
local param     = 'test_model'

local function private_check_before_module ()
    ngx.say ('private check before module was called')
    return 'checked'
end 
local private_local_before_check = function ()
    ngx.say ('private local before module check was called')
    return 'checked'
end 


module (...)

function public_check_with_private_check_before_module ()
    ngx.say ('public check was called with private check before module')
    return private_check_before_module and private_check_before_module () or 'not check'
end
function public_check ()
    ngx.say ('public check was called')
    return 'checked'
end
function public_check_with_inner_check ()
    ngx.say ('public check was called with public inner check')
    if private_check then
        private_check ()
    end
    if _M.private_check then
        _M.private_check ()
    end
    if _M.public_inner_check then
        public_inner_check ()
    end
    return 'checked'
end
function public_set_param ()
    param = 'public change param'
    ngx.say ('public param set was called:', param)
    -- private_set_param ()
    return 'checked'
end
function public_check_param ()
    ngx.say ('public param check was called:', param)
    -- private_check_param ()
    return 'checked'
end
function public_inner_check ()
    ngx.say ('public inner check was called:', param)
    -- private_check_param ()
    return 'checked'
end

local function private_check ()
    ngx.say ('private check was called')
    return 'checked'
end 
local function private_set_param ()
    param = 'private change param'
    ngx.say ('public param set was called:', param)
    return 'checked'
end
local function private_check_param ()
    ngx.say ('public param check was called:', param)
    return 'checked'
end

function public_check_private_local_func ()
    if private_local_check then
        private_local_check ()
    end
    if private_local_before_check then 
        private_local_before_check ()
    end
end

local private_local_check = function ()
    ngx.say ('private local check was called')
    return 'checked'
end 
