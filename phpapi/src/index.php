<?php
try {
	include './common.inc.php'; // 配置
	load_app ();
	
	$app = new $_REQUEST [ACTION] ();
	if ($_REQUEST [DO_METHOD] && method_exists ( $app, $_REQUEST [DO_METHOD] )) {
		$rs = $app->$_REQUEST [DO_METHOD] ();
	} elseif ($_REQUEST [DO_METHOD]) {
		throw new JException ( 'method not exists' );
	}
} catch ( JException $e ) {
	// echo $e->getCode (), ':', $e->getMessage ();
	// header ( 'HTTP/1.1 404 Not Found' );
	$e->ShowErrorMessage ( 1 );
}
 
