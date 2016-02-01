<?php
/**
 * @name:	class.Core.php
 * @desc: 	Core类，主要用于配置变量及一些实例对象的调度
 * @author:	zhys9(jingki) @ 2008-6-3
 * @modified   Melon @ 2010-04-26
 */
class Core {
    /**
     * 
     * @var Db
     */
    public static $db = array ();
    /**
     * 
     * @var Memd
     */
    public static $mem = array ();
    
    /**
     * @access public
     * @var Wc类对象
     * @example 
     * Core::InitWc();
		$valueArray = array(
				'id'=>Core::$vars['id'],
				'page'=>Core::$vars['page'],
				'sid'=>SID,
			);
		$wc_set['cacheTime'] = 300;
		$wc_set['pageName'] = "页面名称";//可选  	
		Wc::cache($valueArray,$wc_set,$_GET['dy']);
     */
    
    public static $wc = '';
    
    public static $configs = array ();
    
    public static $vars = array ();
    
    /**
     * @name Startup
     * @author zhys9
     * @desc 初始化数据库连接
     * @param array $keys 需要初始化哪些数据库
     * @return void
     *
     */
    public static function InitDb($keys = array()) {
        $p = &Core::$configs ['db'];
        if (empty ( $keys )) {
            foreach ( $p as $k => &$v ) {
                if (isset ( Core::$db [$k] ))
                    continue;
                Core::$db [$k] = new Db ( $v, array ('_charset' => $v ['_charset'] ) );
            }
        } else if (is_array ( $keys )) {
            foreach ( $keys as $v ) {
                if (isset ( Core::$db [$v] ))
                    continue;
                Core::$db [$v] = new Db ( $p [$v], array ('_charset' => $p [$v] ['_charset'] ) );
            }
        } else if (is_string ( $keys ) && ! isset ( Core::$db [$keys] )) {
            if (! isset ( $p [$keys] ))
                return;
            Core::$db [$keys] = new Db ( $p [$keys], array ('_charset' => $p [$keys] ['_charset'] ) );
        }
    
    }
    
    /**
     * @name InitMemCache
     * @author zhys9
     * @desc 初始化MemCache连接
     * @param array $keys 需要初始化哪些MemCache
     * @return void
     *
     */
    public static function InitMemCache($keys = array()) {
        load_cfg ( 'Mem' ); //load configure
        $p = &Core::$configs ['mem'];
        if (empty ( $keys )) {
            foreach ( $p as $k => &$v ) {
                if (isset ( Core::$mem [$k] )) {
                    continue;
                }
                Core::$mem [$k] = new Mem ( $v ['server'] );
            }
        } else if (is_array ( $keys )) {
            foreach ( $keys as $v ) {
                if (isset ( Core::$mem [$v] ) || ! isset ( $p [$v] ))
                    continue;
                Core::$mem [$v] = new Mem ( $p [$v] ['server'] );
            }
        } else if (is_string ( $keys ) && ! isset ( Core::$mem [$keys] )) {
            if (! isset ( $p [$keys] ))
                return;
            Core::$mem [$keys] = new Mem ( $p [$keys] ['server'] );
        }
    }
    
    /**
     * @name InitMemCache
     * @author Melon
     * @desc 初始化MemCached连接
     * @param array $keys 需要初始化哪些MemCache
     * @return void
     *
     */
    public static function InitMemdCache($keys = array()) {
        load_cfg ( 'Mem' ); //load configure
        $p = & Core::$configs ['memd'];
        if (empty ( $keys )) {
            foreach ( $p as $k => &$v ) {
                if (isset ( Core::$mem [$k] )) {
                    continue;
                }
                Core::$mem [$k] = new Memd ( $v ['server'], $v ['option'] );
            }
        } else if (is_array ( $keys )) {
            foreach ( $keys as $v ) {
                if (isset ( Core::$mem [$v] ) || ! isset ( $p [$v] ))
                    continue;
                Core::$mem [$v] = new Memd ( $p [$v] ['server'], $p [$v] ['option'] );
            }
        } else if (is_string ( $keys ) && ! isset ( Core::$mem [$keys] )) {
            if (! isset ( $p [$keys] ))
                return;
            Core::$mem [$keys] = new Memd ( $p [$keys] ['server'], $p [$keys] ['option'] );
        }
    }
}

 
 
 
 
 
 
 