<?php
/**
 * @name 最新正整理，视频相关公共函数
 * @author Melon`` @ 1010
 */
class G {
    /**
     * 获得普通视频信息
	  $ids : 视频id ，数字或数组都可以，
      $fli,包含的字段
	  $fle:不包含的字段
	  $dy = c 清缓存，一般不要清缓存

     */
    public static function GetIdsVideo($ids, $fli = '', $fle = '', $dy = '') {
        if (empty ( $ids )) {
            return array ();
        }
        $ids = is_array ( $ids ) ? implode ( ',', $ids ) : $ids;
        
        $data = Http::Get ( 'info.v.56.com', "?ids={$ids}&fli={$fli}&fle={$fle}&dy={$dy}" );
        
        if ($data) {
            $data = json_decode ( $data, TRUE );
            return is_array ( $data ) ? $data : array ();
        } else {
            return array ();
        }
    }
    
    /**
     * @desc 通过相册视频id获取相册视频视频信息
     * @param $ids 相册视频视频id。如：NTAxODM0NTU,MTAxOeM0NTU,54055346,40265656
     * @param $fli 需要返回的字段。如：id,user_id,totaltime。默认返回全部
     * @param $fle 不需返回的字段。
     * @param $dy 清缓存
     * @author Seamaid`` @ 2011
     */
    public static function GetIdsPhotoVideo($ids, $fli = '', $fle = '', $dy = '') {
        $ids = is_array ( $ids ) ? implode ( ',', $ids ) : $ids;
        $data = Http::Get ( 'p.56.com', "/API/vInfo.php?ids={$ids}&fli={$fli}&fle={$fle}&dy={$dy}" );
        if ($data) {
            $data = json_decode ( $data, TRUE );
            return is_array ( $data ) ? $data : array ();
        } else {
            return array ();
        }
    }
    
    /**
     * @desc 通过录制id获取相册视频视频信息
     * @param $ids 录制视频视频id。如：NTAxODM0NTU,MTAxOeM0NTU,54055346,40265656
     * @param $fli 需要返回的字段。如：id,user_id,totaltime。默认返回全部
     * @param $fle 不需返回的字段。
     * @param $dy 清缓存
     * @author Seamaid`` @ 2011
     */
    public static function GetIdsLuzhiVideo($ids, $fli = '', $fle = '', $dy = '') {
        if (empty ( $ids )) {
            return array ();
        }
        $ids = is_array ( $ids ) ? implode ( ',', $ids ) : $ids;
        $data = Http::Get ( 'info.v.56.com', "/?do=Luzhi&ids={$ids}&fli={$fli}&fle={$fle}&dy={$dy}" );
        if ($data) {
            $data = json_decode ( $data, TRUE );
            return is_array ( $data ) ? $data : array ();
        } else {
            return array ();
        }
    }
    
    /**
     * @desc:获取视频图片地址.
     * @param  $rsArray 单个视频字段数组
     * @param 	$proxy 图片是否使用代理，false取源站
     * @param string URL
     * @author:	Melon`` @ 2010
     *
     */
    static public function FlvImg($rsArray, $proxy = true) {
        if ($rsArray ['exercise'] == 'p') {
            $imageURL = $rsArray ['URL_host'];
        } else if ($rsArray ['exercise'] == 'y') {
            $imageURL = "img/mp3.gif";
        } else {
            if (substr ( $rsArray ['URL_host'], 0, 7 ) == 'http://') {
                $imageURL = $rsArray ['URL_host'];
            } else {
                if ($proxy) {
                    preg_match ( "/v(\d+)\.56\.com/i", $rsArray ['img_host'], $pattern );
                    if (( int ) $pattern [1] > 16) {
                        $rsArray ['img_host'] = str_replace ( '.56.com', '.56img.com', $rsArray ['img_host'] );
                    }
                }
                $imageURL = 'http://' . $rsArray ['img_host'] . '/images/' . $rsArray ['URL_pURL'] . "/" . $rsArray ['URL_sURL'] . "/" . $rsArray ['user_id'] . Core::$configs ['config'] ['com_jpg_id'] . $rsArray ['URL_URLid'] . ".jpg";
            
            }
        }
        return $imageURL;
    }
    
