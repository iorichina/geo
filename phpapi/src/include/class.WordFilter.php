<?php
/**
 * @name:	class.WordFilter.php
 * @requires:	class.Http.php / mb_string
 * @desc: 	黑词过滤接口
 * @author:	zhys9(jingki) @ 2008-8-30
 *
 */

class WordFilter {
    
    const API_HOST = 'wordfilter.56.com';
    private static $_API_PORT = 28080;
    const API_ROOT = 'WordFilterServer/wordfilter/';
    
    private $valid_filters = array ('search_front' => 'http://api.v.56.com/API/black_word_lib.php?type=for_search_front', 'search' => 'http://api.v.56.com/API/black_word_lib.php?type=for_search', //匹配到了显示搜索不到结果
'vPrifileA' => 'http://api.v.56.com/API/black_word_lib.php?type=for_vProfileA', //一级关键词，在新上传视频或修改视频资料（标题、标识、简介）时匹配到了一级关键词提示含有敏感词，不允许提交
'vPrifileB' => 'http://api.v.56.com/API/black_word_lib.php?type=for_vProfileB', //二级关键词，对于已经通过审核的视频（包括新上传md5视频和老视频两种情况）修改或填写其资料的时候匹配到了二级关键词，将视频状态设置为未审核。同时在后台突出显示该视频
'reviewA' => 'http://api.v.56.com/API/black_word_lib.php?type=for_reviewA', //一级关键词，匹配到了提示含有敏感词，不允许发表
'reviewB' => 'http://api.v.56.com/API/black_word_lib.php?type=for_reviewB', //二级关键词，匹配到了将留言状态设置为未审核，只有发表者自己能看到留言，其他用户看不到留言，该留言需要客服审核，审核通过后所有人能看到
'album' => 'http://api.v.56.com/API/black_word_lib.php?type=for_album', //专辑，在新增专辑或修改专辑资料（标题、关键词、简介）时匹配到了一级关键词提示含有敏感词，不允许提交
'space' => 'http://api.v.56.com/API/black_word_lib.php?type=for_vSite', //空间相关, 在修改空间（空间大字报/空间标题签名）资料时匹配到了一级关键词提示含有敏感词，不允许提交
'qunA' => 'http://api.v.56.com/API/black_word_lib.php?type=for_qunA', //群组一级关键词，在发表新主题新回复或修改主题回复时匹配到了一级关键词提示含有敏感词，不允许提交
'qunB' => 'http://api.v.56.com/API/black_word_lib.php?type=for_qunB', //群组二级关键词，匹配到了将状态设置为未审核，只有发表者自己能看到帖子，其他用户看不到帖子，该帖子需要客服审核
'userProfile' => 'http://api.v.56.com/API/black_word_lib.php?type=for_profile', //用户资料，在修改用户资料时匹配到了一级关键词提示含有敏感词，不允许提交
'all' => 'http://api.v.56.com/API/black_word_lib.php?type=for_all', //对所有产品的都生效
'match_all' => 'http://api.v.56.com/API/black_word_lib.php?type=for_searchB', //完全匹配关键字，不允许继续搜索。例如完整匹配 “桌球天王” 不允许返回数据   但“桌球天王 20”可以返回数据  
'copyright' => 'http://api.v.56.com/API/black_word_lib.php?type=for_copyright', //版权黑词
'hot_word' => 'http://api.v.56.com/API/black_word_lib.php?type=hot_word' )//热门词，暂时用于评论词条。
;
    private $valid_charset = array ('utf8', 'utf-8', 'gbk', 'gb2312' );
    private $filter = 'search';
    private $charset = 'utf8';
    private $op = '';
    private $wordsSplit = null;
    
    /**
     * @name __construct
     * @author zhys9
     *
     */
    public function __construct($filter = '', $charset = '', $wordsSplit = null) {
        if ($filter && isset ( $this->valid_filters [$filter] )) {
            $this->filter = $this->valid_filters [$filter];
        } else {
            $this->filter = $this->valid_filters [$this->filter];
        }
        if ($charset && in_array ( strtolower ( $charset ), $this->valid_charset )) {
            $this->charset = $charset;
        } else {
            $this->charset = $this->valid_charset [$this->charset];
        }
        if ($wordsSplit) {
            $this->wordsSplit = $wordsSplit;
        }
    }
    
