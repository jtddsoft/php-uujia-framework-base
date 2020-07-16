<?php


namespace uujia\framework\base\common\lib\Event\Cache;

use Generator;
use ReflectionMethod;
use uujia\framework\base\common\consts\EventConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Event\EventHandleInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Event\Name\EventNameInterface;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\lib\Reflection\Reflection;

/**
 * Class EventCacheDataProvider
 *
 * @package uujia\framework\base\common\lib\Event\Cache
 */
abstract class EventCacheDataProvider extends CacheDataProvider {
	
	/**
	 * 事件名称管理对象
	 *
	 * @var EventName
	 */
	protected $_eventNameObj = null;
	
	/**
	 * 事件缓存数据对象
	 *
	 * @var EventCacheData;
	 */
	protected $_eventCacheDataObj;
	
	/**
	 * 反射对象
	 *
	 * @var Reflection;
	 */
	protected $_reflectionObj = null;
	
	/**
	 * 监听者列表的key
	 *  Key=app:event:listens Value=Redis中是有序集合
	 *
	 * @var string
	 */
	protected $_keyListenList = '';
	
	/**
	 * 监听者列表的key
	 *  Key=app:event:triggers Value=Redis中是有序集合
	 *
	 * @var string
	 */
	protected $_keyTriggerList = '';
	
	/**
	 * 监听者key
	 *  Key=app:evtl
	 *
	 * @var string
	 */
	protected $_keyListenPrefix = '';
	
	/**
	 * 触发者key
	 *  Key=app:evtt
	 *
	 * @var string
	 */
	protected $_keyTriggerPrefix = '';
	
	/*******************************
	 * 解析EventHandle 临时存储
	 *******************************/
	
	/**
	 * 类名
	 *
	 * @var string
	 */
	protected $_className = '';
	
	/**
	 * 反射得到所有public方法
	 *
	 * @var ReflectionMethod[]
	 */
	protected $_refMethods = [];
	
	/**
	 * 反射得到注解 EventListener 集合
	 *
	 * @var EventListener[]
	 */
	protected $_evtListeners = [];
	
	/**
	 * 反射得到注解 EventTrigger 集合
	 *
	 * @var EventTrigger[]
	 */
	protected $_evtTriggers = [];
	
	/**
	 * EventCacheDataProvider constructor.
	 *
	 * @param CacheDataManagerInterface   $parent
	 * @param RedisProviderInterface|null $redisProviderObj
	 * @param EventName                   $eventNameObj
	 * @param EventCacheDataInterface     $eventCacheDataObj
	 * @param Reflection                  $reflectionObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 * @AutoInjection(arg = "eventNameObj", type = "cc")
	 * @AutoInjection(arg = "reflectionObj", type = "cc")
	 */
	public function __construct(CacheDataManagerInterface $parent = null,
	                            RedisProviderInterface $redisProviderObj = null,
	                            EventName $eventNameObj = null,
	                            EventCacheDataInterface $eventCacheDataObj = null,
	                            Reflection $reflectionObj = null) {
		$this->_eventNameObj      = $eventNameObj;
		$this->_eventCacheDataObj = $eventCacheDataObj;
		$this->_reflectionObj     = $reflectionObj;
		
		parent::__construct($parent, $redisProviderObj);
	}
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		(!in_array('eventNameObj', $exclude)) && $this->getEventNameObj()->reset($exclude['eventNameObjExclude'] ?? []);
		
