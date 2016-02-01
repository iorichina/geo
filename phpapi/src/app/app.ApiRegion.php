<?php
if (! defined ( 'IN_SYS' )) {
	header ( "HTTP/1.1 404 Not Found" );
	die ();
}
load_app ( 'Api' );
class ApiRegion extends Api {
	/**
	 * 当前模板文件目录
	 *
	 * @var string
	 */
	const THIS_TPL_DIR = 'region';
	public $region = null;
	public function __construct($key = null) {
		parent::__construct ();
	}
	
	/**
	 * 获取并设置区域信息   	
	 */
	protected function regionInfo() {
		if (empty ( $key )) {
			// 采用行政区域参数
			if (! empty ( $_GET ['region'] )) {
				$_region = Tools::convertToUtf8 ( urldecode ( $_GET ['region'] ) );
				$city = explode ( '市', $_region );
				if (! empty ( $city [0] )) {
					$key = explode ( '省', $city [0] );
				} else {
					$key = $_region;
				}
			} elseif (! empty ( $_GET ['area'] )) {
				$_area = Tools::convertToUtf8 ( urldecode ( $_GET ['area'] ) );
				$key = explode ( ',', $_area );
			} elseif (! empty ( $_GET ['prov'] ) || ! empty ( $_GET ['city'] )) {
				$key [] = Tools::convertToUtf8 ( trim ( $_GET ['prov'] ) ); // 关键词——2
				$key [] = empty ( $_GET ['city'] ) ? '' : Tools::convertToUtf8 ( trim ( $_GET ['city'] ) ); // 关键词2——2
			}
			// 采用ip参数
			if (empty ( $key [1] ) && empty ( $key [0] )) {
				if (! empty ( $_GET ['geoip'] )) {
					$ip = trim ( $_GET ['geoip'] );
				} else {
					$ip = ! empty ( $_COOKIE ['geoip'] ) ? trim ( $_COOKIE ['geoip'] ) : '';
				}
				preg_match ( '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip, $matches );
				$ip = $matches [0];
				$key = ! $ip ? null : GeoTool::getAreaNameByIp ( $ip );
			}
		}
		// 市
		if (empty ( $key [1] )) {
			// $key [1] = $key [0];
			// unset ( $key [0] );
		} else {
			$this->region ['city'] = Tools::convertToUtf8 ( $key [1] );
		}
		// 省
		if (empty ( $key [0] )) {
			// unset ( $key [0] );
		} else {
			$this->region ['prov'] = Tools::convertToUtf8 ( $key [0] );
		}
		debugLog ( __CLASS__ . ':region', $this->region );
	}
	
	/**
	 * 输出：地区信息-ip，地区信息默认被url encode
	 * 可选&callback=xxxx参数指定回调函数
	 * 可选&type=json参数指定使用json（IP=》地区信息）格式输出
	 * 可选&f=raw参数指定不对地区信息进行urlencode，当type=json时，此参数值无效
	 * @param  string $ip IP地址，可选，为null时优先选择$_GET['ip']、服务器抓取的客户端IP
	 * @return void     
	 */
	public function geoinfo($ip=null) {
		if (is_null($ip)) {
			if (!empty($_GET['ip'])) {
				$ip = $_GET['ip'];
			}else{
				$ip = g::ip();
			}
		}
		if (empty($ip)) {
			return;
		}
		$key = GeoTool::getAreaLogByIp ( $ip );
		// debugLog ( __CLASS__ . ':geo', $key.'-'.$ip );
		if (!empty($key)) {
			// 
			// $value = (isset($_GET['f']) && 'raw'==$_GET['f'] ?$key:rawurlencode($key)).'-'.$ip;
			$value = $key.'-'.$ip;
			// 
			$this->apiReturn(isset($_GET['type']) && 'json'==$_GET['type'] ?array($ip=>$key):$value, 0);
			// echo $value;
			if (!isset($_GET['ck']) || 'none'!=$_GET['ck']) {
				setcookie('geoinfo', rawurlencode($key).'-'.$ip, TIMESTAMP + 30*60, '/', '.56.com');
			}
		}
	}
}