    /**
     * @desc: 获取flashvars的参数，用于把视频参数拼凑传给flash播放视频.
     * @param  $rsArray 单个视频字段数组
     * @param string URL
     * @author:	Melon`` @ 2010
     *
     */
    static public function GetVars($rsArray) {
        return "img_host=" . $rsArray ['img_host'] . "&host=" . $rsArray ['URL_host'] . "&pURL=" . $rsArray ['URL_pURL'] . "&sURL=" . $rsArray ['URL_sURL'] . "&user=" . $rsArray ['user_id'] . "&URLid=" . $rsArray ['URL_URLid'] . "&totaltimes=" . $rsArray ['totaltime'] . ((strlen ( $rsArray ['effectID'] ) > 3) ? ($rsArray ['effectID']) : "&effectID=" . $rsArray ['effectID']) . "&flvid=" . $rsArray ['id'];
    }
    
    /**
     * @desc: 	算用户目录
     * @author:	Melon`` @ 2010
     *
     */
    static public function UserDir($user_id, $c = 30) {
        $a1 = 0;
        $a2 = 0;
        for($i = 0; $i < strlen ( $user_id ); $i ++) {
            $a1 += (ord ( $user_id {$i} )) * $i; //a charCodeAt(a)
            $a2 += (ord ( $user_id {$i} )) * ($i * 2 + 1);
        }
        $a1 %= $c; //第一级路经
        $a2 %= $c; //第二级路经
        return array ('URL_pURL' => $a1, 'URL_sURL' => $a2, 'p' => $a1, 's' => $a2 );
    }
    
    /**
     * @desc: 	得到flvURL，视频播放页地址
     * @param : $id FLVID
     * @param : $product	站点还是space
     * @param string URL
     * @author:	Melon`` @ 2010
     *
     */
    static public function FlvUrl($id, $pct = 1, $site = true) {
        $host = self::Phost ( $id, $pct );
        return $site ? $host . "/v_" . self::FlvEnId ( $id ) . ".html" : $host . "/spaceDisplay.php?id=" . self::FlvEnId ( $id );
    }
    
    /**
     * @desc: 对视频ID base64encode
     * @param $id FLVID
     * @param  string BASE64
     * @author:	Melon`` @ 2010
     *
     */
    static public function FlvEnId($id) {
        if (is_numeric ( $id )) {
            return str_replace ( '=', '', base64_encode ( $id ) );
        } else {
            return $id;
        }
    }
    
    /**
     * @desc: 对视频ID base64decode
     * @param string BASE64
     * @param  $id    FLVID
     * @author:	Melon`` @ 2010
     *
     */
    static public function FlvDeId($id) {
        if (is_numeric ( $id )) {
            return $id;
        } else {
            return ( int ) base64_decode ( $id );
        }
    }
    
    /**
     * @desc: 从url得到ID
     * @param 	$url string
     * @return $id    FLVID
     * @author:	Melon`` @ 2010
     *
     */
    static public function GetUrlId($url) {
        if (! strstr ( $url, 'http' )) {
            $id = self::flvDeId ( $url );
        } else {
            
            if (strstr ( $url, 'v=' )) {
                $id = explode ( 'v=', trim ( $url ) );
                $id = explode ( '.html', $id [1] );
                $id = self::flvDeId ( $id [0] );
            } elseif (strstr ( $url, 'v_' )) {
                $id = explode ( 'v_', trim ( $url ) );
                $id = explode ( '.html', $id [1] );
                $id = self::flvDeId ( $id [0] );
            } elseif (strstr ( $url, 'v-' )) {
                $id = explode ( 'v-', trim ( $url ) );
                $id = explode ( '.html', $id [1] );
                $id = self::flvDeId ( $id [0] );
            } elseif (strstr ( $url, '_vid-' )) {
                $id = explode ( '_vid-', trim ( $url ) );
                $id = explode ( '.html', $id [1] );
                $id = self::flvDeId ( $id [0] );
            } elseif (strstr ( $url, '.html' )) {
                $id = explode ( '/id', trim ( $url ) );
                $id = explode ( '.html', $id [1] );
                $id = $id [0];
            } else {
                $id = explode ( 'id=', trim ( $url ) );
                $id = explode ( '&', $id [1] );
                $id = $id [0];
            }
        
        }
        return $id;
    }

	/**
	 * 以地址区别一般视频 相册视频和专辑
	 */
	static public function dis_video($url) {
		if (empty ( $url )) {
			return false;
		}
		$sp = '/play_album|p[0-9]+|u[0-9]+|l[0-9]+|viki\.56\.com/i';
		preg_match ( $sp, $url, $out );
		$sp = '/album/';
		if (preg_match ( $sp, $out ['0'], $out2 )) {
			return $out2 ['0'];
		} else {
			$sp = '/p|u|l|viki/';
			preg_match ( $sp, $out ['0'], $out2 );
			return $out2 ['0'];
		}
	}
	/**
	 * 根据专辑的url返回albumid
	 */
	public static function getalbum_id($url) {
		$sp = '/aid-([0-9]+)/i';
		preg_match ( $sp, $url, $out );
		return (empty ( $out ['1'] ) ? false : $out ['1']);
	}

