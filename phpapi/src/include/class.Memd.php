<?php
/**
 * @name memcached客户端  
 * @author Melon`` @ 2010
 */

class Memd {
    
    private $memCache;
    
    /**
     * @desc: 配置，参考 config/inc.Mem.php	
     * @author:	Melon`` @ 2010
     *
     */
    public function __construct(array $configServerArray, array $option) {
        $this->memCache = new Memcached ();
        $this->memCache->addServers ( $configServerArray );
        
        if ($option ['compression'] == FALSE) {
            $this->memCache->setOption ( Memcached::OPT_COMPRESSION, FALSE );
        } else {
            $this->memCache->setOption ( Memcached::OPT_COMPRESSION, TRUE );
        }
        
        $this->memCache->setOption ( Memcached::OPT_HASH, Memcached::HASH_MD5 );
        $this->memCache->setOption ( Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT );
        $this->memCache->setOption ( Memcached::OPT_LIBKETAMA_COMPATIBLE, TRUE );
        $this->memCache->setOption ( Memcached::OPT_NO_BLOCK, TRUE );
        $this->memCache->setOption ( Memcached::OPT_TCP_NODELAY, TRUE );
    }
    
    /**
     * @desc: 读数据	
     * @author:	Melon`` @ 2010
     *
     */
    public function Get($key, $json_decode = true) {
        $value = $this->memCache->get ( $key );
        if ($value === FALSE) {
            return FALSE;
        } else {
            if ($json_decode === TRUE) {
                return json_decode ( $value, true );
            } else {
                return $value;
            }
        }
    }

    /**
     * every key add a memkey,this new memeky save a time,contrast the time ,del the value
     * @author ken
     */
    public function ComGet($key,$time=NULL,$json_decode=true){
    	//not set time,get th特别注意这个e value
    	if (empty($time)) return self::Get($key,$json_decode);
    	//set key's key
    	$keykey = md5($key.'_Comkey');
    	//Get the keykye value
    	$keykey_v = self::Get($keykey);
    	$nowtime = time();
    	if (empty($keykey_v)) {
    		$keykey_v = $nowtime;
    		self::Put($keykey,$keykey_v);
    			
    	}
    	if ($keykey_v+$time<$nowtime){
    		//del
    		self::Del($key);
    		self::Put($keykey,$nowtime);
    		return NULL;
    	}
    
    	return self::Get($key,$json_decode);
    }
    
    /**
     * @desc: 写数据	
     * @author:	Melon`` @ 2010
     *
     */
    public function Put($key, $val, $expire = 864000) {
        return $this->memCache->set ( $key, json_encode ( $val ), intval ( $expire ) );
    }
    
    /**
     * @desc: 删数据	
     * @author:	Melon`` @ 2010
     *
     */
    public function Del($key) {
        return $this->memCache->delete ( $key );
    }
    
    /**
     * @desc: 查看状态
     * @author:	Melon`` @ 2010
     *
     */
    public function Status() {
        return $this->memCache->getStats ();
    }
    
    /**
     * @desc: 获取代号	
     * @author:	Melon`` @ 2010
     *
     */
    public function getResultCode() {
        return $this->memCache->getResultCode ();
    }
    
    /**
     * @desc: 	
     * @author:	Melon`` @ 2010
     *
     */
    public function __destruct() {
        if ($this->memCache) {
        }
    }
}
