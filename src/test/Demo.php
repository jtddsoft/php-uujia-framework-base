<?php


namespace uujia\framework\base\test;


use uujia\framework\base\BaseService;
use uujia\framework\base\common\Base;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Aop\Vistor\AopProxyVisitor;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Redis\RedisProvider;
use uujia\framework\base\common\lib\Reflection\CodeParser;
use uujia\framework\base\common\lib\Reflection\Reflection;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\Log;
use uujia\framework\base\common\RedisDispatcher;
use uujia\framework\base\common\Result;
use uujia\framework\base\common\Runner as Ru;
use uujia\framework\base\common\traits\InstanceTrait;
use uujia\framework\base\test\aop\AopCacheDataProviderTest;
use uujia\framework\base\test\EventTest;
use uujia\framework\base\UU;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;

class Demo extends BaseService {
	use InstanceTrait;
	use TT;
	
	/**
	 * @var Ru
	 * @AutoInjection(name = "Ru")
	 */
	private $_runnerObj;
	
	protected $_x = 2;
	protected $_y = 4;
	
	public function xxx() {
		// list($this->_x, $this->_y) = [$this->_y, $this->_x];
		$this->setX($this->getX() ^ $this->getY());
		$this->setY($this->getY() ^ $this->getX());
		$this->setX($this->getX() ^ $this->getY());
	}
	
	/**
	 * @return int
	 */
	public function getX(): int {
		return $this->_x;
	}
	
	/**
	 * @param int $x
	 */
	public function setX(int $x) {
		$this->_x = $x;
	}
	
	/**
	 * @return int
	 */
	public function getY(): int {
		return $this->_y;
	}
	
	/**
	 * @param int $y
	 */
	public function setY(int $y) {
		$this->_y = $y;
	}
	
	public function __construct() {
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		// $fileMQTTConfig = __DIR__ . '/config/mq_config.php';
		//
		// $arrMQTTConfig = [];
		// if (file_exists($fileMQTTConfig)) {
		// 	$arrMQTTConfig = include $fileMQTTConfig;
		//
		// 	/** @var $logObj Log */
		// 	$logObj = UU::C(Log::class);
		// 	$logObj->getMqttObj()->config($arrMQTTConfig['mqtt']);
		// 	$logObj->setEnabledMQTT(true);
		//
		// }
		
		// 初始化容器
		$this->initContainer();
	}
	
	/**
	 * 初始化容器
	 */
	public function initContainer() {
		// UU::C([
		// 	      Config::class,
		// 	      ErrorCodeList::class,
		// 	      MQCollection::class,
		// 	      Log::class,
		// 	      Result::class,
		// 	      Base::class,
		// 	      Event::class,
		//       ]);
		
		/** @var $configObj ConfigManagerInterface */
		$configObj = $this->getConfig()->getConfigManagerObj(); //UU::C(Config::class);
		$configObj->path(__DIR__ . '/config/error_code.php', '', 99);
		echo microtime(true) . " a\n";
		$paths = glob(__DIR__ . "/config/*_config.php", GLOB_BRACE);
		$configObj->path($paths);
		echo microtime(true) . " b\n";
		/** @var AopProxyFactory $aopProxyFactoryObj */
		$aopProxyFactoryObj = $this->getAopProxyFactory();
		echo microtime(true) . " c\n";
		$aopConfig = $configObj->loadValue('aop.aop');
		if (!empty($aopConfig['cache_path']) && !empty($aopConfig['cache_namespace'])) {
			$aopProxyFactoryObj->setProxyClassFilePath($aopConfig['cache_path']);
			$aopProxyFactoryObj->setProxyClassNameSpace($aopConfig['cache_namespace']);
		}
		echo microtime(true) . " d\n";
		/** @var CacheDataManagerInterface $cacheDataMgr */
		$cacheDataMgr = $this->getCacheDataManager();
		
		$cacheDataMgr->setCacheKeyPrefix(['app']);
		echo microtime(true) . " e\n";
		$this->boot(function () {
			$this->aopProviderReg();
			$this->eventProviderReg();
		});
		echo microtime(true) . " f\n";
		/** @var EventTest $a */
		$a = UU::C(EventTest::class);
		$b = $a->ok();
		
		// echo $a->test() . "\n";
		 $a->test();
		
		
		// echo microtime(true) . " g\n";
		// $aopProxyFactoryObj->setClassName(EventTest::class);
		// $refClass = new Reflection($aopProxyFactoryObj->getClassName());
		// $aopProxyFactoryObj->setReflectionClass($refClass);
		// $aopProxyFactoryObj->getReflectionClass()->load();
		// $aopProxyFactoryObj->buildProxyClassCacheFile();
		// echo microtime(true) . " h\n";
		
		
		// // $file = APP_PATH . '/Test.php';
		// $file = __DIR__ . '/EventTest.php';
		// $code = file_get_contents($file);
		//
		// $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		// $ast = $parser->parse($code);
		//
		// $traverser = new NodeTraverser();
		// $className = EventTest::class;
		// $proxyId = uniqid();
		// $visitor = new AopProxyVisitor($className, $proxyId);
		// $traverser->addVisitor($visitor);
		// $proxyAst = $traverser->traverse($ast);
		// if (!$proxyAst) {
		// 	throw new \Exception(sprintf('Class %s AST optimize failure', $className));
		// }
		// $printer = new Standard();
		// $proxyCode = $printer->prettyPrint($proxyAst);
		//
		// echo $proxyCode;
		
	}
	
