<?php
/**
 * @name:	Curl类
 * @author:	Melon`` @ 2010
 * 
 * 例子：

  $url = 'http://www.baidu.com';
  $curl = new Curl($url);
  $curl->CreateCurl();
  print_r($curl->GetBody());//获取主体
  
  
  $url = 'http://img.v165.56.com/images/20/0/huasha52i56olo56i56.com_sc_119457357592.jpg';
  $curl = new Curl($url,false);
  $curl->CreateCurl();
  print_r($curl->GetHttpStatus());//获取跳转前状态码（302）
  
  print_r($curl->GetHeaderRec());//获取响应头数组
  
 */
class Curl {
    protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
    protected $_url;
    protected $_followlocation;
    protected $_timeout;
    protected $_maxRedirects;
    protected $_cookieFileLocation = './cookie.txt';
    protected $_post;
    protected $_postFields;
    protected $_referer = 'http://www.56.com';
    protected $_headerSend = array ();
    
    protected $_session;
    protected $_webpage;
    protected $_includeHeader;
    protected $_noBody;
    protected $_status;
    protected $_binaryTransfer;
    protected $_headerRec;
    
    public $authentication = 0;
    public $auth_name = '';
    public $auth_pass = '';
    
    /**
     * @desc: 认证	
     * @author:	Melon`` @ 2010
     *
     */
    public function UseAuth($use) {
        $this->authentication = 0;
        if ($use == true)
            $this->authentication = 1;
    }
    
    /**
     * @desc: 用户名	
     * @author:	Melon`` @ 2010
     *
     */
    public function SetName($name) {
        $this->auth_name = $name;
    }
    
    /**
     * @desc: 	密码
     * @author:	Melon`` @ 2010
     *
     */
    public function SetPass($pass) {
        $this->auth_pass = $pass;
    }
    
    /**
     * @desc: 初始化
     * @param $url 地址
     * @param $followlocation 是否获取最终跳转地址
     * @param $timeOut 超时时间
     * @param $maxRedirecs 最大跳跃
     * @param $binaryTransfer 二进制传输
     * @param $includeHeader 是否包含头信息
     * @param $noBody 是否返回内容主体
     * @author:	Melon`` @ 2010
     *
     */
    public function __construct($url, $followlocation = true, $timeOut = 10, $maxRedirecs = 4, $binaryTransfer = false, $includeHeader = true, $noBody = false) {
        $this->_url = $url;
        $this->_followlocation = $followlocation;
        $this->_timeout = $timeOut;
        $this->_maxRedirects = $maxRedirecs;
        $this->_noBody = $noBody;
        $this->_includeHeader = $includeHeader;
        $this->_binaryTransfer = $binaryTransfer;
        
        $this->_cookieFileLocation = './cookie.txt';
    }
    
    /**
     * @desc: 设置来路	
     * @author:	Melon`` @ 2010
     *
     */
    public function SetReferer($referer) {
        $this->_referer = $referer;
    }
    
    /**
     * @desc: 设置cookie地址	
     * @author:	Melon`` @ 2010
     *
     */
    public function SetCookiFileLocation($path) {
        $this->_cookieFileLocation = $path;
    }
    
    /**
     * @desc: 设置POST	
     * @author:	Melon`` @ 2010
     *
     */
    public function SetPost($postFields) {
        $this->_post = true;
        $this->_postFields = $postFields;
    }
    
    /**
     * @desc: 设置浏览器	
     * @author:	Melon`` @ 2010
     *
     */
    public function SetUserAgent($userAgent) {
        $this->_useragent = $userAgent;
    }
    
    /**
     * @desc: 设置请求头 	
     * @author:	Melon`` @ 2010
     *
     */
    public function setHeader($header) {
        $this->_headerSend = $header;
    }
    
    /**
     * @desc: 创建请求	
     * @author:	Melon`` @ 2010
     *
     */
    public function CreateCurl($url = '') {
        $this->_url = $this->_url ? $this->_url : $url;
        
        $s = curl_init ();
        
        curl_setopt ( $s, CURLOPT_URL, $this->_url );
        curl_setopt ( $s, CURLOPT_HTTPHEADER, $this->_headerSend );
        curl_setopt ( $s, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt ( $s, CURLOPT_MAXREDIRS, $this->_maxRedirects );
        curl_setopt ( $s, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $s, CURLOPT_FOLLOWLOCATION, $this->_followlocation );
        curl_setopt ( $s, CURLOPT_COOKIEJAR, $this->_cookieFileLocation );
        curl_setopt ( $s, CURLOPT_COOKIEFILE, $this->_cookieFileLocation );
        
        if ($this->authentication == 1) {
            curl_setopt ( $s, CURLOPT_USERPWD, $this->auth_name . ':' . $this->auth_pass );
        }
        if ($this->_post) {
            curl_setopt ( $s, CURLOPT_POST, true );
            curl_setopt ( $s, CURLOPT_POSTFIELDS, $this->_postFields );
        
        }
        if ($this->_includeHeader) {
            curl_setopt ( $s, CURLOPT_HEADER, true );
            curl_setopt ( $s, CURLINFO_HEADER_OUT, true );
        }
        if ($this->_noBody) {
            curl_setopt ( $s, CURLOPT_NOBODY, true );
        }
        if ($this->_binary) {
            curl_setopt ( $s, CURLOPT_BINARYTRANSFER, true );
        }
        curl_setopt ( $s, CURLOPT_USERAGENT, $this->_useragent );
        curl_setopt ( $s, CURLOPT_REFERER, $this->_referer );
        
        $_buffer = curl_exec ( $s );
        $_body = substr ( strstr ( $_buffer, "\r\n\r\n" ), 4 );
        $this->_webpage = $_body ? $_body : '';
        
        $this->_status = curl_getinfo ( $s, CURLINFO_HTTP_CODE );
        $this->_headerRec = curl_getinfo ( $s );
        
        curl_close ( $s );
    }
    
    /**
     * @desc: 获取http状态码	
     * @author:	Melon`` @ 2010
     *
     */
    public function GetHttpStatus() {
        return $this->_status;
    }
    
    /**
     * @desc: 获取响应头	
     * @author:	Melon`` @ 2010
     *
     */
    public function GetHeaderRec() {
        return $this->_headerRec;
    }
    
    /**
     * @desc: 获取主体	
     * @author:	Melon`` @ 2010
     *
     */
    public function GetBody() {
        return $this->_webpage;
    }
} 

