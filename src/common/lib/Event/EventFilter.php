<?php


namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\InstanceTrait;

/**
 * Class EventFilter
 * 事件过滤器
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventFilter extends BaseClass {
	
	/**
	 * 前缀
	 * @var array
	 */
	public $prefix = [];
	
	/**
	 * Redis对象
	 * @var \Redis|\Swoole\Coroutine\Redis
	 */
	protected $_redisObj;
	
	
	/**
	 * 缓存是否存在
	 * @return bool
	 */
	public function keyExist(): bool {
		$k = $this->getJointKey('*');
		
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisObj();
		$iterator = null;
		// $reKeys = $redis->scan($iterator, $k, 1);
		
		while(false !== ($keys = $redis->scan($iterator, $k, 1))) {
			if (!empty($keys)) {
				return true;
			}
			
			// foreach($keys as $key) {
			// 	echo $key . PHP_EOL;
			// }
		}
		// return !empty($reKeys);
		return false;
	}
	
	/**
	 * key搜索
	 *
	 * @param string $keywords
	 * @param int    $count
	 * @return \Generator
	 */
	public function keyScan($keywords = '*', $count = 20) {
		$k = $this->getJointKey($keywords);
		
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisObj();
		
		$iterator = null;
		while(false !== ($keys = $redis->scan($iterator, $k, 20))) {
			if (empty($keys)) {
				continue;
			}
			
			// foreach($keys as $key) {
			// 	echo $key . PHP_EOL;
			// }
			yield $keys;
		}
	}
	
	/**
	 * 获取拼接后的缓存key
	 *
	 * @param string     $currKey 当前key
	 * @param array|null $prefix  前缀
	 * @return string
	 */
	public function getJointKey($currKey = '', $prefix = null) {
		is_null($prefix) && $prefix = $this->prefix;
		
		// 前缀 + 起始key + 当前key = 最终使用key
		$k = !empty($currKey) ? array_merge($prefix, [$currKey]) : $prefix;
		
		return implode(':', $k);
	}
	
	/**
	 * @return array
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	
	/**
	 * @param array $prefix
	 * @return EventFilter
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		
		return $this;
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->_redisObj;
	}
	
	/**
	 * @param \Redis|\Swoole\Coroutine\Redis $redisObj
	 * @return EventFilter
	 */
	public function setRedisObj($redisObj) {
		$this->_redisObj = $redisObj;
		
		return $this;
	}
	
}