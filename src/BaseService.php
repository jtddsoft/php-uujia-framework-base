<?php

namespace uujia\framework\base;

use smarket3\extensions\backGroundTask\status;
use uujia\framework\base\common\Base;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\ErrorConfig;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Aop\Cache\AopProxyCacheDataProvider;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Event\EventDispatcher;
use uujia\framework\base\common\lib\Log\Logger;
use uujia\framework\base\common\lib\MQ\MQCollection;
use uujia\framework\base\common\lib\Reflection\CachedReader;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\Log;
use uujia\framework\base\common\MQ;
use uujia\framework\base\common\RedisDispatcher;
use uujia\framework\base\common\Result;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\Runner;
use uujia\framework\base\common\traits\NameTrait;

class BaseService {
	use NameTrait;
	
	public function __construct() {
		$this->init();
	}
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
		
		// // 实例化Config
		// UU::C(Config::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Config();
		// 	// $c->cache(ErrorCodeList::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 设置对象准实例化 实例化只能调用一次 之后使用直接UU::C(ErrorCodeList::class)->dosomething()
		// UU::C(ErrorCodeList::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new ErrorCodeList($c->get(Config::class));
		// 	// $c->cache(ErrorCodeList::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 实例化MQTT
		// UU::C(MQCollection::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new MQCollection($c->get(Config::class));
		// 	// $c->cache(MQTT::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		// // 实例化Log
		// UU::C(Log::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Log($c->get(Config::class), $c->get(MQCollection::class));
		// 	// $c->cache(Log::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 实例化Result
		// UU::C(Result::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Result($c->get(ErrorCodeList::class), $c->get(Log::class));
		// 	// $c->cache(Result::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 实例化Base
		// UU::C(Base::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Base($c->get(Result::class));
		// 	// $c->cache(Base::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = static::class;
		$this->name_info['intro'] = '基础服务类';
	}
	
	/**
	 * @return Container
	 */
	public function getContainer(): Container {
		return UU::getInstance()->getContainer();
	}
	
	/**
	 * @return ErrorConfig
	 */
	public function getErrorCodeList() {
		return UU::C(ErrorConfig::class);
	}
	
	/**
	 * @return MQCollection
	 */
	public function getMQCollection() {
		return UU::C(MQCollection::class);
	}
	
	/**
	 * @return MQ
	 */
	public function getMQ() {
		return UU::C(MQ::class);
	}
	
	/**
	 * @return Logger
	 */
	public function getLogger() {
		return UU::C(Logger::class);
	}
	
	/**
	 * @return Log
	 */
	public function getLog() {
		return UU::C(Log::class);
	}
	
	/**
	 * @return Result
	 */
	public function getResult() {
		return UU::C(Result::class);
	}
	
	/**
	 * @return ConfigManagerInterface
	 */
	public function getConfigManager() {
		return UU::C(ConfigManagerInterface::class);
	}
	
	/**
	 * @return Config
	 */
	public function getConfig() {
		return UU::C(Config::class);
	}
	
	/**
	 * @return Base
	 */
	public function getBase() {
		return UU::C(Base::class);
	}
	
	/**
	 * @return Event
	 */
	public function getEvent() {
		return UU::C(Event::class);
	}
	
	/**
	 * @return RedisDispatcher
	 */
	public function getRedisDispatcher() {
		return UU::C(RedisDispatcher::class);
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedis() {
		return $this->getRedisDispatcher()->getRedisObj();
	}
	
	/**
	 * @return CacheDataManagerInterface
	 */
	public function getCacheDataManager() {
		return UU::C(CacheDataManagerInterface::class);
	}
	
	/**
	 * @return EventDispatcher
	 */
	public function getEventDispatcher() {
		return UU::C(EventDispatcher::class);
	}
	
	/**
	 * @return ServerRouteManager
	 */
	public function getServerRouteManager() {
		return UU::C(ServerRouteManager::class);
	}
	
	/**
	 * Date: 2020/8/13
	 * Time: 23:58
	 *
	 * @return AopProxyFactory
	 */
	public function getAopProxyFactory() {
		return UU::C(AopProxyFactory::class);
	}
	
	/**
	 * Date: 2020/9/16
	 * Time: 11:13
	 *
	 * @return Runner
	 */
	public function getRunner() {
		return UU::C(Runner::class);
	}
	
	public function t() {
		return 112233;
	}
}