<?php
/*###########################<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

文件:mysql.inc.php
用途:MYSQL类
作者:是梦的(dreamxyp)[QQ31996798]
更新:2007.02.28

#############################<meta http-equiv="Content-Type" content="text/html; charset=gb2312">*/
class db
{
    protected $_config = array(
    		 		'host' 		=> '127.0.0.1',
        			'database' => null,
        			'username' => null,
        			'password' => null
        		);
   	#@ class cache
    protected $_rs;
    protected $_conn; 		//_connection
    protected $_c;			//_connection cache
    protected $_sql;
    protected $_rsCount;
    protected $_rsArray;
    protected $_dataArray;
    protected $_insertId;
    protected $_queryTimes;
	#cache 查询文件缓存
	protected $_cache			= 0;	// 0:关闭 1:开启
	protected $_cacheTime		= 300; 	// 缓存时间 单位秒
	#db charset
	protected $_charset		    = 'utf-8';//,'utf8','gbk';
	
	public	  $_affected_rows	= 0;
    /*	*******************
    	  Function
    	******************* */
    public function __construct($config,$set=false)
    {
        $this->_config = $config;
    	if($set)
    	{
    		$key = array(
    			'_cache',
    			'_cacheTime',
    			'_charset'
    		);
    		foreach($set as $k => $v)
    		{
    			if(in_array($k,$key))$this->$k = $v;
    		}
    	}
    }
    // protected
    protected function _connect($style='')
    {
        if ($this->_conn) return;
        $this->_conn = mysql_connect(
						        		$this->_config['host'] , 
						        		$this->_config['username'], 
						        		$this->_config['password'], 
						        		0, 
						        		MYSQL_CLIENT_IGNORE_SPACE
    								  ) or die('服务器繁忙，请稍后刷新');
        mysql_select_db($this->_config['database'], $this->_conn);// or die("DateBase Err: " . mysql_errno() . ": " . mysql_error() );
		mysql_query("SET NAMES ".$this->_charset, $this->_conn);
	}
    protected function _getDataArray($sql, $bind)
    {
    	$this->_dataArray = array();
    	$rsCount = $this->rsCount($sql, $bind);
        for ($i = 0; $i < $rsCount; $i++)
        {
            $this->_dataArray[] = $this->rsArray();
        }
        return $this->_dataArray;
    }
    protected function _formatSql($sql, $bind)
    {
    	if (is_array($bind) and !strstr($sql, "?"))
        {
            $sql = $this->bindValue($sql, $bind);
        }else
        {
        	if ($bind)
        	{
        		$sql = $this->quoteInto($sql, $bind);
        	}
        }
        return $sql;
    }
    protected function bindValue($sql, $bind)
    {
        $sqlArray =preg_split(
            "/(\:[A-Za-z0-9_]+)\b/",
            $sql,
            -1,
            PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
        );
        foreach ($sqlArray as &$v)
        {
            if ($v[0] == ':')
                $v = $this->quote($bind[substr($v, 1)]);
        }
        return implode('', $sqlArray);
    }
    //======================================
    // 函数: quoteInto()
    // 功能: 定位
    // 参数: @$text string
    //	     @$value  string
    // 返回: sql string
    //======================================
    protected function quoteInto($text, $value)
    {
        return str_replace('?', $this->quote($value), $text);
    }
    //======================================
    // 函数: quote()
    // 功能: 添加引号防止数据库攻击
    // 参数: string
    // 返回: mysql_real_escape_string
    //======================================
    public  function quote($value)
    {
        if (is_array($value))
        {
            foreach ($value as & $val)
            {
                $val = $this->quote($val);
            }
            return implode(', ', $value);
        } else
        {
        	if (get_magic_quotes_gpc()){
        		return "'" .($value) . "'";
        	}else{
            	return "'" . mysql_real_escape_string($value) . "'";
        	}
        }
    }
    /*	********************************************************
    	 **	公共函数库
    	******************************************************** */
    //======================================
    // 函数: conn()
    // 功能: query
    // 参数: @$sql string
    //	     @$bind  string|array
    // 返回: MYSQL句柄
    //======================================
    public function conn($sql, $bind='')
    {
        $this->_connect();
    	//SQL
    	if ($sql) $this->_sql = $this->_formatSql($sql, $bind);
        //mysql_pconnect
		$this->_rs = mysql_query($this->_sql, $this->_conn);
        $this->_queryTimes++;
        $this->_insertId = mysql_insert_id($this->_conn);
        $this -> _affected_rows = mysql_affected_rows($this -> _conn);
        return $this->_rs;
    }
    //======================================
    // 函数: insert()
    // 功能: 插入一条记录
    // 参数: @$table 表名
    //	     @$bind  array
    // 返回: 本条记录的
    //======================================
    public function insert($table, $bind)
    {
        $cols = array_keys($bind);
        $sql = "INSERT INTO $table " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ')';
        $this->conn($sql, $bind);
        return $this->_insertId;
    }
    //======================================
    // 函数: inserts()一次有多个时用这个
    // 功能: 插入一条记录
    // 参数: @$table 表名
    //	     @$bind  array
    // 返回: 本条记录的IDDELAYED
    //======================================
    public function inserts($table, $bind)
    {
        $cols = array_keys($bind);
        $sql = "INSERT DELAYED INTO $table " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ')';
        $this->conn($sql, $bind);
        return $this->_insertId;
    }
    //======================================
    // 函数: REPLACE()
    // 功能: 插入一条记录
    // 参数: @$table 表名
    //	     @$bind  array
    // 返回: 本条记录的ID
    //======================================
    public function replace($table, $bind)
    {
        $cols = array_keys($bind);
        $sql = "REPLACE INTO $table " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ')';
        $this->conn($sql, $bind);
        return $this->_insertId;
    }
    //    ======================================
    // 函数: update()
    // 功能: 数椐更新
    // 参数: @$table 表名
    //	     @$bind  array
    //       @$where 条件 
    // 返回: 无
    //======================================
    public function update($table, $data, $where = false,$bind = '')
    {
        $this->_connect();
        if($where)$where = $this->_formatSql($where,$bind);
    	$set = array ();
        foreach ($data as $col => $val)
        {
            $set[] = "`$col` = :$col";
        }
        $sql = "UPDATE $table " . 'SET ' . implode(', ', $set) . (($where) ? " WHERE $where" : '');
        return $this->conn($sql, $data);
    }
	//    ======================================
    // 函数: adddate()
    // 功能: 数椐叠加
    // 参数: @$table 表名
    //	     @$bind  array
    //       @$where 条件 
    // 返回: 无
    //======================================
    public function adddate($table, $data, $where = false,$bind = '')
    {
        $this->_connect();
        if($where)$where = $this->_formatSql($where,$bind);
    	$set = array ();
        foreach ($data as $col => $val)
        {
            $set[] = "`$col` = $col + ".(int)$val;
        }
        $sql = "UPDATE $table " . 'SET ' . implode(', ', $set) . (($where) ? " WHERE $where" : '');
        $this->conn($sql);
    }
	//    ======================================
    // 函数: cutdate()
    // 功能: 数椐叠加
    // 参数: @$table 表名
    //	     @$bind  array
    //       @$where 条件 
    // 返回: 无
    //======================================
    public function cutdate($table, $data, $where = false,$bind = '')
    {
        $this->_connect();
        if($where)$where = $this->_formatSql($where,$bind);
    	$set = array ();
        foreach ($data as $col => $val)
        {
            $set[] = "`$col` = $col - ".(int)$val;
        }
        $sql = "UPDATE $table " . 'SET ' . implode(', ', $set) . (($where) ? " WHERE $where" : '');
        $this->conn($sql);
    }
    //======================================
    // 函数: delete()
    // 功能: 删除记录
    // 参数: @$table 表名
    //	     @$where 条件 
    // 返回: 无
    //======================================
    public function delete($table, $where = false, $bind='', $limit=1)
    {
        $sql = "DELETE FROM $table" . (($where) ? " WHERE $where" : '') . ($limit ? (' limit ' . intval($limit)) : '');;
        return $this->conn($sql,$bind);
    }
    //======================================
    // 函数: rsCount()
    // 功能: 得到数椐的行数
    // 参数: @$sql 
    // 返回: int
    //======================================
    public function rsCount($sql = '', $bind = '')
    {
        if ($sql)
            $this->conn($sql, $bind);
        if($this->_rs)
		{
			$this->_rsCount = mysql_num_rows($this->_rs);
			return $this->_rsCount;
		}else
		{
			return false;
		}
    }
    //======================================
    // 函数: rsArray()
    // 功能: 得到数椐数组
    // 参数: @$sql 
    // 返回: array
    //======================================
    public function rsArray($sql = '', $bind = '')
    {
        if ($sql)
            $this->conn($sql, $bind);
		if($this->_rs)
		{
			$this->_rsArray = mysql_fetch_assoc($this->_rs);
			return $this->_rsArray;
		}else
		{
			return false;
		}
    }
    //======================================
    // 函数: dateArray() fetchAll()
    // 功能: 得到数椐数组
    // 参数: @$sql 
    // 返回: array
    //======================================
    public function dataArray($sql = '', $bind = '', $cache = -1)
    {
        if($sql)
        	$this->_sql = $sql;
        if($cache != -1)
         	$this->_cache = $cache;
    	if($this->_cache)
        {
        	$rs = dc::get($this->_sql,'sql',$this->_cacheTime);//读cahce
        	if(empty($rs))
        	{
        		 $rs = $this->_getDataArray($sql, $bind);
        		 dc::set($this->_sql,$rs,'sql');				//写cahche
        	}
        	return $rs;
        }else 
        {
        	return $this->_getDataArray($sql, $bind);
        }
    }
    public function fetchAll($sql = '', $bind = '', $cache = -1)
    {
    	return $this->dataArray($sql, $bind,$cache);
    }
    public function fetchAssoc($sql = null, $bind = null, $cache = -1)
    {
        $result = $this->dataArray($sql, $bind,$cache);
        $data = array();
        foreach($result as &$v)
        {
        	$tmp = array_values($v);
        	$data[$tmp[0]] = $v;
        }
        return $data;
    }
    //======================================
    // 函数: rowRepeat()
    // 功能: 返回某个栏位元的内容是否重复
    // 参数: @$sql 
    // 返回: 有返回true 没有为false
    //======================================
    public function rowRepeat($table, $row, $msg) //表,栏位元,内容
    {
        if (!empty ($table) and !empty ($row) and !empty ($msg))
        {
            $rsArray = $this->rsArray("select count(" . $row . ") as c from " . $table . " where " . $row . " = ? ",$msg);
            if ($rsArray['c'])
            {
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }
    //======================================
    // 函数: insertID()
    // 功能: 返回最后一次插入的自增ID
    // 参数: 无
    //======================================
    public function insertID()
    {
        return $this->_insertId;
    }
    //======================================
    // 函数: queryTimes()
    // 功能: 返回查询的次数
    // 参数: 无
    // 返回: 查询的次数
    //======================================
    public function queryTimes()
    {
        return $this->_queryTimes;
    }
    public function getSql()
    {
        return $this->_sql;
    }
    //======================================
    // 函数: close()
    // 功能: 关闭打开的连接
    // 参数: 无
    // 返回: 无
    //======================================
    public function close()
    {
        if ($this->_conn) mysql_close($this->_conn);
    }

    public function result_to_array($result,$index=-1)
	{
	 if(!$this->hasResult($result)) return null;
	  $ret=array();
	  while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		  if($index!=-1) $ret[]=$row[$index];
		  else $ret[]=$row;
	  }
	  return $ret;
	} 

	public function sql_to_array($sql,$index=-1,$key='',$cachetime=0,$skey='sql')
	{ 
		global $dccache,$isadmincp;
		
		if($dccache) {	
			$key=$key?$key:md5($sql);
			if ($cachetime && !$_REQUEST['i']){
				$ret=dc::get($key,$cachetime,$skey);
			}
			
			if(!$ret || $_REQUEST['i']=='c' ||$isadmincp) {
				$result=$this->conn($sql);
				$ret=$this->result_to_array($result,$index);
				if ($cachetime || $_REQUEST['i']=='c' ||$isadmincp) dc::put($key,$ret,$skey);
			}
		}else{
			$result=$this->conn($sql);
			$ret=$this->result_to_array($result,$index);		
		}
		return $ret;
	} 


    public function num_rows($query){
         $query = mysql_num_rows($query);
         return $query;
      }
    public function hasResult($result)
	{
		if($result && (mysql_num_rows($result) > 0))
			return true;
		else
			return false;
	}
} //end class
?>
