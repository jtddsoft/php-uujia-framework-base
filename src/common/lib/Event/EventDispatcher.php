<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;

/**
 * Class EventDispatcher
 * 事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventDispatcher extends BaseClass implements EventDispatcherInterface {
	
	/**
	 * CacheDataManager对象
	 *
	 * @var CacheDataManagerInterface
	 */
	protected $_cacheDataManagerObj;
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface
	 */
	protected $_redisProviderObj;
	
	/**
	 * EventDispatcher constructor.
	 *
	 * @param CacheDataManagerInterface|null $cacheDataManagerObj
	 * @param RedisProviderInterface|null    $redisProviderObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(CacheDataManagerInterface $cacheDataManagerObj = null, RedisProviderInterface $redisProviderObj = null) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		$this->_redisProviderObj = $redisProviderObj;
		
		parent::__construct();
	}
	
	
	/**
	 * @inheritDoc
	 */
	public function dispatch(object $event) {
		// TODO: Implement dispatch() method.
	}
	
	
	
	/**
	 * @return RedisProviderInterface
	 */
	public function getRedisProviderObj() {
		return $this->_redisProviderObj;
	}
	
	/**
	 * @param RedisProviderInterface $redisProviderObj
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
	 * @return CacheDataManagerInterface
	 */
	public function getCacheDataManagerObj() {
		return $this->_cacheDataManagerObj;
	}
	
	/**
	 * @param CacheDataManagerInterface $cacheDataManagerObj
	 * @return $this
	 */
	public function setCacheDataManagerObj($cacheDataManagerObj) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		
		return $this;
	}
	
	
}