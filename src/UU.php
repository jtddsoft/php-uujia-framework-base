<?php

namespace uujia\framework\base;


use uujia\framework\base\common\Base;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\Console;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\ErrorConfig;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\event\EventRunnerStatus;
use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Aop\Cache\AopProxyCacheDataProvider;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManager;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Event\EventDispatcher;
use uujia\framework\base\common\lib\Log\Logger;
use uujia\framework\base\common\lib\MQ\MQCollection;
use uujia\framework\base\common\lib\Reflection\CachedReader;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\Log;
use uujia\framework\base\common\MQ;
use uujia\framework\base\common\RedisDispatcher;
use uujia\framework\base\common\Result;
use uujia\framework\base\common\Runner;
use uujia\framework\base\common\traits\InstanceTrait;
use uujia\framework\base\common\traits\NameTrait;

/**
 * Class UU
 *
 * @package uujia\framework\base
 */
class UU {
	use NameTrait;
	use InstanceTrait;
	
	/**
	 * @var Container
	 */
	protected $_container;
	
	/**
	 * UU constructor.
	 * 依赖Container
	 *
	 * @param null|Container|mixed $container
	 */
	public function __construct($container = null) {
		// self::$_container = new Container(new TreeFunc()); // $this
		// self::$_container = $container ?? Container::getInstance(new TreeFunc());
		// $this->_container = $container ?? Container::getInstance(new TreeFunc());
		// $this->_container = $container ?? new Container(new TreeFunc());
		$this->_container = $container ?? Container::getInstance();
		
		$this->init();
	}
	
