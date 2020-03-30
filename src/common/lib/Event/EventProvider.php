<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Server\ServerRoute;
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
	
	// 缓存key前缀
	const CACHE_KEY_PREFIX = 'event';
	
	protected $_cacheKeyPrefix = [];
	
	/**
	 * 配置列表
	 *
	 * @var TreeFunc $_list
	 */
	protected $_list;
	
	/**
	 * 要触发的事件对象
	 * （EventHandle 主要取事件标识 addons.rubbish2.user.LoginBefore）
	 *
	 * @var EventHandleInterface $_eventHandle;
	 */
	protected $_eventHandle;
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface $_redisProviderObj
	 */
	protected $_redisProviderObj;
	
	/**
	 * EventProvider constructor.
	 *
	 * @param RedisProviderInterface $redisProvider
	 * @param array                  $cacheKeyPrefix
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(RedisProviderInterface $redisProvider, $cacheKeyPrefix = []) {
		$this->_redisProviderObj = $redisProvider;
		
		$this->_cacheKeyPrefix = $cacheKeyPrefix;
		$this->_cacheKeyPrefix[] = self::CACHE_KEY_PREFIX;
	
		parent::__construct();
	}
	
	/**
	 * 为事件调度提供事件列表
	 * @param EventHandleInterface|object $event
	 * @inheritDoc
	 */
	public function getListenersForEvent(object $event): iterable {
		if (!($event instanceof EventHandleInterface)) {
			return [];
		}
		
		
	}
	
	/**
	 * 触发运行
	 *  1、查缓存是否存在
	 *      1）存在 继续
	 *      2）不存在 构建参数存入缓存
	 */
	public function _run() {
	
	}
	
	/**
	 * @inheritDoc
	 */
	public function fromCache() {
		if (!$this->hasCache()) {
			// 不存在缓存 调起缓存数据管理器 收集数据传来
			
		}
		
		// 读取缓存
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function toCache() {
	
	}
	
	/**
	 * 缓存是否存在
	 * @return bool
	 */
	public function hasCache(): bool {
		$k = $this->getCacheKey('*');
		
		/** @var RedisProviderInterface $redis */
		$redis = $this->getRedisProviderObj()->getRedisObj();
		$re = $redis->keys($k);
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function clearCache() {
	
	}
	
	/**
	 * 获取拼接后的缓存key
	 *
	 * @param string $currKey 当前key
	 * @return string
	 */
	public function getCacheKey($currKey = '') {
		// 前缀 + 起始key + 当前key = 最终使用key
		$k = array_merge($this->getCacheKeyPrefix(), [$currKey]);
		
		return implode(':', $k);
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
	 * @return TreeFuncData
	 */
	public function getListData(string $key): TreeFuncData {
		return $this->getList()->getData();
	}
	
	/**
	 * 获取列表项值
	 *
	 * @param string $key
	 * @return array|string|int|null
	 */
	public function getListDataValue(string $key) {
		return $this->getListValue($key)->getDataValue();
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
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
	 * @return EventProvider
	 */
	public function setEventHandle(EventHandleInterface $eventHandle) {
		$this->_eventHandle = $eventHandle;
		
		return $this;
	}
	
	/**
	 * @return RedisProviderInterface
	 */
	public function getRedisProviderObj(): RedisProviderInterface {
		return $this->_redisProviderObj;
	}
	
	/**
	 * @param RedisProviderInterface $redisProviderObj
	 * @return EventProvider
	 */
	public function setRedisProviderObj(RedisProviderInterface $redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
		
		return $this;
	}
	
}