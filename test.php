<?php

use uujia\framework\base\common\Base;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\lib\Reflection\Reflection as UUReflection;
use uujia\framework\base\UU;
use uujia\framework\base\common\Config;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	include __DIR__ . '/vendor/autoload.php';
	
	echo 'Input command: ';
	$command = trim(fgets(STDIN));
	
	$t1 = microtime(true);
	
	switch ($command) {
		case 'demo':
			// $demo = new \uujia\framework\base\test\Demo();
			$demo = UU::C(\uujia\framework\base\test\Demo::class);
			
			for ($i = 0; $i < 1; $i++) {
				var_dump($demo->test());
				// $demo->test();
			}
			
			// var_dump(UU::C(Base::class)->rt()->ok());
			// echo json_encode(UU::C(Base::class)->ok(), JSON_UNESCAPED_UNICODE) . "\n";
			var_dump(UU::C(Base::class)->ok());
			// var_dump($demo);
			// var_dump(UU::C(Config::class));
			$t2 = microtime(true);
			$t0 = $t2 - $t1;
			echo "startTime: {$t1}, endTime: {$t2}. t: {$t0}\n";
			break;
		
		case 'mqs':
			$demo = new \uujia\framework\base\test\Demo();
			
			$demo->subscribeRabbitMQ();
			break;
		
		case 'mqst':
			$demo = new \uujia\framework\base\test\Demo();
			
			$demo->subscribeMQTT();
			break;
		
		case 'mqss':
			$demo = new \uujia\framework\base\test\Demo();
			
			$demo->publishMQTT();
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
		
		case 'evt':
			echo \uujia\framework\base\common\consts\CacheConstInterface::DATA_PROVIDER_KEY_EVENT;
			break;
		
		case 'pcre':
			$ee = 'app:evtl:app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca:tmp';
			preg_match_all(EventName::PCRE_NAME_FULL, $ee, $m, PREG_SET_ORDER);
			var_dump($m);
			break;
		
		case 'pcre2':
			$ee = 'onAdd1XX';
			preg_match_all(\uujia\framework\base\common\lib\Event\EventHandle::PCRE_FUNC_LISTENER_NAME, $ee, $m, PREG_SET_ORDER);
			var_dump($m);
			break;
		
		case 'anno':
			$refObj = new UUReflection(\uujia\framework\base\test\EventTest::class, '', UUReflection::ANNOTATION_OF_CLASS);
			$refObj->load();
			
			$_evtListener = $refObj
				->annotation(\uujia\framework\base\common\lib\Annotation\EventListener::class)
				->getAnnotationObjs();
			
			$_evtTrigger = $refObj
				->annotation(\uujia\framework\base\common\lib\Annotation\EventTrigger::class)
				->getAnnotationObjs();
			
			var_dump($_evtListener);
			var_dump($_evtTrigger);
			
			break;
			
		case 'xxx':
			$demo = new \uujia\framework\base\test\Demo();
			$demo->xxx();
			var_dump($demo);
			break;
	}
	
}

