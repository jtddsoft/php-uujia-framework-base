<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	include __DIR__ . '/vendor/autoload.php';
	
	$demo = new \uujia\framework\base\test\Demo();
	
	for($i = 0; $i < 10; $i++) {
		var_dump($demo->test());
	}
	
	
}

