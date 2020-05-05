<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;

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
	 * EventDispatcher constructor.
	 *
	 * @param CacheDataManager            $cacheDataManagerObj
	 * @param RedisProviderInterface|null $redisProviderObj
	 * @param ServerRouteManager|null     $serverRouteManagerObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(CacheDataManager $cacheDataManagerObj = null,
	                            RedisProviderInterface $redisProviderObj = null,
	                            ServerRouteManager $serverRouteManagerObj = null) {
		$this->_cacheDataManagerObj   = $cacheDataManagerObj;
		$this->_redisProviderObj      = $redisProviderObj;
		$this->_serverRouteManagerObj = $serverRouteManagerObj;
		
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
	
	
}