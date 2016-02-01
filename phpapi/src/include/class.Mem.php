<?php
/**
 * @name:	class.Mem.php
 * @desc: 	memcache 操作类库
 * @author:	zhys9(jingki) @ 2008-6-1
 	<code>
 		$configs = array();
 		$configs['server'][0] = array(
 									'host'	=> '192.168.1.38',
 									'port'	=> 22222,
 									'weight'=> 11
 								);
 		$mem = & new Mem($configs['server']);
 		$mem->Put('test', 'jingki', 10);
 		$rs = $mem->Get('test');
 		echo $rs;
 	</code>
	@modified   Melon @ 2010-04-26
 */

class Mem {
    
    private $memCache;
    
    private $compression = TRUE;
    private $compressMinSize = 2000;
    private $compressLevel = 0.2;
    
    const PERSISTENT = true;
    const WEIGHT = 10;
    const TIMEOUT = 1;
    const RETRYINTERVAL = 15;
    
    /**
     * @desc: 配置，参考 config/inc.Mem.php
     * @author:	Melon`` @ 2010
     *
     */
    public function Mem(array $configServerArray) {
        if (is_array ( $configServerArray )) {
            foreach ( $configServerArray as $val ) {
                $this->AddServer ( $val );
            }
        }
    }
    
    protected function _Connect() {
        if (! $this->memCache) {
            $this->memCache = new Memcache ();
        } else {
            return;
        }
    }
    
    /**
     * @name AddServer
     * @author zhys9
     * @desc 添加一个server
     *
     */
    public function AddServer($arr) {
        $persistent = isset ( $arr ['persistent'] ) && $arr ['persistent'] ? $arr ['persistent'] : self::PERSISTENT;
        $timeout = isset ( $arr ['timeout'] ) && $arr ['timeout'] ? $arr ['timeout'] : self::TIMEOUT;
        $retry_interval = isset ( $arr ['retry_interval'] ) && $arr ['retry_interval'] ? $arr ['retry_interval'] : self::RETRYINTERVAL;
        $this->_Connect ();
        $this->memCache->addServer ( $arr ['host'], $arr ['port'], $persistent, $arr ['weight'], $timeout, $retry_interval );
        if ($this->compression) {
            $this->memCache->setCompressThreshold ( $this->compressMinSize, $this->compressLevel );
        }
    }
    
    /**
     * @name Get
     * @author zhys9
     * @desc 取出某个值
     * @param string $key
     * @return mixed
     *
     */
    public function Get($key) {
        $this->_Connect ();
        $value = $this->memCache->get ( $key );
        if ($value === FALSE) {
            return FALSE;
        } else {
            return json_decode ( $value, true );
        }
    }
    
    /**
     * @name Put
     * @author zhys9
     * @desc 存储一条数据
     * @param string $key
     * @param mixed $val
     * @param int $expire 有效期
     * @return bool
     *
     */
    public function Put($key, $val, $expire = 86400000) {
        $this->_Connect ();
        return $this->memCache->set ( $key, json_encode ( $val ), false, intval ( $expire ) );
    }
    
    /**
     * @name Del
     * @author zhys9
     * @desc 删除某个值
     * @param string $key
     * @return bool
     *
     */
    public function Del($key) {
        $this->_Connect ();
        return $this->memCache->delete ( $key );
    }
    
    /**
     * @name Status
     * @author zhys9
     * @desc 查看状态
     * @return array
     *
     */
    public function Status() {
        $this->_Connect ();
        return $this->memCache->getExtendedStats ();
    }
    
    /**
     * @name Flush
     * @author zhys9
     * @desc 清除所有memcache数据，慎用！
     *
     */
    public function Flush() {
        return $this->flush ( $this->memCache );
    }
    
    /**
     * @desc: 关闭连接
     * @author:	Melon`` @ 2010
     *
     */
    public function __destruct() {
        if ($this->memCache) {
            $this->memCache->close ();
        }
    }
}
?>