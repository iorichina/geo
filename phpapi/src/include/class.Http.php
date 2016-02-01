<?php
/**
 * @File name:	class.Http.php
 * @Useage: 新架构后，原Http::Get会返回503，504，403错误的html内容。
 * 修正后，增加判断返回状态，获得更多的连接返回信息，增加数据开关和获得全部头部信息开关。cooike单独调用获取。 
 * @Author:	kend 2011-03-09
 * @example:
		if ('html' == $_GET['data']){
			Http::$isdata=false;
		}
		$a = Http::Get('www.56.com', '/abc.php',80);//403,页面不存在
 */
class Http {
    public static $_cookies = '';
    public static $_error = '';
    public static $isdata = true; //默认ture为数据访问，出现403，504页面时候，不返回html，返回null;当为false,直接返回内容
    

    public static $http_status = ''; //返回http状态:'HTTP/1.1 403 Forbidden'
    public static $http_code = ''; //返回http状态代号:200,403
    

    public static $_allheader = false; //是否获取全部header，特别应用时开启 
    public static $_response_headers = array ();
    public static $_response_cookies = array ();
    
    /**
     * POST
     * 不需要socket mod
     *
     */
    public static function Post($host, $page, $data, $port = 80, $timeout = 3) {
        $fp = fsockopen ( $host, $port, $errno, $errstr, $timeout );
        if (! $fp) {
            self::$_error = $errstr . ' (' . $errno . ')';
            return false;
        } else {
            if (is_array ( $data )) {
                $content = array ();
                foreach ( $data as $k => $v ) {
                    $content [] = $k . "=" . rawurlencode ( $v );
                }
                $content = implode ( "&", $content );
            } else {
                $content = $data;
            }
            
            //$stream
            $stream = "POST /$page HTTP/1.0\r\n";
            $stream .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $stream .= "Host: $host\r\n";
            $stream .= "Content-Length: " . strlen ( $content ) . "\r\n";
            if (self::$_cookies) {
                $stream .= "Connection: Close\r\n";
                $stream .= self::$_cookies;
            } else {
                $stream .= "Connection: Close\r\n\r\n";
            }
            
            fwrite ( $fp, $stream );
            usleep ( 10 );
            fwrite ( $fp, $content );
            stream_set_timeout ( $fp, $timeout );
            
            return self::getContent ( $fp );
        }
    }
    
    public static function Get($host, $pos, $port = 80, $timeout = 3) {
        $fp = fsockopen ( $host, $port, $errno, $errstr, $timeout );
        if (! $fp) {
            self::$_error = $errstr . ' (' . $errno . ')';
            return false;
        } else {
            $stream = "GET /$pos HTTP/1.0\r\n";
            $stream .= "Host: $host\r\n";
            if (self::$_cookies) {
                $stream .= "Connection: Close\r\n";
                $stream .= self::$_cookies;
            } else {
                $stream .= "Connection: Close\r\n\r\n";
            }
            
            fwrite ( $fp, $stream );
            usleep ( 10 );
            stream_set_timeout ( $fp, $timeout );
            return self::getContent ( $fp );
        }
    }
    
    /**
     * fsockopen返回内容
     * @param socket $fp
     */
    static public function getContent($fp) {
        
        $res = stream_get_contents ( $fp );
        if (empty ( $res ))
            return false;
        
     //加入http状态判断
        self::checkHttpStatus ( $res );
        
        $info = stream_get_meta_data ( $fp );
        fclose ( $fp );
        
        //time out
        if ($info ['timed_out']) {
            self::$http_code = 504;
            return false;
        } else {
            return self::getData ( $res );
        }
    
    }
    
