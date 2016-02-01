<?php
/**
 * @File name:	func.Global.php
 * @desc:	全局函数
 * @author:	jingki @ 2008-1-24
 * @modified   Melon @ 2010-04-26
 * @version guizhi@2012.12.28
 */
/**
 * 检查是否正确调用程序
 */
function checkInSys() {
	if (! defined ( 'IN_SYS' )) {
		header ( "HTTP/1.1 404 Not Found" );
		die ();
	}
}
checkInSys ();

/**
 * 自动加载类文件
 *
 * @return void
 */
function __autoload($class) {
	// $class_file = ROOT_DIR.(strpos($class,'M_')===0?'model':'lib').'/class.' . $class . '.php';
	$class_file = ROOT_DIR . 'include/class.' . $class . '.php';
	if (class_exists ( $class_file, false )) {
		return;
	} elseif (! is_readable ( $class_file )) {
		debugLog ( "unable to read class file " . $class, debug_backtrace () );
		throw new Exception ( "unable to read class file " );
	} else {
		include ($class_file);
	}
}

/**
 * load app
 * 如果app name是多个单词的则每个单词以下划线分割，程序会自动加载对应的类
 * e.g.
 * game_list => 加载 app.GameList.php
 *
 * @param string $appName
 *        	application name
 */
function load_app($appName = '') {
	if (empty ( $appName )) {
		$_REQUEST [ACTION] = $_REQUEST [ACTION] ? ucfirst ( $_REQUEST [ACTION] ) : DEFAULT_ACTION;
		$_REQUEST [DO_METHOD] = $_REQUEST [DO_METHOD] ? ucfirst ( $_REQUEST [DO_METHOD] ) : DEFAULT_DO_METHOD;
		if (strpos ( $_REQUEST [ACTION], '_' ) !== false) {
			$_REQUEST [ACTION] = str_replace ( '_', ' ', $_REQUEST [ACTION] );
			$_REQUEST [ACTION] = str_replace ( ' ', '', ucwords ( $_REQUEST [ACTION] ) );
		}
		if (strpos ( $_REQUEST [DO_METHOD], '_' ) !== false) {
			$_REQUEST [DO_METHOD] = str_replace ( '_', ' ', $_REQUEST [DO_METHOD] );
			$_REQUEST [DO_METHOD] = str_replace ( ' ', '', ucwords ( $_REQUEST [DO_METHOD] ) );
		}
		$appName = $_REQUEST [ACTION];
	}
	load ( APP_DIR . 'app.' . ucfirst ( $appName ) . '.php' );
}

/**
 * load conifgure file
 */
function load_cfg($cfgName = 'System') {
	load ( ROOT_DIR . 'config/inc.' . ucfirst ( $cfgName ) . '.php' );
}

/**
 * include a file
 *
 * @name load
 * @param string $file
 *        	file name
 * @return void
 *
 */
function load($file, $repeat = false) {
	if (file_exists ( $file )) {
		if ($repeat === true) {
			include ($file);
		} else {
			include_once ($file);
		}
	} else {
		throw new JException ( "file not exists" . (DEBUG ? $file : '') );
	}
}

/**
 *
 * @name 调用模板
 * @author Melon`` @ 2008
 */
function template($name, $temDir = 'default') {
	return sprintf ( "%s/{$temDir}/tpl.%s.php", substr ( TPL_DIR, - 1, 1 ) === '/' ? substr ( TPL_DIR, 0, strlen ( TPL_DIR ) - 1 ) : TPL_DIR, $name );
}

/**
 * send http headers for cache control
 *
 * @name send_cache_headers
 * @param int $expire
 *        	expire time in seconds
 * @return void
 *
 */
function send_cache_headers($expire = 30, $charset = '') {
	if ($expire == 0) {
		@header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		@header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		@header ( "Cache-Control: no-cache, no-store, must-revalidate" );
		@header ( "Pragma: no-cache" );
	} else {
		@header ( "Expires: " . gmdate ( "D, d M Y H:i:s", time () + $expire ) . " GMT" );
		@header ( "Cache-Control: max-age=" . $expire );
	}
	if ($charset)
		@header ( "Content-type: text/html; charset=UTF-8" );
}