	public function test() {
		// return glob(__DIR__ . "/../config/*_config.php", GLOB_BRACE);
		// return UU::C(Base::class)->rt()->ok();
		
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisDispatcher()->getRedisObj();
		if ($this->getRedisDispatcher()->getRedisProviderObj()->isErr()) {
			$this->getResult()->setLastReturn($this->getRedisDispatcher()->getRedisProviderObj()->getLastReturn());
			return $this->getResult()->rt()->return_error();
		}
		echo $this->tt('t') . "\n";
		echo microtime(true) . " aa1\n";
		$redis->set('aaa', 'cccc');
		echo microtime(true) . " aa2\n";
		return UU::C(Base::class)->ok();
	}
	
	public function subscribeRabbitMQ() {
		$mq = $this->getMQCollection()->getRabbitMQObj();
		$mq->connect()
		   ->queue(Log::RABBITMQ_QUEUE)
		   ->exchange(Log::RABBITMQ_EXCHANGE)
		   ->routingKey(Log::RABBITMQ_ROUTING_KEY)
		   ->routingKeyBinding(Log::RABBITMQ_ROUTING_KEY_BINDING)
		   ->setCallbackSubscribe(function ($body, $envelope, $queue) {
			   /** @var $envelope \AMQPEnvelope */
			   /** @var $queue \AMQPQueue */
			   echo $body . "\n";
		   })
		   ->subscribe();
		
	}
	
	public function subscribeMQTT() {
		$mq = $this->getMQCollection()->getMQTTObj();
		$mq->topics('Logger_2019')
		   ->clientId('Logger2019')
		   ->connect()
		   ->setCallbackSubscribe(function ($message) {
			   // echo json_encode($message) . "\n";
			   var_dump($message);
		   })
		   ->subscribe();
		
	}
	
	public function publishMQTT() {
		$mq = $this->getMQCollection()->getMQTTObj();
		$mq->topics('Logger_2019')
		   ->clientId('Logger20191')
			// ->connect()

           ->publish('111111111222222222');
		
	}
	
	public function event() {
		$event = $this->getEvent();
		$event->listen('a#*', function ($param) {
			// echo Json::je($param);
			
			return $this->getResult()->ok();
		});
		
		return $event->trigger('a', [1]);
	}
	
	private function funA($a) {
		// $a = 1;
		for ($i = 0; $i < $a; $i++) {
			yield $i;
		}
	}
	
	public function testYield() {
		foreach ($this->funA(2) as $item) {
			echo 'do Demo::testYield=' . $item . "\n";
		}
	}
	
	public function eventProviderReg() {
		/** @var CacheDataManagerInterface $cacheDataMgr */
		$cacheDataMgr = $this->getCacheDataManager();
		
		// $cacheDataMgr->setCacheKeyPrefix(['app']);
		
		/** @var EventCacheDataProviderTest $eventCacheDataProvider */
		$eventCacheDataProvider = UU::C(EventCacheDataProviderTest::class);
		
		$cacheDataMgr->regProvider(CacheConstInterface::DATA_PROVIDER_KEY_EVENT, $eventCacheDataProvider);
	}
	
	public function tiggerEvent() {
		// $eventDispatcher = $this->getEventDispatcher();
		// $eventDispatcher->dispatch(UU::C(EventTest::class));
		/** @var EventTest $eventTest */
		$eventTest = UU::C(EventTest::class);
		$re = $eventTest->addAfter();
		return $re;
	}
	
	public function aopProviderReg() {
		/** @var CacheDataManagerInterface $cacheDataMgr */
		$cacheDataMgr = $this->getCacheDataManager();
		
		/** @var AopCacheDataProviderTest $aopCacheDataProvider */
		$aopCacheDataProvider = UU::C(AopCacheDataProviderTest::class);
		
		$cacheDataMgr->regProvider(CacheConstInterface::DATA_PROVIDER_KEY_AOP, $aopCacheDataProvider);
	}
	
}

trait TT {
	public function tt($ttt) {
		$x = function () use ($ttt) {
			return parent::$ttt();
		};
		return $x();
	}
}