    /**
     * 获得最后数据
     * @param string $res
     */
    static public function getData($res) {
        $data = substr ( $res, (strpos ( $res, "\r\n\r\n" ) + 4) );
        //判断状态
        if (self::$http_code != 200 && self::$isdata) {
            return false;
        }
        if (strpos ( strtolower ( $res ), "transfer-encoding: chunked" ) !== FALSE) {
            $data = self::unchunkHttp ( $data );
        }
        return $data;
    }
    /**
     * 检查http状态
     * 默认有数据为200
     **/
    static public function checkHttpStatus($res) {
        $rs = preg_match ( '/http\/[0-9\.]* ([0-9]*) [0-9A-Za-z]*/i', $res, $matches );
        if ($rs) {
            self::$http_status = $matches ['0'];
            self::$http_code = $matches ['1'];
        } else {
            //有内容，默认为200
            self::$http_code = 200;
        }
        
        if (! self::$_allheader)
            return true;
        
     //其他状态
        $headerArray = array ();
        $headerStr = substr ( $res, 0, strpos ( $res, "\r\n\r\n" ) );
        $headers = explode ( "\r\n", $headerStr );
        
        foreach ( $headers as $k => &$v ) {
            $find = preg_match ( "/^([\w\-]+):(.*)$/i", $v, $header );
            if (! $find) {
                $headerArray [$k] = $v;
            } else {
                $headerName = $header [1];
                $headerValue = trim ( $header [2] );
                if (! isset ( $headerArray [$headerName] )) {
                    $headerArray [$headerName] = $headerValue;
                } elseif (! is_array ( $headerArray [$headerName] )) {
                    $oldValue = $headerArray [$headerName];
                    $headerArray [$headerName] = array ();
                    $headerArray [$headerName] [] = $oldValue;
                    $headerArray [$headerName] [] = $headerValue;
                } elseif (is_array ( $headerArray [$headerName] )) {
                    $headerArray [$headerName] [] = $headerValue;
                }
            }
        }
        self::$_response_headers = $headerArray;
        
        return true;
    }
    
    /**
     * 针对chunked数据返回
     * @param string $data
     */
    static function unchunkHttp($data) {
        $bp = 0;
        $outData = "";
        while ( $bp < strlen ( $data ) ) {
            $rawnum = substr ( $data, $bp, strpos ( substr ( $data, $bp ), "\r\n" ) + 2 );
            $num = hexdec ( trim ( $rawnum ) );
            $bp += strlen ( $rawnum );
            $chunk = substr ( $data, $bp, $num );
            $outData .= $chunk;
            $bp += strlen ( $chunk );
        }
        return $outData;
    }
    
    /**
     * @name SetCookie
     * @author zhys9
     * @desc set cookies
     * @param array $cookie_array
     *
     */
    public static function SetCookie(array $cookie_array) {
        if ($cookie_array) {
            self::$_cookies = 'Cookie:';
            $end = '';
            foreach ( $cookie_array as $k => $v ) {
                self::$_cookies .= $end . " " . $k . '=' . $v;
                $end = ';';
            }
            self::$_cookies .= "\r\n\r\n";
        }
    }
    
    /**
     * @name GetResponseCookies
     * @author zhys9
     * @desc get response cookie
     * @param array $cookie_array
     *
     */
    public static function GetResponseCookies() {
        $cookies = array ();
        if (is_array ( self::$_response_headers ['Set-Cookie'] ))
            foreach ( self::$_response_headers ['Set-Cookie'] as $v ) {
                list ( $ckn, $ckv ) = explode ( '=', array_shift ( explode ( '; ', $v ) ) );
                if ($ckv == 'deleted')
                    continue;
                $cookies [$ckn] = $ckv;
            }
        self::$_response_cookies = $cookies;
        return $cookies;
    }
    
    /**
     * @desc: 重置	
     * @author:	Melon`` @ 2010
     *
     */
    public static function ResetAll() {
        
        self::$_cookies = '';
        self::$_error = '';
        
        self::$isdata = true;
        self::$http_status = '';
        self::$http_code = '';
        
        self::$_allheader = false;
        self::$_response_headers = array ();
        self::$_response_cookies = array ();
    
    }
}
