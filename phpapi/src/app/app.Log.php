<?php
if (! defined ( 'IN_SYS' )) {
	header ( "HTTP/1.1 404 Not Found" );
	die ();
}
load_app ( 'Index' );
class Log extends Index {
	private $log_admin = array (
			'iorichina' 
	);
	/**
	 *
	 * @param string $config        	
	 */
	public function __construct($config = null) {
		if (! in_array ( USER_ID, $this->log_admin )) {
			header ( 'HTTP/1.1 404 Not Found' );
			die ( 'access deny' );
		}
		
		$this->cache_time = 0;
	}
	
	/**
	 * 记录错误信息
	 * 
	 * @param unknown $info        	
	 */
	public static function logError($info) {
		$filename = LOG_DIR . 'ugc_index_error.' . date ( 'Y-m' ) . '.log';
		self::log ( $filename, $info );
	}
	public static function log($filename, $info) {
		file_get_contents ( $filename );
	}
	
	/**
	 * 查看error log
	 */
	public function error() {
		echo '<a href="?clear">clear</a><br />';
		$filename = LOG_DIR . 'ugc_index_error.' . date ( 'Y-m' ) . '.log';
		echo '<pre>' . file_get_contents ( $filename ) . '</pre>';
	}
	/**
	 * 清空error log
	 */
	public function errorClear() {
		echo '<a href="?">return</a><br />';
		$filename = LOG_DIR . 'ugc_index_error.' . date ( 'Y-m' ) . '.log';
		unlink ( $filename );
	}
	
	/**
	 * 查看debug log
	 */
	public function debug() {
		echo '<a href="?clear">clear</a><br />';
		$filename = LOG_DIR . 'ugc_index_debug.log';
		echo '<pre>' . file_get_contents ( $filename ) . '</pre>';
	}
	/**
	 * 清空debug log
	 */
	public function debugClear() {
		echo '<a href="?">return</a><br />';
		$filename = LOG_DIR . 'ugc_index_debug.log';
		unlink ( $filename );
	}
}