		return parent::reset($exclude);
	}
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 获取收集事件类名集合
	 *  yield [];
	 *
	 * @return Generator
	 */
	abstract public function getEventClassNames(): Generator;
	
	/**
	 * 加载收集的事件类集合
	 *
	 * @return $this
	 */
	public function loadEventHandles() {
		// $refObj = new UUReflection('', '', UUReflection::ANNOTATION_OF_CLASS);
		// $refObj = new UUReflection('', '', UUReflection::ANNOTATION_OF_CLASS);
		
		foreach ($this->getEventClassNames() as $itemClassName) {
			$this->parseEventHandle($itemClassName)
			     ->toCacheEventListenLocal();
		}
		
		return $this;
	}
	
	/**
	 * 解析事件类
	 *
	 * @param $className
	 *
	 * @return $this
	 */
	public function parseEventHandle($className) {
		$refObj = $this->getReflectionObj()
		               ->reset()
		               ->setClassName($className)
		               ->load();
		
		$this->setRefMethods($refObj->methods(Reflection::METHOD_OF_PUBLIC)
		                            ->getMethodObjs());
		
		$this->setEvtListeners($refObj->annotation(EventListener::class)
		                              ->getAnnotationObjs());
		
		$this->setEvtTriggers($refObj->annotation(EventTrigger::class)
		                             ->getAnnotationObjs());
		
		// // 根据EventHandle确定下EventName的初始信息 例如：evtt、evtl
		// $_evtNameObj = $this->getEventNameObj()->reset();
		//
		// $_evtExistL = false;
		// $_evtExistT = false;
		//
		// $_evtNameSpaceL = '';
		// $_evtNameSpaceT = '';
		//
		// if (!empty($_evtListener) && !empty($_evtTrigger)) {
		// 	$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_TRIGGER_LISTENER);
		//
		//
		// } elseif (!empty($_evtListener)) {
		// 	$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_LISTENER);
		// } elseif (!empty($_evtTrigger)) {
		// 	$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_TRIGGER);
		// }
		
		// $result = [
		// 	'className'     => $className,
		// 	'publicMethods' => $_refMethods,
		// 	'listener'      => $_evtListener,
		// 	'trigger'       => $_evtTrigger,
		// ];
		
		return $this;
	}
	
	/**************************************************************
	 * cache event listen
	 **************************************************************/
	
	/**
	 * 清空EventListen相关缓存
	 *  1、一个列表
	 *  2、一堆key
	 *
	 * @return $this
	 */
	public function clearCacheEventListen() {
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisObj();
		
		// 1、清空列表
		
		// 监听者列表缓存中的key
		$keyListenList = $this->getKeyListenList();
		
		// 清空缓存key
		$redis->del($keyListenList);
		
		// 2、清空一堆key
		
		// 搜索key
		$k = $this->getEventNameObj()->getAppName() . ':' . EventConstInterface::CACHE_KEY_PREFIX_LISTENER . ':*';
		
		$iterator = null;
		
		while(false !== ($keys = $redis->scan($iterator, $k, 20))) {
			if (empty($keys)) {
				continue;
			}
			
			$redis->del($keys);
		}
		
		return $this;
	}
	
	/**
	 * 写本地事件监听到缓存
	 *
	 * @return Generator
	 */
	public function makeCacheEventListenLocal() {
		/********************************
		 * 分两部分
		 *  1、只是个列表 方便遍历
		 *  2、String类型的key value。其中key就是监听者要监听的事件名称（可能有通配符模糊匹配）
		 ********************************/
		
		/********************************
		 * 解析class
		 ********************************/
		
		$_listeners = $this->getEvtListeners();
		$_methods   = $this->getRefMethods();
		$_className = $this->getClassName();
		
		if (empty($_listeners)) {
			yield [];
		}
		
		// todo: 是否之前反射时需要实例化EventHandle 调用一下某个方法自定义一些操作？
		
		
		// 遍历每一个监听注解
		foreach ($_listeners as $listener) {
			// 命名空间（事件名头部、事件名前缀）
			$namespace = $listener->namespace;
			
			// uuid
			$uuid = !empty($listener->uuid) ? $listener->uuid : '*';
			
			// evt
			$evt = $listener->evt;
			
			// weight
			$weight = $listener->weight;
			
			if (empty($namespace)) {
				continue;
			}
			
			// 寻找匹配的行为名称和触发时机{behavior_name}.{trigger_timing}
			if (empty($evt)) {
				foreach ($_methods as $method) {
					preg_match_all(EventHandleInterface::PCRE_FUNC_LISTENER_NAME, $method->getName(), $m, PREG_SET_ORDER);
					if (empty($m)) {
						continue;
					}
					
					$name = "{$namespace}.{$m[1]}.{$m[2]}:{$uuid}";
					
					yield [
						'weight'    => $weight,
						'name'      => $name,
						'className' => $_className,
					];
				}
			} else {
				foreach ($evt as $ev) {
					if (empty($ev)) {
						continue;
					}
					
					$name = "{$namespace}.{$ev}:{$uuid}";
					
					yield [
						'weight'    => $weight,
						'name'      => $name,
						'className' => $_className,
					];
				}
			}
		}
	}
	
	/**
	 * 写本地事件监听到缓存
	 *
	 * @return $this
	 */
	public function toCacheEventListenLocal() {
		// 类名
		$className = $this->getClassName();
		
		// 监听者列表缓存中的key
		$keyListenList = $this->getKeyListenList();
		
		foreach ($this->makeCacheEventListenLocal() as $item) {
			$_weight = $item['weight'] ?? 100;
			$_name   = $item['name'] ?? '';
			
			if (empty($_name)) {
				continue;
			}
			
			// 写入监听列表到缓存
			// $this->getRedisObj()->zAdd($keyListenList, $_weight, $_name);
			$this->getRedisObj()->hSet($keyListenList, $_name, $className);
			
			// 构建缓存数据 并转json 【本地】
			$jsonData = $this->getEventCacheDataObj()
			                 ->reset()
			                 ->setServerName('main')
			                 ->setServerType('event')
			                 ->setClassNameSpace($className)
			                 ->setParam([])
			                 ->toJson();
			
			// 构建EventName 生成key
			$_evtNameObj = $this->getEventNameObj()
			                    ->reset()
			                    ->setModeName(EventConstInterface::CACHE_KEY_PREFIX_LISTENER)
			                    ->switchLite()
			                    ->parse($_name, EventNameInterface::PCRE_NAME_FULL_LIKE)
								->switchFull()
								->setIgnoreTmp(true)
			                    ->makeEventName();
			
			if ($_evtNameObj->isErr()) {
				continue;
			}
			
			$_evtKey = $_evtNameObj->getEventName();
			
			// 写入缓存key
			$this->getRedisObj()->zAdd($_evtKey, $_weight, $jsonData);
		}
		
		return $this;
	}
	
	/**************************************************************
	 * cache event trigger
	 **************************************************************/
	
	/**
	 * 清空EventTrigger相关缓存
	 *  1、一个列表
	 *  2、一堆key
	 *
	 * @return $this
	 */
	public function clearCacheEventTrigger() {
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisObj();
		
		// 1、清空列表
		
		// 监听者列表缓存中的key
		$keyTriggerList = $this->getKeyTriggerList();
		
		// 清空缓存key
		$redis->del($keyTriggerList);
		
		// 2、清空一堆key
		
		// 搜索key
		$k = $this->getEventNameObj()->getAppName() . ':' . EventConstInterface::CACHE_KEY_PREFIX_TRIGGER . ':*';
		
		$iterator = null;
		
		while(false !== ($keys = $redis->scan($iterator, $k, 20))) {
			if (empty($keys)) {
				continue;
			}
			
			$redis->del($keys);
		}
		
		return $this;
	}
	
	/**
	 * 写本地事件触发者到缓存
	 *
	 * @param $className
	 *
	 * @return Generator
	 */
	public function makeCacheEventTriggerLocal($className) {
		/********************************
		 * 分两部分
		 *  1、只是个列表 方便遍历
		 *  2、String类型的key value。其中key就是监听者要监听的事件名称（可能有通配符模糊匹配）
		 ********************************/
		
		/********************************
		 * 解析class
		 ********************************/
		
		$_listeners = $this->getEvtListeners();
		$_methods   = $this->getRefMethods();
		$_className = $this->getClassName();
		
		if (empty($_listeners)) {
			yield [];
		}
		
		// todo: 是否之前反射时需要实例化EventHandle 调用一下某个方法自定义一些操作？
		
		
		// 遍历每一个监听注解
		foreach ($_listeners as $listener) {
			// 命名空间（事件名头部、事件名前缀）
			$namespace = $listener->namespace;
			
			// uuid
			$uuid = !empty($listener->uuid) ? $listener->uuid : '*';
			
			// evt
			$evt = $listener->evt;
			
			// weight
			$weight = $listener->weight;
			
			if (empty($namespace)) {
				continue;
			}
			
			// 寻找匹配的行为名称和触发时机{behavior_name}.{trigger_timing}
			if (empty($evt)) {
				foreach ($_methods as $method) {
					preg_match_all(EventHandleInterface::PCRE_FUNC_LISTENER_NAME, $method->getName(), $m, PREG_SET_ORDER);
					if (empty($m)) {
						continue;
					}
					
					$name = "{$namespace}.{$m[1]}.{$m[2]}:{$uuid}";
					
					yield [
						'weight'    => $weight,
						'name'      => $name,
						'className' => $_className,
					];
				}
			} else {
				foreach ($evt as $ev) {
					if (empty($ev)) {
						continue;
					}
					
					$name[] = "{$namespace}.{$ev}:{$uuid}";
					
					yield [
						'weight'    => $weight,
						'name'      => $name,
						'className' => $_className,
					];
				}
			}
		}
	}
	
	/**
	 * 写本地事件触发者到缓存
	 *
	 * @param $className
	 *
	 * @return $this
	 */
	public function toCacheEventTriggerLocal($className) {
		// 监听者列表缓存中的key
		$keyListenList = $this->getKeyListenList();
		
		foreach ($this->makeCacheEventListenLocal($className) as $item) {
			$_weight = $item['weight'] ?? 100;
			$_name   = $item['name'] ?? '';
			
			if (empty($name)) {
				continue;
			}
			
			// 写入监听列表到缓存
			// $this->getRedisObj()->zAdd($keyListenList, $_weight, $_name);
			$this->getRedisObj()->hSet($keyListenList, $_name, $className);
			
			// 构建缓存数据 并转json 【本地】
			$jsonData = $this->getEventCacheDataObj()
			                 ->reset()
			                 ->setServerName('main')
			                 ->setServerType('event')
			                 ->setClassNameSpace($className)
			                 ->setParam([])
			                 ->toJson();
			
			// 构建EventName 生成key
			$_evtNameObj = $this->getEventNameObj()
			                    ->reset()
			                    ->setModeName(EventConstInterface::CACHE_KEY_PREFIX_LISTENER)
			                    ->switchLite()
			                    ->parse($name)
			                    ->makeEventName();
			
			if ($_evtNameObj->isErr()) {
				continue;
			}
			
			$_evtKey = $_evtNameObj->getEventName();
			
			// 写入缓存key
			$this->getRedisObj()->zAdd($_evtKey, $_weight, $jsonData);
		}
		
		return $this;
	}
	
	/**
	 * 构建缓存Key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function makeCacheKey($key = '') {
		// todo: 将事件属性转成前缀数组
		$this->setCacheKeyPrefix($this->getEventNameObj()->toPrefixArr());
		
		return parent::makeCacheKey($key); // TODO: Change the autogenerated stub
	}
	
	/**************************************************************
	 * Cache
	 **************************************************************/
	
	/**
	 * 构建并获取数据 如果缓存没有就写入缓存
	 *
	 * @return mixed
	 */
	public function make() {
		if (!$this->hasCache()) {
			// 不存在缓存 调起缓存数据管理器 收集数据传来
			$this->toCache();
		}
		
		return $this->fromCache();
	}
	
	/**
	 * 从缓存读取
	 */
	public function fromCache() {
		return [];
	}
	
	/**
	 * 写入缓存
	 */
	public function toCache() {
		// 先清空
		$this->clearCache();
		
		// 加载事件
		$this->loadEventHandles();
		
		return $this;
	}
	
	/**
	 * 缓存是否存在
	 *
	 * @return bool
	 */
	public function hasCache(): bool {
		return false;
	}
	
	/**
	 * 清空缓存
	 */
	public function clearCache() {
		$this->clearCacheEventListen();
		
		parent::clearCache();
		
		return $this;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
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
	public function setEventNameObj(EventName $eventNameObj) {
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
	
	/**
	 * @return Reflection
	 */
	public function getReflectionObj(): Reflection {
		if (empty($this->_reflectionObj)) {
			$this->_reflectionObj = new Reflection();
		}
		
		return $this->_reflectionObj;
	}
	
	/**
	 * @param Reflection $reflectionObj
	 *
	 * @return $this
	 */
	public function setReflectionObj(Reflection $reflectionObj) {
		$this->_reflectionObj = $reflectionObj;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getKeyListenList(): string {
		if (empty($this->_keyListenList)) {
			// 构建key的层级数组
			// $keys   = [];
			$keys = $this->getParent()->getCacheKeyPrefix();
			$keys[] = 'event';
			$keys[] = 'listens';
			
			// key的层级数组转成字符串key
			$this->_keyListenList = Arr::arrToStr($keys, ':');
		}
		
		return $this->_keyListenList;
	}
	
	/**
	 * @param string $keyListenList
	 *
	 * @return $this
	 */
	public function setKeyListenList(string $keyListenList) {
		$this->_keyListenList = $keyListenList;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getKeyTriggerList(): string {
		if (empty($this->_keyTriggerList)) {
			// 构建key的层级数组
			$keys   = [];
			$keys[] = $this->getParent()->getCacheKeyPrefix();
			$keys[] = 'event';
			$keys[] = 'triggers';
			
			// key的层级数组转成字符串key
			$this->_keyTriggerList = Arr::arrToStr($keys, ':');
		}
		
		return $this->_keyTriggerList;
	}
	
	/**
	 * @param string $keyTriggerList
	 *
	 * @return $this
	 */
	public function setKeyTriggerList(string $keyTriggerList) {
		$this->_keyTriggerList = $keyTriggerList;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getKeyListenPrefix(): string {
		if (empty($this->_keyListenPrefix)) {
			// 构建key的层级数组
			$keys   = [];
			$keys[] = $this->getParent()->getCacheKeyPrefix();
			$keys[] = EventConstInterface::CACHE_KEY_PREFIX_LISTENER;
			
			// key的层级数组转成字符串key
			$this->_keyListenPrefix = Arr::arrToStr($keys, ':');
		}
		
		return $this->_keyListenPrefix;
	}
	
	/**
	 * @param string $keyListenPrefix
	 *
	 * @return $this
	 */
	public function setKeyListenPrefix(string $keyListenPrefix) {
		$this->_keyListenPrefix = $keyListenPrefix;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getKeyTriggerPrefix(): string {
		if (empty($this->_keyTriggerPrefix)) {
			// 构建key的层级数组
			$keys   = [];
			$keys[] = $this->getParent()->getCacheKeyPrefix();
			$keys[] = EventConstInterface::CACHE_KEY_PREFIX_TRIGGER;
			
			// key的层级数组转成字符串key
			$this->_keyTriggerPrefix = Arr::arrToStr($keys, ':');
		}
		
		return $this->_keyTriggerPrefix;
	}
	
	/**
	 * @param string $keyTriggerPrefix
	 *
	 * @return $this
	 */
	public function setKeyTriggerPrefix(string $keyTriggerPrefix) {
		$this->_keyTriggerPrefix = $keyTriggerPrefix;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getClassName(): string {
		return $this->_className;
	}
	
	/**
	 * @param string $className
	 *
	 * @return $this
	 */
	public function setClassName(string $className) {
		$this->_className = $className;
		
		return $this;
	}
	
	/**
	 * @return ReflectionMethod[]
	 */
	public function &getRefMethods(): array {
		return $this->_refMethods;
	}
	
	/**
	 * @param ReflectionMethod[] $refMethods
	 *
	 * @return $this
	 */
	public function setRefMethods(array $refMethods) {
		$this->_refMethods = $refMethods;
		
		return $this;
	}
	
	/**
	 * @return EventListener[]
	 */
	public function &getEvtListeners(): array {
		return $this->_evtListeners;
	}
	
	/**
	 * @param EventListener[] $evtListeners
	 *
	 * @return $this
	 */
	public function setEvtListeners(array $evtListeners) {
		$this->_evtListeners = $evtListeners;
		
		return $this;
	}
	
	/**
	 * @return EventTrigger[]
	 */
	public function &getEvtTriggers(): array {
		return $this->_evtTriggers;
	}
	
	/**
	 * @param EventTrigger[] $evtTriggers
	 *
	 * @return $this
	 */
	public function setEvtTriggers(array $evtTriggers) {
		$this->_evtTriggers = $evtTriggers;
		
		return $this;
	}
	
	
}