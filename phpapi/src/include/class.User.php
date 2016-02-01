<?php
/**
 * @name:	class.User.php
 * @desc: 	ȡûϽӿ
 * @author:	zhys9(jingki) @ 2008-8-29
 * @require:	class.UserLocation.php
 * class.Http.php
 *
 * <code>
 * // encoding utf-8 :
 * $rs = User::GetProfile('jingki');
 * print_r($rs);
 * //output:
 * Array
 * (
 * [Account] => jingki		//user_id
 * [OtherEmail] => 		//
 * [FirstName] => ҵĹԵ		//ʵ
 * [nickname] => ҵ		//ǳ
 * [BirthDay] => 30
 * [BirthMonth] => 12
 * [BirthYear] => 1983
 * [Gender] => M		//M У	F Ů N
 * [Occupation] => 	//ְҵ
 * [PostCode] => 0		//	ͨself::UserLocation() ת
 * [DateCreate] => 2006-11-24 15:18:02
 * [TelMobile] =>
 * [LastLogin] => 1219668671
 * [qq] => 0
 * [msn] =>
 * [homepage] =>
 * [mobile] =>
 * [salary] => 0
 * [emotion] => 0
 * [blood] => u
 * [figure] => 0
 * [education] => 0
 * [adv_type] => 0
 * [adv_score] => 0
 * [sign] =>
 * [reg_ip] =>
 * [reg_from] =>
 * [head] => 0		//ͷţÿ޸ͷ󶼻updateֵڱcache
 * [last_login] => 1219668671
 * [last_ip] => 121.32.91.94
 * )
 *
 * // encoding gbk :
 * User::Charset('gbk');
 * $rs = User::GetProfile('jingki');
 * print_r($rs);
 * </code>
 *
 */

class User {
    
    const UFACE_ROOT_URI = 'http://uface.56img.com/';
    
    const API_HOST = 'info.ucenter.56.com';
    const API_PORT = '80';
    //charset utf-8
    const API_ROOT_UTF8 = 'user_info'; // user_info?xxx
    //cache
    const API_ROOT_CLEAN = 'clear_user_info'; // clear_user_info?xxx
    

    //charset utf-8 add by vincent µĽӿ
    const API_ROOT = 'userinfo2010.php';
    
    private static $charset = 'utf8';
    
    /**
     * @name Charset
     * @author zhys9
     * @param string $charset utf8/utf-8/gbk/gb2312
     *
     */
    public function Charset($charset = 'utf8') {
        $charset = strtolower ( $charset );
        if (in_array ( $charset, array ('utf8', 'utf-8', 'gb2312', 'gbk' ) )) {
            self::$charset = $charset;
        }
    
    }
    
    /**
     * @name GetProfile
     * @author zhys9
     * @desc ȡû
     * @param string $user_id
     * @param bool $big_photo
     * @return array
	   按一个一个用户取
     *
     */
    public static function GetProfile($user_id, $big_photo = false) {
        
        $root_uri = self::API_ROOT_UTF8 . '?';
        $uri = $root_uri . $user_id;
        
        //send request
        $rs = Http::Get ( self::API_HOST, $uri, self::API_PORT );
        if ($rs) {
            $info = array ();
            @parse_str ( $rs, $info );
            $nickname = $info ['LastName'];
            unset ( $info ['LastName'] );
            $info ['nickname'] = $nickname ? $nickname : $info ['Account'];
            $info ['photo'] = self::GetPhotoUrl ( $user_id, $big_photo, $info ['head'] );
            if (self::$charset == 'gb2312' || self::$charset == 'gbk') {
                foreach ( $info as &$vv ) {
                    $vv = G::mb ( $vv, 'GB2312', 'UTF-8' );
                }
            }
            return $info;
        } else {
            return array ();
        }
    
    }
    
    /**
     * @name GetUsersProfile
     * @author zhys9
     * @param array $user_id_array û
     * @param bool $big_photo
     * @return array
     * 按多个用户取，传入数组 得到数组，每个用户请求一次用户接口
     */
    public static function GetUsersProfile(array $user_id_array, $big_photo = true) {
        foreach ( $user_id_array as &$v ) {
            $v = self::GetProfile ( $v, $big_photo );
        }
        return $user_id_array;
    }
    