/**
 * 获取已登录用户ID，如果已登录用户则返回登录的member_id，否则返回false
 *
 * @return string/false 已登录用户则返回登录的member_id，否则返回false
 *        
 */
if (! function_exists ( 'get_ursname' )) {
	function get_ursname() {
		$cookie_name = 'pass_hex';
		$cookie_value = &$_COOKIE ['pass_hex'];
		
		// get user_id
		if ($cookie_value) {
			list ( $user_id ) = explode ( "@", $_COOKIE ['member_id'] );
			$user_id = trim ( strtolower ( $user_id ) );
		}
		
		if (strlen ( $cookie_value ) == 32 && date ( "Ymd" ) < 20090128) {
			$member_id = isset ( $_COOKIE ['member_id'] ) ? $_COOKIE ['member_id'] : null;
			$pass_hex = isset ( $_COOKIE ['pass_hex'] ) ? $_COOKIE ['pass_hex'] : null;
			$member_login = isset ( $_COOKIE ['member_login'] ) ? $_COOKIE ['member_login'] : null;
			
			if (md5 ( substr ( base64_encode ( $member_id . "|" . $pass_hex ), 0, 20 ) ) == $member_login) {
				return $user_id;
			} else {
				return false;
			}
		}
		
		if (empty ( $cookie_value ) || strlen ( $cookie_value ) != 40)
			return false;
		
		$checksum = $random_key = $secret_key = $key_version = "";
		$tmp_key = $md5_key = "";
		$handle = fopen ( "/dev/shm/secrectkey.56", "r" );
		$valid_secret_keys = array ();
		if ($handle) {
			while ( ! feof ( $handle ) ) {
				$buffer = trim ( fgets ( $handle, 4096 ) );
				if (empty ( $buffer ) || substr_compare ( $buffer, "#", 0, 1 ) == 0)
					continue;
				list ( $k, $v ) = explode ( " ", $buffer, 2 );
				$valid_secret_keys ["$k"] = $v;
			}
			fclose ( $handle );
		}
		// check key format & get checksum, tmp_key
		if (sscanf ( $cookie_value, "%39s%u", $tmp_key, $checksum ) != 2)
			return false;
			
			// checksum
		if (substr ( sprintf ( "%u", crc32 ( $tmp_key ) ), - 1 ) != $checksum)
			return false;
			
			// get $key_version, $random_key, $key
		list ( $key_version, $random_key, $md5_key ) = sscanf ( $tmp_key, "%3s%4s%s" );
		
		// check version of secret key
		if (! array_key_exists ( $key_version, $valid_secret_keys ))
			return false;
			
			// check md5_key
		if ($md5_key != md5 ( sprintf ( "%s|%s|%s", $user_id, $valid_secret_keys [$key_version], $random_key ) ))
			return false;
		
		return $user_id;
	}
}

/**
 * 获取登录的56ID
 *
 * @param string $username        	
 */
function u_get_user_id($username = null) {
	$username = $username ? $username : get_ursname ();
	if ($username) {
		$username_no_suffix = explode ( '@', $username );
		$username_no_suffix = $username_no_suffix [0];
		return $username_no_suffix;
	}
	return false;
}

/**
 * : 通用分页函数
 *
 * @param $perPage 每页条数        	
 * @param $file 文件路径        	
 * @param $omit $_GET中需要去掉的参数        	
 * @param $totalCount 总条数
 *        	，可由Core::$vars['multi']['totalCount']获取
 * @param $currentPage 当前页数，可由Core::$vars['page']获取        	
 * @return 分页数组
 * @author :	Melon`` @ 2010
 *        
 */
