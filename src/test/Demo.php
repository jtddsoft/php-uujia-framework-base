<?php


namespace uujia\framework\base\test;


use uujia\framework\base\BaseService;
use uujia\framework\base\common\Base;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManager;
use uujia\framework\base\common\lib\Redis\RedisProvider;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\Log;
use uujia\framework\base\common\Redis;
use uujia\framework\base\common\Runner as Ru;
use uujia\framework\base\common\traits\InstanceTrait;
use uujia\framework\base\UU;
use uujia\framework\base\common\lib\Annotation\AutoInjection;

class Demo extends BaseService {
	use InstanceTrait;
	
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
		
		/** @var $configObj ConfigManager */
		$configObj = $this->getConfig()->getConfigManagerObj(); //UU::C(Config::class);
		$configObj->path(__DIR__ . '/config/error_code.php', '', 99);
		
		$paths = glob(__DIR__ . "/config/*_config.php", GLOB_BRACE);
		$configObj->path($paths);
		
		// 获取容器配置container_config
		$_containerConfig = $configObj->loadValue('container.container');
		$_containerAlias  = $_containerConfig['alias'] ?? [];
		$_containerAs     = $_containerConfig['as'] ?? [];
		
		if (!empty($_containerAlias) || !empty($_containerAs)) {
			// $_containerObj = $this->getContainer();
			// $_containerObj->list()->setAlias($_containerAlias);
			$this->getContainer()
			     ->list()
			     ->setAlias($_containerAlias)
			     ->setAs($_containerAs);
		}
		
		$this->getRedis()
			// ->setRedisProviderObj(new RedisProvider())
			 ->loadConfig();
	}
	
	public function test() {
		// return glob(__DIR__ . "/../config/*_config.php", GLOB_BRACE);
		// return UU::C(Base::class)->rt()->ok();
		
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedis()->getRedisObj();
		if ($this->getRedis()->getRedisProviderObj()->isErr()) {
			$this->getResult()->setLastReturn($this->getRedis()->getRedisProviderObj()->getLastReturn());
			return $this->getResult()->rt()->return_error();
		}
		
		$redis->set('aaa', 'cccc');
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
		
		$cacheDataMgr->setCacheKeyPrefix(['app']);
		
		/** @var EventCacheDataProviderTest $eventCacheDataProvider */
		$eventCacheDataProvider = UU::C(EventCacheDataProviderTest::class);
		
		$cacheDataMgr->regProvider(CacheConstInterface::DATA_PROVIDER_KEY_EVENT, $eventCacheDataProvider);
	}
	
	public function tiggerEvent() {
		// $eventDispatcher = $this->getEventDispatcher();
		// $eventDispatcher->dispatch(UU::C(EventTest::class));
		/** @var EventTest $eventTest */
		$eventTest = UU::C(EventTest::class);
		$re = $eventTest->addBefore();
		return $re;
	}
	
}