    /**
     * @name Set
     * @author zhys9
     *
     */
    public function Set($filter, $charset) {
        $this->__construct ( $filter, $charset );
    }
    
    /**
     * @name Remove
     * @author zhys9
     * @desc 过滤掉$string中可能包含的黑词
     * @param string $string 待过滤字符串
     * @param string $replace 用什么字符替换可能包含的黑词，比如替换为星号 ***
     * @return string
     *
     */
    public function Remove($string, $replace = '') {
        if (! trim ( $string ))
            return '';
        $this->op = 'filter';
        $post = $this->buildRequest ( $string );
        if (gettype ( $post ) == 'string') { // mbstring not installed
            return $string;
        }
        $rs = $this->callApi ( $post );
        if ($rs !== false) {
            if ($this->charset == 'gbk' || $this->charset == 'gb2312') {
                $rs = mb_convert_encoding ( $rs, 'GBK', 'UTF-8' );
            }
            return $rs;
        } else { //api reponse error
            exit ( '0' ); //本地的Badword已经无法使用，因为生成hash表时占用内存超过8M。直接退出给出信息
        }
    }
    
    /**
     * @name GetBadWords
     * @author zhys9
     * @desc 获取字符串中可能包含的黑词，无黑词是返回空数组 ps: 可用于检测是否包含黑词
     * @param string $string 待过滤字符串
     * @return array 包含的黑词
     *
     */
    public function GetBadWords($string) {
        if (! trim ( $string ))
            return array ();
        $this->op = 'words';
        $post = $this->buildRequest ( $string );
        if (gettype ( $post ) == 'string') { // mbstring not installed
            return '';
        }
        $rs = $this->callApi ( $post );
        if ($this->charset == 'gbk' || $this->charset == 'gb2312') {
            $rs = mb_convert_encoding ( $rs, 'GBK', 'UTF-8' );
        }
        return explode ( ',', ( string ) $rs );
    }
    
    /**
     * @name isMatchAll
     * @author Melon``
     * @desc 是否完整匹配不允许直接查询的关键字
     * @example 例如完整匹配 “桌球天王” 返回 “true”   “桌球天王 20” 返回false 
     * @param string $string 字符串
     * @return string true|false  是字符窜
     *
     */
    public function isMatchAll($string) {
        if (! trim ( $string ))
            return FALSE;
        $this->op = 'match';
        $post = $this->buildRequest ( $string );
        if (gettype ( $post ) == 'string') { // mbstring not installed
            return $string;
        }
        $rs = $this->callApi ( $post );
        if ($_GET ['dg'] == 'ml') {
            echo 'isMatchAll:';
            echo '<br>';
            echo $rs;
            echo '<br>';
        }
        if ($rs !== false) {
            if (trim ( strtolower ( $rs ) ) == 'true') {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    
    /**
     * @name buildRequest
     * @author zhys9
     * @desc build data
     * @param string $string
     * @param string $replace 替换成什么字符
     * @return array
     *
     */
    protected function buildRequest($string, $replace = '') {
        if ($this->charset == 'gbk' || $this->charset == 'gb2312') {
            if (function_exists ( "mb_convert_encoding" )) {
                $string = mb_convert_encoding ( $string, 'UTF-8', 'GBK' );
            } else {
                return $string;
            }
        }
        $re = array ('text' => $string, 'word' => $string, 'replaceCh' => $replace, 'wordsUrl' => $this->filter, 'outType' => 'text' );
        if ($this->wordsSplit) {
            $re ['wordsSplit'] = $this->wordsSplit;
        }
        return $re;
    }
    
    /**
     * @name callApi
     * @author zhys9
     * @desc send request
     * @param array $post_data data for posting
     * @return string
     *
     */
    protected function callApi($post_data) {
        //指定黑词库的编码 和 分隔符  2011-06-22 seamaidmm
        if (is_array ( $post_data )) {
            $post_data ['wordsCharset'] = "utf-8";
            $post_data ['wordsSplit'] = "\n";
        }
        
        $rs = Http::Post ( self::API_HOST, self::API_ROOT . $this->op, $post_data, self::$_API_PORT );
        
        return $rs;
    
    }

}
