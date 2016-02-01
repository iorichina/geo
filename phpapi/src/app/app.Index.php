<?php
if (! defined ( 'IN_SYS' )) {
	header ( "HTTP/1.1 404 Not Found" );
	die ();
}
class Index {
	// 缓存对象
	protected $cache_obj = null;
	/**
	 * 缓存时间
	 *
	 * @var number default null
	 */
	public $cache_time = null;
	public function __construct() {
		// 缓存时间
		$this->cache_time = isset ( $_REQUEST ['expire'] ) ? intval ( $_REQUEST ['expire'] ) : 300;
	}
	
	/**
	 * 返回用户信息，增加缓存
	 *
	 * @param string $_user_id        	
	 * @param bool $big_photo
	 *        	false
	 * @param number $cache_time
	 *        	3600
	 * @return array 不管用户是否存在都返回相关信息，可以使用字段“Account”判断用户是否存在
	 */
	protected function getUserInfo($_user_id, $big_photo = false, $cache_time = 3600) {
		if (empty ( $_user_id )) {
			return array ();
		}
		$_key = md5 ( __METHOD__ . $_user_id . $big_photo );
		
		$cache_info = $this->cacheData ( $_key, null, $cache_time );
		debugLog ( __METHOD__ . ':cache:' . ! empty ( $this->cache_obj ) . ':' . $_key . ':' . $_user_id, empty ( $cache_info ['user_id'] ) ? 'miss' : 'hit' );
		if (! empty ( $cache_info ['user_id'] )) {
			return $cache_info;
		} else {
			$_data = User::GetProfile ( $_user_id );
			debugLog ( __METHOD__ . ':User::GetProfile:' . $_user_id, $_data );
			
			$return ['user_id'] = $_user_id;
			$return ['nickname'] = empty ( $_data ['nickname'] ) ? $_user_id : $_data ['nickname'];
			$return ['head'] = empty ( $_data ['head'] ) ? null : $_data ['head'];
			$return ['photo'] = User::GetPhotoUrl ( $_user_id, $big_photo, $return ['head'] );
			$return ['user_url'] = User::UserUrl ( $_user_id );
			// 可以使用这个字段来判断用户是否存在
			$return ['Account'] = empty ( $_data ['Account'] ) ? null : $_data ['Account'];
			
			$this->cacheData ( $_key, $return, $cache_time );
			return $return;
		}
	}
	
	/**
	 * 返回接口使用的url内容
	 *
	 * @param string $api_url        	
	 * @return array
	 */
	public static function getApiInfo($api_url) {
		$api = parse_url ( $api_url );
		return array (
				'host' => empty ( $api ['host'] ) ? '' : $api ['host'],
				'script' => (empty ( $api ['path'] ) ? '' : substr ( $api ['path'], 1 )) . (empty ( $api ['query'] ) ? '' : '?' . $api ['query']),
				'port' => empty ( $api ['port'] ) ? '80' : $api ['port'] 
		);
	}
	
	/**
	 * 取数组中随机num个数据
	 *
	 * @param array $data        	
	 * @param int $num        	
	 * @return array
	 */
	public function getRandArray($data, $num) {
		if (empty ( $data ) || $num == 0) {
			return array ();
		}
		
		if ($num === - 1) {
			$num = count ( $data );
		} else {
			$count = count ( $data );
			if ($count < $num) {
				$num = $count;
			}
		}
		if ($num == 0) {
			return array ();
		}
		
		// 随机
		$keys = array_rand ( $data, $num );
		if ($num == 1) {
			return array (
					$keys => $data [$keys] 
			);
		}
		
		$return = array ();
		foreach ( $keys as $key ) {
			$return [$key] = $data [$key];
		}
		return $return;
	}
	/**
	 * 返回子数组
	 *
	 * @param array $data        	
	 * @param number $num        	
	 * @param number $offset        	
	 * @return array
	 */
	protected function getSubArray($data, $num, $offset = 0) {
		if (empty ( $data ) || $num == 0 || $offset < 0) {
			return array ();
		}
		
		$count = count ( $data );
		if ($num === - 1) {
			$num = count ( $data );
		} else {
			if ($count < $num) {
				return $data;
			}
		}
		if ($num == 0 || $offset > $count - $num) {
			return array ();
		}
		
		// 子数组
		return array_slice ( $data, $offset, $num, true );
	}
	
	/**
	 * 格式化totaltime
	 *
	 * @param number $time        	
	 * @return string
	 */
	protected function formatTotalTime($time) {
		if (! $this->api_totaltime_format) {
			return $time;
		}
		return timeShowFormat ( $time );
	}
	
	/**
	 * 格式化图片，以及其他处理
	 *
	 * @param string $img        	
	 */
	protected static function formatImg($img) {
		if (empty ( $img )) {
			return $img;
		}
		
		return preg_replace ( self::imgFormats ( true ), self::imgFormats ( false ), $img, 1 );
	}
	protected static function imgFormats($pattern = true) {
		if ($pattern === true) {
			return '/(v\d+.*?\.56img\.com.+?)hd\.jpg/';
		} else {
			return '${1}hd_m.jpg';
		}
	}
	
	/**
	 * 获取/设置缓存
	 *
	 * @param string $key        	
	 * @param mixed $val        	
	 * @param number $cache_time
	 *        	null or false will do nothing.
	 *        	-1 will del cache only.
	 *        	empty $val and CACHE===true will comget cache.
	 *        	else will put data into cache.
	 */
	protected function cacheData($key, $val = null, $cache_time = 0) {
		if (null === $cache_time || false === $cache_time || CACHE_NONE === true) {
			return;
		}
		if (empty ( $this->cache_obj )) {
			Core::InitMemdCache ( 'tt' );
			$this->cache_obj = &Core::$mem ['tt'];
		}
		if (empty ( $this->cache_obj )) {
			return false;
		}
		$key = 'ugc' . $key;
		$cache_time = intval ( $cache_time );
		
		if ($cache_time == - 1) {
			$return = $this->cache_obj->Del ( $key );
		} elseif (empty ( $val ) && CACHE !== false) {
			$return = $this->cache_obj->ComGet ( $key, $cache_time );
		} elseif (! empty ( $val )) {
			$return = $this->cache_obj->Put ( $key, $val, $cache_time );
		}
		return $return;
	}
	
	/**
	 * 发送no cache头
	 */
	protected function noCacheHeader() {
		if (! headers_sent ()) {
			header ( "Expires: " . gmdate ( "D, d M Y H:i:s", strtotime ( '1997-01-01 08:00:00' ) ) . " GMT" );
			header ( "Cache-Control: private, no-cache, no-store, must-revalidate" );
			header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
			header ( "Pragma: no-cache" );
		}
	}
}