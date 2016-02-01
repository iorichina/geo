# geo
geoip 接口获取geoip信息
时间 2014-3-19

接口地址    http://yourhost/geoinfo
接口描述    HTTP GET
参数          类型  说明     允许空
ip          string  客户端ip    是
callback    string  回调函数    是
type    string  =json，以json字符串“{ip:*客户端ip*,geo:*行政区*}”形式输出；
否则以“*行政区*-*ip*”形式输出   是
            
            

返回  Json  
        
        
备注  接口会同时设置半小时期限的cookie，例子：
geoinfo=%E5%B9%BF%E4%B8%9C%E7%9C%81%E6%BD%AE%E5%B7%9E%E5%B8%82-113.107.234.109; expires=30分钟; path=/; domain=.56.com

rewrite:
rewrite /geoinfo /index.php?action=ApiRegion&do=geoinfo last;