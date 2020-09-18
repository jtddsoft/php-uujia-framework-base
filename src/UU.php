<?php

namespace uujia\framework\base;


use uujia\framework\base\common\Config;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Aop\Cache\AopProxyCacheDataProvider;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Reflection\CachedReader;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\RedisDispatcher;
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
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function boot(string $appName = 'app', array $configPaths = [], \Closure $aopEnabledBeforeCallBack = null) {
		/** @var UU $me */
		$me = static::getInstance();
		
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
				return false;
			}
		}
		
		$me->getContainer()
		   ->setAopEnabled($_aopEnabled);
		
		// ->setAopIgnore($_aopIgnore);
		
		return true;
	}
	
	/**
	 * 返回从容器中获取对象实例
	 *
	 * @param string|array  $objName
	 * @param null|\Closure $obj
	 *
	 * @return mixed
	 */
	public static function C($objName, $obj = null) {
		/** @var UU $me */
		$me = static::getInstance();
		
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