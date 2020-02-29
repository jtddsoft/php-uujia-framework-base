<?php


use uujia\framework\base\common\Base;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\UU;

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
			
			var_dump(UU::C(Base::class)->rt()->ok());
			
			
			break;
			
		case 'mqs':
			$demo = new \uujia\framework\base\test\Demo();
			
			$demo->subscribeRabbitMQ();
			break;
			
		case 'config':
			$config = new \uujia\framework\base\test\ConfigTest();
			$config->toString();
			break;
		
		case 'err':
			$err = new \uujia\framework\base\test\ErrorCodeListTest();
			$err->toString();
			break;
			
		case 'di':
			// // 反射获取类的构造函数
			// $refMethod = new ReflectionMethod(\uujia\framework\base\common\Log::class, '__construct');
			// // 获取构造函数参数列表
			// $params = $refMethod->getParameters();
			//
			// foreach ($params as $key => $param) {
			// 	// if ($param->isPassedByReference()) {
			// 	// 	$re_args[$key] = &$args[$key];
			// 	// } else {
			// 	// 	$re_args[$key] = $args[$key];
			// 	// }
			//
			// 	// 如果有类型约束 并且是个类 就构建这个依赖
			// 	if ($param->hasType() && $param->getClass() !== null) {
			// 		echo $param->getClass()->name . "\n";
			// 	}
			// }
			
			// sleep(100);
			$demo = new \uujia\framework\base\test\Demo();
			
			foreach (UU::getContainer() as $key => $item) {
				echo $item->getNameInfo()['name'] . " " . $item->getNameInfo()['intro'] . "\n"; // . dump($item);
			}
			
			break;
			
		case 'p':
			$total = 100;
			for ($i = 1; $i <= $total; $i++) {
				printf("progress: [%-50s] %d%% Done\r", str_repeat('#', $i / $total * 50), $i / $total * 100);
				usleep(10000);
			}
			echo "\n";
			echo "Done!\n";
			break;
			
		case 'calls':
			\uujia\framework\base\test\Demo::test();
			break;
			
		case 'event':
			$demo = new \uujia\framework\base\test\Demo();
			
			echo Json::je($demo->event());
			break;
	}
	
}

