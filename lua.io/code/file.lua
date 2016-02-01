local io = io;
local setmetatable          = setmetatable;

--[[ init module --]]
module(...);
--[[ indexed by current module env. --]]
local mt = {__index = _M};

--[[ instantiation ]]
function new(self)
    local set = {};
    setmetatable(set, mt);
    return set;
end

-- 获得文件长度
function filesize(self, filename)
    local file, err = io.open(filename, "rb");
    if not file then 
        return 0, err;
    end
    local len = file:seek("end");
    file:close();
    return len;
end

-- 判断文件是否存在
function file_exists(self, path)
    local file, err = io.open (path, "rb");
    if not file then 
        return false, err;
    end
    file:close ();
    return true;
end

-- 加载文件操作，文件存在则加载，不存在则返回nil和错误信息
function include (self, path)
    local exists, err = self:file_exists (path);
    if exists then
        return require (path);
    end
    return nil, err;
end

return _M;