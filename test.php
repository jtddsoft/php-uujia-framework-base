<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	include __DIR__ . '/vendor/autoload.php';
	
	$demo = new uujia\framework\base\test\Demo();
	
	var_dump($demo->test());
	
}

