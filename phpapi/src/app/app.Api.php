<?php
if (! defined ( 'IN_SYS' )) {
	header ( "HTTP/1.1 404 Not Found" );
	die ();
}
load_app ( 'Index' );
class Api extends Index {
	
	/**
	 * 回调函数
	 *
	 * @var string default null
	 */
	public $api_callback = '';
	/**
	 * 是否保留data中的非数字key
	 *
	 * @var boolean
	 */
	public $api_data_key = null;
	/**
	 * 是否格式化API数据的totaltime
	 *
	 * @var bool true
	 */
	public $api_totaltime_format = true;
	/**
	 * 是否将数据按块截取
	 *
	 * @var bool true
	 */
	public $block_data = true;
	/**
	 * 将使用视频截图
	 * @var bool true
	 */
	public $use_video_photo = true;
	/**
	 * api回调
	 *
	 * @param array $data        	
	 * @param string $return
	 *        	true will return data only.
	 *        	0 or api callback is set, will echo json data.
	 *        	null will do nothing.
	 * @return mixed
	 */
	protected function apiReturn($data, $return = null) {
		if ($return === true) {
			if (is_array($data) && ! empty ( $data ['data'] )) {
				$data ['data'] = self::formatImg ( $data ['data'] );
			}
			return $data;
		}
		
		if (! empty ( $this->api_callback ) || $return === 0) {
			if (is_array($data) && ! empty ( $data ['data'] )) {
				// 发送cache header
				if (! headers_sent ()) {
					header ( "Expires: " . gmdate ( "D, d M Y H:i:s", TIMESTAMP + $this->cache_time ) . " GMT" );
					header ( "Cache-Control: public; max-age=" . $this->cache_time );
				}
				
				// 默认data中不要key
				if (empty ( $this->api_data_key )) {
					$data ['data'] = array_values ( $data ['data'] );
				}
				
				$data ['data'] = self::formatImg ( $data ['data'] );
			}
			echo $this->api_callback, 
				empty ( $this->api_callback ) ? '' : '(', 
				is_string($data)?(empty ( $this->api_callback )?'':'"').$data.(empty ( $this->api_callback )?'':'"'):json_encode ( $data ),
				empty ( $this->api_callback ) ? '' : ')';
		}
	}
	
	/**
	 * 对API图片进行格式化
	 *
	 * @see Index::formatImg()
	 */
	protected static function formatImg($img) {
		if (is_string ( $img )) {
			return parent::formatImg ( $img );
		} elseif (is_array ( $img )) {
			$img_keys = array (
					'pic',
					'flvimg',
					'img' 
			);
			// foreach ($img_keys as $data_key){
			// if (empty($img[$data_key])) {
			// continue;
			// }
			// $img [$key] = self::formatImg ( $val );
			// return $img;
			// }
			// 这样子能够搜索多个层次的数据，增加点消耗但可尽量避免漏掉处理
			foreach ( $img as $key => $val ) {
				if (is_array ( $val )) {
					$img [$key] = self::formatImg ( $val );
				} elseif (in_array ( $key, $img_keys )) {
					$img [$key] = self::formatImg ( $val );
				}
			}
			return $img;
		} else {
			return $img;
		}
	}
	
	/**
	 * 取接口数据
	 *
	 * @param string $this_api        	
	 */
	protected function apiData($this_api) {
		
		// 解析接口路径
		$api = self::getApiInfo ( $this_api );
		debugLog ( __METHOD__ . '|api|' . $this_api, $api );
		
		// 获取接口数据
		$source_data = Http::Get ( $api ['host'], $api ['script'], $api ['port'] );
		$source_data = empty ( $source_data ) ? array () : json_decode ( $source_data, true );
		debugLog ( __METHOD__ . '|source data', $source_data );
		
		return $source_data;
	}
	public function __construct() {
		parent::__construct ();
		
		// 回调函数
		$this->api_callback = empty ( $_REQUEST ['callback'] ) ? $this->api_callback : $_REQUEST ['callback'];
		$this->api_callback = str_ireplace ( array (
				'cookie',
				'alert' 
		), '', $this->api_callback );
		preg_match ( '/[a-z0-9\.]+/i', $this->api_callback, $matches );
		$this->api_callback = empty ( $matches [0] ) ? null : $matches [0];
		
		// data中的key
		$this->api_data_key = isset ( $_REQUEST ['data_key'] ) ? true : false;
	}
	
	/**
	 * 数据分块，确保是$block_num的倍数
	 *
	 * @param array $data        	
	 * @param number $block_num
	 *        	8
	 * @param bool $sub_rand
	 *        	null, true will return rand array
	 * @param bool $set_block
	 *        	true, false/null or $_REQUEST ['no_block'] specified will no nothing to data
	 */
	protected function dataBlock(&$data, $block_num = 8, $sub_rand = null, $set_block = true) {
		$set_block = isset ( $_REQUEST ['no_block'] ) ? null : $set_block;
		if ($set_block !== true || $this->block_data != true) {
			return;
		}
		
		$data_total = count ( $data );
		$data_ex = $data_total % $block_num;
		if ($data_ex != 0) {
			if ($sub_rand === true) {
				$data = $this->getRandArray ( $data, $data_total - $data_ex );
			} else {
				// 子数组
				$data = $this->getSubArray ( $data, $data_total - $data_ex );
			}
		}
	}
}