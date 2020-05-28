<?php


namespace uujia\framework\base\common\lib\Event\Cache;

use ReflectionMethod;
use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Event\EventHandleInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\lib\Reflection\Reflection as UUReflection;

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
	 * EventCacheDataProvider constructor.
	 *
	 * @param null                        $parent
	 * @param RedisProviderInterface|null $redisProviderObj
	 * @param EventName                   $eventNameObj
	 * @param EventCacheData|null         $eventCacheDataObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 * @AutoInjection(arg = "eventNameObj", type = "cc")
	 */
	public function __construct($parent = null,
	                            RedisProviderInterface $redisProviderObj = null,
	                            EventName $eventNameObj = null,
	                            EventCacheData $eventCacheDataObj = null) {
		$this->_eventNameObj      = $eventNameObj;
		$this->_eventCacheDataObj = $eventCacheDataObj;
		
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
	 * cache
	 **************************************************************/
	
	/**
	 * 写本地事件监听到缓存
	 *
	 * @param array $param
	 *
	 * @return $this
	 */
	public function toCacheEventListenLocal($param = []) {
		/********************************
		 * 分两部分
		 *  1、只是个列表 方便遍历
		 *  2、String类型的key value。其中key就是监听者要监听的事件名称（可能有通配符模糊匹配）
		 ********************************/
		
		// 监听者列表缓存中的key
		$keyListenList = $this->getKeyListenList();
		
		/********************************
		 * 拆分param
		 ********************************/
		
		/** @var EventListener[] $_listeners */
		$_listeners = $param['listener'];
		
		/** @var ReflectionMethod[] $_methods */
		$_methods = $param['publicMethods'];
		
		$_className = $param['className'];
		
		if (empty($_listeners)) {
			return $this;
		}
		
		// todo: 是否之前反射时需要实例化EventHandle 调用一下某个方法自定义一些操作？
		
		// 遍历每一个监听注解
		foreach ($_listeners as $listener) {
			// 命名空间（事件名头部、事件名前缀）
			$namespace = $listener->nameSpace;
			
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
					
					// 写入监听列表到缓存
					$this->getRedisObj()->zAdd($keyListenList, $weight, $name);
					
					// 构建缓存数据 并转json 【本地】
					$jsonData = $this->getEventCacheDataObj()
					                 ->reset()
					                 ->setServerName('main')
					                 ->setServerType('event')
					                 ->setClassNameSpace($_className)
					                 ->setParam([])
					                 ->toJson();
					
					// 构建EventName 生成key
					$_evtNameObj = $this->getEventNameObj()->reset();
					$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_LISTENER);
					
					
					// 写入缓存key
					
					
				}
			} else {
				foreach ($evt as $ev) {
					if (empty($ev)) {
						continue;
					}
					
					$name[] = "{$namespace}.{$ev}:{$uuid}";
				}
			}
		}
		
		return $this;
	}
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 获取收集事件类名集合
	 *
	 * @return \Generator
	 */
	public function getEventClassNames() {
		yield [];
	}
	
	/**
	 * 加载事件类
	 *
	 * @return \Generator
	 */
	public function loadEventHandle() {
		// $refObj = new UUReflection('', '', UUReflection::ANNOTATION_OF_CLASS);
		$refObj = new UUReflection('', '', UUReflection::ANNOTATION_OF_CLASS);
		
		foreach ($this->getEventClassNames() as $itemClassName) {
			$refObj
				->setClassName($itemClassName)
				->load();
			
			$_refMethods = $refObj
				->methods(UUReflection::METHOD_OF_PUBLIC)
				->getMethodObjs();
			
			$_evtListener = $refObj
				->annotation(EventListener::class)
				->getAnnotationObjs();
			
			$_evtTrigger = $refObj
				->annotation(EventTrigger::class)
				->getAnnotationObjs();
			
			// 根据EventHandle确定下EventName的初始信息 例如：evtt、evtl
			$_evtNameObj = $this->getEventNameObj()->reset();
			
			$_evtExistL = false;
			$_evtExistT = false;
			
			$_evtNameSpaceL = '';
			$_evtNameSpaceT = '';
			
			if (!empty($_evtListener) && !empty($_evtTrigger)) {
				$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_TRIGGER_LISTENER);
				
				
			} elseif (!empty($_evtListener)) {
				$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_LISTENER);
			} elseif (!empty($_evtTrigger)) {
				$_evtNameObj->setModeName(EventConst::CACHE_KEY_PREFIX_TRIGGER);
			}
			
			$result = [
				'className'     => $itemClassName,
				'publicMethods' => $_refMethods,
				'listener'      => $_evtListener,
				'trigger'       => $_evtTrigger,
			];
			
			yield $result;
		}
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
	 * @return string
	 */
	public function getKeyListenList(): string {
		if (empty($this->_keyListenList)) {
			// 构建key的层级数组
			$keys   = [];
			$keys[] = $this->getParent()->getCacheKeyPrefix();
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
	
	
}