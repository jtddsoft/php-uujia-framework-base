<?php


namespace uujia\framework\base\common\lib\Event\Cache;

use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\lib\Utils\Reflection as UUReflection;

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
	 * EventCacheDataProvider constructor.
	 *
	 * @param EventName $eventNameObj
	 *
	 * @AutoInjection(arg = "eventNameObj", type = "cc")
	 */
	public function __construct(EventName $eventNameObj) {
		$this->_eventNameObj = $eventNameObj;
		
		parent::__construct();
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
		$refObj = new UUReflection('', '', UUReflection::ANNOTATION_OF_CLASS);
		
		foreach ($this->getEventClassNames() as $itemClassName) {
			$refObj
				->setClassName($itemClassName)
				->load();
			
			$_refMethods = $refObj
				->methods(UUReflection::METHOD_OF_PUBLIC);
			
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
	 * @return string
	 */
	public function makeCacheKey() {
		// todo: 将事件属性转成前缀数组
		$this->setCacheKeyPrefix($this->getEventNameObj()->toPrefixArr());
		
		return parent::makeCacheKey(); // TODO: Change the autogenerated stub
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
	
	
}