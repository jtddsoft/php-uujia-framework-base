<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use uujia\framework\base\common\consts\ResultConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;
use uujia\framework\base\common\lib\Utils\Ret;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class EventDispatcher
 * 事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventDispatcher extends BaseClass implements EventDispatcherInterface {
	use ResultTrait;
	
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
	 * @param CacheDataManagerInterface   $cacheDataManagerObj
	 * @param RedisProviderInterface|null $redisProviderObj
	 * @param ServerRouteManager|null     $serverRouteManagerObj
	 *
	 * @AutoInjection(arg = "cacheDataManagerObj", name = "CacheDataManager")
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(CacheDataManagerInterface $cacheDataManagerObj = null,
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
		$this->resetResult();
		
		// 创建优先级队列
		$objPQ = new \SplPriorityQueue();
		
		// todo：调用事件供应商
		/** @var EventProvider $eventProviderObj */
		$eventProviderObj = $this->getContainer()
		                         ->get(EventProvider::class);
		
		// 遍历获取符合条件的事件进行触发
		foreach ($eventProviderObj->getListenersForEvent($event) as $i => $item) {
			/** @var EventListenerProxy $item */
			$item->handle();
			
			if ($item->isErr()) {
				$this->assignLastReturn($item->getLastReturn());
				
				return $this;
			}
			
			$reData = $item->getData();
			
			$objPQ->insert($item->getLastReturn(),
			               $reData['weight'] ?? ResultConstInterface::RESULT_WEIGHT_DEFAULT);
			
			// 是否终止向下执行
			if ($item->getRunStatus()->isPropagationStopped()) {
				break;
			}
		}
		
		// todo：排序返回值优先级
		
		//mode of extraction
		$objPQ->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
		
		//Go to TOP
		$objPQ->top();
		
		if ($objPQ->valid()) {
			$this->assignLastReturn($objPQ->current());
		}
		
		return $this;
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