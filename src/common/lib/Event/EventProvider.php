<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\ListenerProviderInterface;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\consts\EventConstInterface;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Event\Cache\EventCacheData;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Event\Name\EventNameInterface;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Tree\TreeFuncData;

/**
 * Class EventProvider
 * 事件监听者供应商
 *  用于将对应事件监听者提供给事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventProvider extends BaseClass implements ListenerProviderInterface, CacheClassInterface {
	use CacheClassTrait;
	
	/**
	 * CacheDataManager对象
	 *
	 * @var CacheDataManager
	 */
	protected $_cacheDataManagerObj;
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface
	 */
	protected $_redisProviderObj;
	
	/**
	 * ServerRouteManager对象
	 *
	 * @var ServerRouteManager
	 */
	protected $_serverRouteManagerObj;
	
	/**
	 * 监听key前缀集合
	 *
	 * @var array
	 */
	protected $_cacheKeyListenPrefix = [];
	
	/**
	 * 事件列表
	 *  尽可能按需加载 触发到哪个事件就从缓存加载到列表
	 *
	 * @var TreeFunc
	 */
	protected $_list;
	
	// /**
	//  * 要触发的事件对象
	//  *  事件调度会传来EventHandle对象的触发形态
	//  * （EventHandle 主要取事件标识 addons.rubbish2.user.LoginBefore）
	//  *
	//  * @var EventHandle;
	//  */
	// protected $_eventHandle;
	
	/**
	 * 要触发的事件对象
	 *  事件调度会传来EventName对象
	 * （EventName 取事件标识 addons.rubbish2.user.login.before）
	 *
	 * @var EventName;
	 */
	protected $_eventNameObj;
	
	/**
	 * 事件缓存数据对象
	 *
	 * @var EventCacheData;
	 */
	protected $_eventCacheDataObj;
	
	/**
	 * 事件筛选器对象
	 *  用于查找匹配的监听对象
	 *
	 * @var EventFilter;
	 */
	protected $_eventFilterObj;
	
	/**
	 * 配置项
	 *
	 * @var array
	 */
	protected $_config = [];
	
	// /**
	//  * 最后一次key的搜索
	//  *
	//  * @var array
	//  */
	// protected $_lastKeys = [];
	
	
	/**
	 * EventProvider constructor.
	 *
	 * @param CacheDataManager|null       $cacheDataManagerObj
	 * @param RedisProviderInterface|null $redisProviderObj
	 * @param ServerRouteManager|null     $serverRouteManagerObj
	 * @param EventCacheData|null         $eventCacheDataObj
	 * @param EventFilter|null            $eventFilterObj
	 * @param array                       $cacheKeyListenPrefix
	 */
	public function __construct(CacheDataManager $cacheDataManagerObj = null,
	                            RedisProviderInterface $redisProviderObj = null,
	                            ServerRouteManager $serverRouteManagerObj = null,
	                            EventCacheData $eventCacheDataObj = null,
	                            EventFilter $eventFilterObj = null,
	                            $cacheKeyListenPrefix = []) {
		$this->_cacheDataManagerObj   = $cacheDataManagerObj;
		$this->_redisProviderObj      = $redisProviderObj;
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		
		$this->_eventCacheDataObj = $eventCacheDataObj;
		$this->_eventFilterObj    = $eventFilterObj;
		
		$this->_cacheKeyListenPrefix   = $cacheKeyListenPrefix;
		$this->_cacheKeyListenPrefix[] = EventConstInterface::CACHE_KEY_PREFIX_LISTENER;
		
		parent::__construct();
	}
	
	/**
	 * 为事件调度提供事件列表
	 *
	 * @param EventHandleInterface|object $event
	 *
	 * @inheritDoc
	 */
	public function getListenersForEvent(object $event): iterable {
		if (!($event instanceof EventNameInterface)) {
			return [];
		}
		
		$this->_setEventNameObj($event);
		
		yield from $this->_make();
	}
	
	/**
	 * 构建列表生成器
	 *  1、查缓存是否存在
	 *      1）存在 继续
	 *      2）不存在 构建参数存入缓存
	 */
	public function _make() {
		if (!$this->hasCache()) {
			// 不存在缓存 调起缓存数据管理器 收集数据传来
			$this->toCache();
		}
		
		// 读取缓存
		yield from $this->fromCache();
	}
	
	/**
	 * 读取缓存
	 *
	 * @inheritDoc
	 */
	public function fromCache() {
		// todo: 从触发的EventHandle中解出当前触发事件名
		$_evtNameObj = $this->getEventNameObj();
		
		$_isParsed = $_evtNameObj->isParsed();
		if (!$_isParsed) {
			// todo: 异常
			yield [];
		}
		
		$k = $_evtNameObj->makeEventName();
		
		// $k = $this->getCacheKey('*');
		
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisObj();
		
		// $iterator = null;
		// while(false !== ($keys = $redis->scan($iterator, $k, 1))) {
		// 	foreach($keys as $key) {
		// 		// echo $key . PHP_EOL;
		//
		// 	}
		// }
		
		// $reData = $redis->zrange($k, 0, -1);
		
		foreach ($this->scanCacheKey($k) as $_key) {
			$i = 0;
			while (!empty($cacheData = $redis->zrange($_key, $i, 10))) {
				$i++;
				
				yield from $this->makeCacheToServerParameter($_key, $cacheData);
			}
		}
	}
	
	/**
	 * 调用缓存管理器收集数据记入缓存
	 *
	 * @inheritDoc
	 */
	public function toCache() {
		// 调用缓存数据供应商
		// $this->getParent()
		//      ->getCacheDataManagerObj()
		//      ->getProviderList()
		//      ->getKeyDataValue(CacheConst::DATA_PROVIDER_KEY_EVENT);
		$this->getCacheDataProvider();
		
		return $this;
	}
	
	/**
	 * 缓存是否存在
	 *
	 * @return bool
	 */
	public function hasCache(): bool {
		// $k = $this->getCacheKey('*');
		//
		// /** @var \Redis|\Swoole\Coroutine\Redis $redis */
		// $redis = $this->getRedisProviderObj()->getRedisObj();
		// $iterator = null;
		// $reKeys = $redis->scan($iterator, $k, 1);
		//
		// // while(false !== ($keys = $redis->scan($iterator))) {
		// // 	foreach($keys as $key) {
		// // 		echo $key . PHP_EOL;
		// // 	}
		// // }
		// return !empty($reKeys);
		
		return $this->getEventFilterObj()
		            ->setRedisObj($this->getRedisObj())
		            ->setPrefix($this->getCacheKeyListenPrefix())
		            ->keyExist();
	}
	
	/**
	 * @inheritDoc
	 */
	public function clearCache() {
		// $k = $this->getCacheKey('*');
		//
		// /** @var \Redis|\Swoole\Coroutine\Redis $redis */
		// $redis = $this->getRedisObj();
		//
		// $iterator = null;
		// while(false !== ($keys = $redis->scan($iterator, $k, 20))) {
		// 	// foreach($keys as $key) {
		// 	// 	echo $key . PHP_EOL;
		// 	// }
		// 	$redis->del($keys);
		// }
		//
		// return $this;
		
		foreach ($this->getEventFilterObj()
		              ->setPrefix($this->getCacheKeyListenPrefix())
		              ->keyScan('*', 0) as $item) {
			$this->getRedisObj()->del($item);
		}
		
		return $this;
	}
	
	/**
	 * 将缓存构建到监听代理服务参数对象
	 *
	 * @param string $key
	 * @param array  $cacheData
	 *
	 * @return \Generator 事件监听代理EventListenerProxy
	 */
	private function makeCacheToServerParameter($key, $cacheData = []) {
		$_serverRouteManagerObj = $this->getServerRouteManagerObj();
		
		// 如果列表不存在对应的key 说明从未构建过
		if (!($this->getList()->has($key))) {
			$this->getList()->set($key, new TreeFunc());
			$evtItem = $this->getList()->get($key);
			
			// 构建
			foreach ($cacheData as $i => $item) {
				// if (!Json::isJson($item)) {
				// 	// todo: 错误处理
				// 	continue;
				// }
				//
				// $_dataCache = Json::decode($item);
				
				/**
				 * 加载缓存数据解析
				 *
				 * @var EventCacheDataInterface $cacheDataObj
				 */
				$cacheDataObj = $this->getEventCacheDataObj();
				$cacheDataObj->parse($item);
				
				/**
				 * 添加监听代理到事件列表
				 *  事件列表由事件名作为key 多个事件监听代理作为value列表项
				 *  {eventName1} => [
				 *      {eventListenerProxy1},
				 *      {eventListenerProxy2},
				 *      ...
				 *  ],
				 *  {eventName2}...
				 */
				$evtLPItem = new TreeFunc();
				$evtItem->_setLastNewItem($evtLPItem);
				
				$evtLPItem->getData()
				          ->set(function ($data, $it, $params) use ($cacheDataObj, $_serverRouteManagerObj) {
					          $_eventListenerProxyObj = new EventListenerProxy(clone $this->_eventNameObj,
					                                                           $_serverRouteManagerObj);
					          $_eventListenerProxyObj
						          ->_setContainer($this->getContainer())
						          ->loadCache($cacheDataObj);
					
					          return $_eventListenerProxyObj;
				          });
				
				$evtItem->add($evtLPItem);
				
				/** @var EventListenerProxy $evtLPObj */
				$evtLPObj = $evtLPItem->getData()->get();
				
				yield $evtLPObj;
			}
		} else {
			// 清空返回值
			$this->clearSPRet($key);
			
			$evtItem = $this->getList()->get($key);
			
			foreach ($evtItem as $i => $evtLPItem) {
				/** @var EventListenerProxy $evtLPObj */
				$evtLPObj = $evtLPItem->getData()->get();
				
				yield $evtLPObj;
			}
		}
		
		// foreach ($cacheData as $i => $item) {
		// 	if (!Json::isJson($item)) {
		// 		// todo: 错误处理
		// 		continue;
		// 	}
		//
		// 	$_dataCache = Json::decode($item);
		//
		// 	$this->getList()
		// 	     ->addKeyNewItemData($key,
		// 		     // subItem
		// 		     function ($data, $it, $params) use ($_dataCache, $_serverRouteManagerObj) {
		// 			     $_eventListenerProxyObj = new EventListenerProxy($_serverRouteManagerObj);
		// 			     $_eventListenerProxyObj
		// 				     ->setSPServerName($_dataCache[EventConstInterface::CACHE_SP_SERVERNAME] ?? '')
		// 				     ->setSPServerType($_dataCache[EventConstInterface::CACHE_SP_SERVERTYPE] ?? '')
		// 				     ->_setContainer($this->getContainer())
		// 				     ->make();
		//
		// 			     return $_eventListenerProxyObj;
		// 		     });
		//
		// 	// // item
		// 	// function ($data, $it, $params) {
		// 	//     // 获取汇总列表中所有配置
		// 	//     /** @var TreeFunc $it */
		// 	//     $it->cleanResults();
		// 	//
		// 	//     /**
		// 	//      * 遍历指定key下所有缓存供应商收集数据
		// 	//      */
		// 	//     $it->wForEach(function ($_item, $index, $me, $params) {
		// 	//      /** @var TreeFunc $_item */
		// 	//      /** @var TreeFunc $me */
		// 	//
		// 	//      $reEvtLP = $_item->getData()->get($params, false, false);
		// 	//
		// 	//      if (!($reEvtLP instanceof EventListenerProxy)) {
		// 	// 	     // todo: 类型不匹配 应该为事件监听代理对象
		// 	// 	     return false;
		// 	//      }
		// 	//
		// 	//      /** @var EventListenerProxyInterface $eventListenerProxy */
		// 	//      $eventListenerProxy = $reEvtLP;
		// 	//      $re                 = $eventListenerProxy->handle();
		// 	//
		// 	//      // Local返回值复制
		// 	//      $_item->getData()->setLastReturn($re);
		// 	//
		// 	//      // 加入到返回值列表
		// 	//      $me->setLastReturn($re);
		// 	//
		// 	//      if ($_item->getData()->isErr()) {
		// 	// 	     return false;
		// 	//      }
		// 	//
		// 	//      return true;
		// 	//     }, $params);
		// 	//
		// 	//     // return $this->ok();
		// 	//     return $it->getLastReturn();
		// 	// });
		//
		// 	yield $this
		// 		->getList()
		//
		// 		// set item
		// 		// 获取最后一次配置数据
		// 		->getLastSetItemData()
		//
		// 		// add subitem
		// 		// 从Data返回Item
		// 		->getParent()
		// 		// 获取最后一次新增的子项
		// 		->getLastNewItemData()
		// 		->get();
		// }
	}
	
	/**
	 * 获取拼接后的缓存key
	 *
	 * @param string $currKey 当前key
	 *
	 * @return string
	 */
	public function getCacheKey($currKey = '') {
		// // 前缀 + 起始key + 当前key = 最终使用key
		// $k = !empty($currKey) ? array_merge($this->getCacheKeyPrefix(), [$currKey]) : $this->getCacheKeyPrefix();
		//
		// return implode(':', $k);
		
		return $this->getEventFilterObj()
		            ->setPrefix($this->getCacheKeyListenPrefix())
		            ->getJointKey($currKey);
	}
	
	/**
	 * 遍历符合条件的缓存key
	 *
	 * @param string $currKey 当前key
	 *
	 * @return \Generator
	 */
	public function scanCacheKey($currKey = '*') {
		foreach ($this->getEventFilterObj()
		              ->setPrefix($this->getCacheKeyListenPrefix())
		              ->keyScan($currKey, 0) as $_key) {
			yield $_key;
		}
	}
	
	/**
	 * 清空返回值
	 *
	 * @param string $k
	 *
	 * @return $this
	 */
	public function clearSPRet($k) {
		if (empty($k)) {
			return $this;
		}
		
		$evtItem = $this->getList()->get($k);
		if (empty($evtItem)) {
			return $this;
		}
		
		$evtItem->forEach(function (&$item, $k, $me, $params) {
			/** @var TreeFunc $item */
			
			/** @var EventListenerProxyInterface $evtLP */
			$evtLP = $item->getDataValue();
			$evtLP->clearSPRet();
			
			return true;
		});
		
		return $this;
	}
	
	/**
	 * 获取列表
	 *
	 * @return TreeFunc
	 */
	public function getList(): TreeFunc {
		return $this->_list;
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 *
	 * @return TreeFuncData
	 */
	public function getListData(string $key): TreeFuncData {
		return $this->getList()->getData();
	}
	
	/**
	 * 获取列表项值
	 *
	 * @param string $key
	 *
	 * @return array|string|int|null
	 */
	public function getListDataValue(string $key) {
		return $this->getListValue($key)->getDataValue();
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 *
	 * @return TreeFunc
	 */
	public function getListValue(string $key): TreeFunc {
		return $this->getList()->get($key);
	}
	
	/**
	 * @return array
	 */
	public function getCacheKeyListenPrefix() {
		return $this->_cacheKeyListenPrefix;
	}
	
	/**
	 * @param array $cacheKeyPrefix
	 * @param bool  $isAddListenPrefix
	 *
	 * @return $this
	 */
	public function setCacheKeyListenPrefix(array $cacheKeyPrefix, $isAddListenPrefix = true) {
		$this->_cacheKeyListenPrefix = $cacheKeyPrefix;
		$isAddListenPrefix && $this->_cacheKeyListenPrefix[] = EventConstInterface::CACHE_KEY_PREFIX_LISTENER;
		
		return $this;
	}
	
	// /**
	//  * @return EventHandle
	//  */
	// public function getEventHandle(): EventHandle {
	// 	return $this->_eventHandle;
	// }
	//
	// /**
	//  * @param EventHandleInterface $eventHandle
	//  *
	//  * @return $this
	//  */
	// public function setEventHandle(EventHandle $eventHandle) {
	// 	$this->_eventHandle = $eventHandle;
	//
	// 	return $this;
	// }
	
	/**
	 * @return EventName
	 */
	public function getEventNameObj(): EventName {
		return $this->_eventNameObj;
	}
	
	/**
	 * @param EventName $eventNameObj
	 *
	 * @return $this
	 */
	public function _setEventNameObj(EventName $eventNameObj) {
		$this->_eventNameObj = $eventNameObj;
		
		return $this;
	}
	
	/**
	 * @return EventCacheData
	 */
	public function getEventCacheDataObj(): EventCacheData {
		return $this->_eventCacheDataObj;
	}
	
	/**
	 * @param EventCacheData $eventCacheDataObj
	 *
	 * @return $this
	 */
	public function setEventCacheDataObj(EventCacheData $eventCacheDataObj) {
		$this->_eventCacheDataObj = $eventCacheDataObj;
		
		return $this;
	}
	
	// /**
	//  * @return array
	//  */
	// public function getLastKeys(): array {
	// 	return $this->_lastKeys;
	// }
	//
	// /**
	//  * @param array $lastKeys
	//  *
	//  * @return $this
	//  */
	// public function setLastKeys(array $lastKeys) {
	// 	$this->_lastKeys = $lastKeys;
	//
	// 	return $this;
	// }
	
	/**
	 * @return RedisProviderInterface
	 */
	public function getRedisProviderObj() {
		return $this->_redisProviderObj;
	}
	
	/**
	 * @param RedisProviderInterface $redisProviderObj
	 *
	 * @return $this
	 */
	public function setRedisProviderObj($redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
		
		return $this;
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->getRedisProviderObj()->getRedisObj();
	}
	
	/**
	 * @return CacheDataManager
	 */
	public function getCacheDataManagerObj() {
		return $this->_cacheDataManagerObj;
	}
	
	/**
	 * @param CacheDataManager $cacheDataManagerObj
	 *
	 * @return $this
	 */
	public function setCacheDataManagerObj($cacheDataManagerObj) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		
		return $this;
	}
	
	/**
	 * @return ServerRouteManager
	 */
	public function getServerRouteManagerObj(): ServerRouteManager {
		return $this->_serverRouteManagerObj;
	}
	
	/**
	 * @param ServerRouteManager $serverRouteManagerObj
	 *
	 * @return $this
	 */
	public function setServerRouteManagerObj(ServerRouteManager $serverRouteManagerObj) {
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		
		return $this;
	}
	
	/**
	 * @return TreeFunc|null
	 */
	public function getCacheDataProvider() {
		$cdMgr      = $this->getCacheDataManagerObj();
		$cdProvider = $cdMgr->getProviderList()->get(CacheConstInterface::DATA_PROVIDER_KEY_EVENT);
		
		return $cdProvider;
	}
	
	/**
	 * @return EventFilter
	 */
	public function getEventFilterObj(): EventFilter {
		return $this->_eventFilterObj;
	}
	
	/**
	 * @param EventFilter $eventFilterObj
	 *
	 * @return $this
	 */
	public function setEventFilterObj(EventFilter $eventFilterObj) {
		$this->_eventFilterObj = $eventFilterObj;
		
		return $this;
	}
	
}