<?php
// 送utf-8响应头
header ( "Content-Type: text/html; charset=UTF-8" );
// 获取cli参数
define ( 'CLI_MODE', PHP_SAPI === 'cli' );
if (! empty ( $argv [1] )) {
	parse_str ( $argv [1], $cli_params );
	if (! empty ( $cli_params ) && is_array ( $cli_params )) {
		foreach ( $cli_params as $key => $val ) {
			$_REQUEST [$key] = $val;
		}
	}
}

date_default_timezone_set ( 'Asia/Shanghai' );
define ( "IN_SYS", true );
define ( "TIMESTAMP", $_SERVER ['REQUEST_TIME'] ); // 时间戳
define ( "ROOT_DIR", str_replace ( "\\", "/", dirname ( __FILE__ ) ) . "/" ); // 根目录

include ROOT_DIR . 'include/func.Global.php'; // 全局函数
include ROOT_DIR . 'config/inc.System.php'; // 全局配置

define ( "USER_ID", u_get_user_id () ); // 56用户名
define ( "IS_GUEST", ! USER_ID || preg_match ( "/^(guest|guest_|sg_|game-){1}([a-z0-9]{2,}_)?\d{4,}$/i", USER_ID ) );
// init define
// key of app e.g: app.php?action=index / $_REQUEST['action'] = index
define ( 'ACTION', 'action' );
// default action
define ( 'DEFAULT_ACTION', 'Ugc' );

// key of app e.g: app.php?app=index&do=phpinfo / $_REQUEST['do'] = phpinfo
define ( 'DO_METHOD', 'do' );
// default action
define ( 'DEFAULT_DO_METHOD', 'Run' );

// dir of apps
define ( 'APP_DIR', ROOT_DIR . 'app/' );
// dir of templates
define ( 'TPL_DIR', ROOT_DIR . 'template/' );
// dir of logs
define ( 'LOG_DIR', '/diska/logs/' );

define ( "BASE_URL", 'http://' . CLI_MODE ? 'w.56.com/geo' : ($_SERVER ['HTTP_HOST'] . dirname ( $_SERVER ["REQUEST_URI"] )) . '/' );
define ( "HOME_URL", 'http://' . CLI_MODE ? 'w.56.com/' : ($_SERVER ['HTTP_HOST'] . dirname ( dirname ( $_SERVER ["REQUEST_URI"] ) )) . '/' );
define ( 'APP_ENV', BASE_URL );

// 是否读/写缓存(memcache/tt)
define ( "CACHE_NONE", (isset ( $_REQUEST ['i'] ) && $_REQUEST ['i'] == 'none') );
// 缓存
if ((isset ( $_REQUEST ['i'] ) && $_REQUEST ['i'] == 'c') || (isset ( $_REQUEST ['dy'] ) && $_REQUEST ['dy'] == 'c')) {
	define ( "CACHE", FALSE );
} else {
	define ( "CACHE", TRUE );
}

// 调试,只有授权用户才能调用调试
if (! empty ( $_REQUEST ['dg'] ) && 'ml' == $_REQUEST ['dg'] && ('iorichina' == USER_ID || CLI_MODE)) {
	ini_set ( 'display_errors', TRUE );
	error_reporting ( E_ALL ^ E_NOTICE );
	define ( "DEBUG", true );
} else {
	define ( "DEBUG", false );
}
define ( 'DEBUG_LOG', (DEBUG === true) && (isset ( $_REQUEST ['log'] )) ? ($_REQUEST ['log'] ? $_REQUEST ['log'] : true) : false );
define ( 'DEBUG_SQL', (DEBUG === true) && isset ( $_REQUEST ['debug_sql'] ) );