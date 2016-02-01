local redis = require ('resty.redis');

local cache = redis.new();
cache:connect('127.0.0.1', '6379');

local rs = cache:get('test');
ngx.say(rs);

rs = cache:set('testt', "abccc");
ngx.say(rs);

rs = cache:get('testt');
ngx.say(rs);