	// /**
	//  * @return Container
	//  */
	// public static function getContainer() {
	// 	// $me = static::getInstance();
	//
	// 	return self::$_container;
	// }
	//
	// /**
	//  * @param Container $container
	//  */
	// public static function _setContainer($container) {
	// 	self::$_container = $container;
	// }
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = static::class;
		$this->name_info['intro'] = '容器总管';
	}
	
	/**
	 * 引导
	 *
	 * @param string   $appName
	 * @param array    $configPaths ['100' => [__DIR__ . '/config/error_code.php'], '99' => [...], ...]
	 * @param \Closure $aopEnabledBeforeCallBack
	 *
	 * @return UU
	 * @throws \ReflectionException
	 */
	public static function boot(string $appName = 'app',
	                            array $configPaths = [],
	                            \Closure $aopEnabledBeforeCallBack = null) {
		/** @var UU $me */
		$me = static::getInstance();
		
		/**
		 * Config应为最先加载项 不能让容器去创建 否则容器本身还有一些隐式依赖
		 * 而这些依赖需要加载配置项 而Config还没创建 只能使用默认配置 这就造成一部分无法使用正确的配置
		 */
		$me->getContainer()->set(ConfigManagerInterface::class, function (TreeFuncData $data, TreeFunc $it, Container $c) {
			$obj = new ConfigManager();
			return $obj;
		});
		
		$me->getContainer()->set(Config::class, function (TreeFuncData $data, TreeFunc $it, Container $c) {
			$obj = new Config($c->get(ConfigManagerInterface::class));
			return $obj;
		});
		
		/**
		 * 配置加载
		 * @var $configObj ConfigManagerInterface
		 */
		$configObj = $me->getContainer()
		                ->get(Config::class)
		                ->getConfigManagerObj();
		
		foreach ($configPaths as $weight => $paths) {
			$configObj->path($paths, '', $weight);
		}
		
		/**
		 * 运行者 负责一些全局变量参数之类
		 * @var $runnerObj RunnerManagerInterface
		 */
		$runnerObj = $me->getContainer()
		                ->get(Runner::class)
						->getRunnerManagerObj();
		
		$runnerObj->_setAppName($appName);
		
		// 获取容器配置container_config
		$_containerConfig = $configObj->loadValue('container');
		
		// container.container
		$_configContainer = $_containerConfig['container'];
		$_containerAlias  = $_configContainer['alias'] ?? [];
		$_containerAs     = $_configContainer['as'] ?? [];
		
		// container.aop
		$_configAop     = $_containerConfig['aop'];
		$_aopEnabled    = $_configAop['enabled'] ?? false;
		$_aopScanParent = $_configAop['scan']['parent'] ?? false;
		$_aopIgnore     = $_configAop['ignore'] ?? [];
		
		// if (!empty($_containerAlias) || !empty($_containerAs)) {
		// $_containerObj = $this->getContainer();
		// $_containerObj->list()->setAlias($_containerAlias);
		$me->getContainer()
			// ->setAopEnabled($_aopEnabled)
           ->setAopScanParent($_aopScanParent)
		   ->setAopIgnore($_aopIgnore)
		   ->list()
		   ->setAlias($_containerAlias)
		   ->setAs($_containerAs);
		// }
		
		// server_config
		$_serverConfig = $configObj->loadValue('server');
		if (!empty($_serverConfig)) {
			$me->getContainer()
			   ->get(ServerRouteManager::class)
			   ->config($_serverConfig);
		}
		
		// Redis
		$me->getContainer()
		   ->get(RedisDispatcher::class)
			// ->setRedisProviderObj(new RedisProvider())
           ->loadConfig();
		
		// CachedReader
		$me->getContainer()
		   ->invoke(CachedReader::class);
		
		/**
		 * 数据缓存供应商管理
		 * @var CacheDataManagerInterface $cacheDataMgr
		 */
		$cacheDataMgr = $me->getContainer()
		                   ->get(CacheDataManagerInterface::class);
		
		$cacheDataMgr->setCacheKeyPrefix([$runnerObj->getAppName()]);
		
		/**
		 * @var AopProxyCacheDataProvider $aopProxyCacheDataProvider
		 */
		$aopProxyCacheDataProvider = $me->getContainer()
		                                ->get(AopProxyCacheDataProvider::class);
		
		$cacheDataMgr->regProvider(CacheConstInterface::DATA_PROVIDER_KEY_AOP_PROXY_CLASS, $aopProxyCacheDataProvider);
		
		/**
		 * Aop代理工厂（用于生成代理类）
		 * @var AopProxyFactory $aopProxyFactoryObj
		 */
		$aopProxyFactoryObj = $me->getContainer()
		                         ->get(AopProxyFactory::class);
		
		$aopConfig = $configObj->loadValue('aop.aop');
		if (!empty($aopConfig['cache_path']) && !empty($aopConfig['cache_namespace'])) {
			$aopProxyFactoryObj->setProxyClassFilePath($aopConfig['cache_path']);
			$aopProxyFactoryObj->setProxyClassNameSpace($aopConfig['cache_namespace']);
		}
		
		// 忽略boot调用者自身aop
		$me->getContainer()->addAopIgnore(static::class);
		
		if (!empty($aopEnabledBeforeCallBack)) {
			$res = call_user_func_array($aopEnabledBeforeCallBack, []);
			if ($res === false) {
				return $me;
			}
		}
		
		$me->getContainer()
		   ->setAopEnabled($_aopEnabled);
		
		// ->setAopIgnore($_aopIgnore);
		
		return $me;
	}
	
	/**
	 * Date: 2020/10/17
	 * Time: 16:26
	 *
	 * @return UU
	 */
	public static function bootAfter() {
		/** @var UU $me */
		$me = static::getInstance();
		
		// event bootAfter
		$eventRunnerStatus = $me->getContainer()->get(EventRunnerStatus::class);
		$eventRunnerStatus->bootAfter();
		
		return $me;
	}
	
	/**
	 * 返回从容器中获取对象实例
	 *
	 * @param string|array  $objName
	 * @param null|\Closure $obj
	 *
	 * @return mixed
	 */
	public static function C($objName = '', $obj = null) {
		/** @var UU $me */
		$me = static::getInstance();
		
		if (empty($objName) && empty($obj)) {
			return $me->getContainer();
		}
		
		// 【注意】如果为数组 则批量注入（并非是获取 只有为字符串类名时才是获取）
		if (is_array($objName)) {
			foreach ($objName as $key => $row) {
				$me->getContainer()->set($row, $obj);
			}
			
			return $me->getContainer();
		}
		
		if ($obj === null) {
			// 读取
			return $me->getContainer()->get($objName);
		} else {
			// 设置
			return $me->getContainer()->set($objName, $obj);
		}
	}
	
	
	/**
	 * @return ErrorConfig
	 */
	public static function getErrorCodeList() {
		return self::C(ErrorConfig::class);
	}
	
	/**
	 * @return MQCollection
	 */
	public static function getMQCollection() {
		return self::C(MQCollection::class);
	}
	
	/**
	 * @return MQ
	 */
	public static function getMQ() {
		return self::C(MQ::class);
	}
	
	/**
	 * @return Logger
	 */
	public static function getLogger() {
		return self::C(Logger::class);
	}
	
	/**
	 * @return Log
	 */
	public static function getLog() {
		return self::C(Log::class);
	}
	
	/**
	 * @return Result
	 */
	public static function getResult() {
		return self::C(Result::class);
	}
	
	/**
	 * @return ConfigManagerInterface
	 */
	public static function getConfigManager() {
		return self::C(ConfigManagerInterface::class);
	}
	
	/**
	 * @return Config
	 */
	public static function getConfig() {
		return self::C(Config::class);
	}
	
	/**
	 * @return Base
	 */
	public static function getBase() {
		return self::C(Base::class);
	}
	
	/**
	 * @return Event
	 */
	public static function getEvent() {
		return self::C(Event::class);
	}
	
	/**
	 * @return RedisDispatcher
	 */
	public static function getRedisDispatcher() {
		return self::C(RedisDispatcher::class);
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public static function getRedis() {
		return self::getRedisDispatcher()->getRedisObj();
	}
	
	/**
	 * @return CacheDataManagerInterface
	 */
	public static function getCacheDataManager() {
		return self::C(CacheDataManagerInterface::class);
	}
	
	/**
	 * @return EventDispatcher
	 */
	public static function getEventDispatcher() {
		return self::C(EventDispatcher::class);
	}
	
	/**
	 * @return ServerRouteManager
	 */
	public static function getServerRouteManager() {
		return self::C(ServerRouteManager::class);
	}
	
	/**
	 * Date: 2020/8/13
	 * Time: 23:58
	 *
	 * @return AopProxyFactory
	 */
	public static function getAopProxyFactory() {
		return self::C(AopProxyFactory::class);
	}
	
	/**
	 * Date: 2020/9/16
	 * Time: 11:13
	 *
	 * @return Runner
	 */
	public static function getRunner() {
		return self::C(Runner::class);
	}
	
	/**
	 * Date: 2020/10/15
	 * Time: 10:33
	 *
	 * @return Console
	 */
	public static function getConsole() {
		return self::C(Console::class);
	}
	
	/**
	 * @return Container
	 */
	public function getContainer() {
		return $this->_container;
	}
	
	/**
	 * @param Container $container
	 *
	 * @return $this
	 */
	public function setContainer($container) {
		$this->_container = $container;
		
		return $this;
	}
	
	
}