	/**
	 * 可获取多个专辑信息的接口
	 *
	 * @version 20130827
	 * @param unknown $aids
	 * @param string $fli
	 *        	vids,viewsTotal,commentTotal,user
	 * @param number $v_ext
	 * @return Ambigous <multitype:, mixed>
	 */
	public static function getIdsAlbums($aids, $fli = 'id,title,akey,author,cover,views,subscriptions,create_time,videoList', $v_ext = 0) {
		$aids = is_array ( $aids ) ? implode ( ',', $aids ) : $aids;
		$data = Http::Get ( 'info.album.56.com', 'api/getAlbumInfoById.php?aids=' . $aids . '&v_ext=' . $v_ext . '&fli=' . $fli );
		return $data ? json_decode ( $data, true ) : array ();
	}
	
    /**
     * @desc: 	得到主机   $pct 产品id 没有产品ID时取用户
     * @author:	Melon`` @ 2010
     *
     */
    static public function Phost(&$str, $pct = false) {
        if ($pct === false) {
            $len = strlen ( $str );
            $rs = 0;
            for($i = 0; $i < $len; $i ++)
                $rs += ord ( $str [$i] );
            $host = "http://www.56.com/w" . ($rs % 88 + 11);
        } else {
            $id = self::FlvDeId ( $str );
            $pct = self::Pct ( $pct );
            $host = "http://www.56.com/$pct" . ($id % 88 + 11);
        }
        return $host;
    }

    /**
     * 以地址区别一般视频 相册视频和专辑
     */
    static public function disVideo($url) {
    	if (empty ( $url )) {
    		return false;
    	}
    	$sp = '/play_album|p[0-9]+|u[0-9]+|l[0-9]+|viki\.56\.com/i';
    	preg_match ( $sp, $url, $out );
    	$sp = '/album/';
    	if (preg_match ( $sp, $out ['0'], $out2 )) {
    		return $out2 ['0'];
    	} else {
    		$sp = '/p|u|l|viki/';
    		preg_match ( $sp, $out ['0'], $out2 );
    		return $out2 ['0'];
    	}
    }
    
    /**
     * @desc: 	产品
     * @param $mode = id　返回ID string  $mode = name　返回name  string $mode = id,name||其它　返回id和name　Array
     * @author:	Melon`` @ 2010
     *
     */
    static public function Pct($pct, $mode = 'id') {
        $pctArray = array (1 => 'u', 2 => 'l', 3 => 'p' );
        $pctName = array ('u' => '上传', 'l' => '录制', 'p' => '动感相册' );
        if (is_numeric ( $pct ))
            $pct = $pctArray [$pct];
        
        if ($mode == 'id') {
            return $pct;
        } else if ($mode == 'name') {
            return $pctName [$pct];
        } else {
            return array ('id' => $pct, 'name' => $pctName [$pct] );
        }
    }
    
    /**
     * @desc: 	图片代理
     * @example
     * e.g: v21.56.com To img.v21.56.com.
     * 兼容  http://v21.56.com To http://v21.56.com.
     * http://v21.56.com/images/0/21/jingkii56olo56i56.com_118733351699.jpg To http://img.v21.56.com/images/0/21/jingkii56olo56i56.com_118733351699.jpg.
     * 兼容 http://l.p302.56.com/photo2video/upImg/d1/15/68rebill57919.jpg    Add By Rebill 2010-06-01.
     *
     * @author:	Melon`` @ 2010
     *
     */
    static public function ImgProxy($vhost) {
        if (stristr ( $vhost, 'img.v' )) {
            $vhost = str_ireplace ( 'img.v', 'v', $vhost );
        }
        if (stristr ( $vhost, 'img.p' )) {
            return $vhost;
        }
        //兼容新存储格式(l.p302.56.com)
        preg_match ( "/\w\.p(\d+)\.56\.com[\w\/\.]*/i", $vhost, $pattern );
        if (( int ) $pattern [1] > 300) {
            if (strstr ( $vhost, 'http://' . $pattern [0] )) {
                $vhost = str_ireplace ( 'http://' . $pattern [0], 'http://img.' . $pattern [0], $vhost );
            } else {
                $vhost = 'img.' . $pattern [0] . substr ( $pattern [0], 1 );
            }
            return $vhost;
        }
        preg_match ( "/(v|p)(\d+)\.56\.com[\w\/\.]*/i", $vhost, $pattern );
        if (( int ) $pattern [2] > 16 && $parrern [1] == 'v') {
            $vhost = str_ireplace ( '.56.com', '.56img.com', $vhost );
        } elseif (( int ) $pattern [2] > 16 || $pattern [1] == 'p') {
            if (strstr ( $vhost, 'http://' . $pattern [1] )) {
                $vhost = str_ireplace ( 'http://' . $pattern [1], 'http://img.' . $pattern [1], $vhost );
            } else {
                $vhost = 'img.' . $pattern [1] . substr ( $pattern [0], 1 );
            }
        }
        return $vhost;
    }
    