function splitPage($perPage = 20, $file = '', $omit = array(), $totalCount = '', $currentPage = '') {
	$sp = new ShowPage ();
	$sp->setvar ( array_merge ( array (
			'page',
			'i',
			'dy' 
	), $omit ) );
	$totalCount = $totalCount ? ( int ) $totalCount : Core::$vars ['multi'] ['totalCount'];
	$currentPage = $currentPage ? ( int ) $currentPage : Core::$vars ['page'];
	$sp->file = $file;
	$sp->set ( $perPage, $totalCount, $currentPage );
	Core::$vars ['multi'] ['string'] = $sp->output ( true );
	Core::$vars ['multi'] ['totalPage'] = $sp->getTotalPage ();
	return Core::$vars ['multi'];
}

/**
 * : 格式化时间
 *
 * @author :	Melon`` @ 2010
 *        
 */
function timeformat($time, $i = '1') {
	switch ($i) {
		case 2 :
			return date ( "Y-m-d", $time );
			break;
		case 3 :
			return date ( "H:i:s", $time );
			break;
		case 4 :
			return date ( "m-d", $time );
			break;
		case 5 :
			$left = $_SERVER ['REQUEST_TIME'] - $time;
			if ($left < 0) {
				return '刚刚发布';
			}
			$sec_per_min = 60;
			$sec_per_hour = 3600;
			$sec_per_day = $sec_per_hour * 24;
			$sec_per_week = $sec_per_day * 7;
			$sec_per_month = $sec_per_day * 30;
			// $sec_per_year = $sec_per_day * 365;
			
			if ($left > 3 * $sec_per_month) {
				return date ( "Y-m-d", $time );
			}
			
			if ($left >= $sec_per_month) {
				$m = floor ( $left / $sec_per_month );
				$left -= $m * $sec_per_month;
			}
			if ($left >= $sec_per_week) {
				$w = floor ( $left / $sec_per_week );
				$left -= $w * $sec_per_week;
			}
			if ($left >= $sec_per_day) {
				$d = floor ( $left / $sec_per_day );
				$left -= $d * $sec_per_day;
			}
			if ($left >= $sec_per_hour) {
				$h = floor ( $left / $sec_per_hour );
				$left -= $h * $sec_per_hour;
			}
			if ($left >= $sec_per_min) {
				$im = floor ( $left / $sec_per_min );
				$left -= $im * $sec_per_min;
			}
			
			// $ret [0] .= $y ? $y . '年' : '';
			$ret [1] = $m ? $m . '个月' : '';
			$ret [2] = $w ? $w . '周' : '';
			$ret [3] = $d ? $d . '天' : '';
			$ret [4] = $h ? $h . '小时' : '';
			$ret [5] = $im ? $im . '分钟' : '';
			$ret [6] = $left ? $left . '秒' : '';
			
			$max_return_fields = 2;
			$now = '';
			foreach ( $ret as $item ) {
				if (empty ( $item )) {
					continue;
				}
				$now .= $item;
				$max_return_fields --;
				if ($max_return_fields <= 0 || $m || $w || $d) {
					break;
				}
			}
			unset ( $item );
			
			if (empty ( $now )) {
				return null;
			} else {
				return $now . '前';
			}
			
			break;
		case 6 :
			return date ( "Y-m-d H:i", $time );
			break;
		case 7 :
			return date ( "Y-m", $time );
			break;
		case 8 :
			return date ( "Y年m月", $time );
			break;
		case 9 :
			return date ( "ymd", $time );
			break;
		case 10 :
			return date ( "ym", $time );
			break;
		case 11 :
			return date ( "d", $time );
			break;
		case 12 :
			return date ( "Y", $time );
			break;
		case 13 :
			return date ( "H:i", $time );
			break;
		default :
			return date ( "Y-m-d H:i:s", $time );
			break;
	}
}

/**
 * 判断是否属于56用户名
 */
function is_user_id($user_id) {
	return preg_match ( "/^[a-z][a-z0-9\.\-_]{2,25}$/i", $user_id );
}

