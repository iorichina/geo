<?php
if (! defined ( 'IN_SYS' )) {
	header ( "HTTP/1.1 404 Not Found" );
	die ();
}

Core::$configs ['mem'] = array ();

/**
 * new group *
 */
/**
 * memcache servers configure
 */
Core::$configs ['memd'] ['tt'] ['option'] = array (
		'compression' => FALSE 
);
Core::$configs ['memd'] ['tt'] ['server'] = array (
		array (
				'host' => '10.11.80.39',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.39',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.40',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.40',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.41',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.41',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.42',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.42',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.43',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.43',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.44',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.44',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.45',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.45',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.46',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.46',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.47',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.47',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.48',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.48',
				'port' => 20001,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.49',
				'port' => 20000,
				'weight' => 75 
		),
		array (
				'host' => '10.11.80.49',
				'port' => 20001,
				'weight' => 75 
		) 
);