    /**
     * @desc 获取专辑播放地址
     * @author Melon`` @ 2010
     */
    static function GetAlbumPlayUrl($aid, $vid, $o = 0) {
        return "http://www.56.com/w" . ($aid % 89 + 11) . "/" . 'play_album-aid-' . $aid . '_vid-' . (is_numeric ( $vid ) ? self::FlvEnId ( $vid ) : $vid) . ($o ? '_o-' . $o : '') . '.html';
    }
    
    /**
     * @desc 专辑信息页地址
     * @author Melon`` @ 2010
     */
    static function GetAlbumInfoUrl($aid) {
        return "http://www.56.com/w" . ($aid % 89 + 11) . "/" . 'album-aid-' . $aid . '.html';
    }
    
    /*
	 * 转码
	 */
    static public function mb(&$string, $to_encoding = "UTF-8", $from = "GB2312") {
        return mb_convert_encoding ( $string, $to_encoding, $from );
    }
    
    /**
     * @desc:获得活动中使用的swf地址
	   @vid:视频id
	   $pct=类型1、上传视频 2、相册视频 3 录制视频
	   $play n 不自动播放 y 自动播放
     */
    static public function flvHdSwf($vid, $pct = 1, $play = "n") {
        $swf = '';
        if (! is_numeric ( $vid )) {
            $vid = self::GetUrlId ( $vid );
        }
        switch ($pct) {
            case 1 : //上传视频
                if ($play == 'n') {
                    $swf = sprintf ( "http://player.56.com/sp_%s.swf", self::FlvEnId ( $vid ) );
                } else {
                    $swf = sprintf ( "http://player.56.com/sp2_%s.swf", self::FlvEnId ( $vid ) );
                }
                break;
            case 2 : //相册视频
                if ($play == 'n') {
                    $swf = sprintf ( "http://player.56.com/deux_%s.swf", $vid );
                } else {
                    $swf = sprintf ( "http://player.56.com/p2_%s.swf", $vid );
                }
                break;
            case 3 : // 录制视频
                if ($play == 'n') {
                    $swf = sprintf ( "http://www.56.com/flashApp/v_player_simple.11.04.28.b.swf?vid=%s&video_type=rec&", self::FlvEnId ( $vid ) );
                } else {
                    $swf = sprintf ( "http://www.56.com/flashApp/v_player_simple.11.04.28.b.swf?vid=%s&video_type=rec&auto_start=on&", self::FlvEnId ( $vid ) );
                }
                break;
            default : //默认就按上传视频吧
                if (! is_numeric ( $vid )) {
                    $vid = self::GetUrlId ( $vid );
                    if ($play == 'n') {
                        $swf = sprintf ( "http://player.56.com/sp_%s.swf", self::FlvEnId ( $vid ) );
                    } else {
                        $swf = sprintf ( "http://player.56.com/sp2_%s.swf", self::FlvEnId ( $vid ) );
                    }
                }
        }
    }
    /**
     * 发站内消息接口
     * @param string $toid
     * @param string $content
     * @param string $from
     */
    public static function sysMsg($toid, $content, $from = false) {
        if (! $toid)
            return null;
        if (! $from)
            $from = 'cs';
        $key = md5 ( 'msg_no_ip_ban' );
        $page = "api/sendsys.php?from={$from}&to={$toid}&content=" . urlencode ( "$content" ) . ($key ? '&key=' . $key : '');
        $return = Http::Get ( 'msg.56.com', $page );
        return $return;
    }
    
    static public function ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $hdr_ip = stripslashes($_SERVER['HTTP_CLIENT_IP']);
        } else
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $hdr_ip = stripslashes($_SERVER['HTTP_X_FORWARDED_FOR']);
            } else
            {
                $hdr_ip = stripslashes($_SERVER['REMOTE_ADDR']);
            }
        }
        return $hdr_ip;
    }
}