/**
 * : 格式化sql
 *
 * @example $bind = array('value_1','value_2','value_3');
 *          $sql = "delete from content where cid in (".array_to_sql($bind).")";
 * @author :	Melon`` @ 2010
 *        
 */
function array_to_sql($data) {
	if (! is_array ( $data )) {
		if (empty ( $data )) {
			return "''";
		}
		return "'{$data}'";
	} else {
		$count = count ( $data );
		$ret = "";
		for($i = 0; $i < $count; $i ++) {
			$ret .= "'" . $data [$i] . "'";
			if ($i != $count - 1) {
				$ret .= ",";
			}
		}
		return $ret;
	}
}

/**
 * : 写文件
 *
 * @author :	Melon`` @ 2010
 *        
 */
function write($fileName, $content, $type = "w") {
	$fd = fopen ( $fileName, $type );
	if ($fd) {
		fwrite ( $fd, $content );
		fclose ( $fd );
		return true;
	} else {
		return false;
	}
}

/**
 * : 检查目录是否存在,没有新建
 *
 * @param $dir 目录相对路径        	
 * @param $recursive 递归        	
 * @return ture:成功 flase:失败
 * @author :	Melon`` @ 2010
 *        
 */
function sdir($dir, $recursive = false) {
	if (! file_exists ( $dir )) {
		@ mkdir ( $dir, 0777, $recursive );
		if (file_exists ( $dir )) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

/**
 * : 删除一个目录，rmdir别名
 *
 * @param $dir 目录相对路径        	
 * @return ture:成功 flase:失败
 * @author :	Melon`` @ 2010
 *        
 */
function deldir($path) {
	return srmdir ( $path );
}

/**
 * : 中文截割GBK
 *
 * @author :	Melon`` @ 2010
 *        
 */
function ssubstr($string, $sublen = 20) {
	if ($sublen >= strlen ( $string )) {
		return $string;
	}
	for($i = 0; $i < $sublen - 2; $i ++) {
		if (ord ( $string {$i} ) < 127) {
			$s .= $string {$i};
			continue;
		} else {
			if ($i < $sublen - 3) {
				$s .= $string {$i} . $string {++ $i};
				continue;
			}
		}
	}
	return $s . '..';
}

/**
 * : 获取客户端IP
 *
 * @author :	Melon`` @ 2010
 *        
 */
function ip() {
	if (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
		$hdr_ip = stripslashes ( $_SERVER ['HTTP_CLIENT_IP'] );
	} else {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$hdr_ip = stripslashes ( $_SERVER ['HTTP_X_FORWARDED_FOR'] );
		} else {
			$hdr_ip = stripslashes ( $_SERVER ['REMOTE_ADDR'] );
		}
	}
	return $hdr_ip;
}

/**
 * : format time length to 00:00:00
 *
 * @param $timeLength 90ms
 *        	to 01:30
 * @author :	jk 2007-04-23
 *        
 */
function formatTime($timeLenght) {
	date_default_timezone_set ( 'UTC' );
	$t = date ( 'H:i:s', ceil ( $timeLenght / 1000 ) );
	if (substr ( $t, 0, 2 ) == '00')
		$t = substr ( $t, 3 );
	return $t;
}

/**
 * 写log
 *
 * @param $key 文件名        	
 * @param $value 内容        	
 * @param $percent 1-100
 *        	写log的概率，用于大量写log操作但又不需完全记录的情况。
 * @return boolean
 * @author Melon`` @ 2009
 */
function mLog($key, $value, $percent = 100) {
	if ($percent == 100 || rand ( 0, 100 - $percent ) === 0) {
		! is_dir ( "./log" ) && @mkdir ( "./log", 0777 );
		! is_dir ( "./log/{$key}" ) && @mkdir ( "./log/{$key}", 0777 );
		
		$value = timeformat ( TIMESTAMP ) . '    |    ' . $value . "\n";
		
		$fp = @fopen ( "./log/{$key}/" . date ( "Y-m-d" ) . ".log", 'a+' );
		$w = @fwrite ( $fp, $value, strlen ( $value ) );
		@fclose ( $fp );
		return $w;
	}
	return FALSE;
}

/**
 * 异步回调函数
 *
 * @author Melon`` @ 09
 */
function mCallback($data, $script = FALSE, $delay = FALSE, $appendScript = '') {
	$append = array (
			'reload' => Core::$vars ['reload'],
			'hide' => Core::$vars ['hide'] 
	);
	$data = array_merge ( $data, $append );
	$data = json_encode ( $data );
	if ($script === TRUE) {
		echo "<script type=\"text/javascript\">\n";
		echo "try{document.domain = \"56.com\";}catch(e){};\n";
	}
	if ($delay) {
		echo "setTimeout(function(){\n";
	}
	if ($_REQUEST ['callback'] && preg_match ( '/[\w\_\.]+/i', $_REQUEST ['callback'], $match )) {
		echo sprintf ( "%s(%s);\n", $match [0], $data );
	}
	if ($appendScript) {
		echo $appendScript . "\n";
	}
	if ($delay) {
		echo "},{$delay});\n";
	}
	if ($script === TRUE) {
		echo "</script>\n";
	}
}

/**
 * FirePHP
 *
 * @author Melon`` @ 2010
 */
function Fb() {
	if (FIREPHP === TRUE) {
		$instance = FirePHP::getInstance ( true );
		$args = func_get_args ();
		return call_user_func_array ( array (
				$instance,
				'fb' 
		), $args );
	} else {
		@header ( 'FIREPHP-Warning: fp=ml' );
	}
}

/*
 * @name:find_data_from_url @desc:根据视频id或者播放页地址获得视频信息 @ $pct = 1:上传视频 $pct = 3: 相册视频 $pct=2:录制视频
 */
function find_data_from_url($url, $pct = '1') {
	if (is_numeric ( $url )) {
		$vid = $url;
		$url = G::FlvUrl ( $vid, $pct );
	} else {
		$vid = G::GetUrlId ( $url );
	}
	
	// 普通视频,录制视频
	if (preg_match ( "/http\:\/\/www\.56\.com\/u[0-9]{2}\/v_[a-z0-9]+\.html/i", $url )) {
		$data = G::GetIdsVideo ( $vid );
	}
	// 录制视频
	if (preg_match ( "/http\:\/\/www\.56\.com\/l[0-9]{2}\/v_[a-z0-9]+\.html/i", $url )) {
		$data = G::GetIdsLuzhiVideo ( $vid );
	}
	// 相册视频
	if (preg_match ( "/http\:\/\/www\.56\.com\/p[0-9]{2}\/v_[a-z0-9]+\.html/i", $url, $match )) {
		$data = G::GetIdsPhotoVideo ( $vid );
	}
	$data = $data [$vid];
	return $data;
}

/**
 * log something
 *
 * @param string $title        	
 * @param array $data        	
 * @param bool $debug        	
 */
function debugLog($title, $data, $debug = -10000) {
	$debug === - 10000 && ($debug = defined ( 'DEBUG_LOG' ) && DEBUG_LOG);
	if ($debug) {
		$echo = date ( 'Y-m-d H:i:s', time () ) . DEBUG_LOG . '|-------------------' . "\n" . $title . "\n";
		$echo .= var_export ( $data, true ) . "\n";
		$echo .= '-------------------|' . "\n";
		
		writeLogs ( 'geo_debug.log', $echo );
	}
}
function writeLogs($file_name, $msg) {
	$filename = LOG_DIR . $file_name;
	return file_put_contents ( $filename, $msg, FILE_APPEND );
	
	if (! file_exists ( $filename )) {
		file_put_contents ( $filename, '', FILE_APPEND );
	}
	if (! file_exists ( $filename )) {
		return;
	}
	$fr = fopen ( $filename, 'r+' );
	if (! $fr) {
		return;
	}
	fwrite ( $fr, $msg );
	fclose ( $fr );
}
