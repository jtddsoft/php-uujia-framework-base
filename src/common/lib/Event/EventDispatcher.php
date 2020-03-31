<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;

/**
 * Class EventDispatcher
 * 事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventDispatcher extends BaseClass implements EventDispatcherInterface {
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface $_redisProviderObj
	 */
	protected $_redisProviderObj;
	
	
	/**
	 * @inheritDoc
	 */
	public function dispatch(object $event) {
		// TODO: Implement dispatch() method.
	}
	
	
	
	/**
	 * @return RedisProviderInterface
	 */
	public function getRedisProviderObj(): RedisProviderInterface {
		return $this->_redisProviderObj;
	}
	
	/**
	 * @param RedisProviderInterface $redisProviderObj
	 * @return $this
	 */
	public function setRedisProviderObj(RedisProviderInterface $redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
	
		return $this;
	}
	
	/**
	 * @return \Redis
	 */
	public function getRedisObj() {
		return $this->getRedisProviderObj()->getRedisObj();
	}
	
	
}