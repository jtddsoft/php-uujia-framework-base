<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\consts\CacheConst;
use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Utils\Json;

/**
 * Class EventProvider
 * 事件监听者供应商
 *  用于将对应事件监听者提供给事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventProvider extends BaseClass implements ListenerProviderInterface, CacheClassInterface {
	use CacheClassTrait;
	
	// 缓存key前缀
	const CACHE_KEY_PREFIX = 'event';
	
	/**
	 * @var EventDispatcher
	 */
	protected $_parent;
	
	protected $_cacheKeyPrefix = [];
	
	/**
	 * 事件列表
	 *  尽可能按需加载 触发到哪个事件就从缓存加载到列表
	 *
	 * @var TreeFunc
	 */
	protected $_list;
	
	/**
	 * 要触发的事件对象
	 *  事件调度会传来EventHandle对象的触发形态
	 * （EventHandle 主要取事件标识 addons.rubbish2.user.LoginBefore）
	 *
	 * @var EventHandleInterface;
	 */
	protected $_eventHandle;
	
	/**
	 * 配置项
	 *
	 * @var array
	 */
	protected $_config = [];
	
	/**
	 * 最后一次key的搜索
	 *
	 * @var array
	 */
	protected $_lastKeys = [];
	
	
	/**
	 * EventProvider constructor.
	 *
	 * @param null  $parent
	 * @param array $config 暂用做保留*
	 * @param array $cacheKeyPrefix
	 */
	public function __construct($parent = null, $config = [], $cacheKeyPrefix = []) {
		$this->_parent = $parent;
		$this->_config = $config;
		
		$this->_cacheKeyPrefix   = $cacheKeyPrefix;
		$this->_cacheKeyPrefix[] = self::CACHE_KEY_PREFIX;
		
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
		if (!($event instanceof EventHandleInterface)) {
			return [];
		}
		
		$this->setEventHandle($event);
		
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
		$this->getEventHandle();
		
		$k = $this->getCacheKey('*');
		
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
		
		foreach ($this->scanCacheKey('*') as $_key) {
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
		$this->getParent()
		     ->getCacheDataManagerObj()
		     ->getProviderList()
		     ->getKeyDataValue(CacheConst::DATA_PROVIDER_KEY_EVENT);
		
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
		
		return EventFilter::factory()
		                  ->setRedisObj($this->getRedisObj())
		                  ->setProfix($this->getCacheKeyPrefix())
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
		
		foreach (EventFilter::factory()
		                    ->setProfix($this->getCacheKeyPrefix())
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
	 * @return \Generator
	 */
	private function makeCacheToServerParameter($key, $cacheData = []) {
		$_serverRouteManagerObj = $this->getServerRouteManager();
		
		// 如果列表不存在对应的key 说明从未构建过
		if (!($this->getList()->has($key))) {
			$this->getList()->set($key, new TreeFunc());
			$evtItem = $this->getList()->get($key);
			
			// 构建
			foreach ($cacheData as $i => $item) {
				if (!Json::isJson($item)) {
					// todo: 错误处理
					continue;
				}
				
				$_dataCache = Json::decode($item);
				
				$evtLPItem = new TreeFunc();
				$evtItem->_setLastNewItem($evtLPItem);
				
				$evtLPItem->getData()
				        ->set(function ($data, $it, $params) use ($_dataCache, $_serverRouteManagerObj) {
					        $_eventListenerProxyObj = new EventListenerProxy($_serverRouteManagerObj);
					        $_eventListenerProxyObj
						        ->resetSP()
						        ->setSPServerName($_dataCache[EventConst::CACHE_SP_SERVERNAME] ?? '')
						        ->setSPServerType($_dataCache[EventConst::CACHE_SP_SERVERTYPE] ?? '')
						        ->setSPParam($_dataCache[EventConst::CACHE_SP__PARAM] ?? [])
						        ->_setContainer($this->getContainer())
						        ->make();
					
					        return $_eventListenerProxyObj;
				        });
				
				$evtItem->add($evtLPItem);
				
				yield $evtLPItem->getData()->get();
			}
		} else {
			$evtItem = $this->getList()->get($key);
			
			foreach ($evtItem as $i => $evtLPItem) {
				yield $evtLPItem->getData()->get();
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
		// 				     ->setSPServerName($_dataCache[EventConst::CACHE_SP_SERVERNAME] ?? '')
		// 				     ->setSPServerType($_dataCache[EventConst::CACHE_SP_SERVERTYPE] ?? '')
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
		
		return EventFilter::factory()
		                  ->setProfix($this->getCacheKeyPrefix())
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
		foreach (EventFilter::factory()
		                    ->setProfix($this->getCacheKeyPrefix())
		                    ->keyScan($currKey, 0) as $_key) {
			yield $_key;
		}
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
	public function getCacheKeyPrefix() {
		return $this->_cacheKeyPrefix;
	}
	
	/**
	 * @param array $cacheKeyPrefix
	 *
	 * @return $this
	 */
	public function setCacheKeyPrefix(array $cacheKeyPrefix) {
		$this->_cacheKeyPrefix = $cacheKeyPrefix;
		
		return $this;
	}
	
	/**
	 * @return EventHandleInterface
	 */
	public function getEventHandle(): EventHandleInterface {
		return $this->_eventHandle;
	}
	
	/**
	 * @param EventHandleInterface $eventHandle
	 *
	 * @return $this
	 */
	public function setEventHandle(EventHandleInterface $eventHandle) {
		$this->_eventHandle = $eventHandle;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getLastKeys(): array {
		return $this->_lastKeys;
	}
	
	/**
	 * @param array $lastKeys
	 *
	 * @return $this
	 */
	public function setLastKeys(array $lastKeys) {
		$this->_lastKeys = $lastKeys;
		
		return $this;
	}
	
	/**
	 * @return EventDispatcher
	 */
	public function getParent(): EventDispatcher {
		return $this->_parent;
	}
	
	/**
	 * @param EventDispatcher $parent
	 *
	 * @return EventProvider
	 */
	public function setParent(EventDispatcher $parent) {
		$this->_parent = $parent;
		
		return $this;
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->getParent()->getRedisObj();
	}
	
	public function getCacheDataManager() {
		return $this->getParent()->getCacheDataManagerObj();
	}
	
	public function getCacheDataProvider() {
		$cdMgr      = $this->getCacheDataManager();
		$cdProvider = $cdMgr->getProviderList()->get(CacheConst::DATA_PROVIDER_KEY_EVENT);
		
		return $cdProvider;
	}
	
	/**
	 * @return ServerRouteManager
	 */
	public function getServerRouteManager(): ServerRouteManager {
		return $this->getParent()->getServerRouteManagerObj();
	}
	
}