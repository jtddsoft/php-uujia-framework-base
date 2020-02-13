<?php


if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	include __DIR__ . '/vendor/autoload.php';
	
	echo 'Input command: ';
	$command = trim(fgets(STDIN));
	
	switch ($command) {
		case 'demo':
			$demo = new \uujia\framework\base\test\Demo();
			
			for($i = 0; $i < 1; $i++) {
				var_dump($demo->test());
			}
			break;
			
		case 'config':
			$config = new \uujia\framework\base\test\ConfigTest();
			$config->toString();
			break;
		
		case 'err':
			$err = new \uujia\framework\base\test\ErrorCodeListTest();
			$err->toString();
			break;
	}
	
}