    /**
     * @name GetPhotoUrl
     * @author zhys9
     * @desc ûͷURL
     * @param string $user_id
     * @param bool $big Ƿ񷵻شͼ
     * @param int $head ûÿ޸ͷһֵڣڱͷcache
     * 根据用户名获得昵称
     */
    public static function GetPhotoUrl($user_id, $big = false, $head = '') {
        $ascLevel_01 = $ascLevel_02 = 0;
        for($i = 0; $i < strlen ( $user_id ); $i ++) {
            $ascLevel_01 += (ord ( $user_id {$i} )) * $i; //a charCodeAt(a)
            $ascLevel_02 += (ord ( $user_id {$i} )) * ($i * 2 + 1);
        }
        $ascLevel_01 %= 100; //һ·
        $ascLevel_02 %= 100; //ڶ·
        

        $uri = self::UFACE_ROOT_URI . 'photo/' . $ascLevel_01 . '/' . $ascLevel_02 . '/' . $user_id;
        return $uri . ($big ? '_b' : '') . '_56.com_.jpg' . ($head ? '?' . $head : '');
    }
    
    /**
     * @name UserLocation
     * @author zhys9
     * @desc ûڵתעcharset UTF-8
     * @require class.UserLocation.php
     * @param int/string $param /		½ṹ: 㶫ʡ||
     * @return string/int δintʱضӦĵ֮ص
     * <code>
     * $t = User::UserLocation("㶫ʡ||");
     * $s = User::UserLocation($t);
     * echo $t;
     * print_r($s);
     * </cdoe>
     *
     */
    public static function UserLocation($param) {
        if (is_numeric ( $param )) {
            return UserLocation::CodeToName ( $param );
        } else {
            $param = explode ( '|', $param );
            return UserLocation::NameToCode ( $param [0], $param [1], $param [2] );
        }
    }
    
    /**
     * @name GetProfile
     * @author zhys9
     * @desc ȡû
     * @param string $user_id
     * @param bool $big_photo
     * @param int $short 0|1  1ֻҪϢ0ȫϢ
     * @return array
     *
     * @ĳֶ֧û
     * @modify Melon`` 2010
	   一个或者多个用户名取得用户信息，请求一次用户接口
     */
    public static function GetProfile2011($user_id, $big_photo = true, $short = 0) {
        $root_uri = self::API_ROOT . '?short=' . $short . '&charset=' . self::$charset . '&user_id=';
        $uri = $root_uri . $user_id;
        
        //if(DEBUG) _debug("CallUserApi: $uri");
        //send request
        $rs = Http::Get ( self::API_HOST, $uri, self::API_PORT );
        if ($rs) {
            $info = array ();
            if (strpos ( $user_id, ',' ) == TRUE) {
                $rs = unserialize ( $rs );
                if (is_array ( $rs )) {
                    foreach ( $rs as $k => & $v ) {
                        $v ['nickname'] = $v ['LastName'] ? $v ['LastName'] : $v ['Account'];
                        unset ( $rs [$k] ['LastName'] );
                        $v ['photo'] = self::GetPhotoUrl ( $v ['Account'], $big_photo, $v ['head'] );
                    }
                }
                $info = $rs;
            } else {
                @parse_str ( $rs, $info );
                $nickname = $info ['LastName'];
                unset ( $info ['LastName'] );
                $info ['nickname'] = $nickname ? $nickname : $info ['Account'];
                $info ['photo'] = self::GetPhotoUrl ( $user_id, $big_photo, $info ['head'] );
            }
            return $info;
        } else {
            return array ();
        }
    
    }
    
    /**
     * @name GetUsersProfile
     * @author zhys9
     * @param array $user_id_array û
     * @param bool $big_photo
     * @return array
     *
     * @modify Melon`` 2010
	 一个或者多个用户名取得用户信息，请求一次用户接口,传入数组
     */
    public static function GetUsersProfile2011(array $user_id_array, $big_photo = true, $short = 0) {
        $users = implode ( ',', array_unique ( $user_id_array ) );
        return self::GetProfile2011 ( $users, $big_photo, $short );
    }

    /**
     * @desc 用户头像代理域名加速
     * @param string $photo 用户头像地址
     */
    public static function photoProxy($photo) {
        return stripos ( $photo, 'uface.56.com/photo' ) ? str_ireplace ( 'uface.56.com/photo', 'uface.56img.com/photo', $photo ) : $photo;
    }

    /**
     * @name UserUrl
     * @author zhys9
     * @param string $user_id ID
     * @return string 空间地址
     *
     */
    public static function UserUrl ($user_id)  {
    	return sprintf("http://i.56.com/u/%s",$user_id);
    }
    
}