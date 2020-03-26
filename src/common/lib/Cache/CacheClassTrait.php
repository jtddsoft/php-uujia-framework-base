<?php

namespace uujia\framework\base\common\lib\Cache;

trait CacheClassTrait {
	
	// /**
	//  * @var RedisProvider $_redisProviderObj
	//  */
	// protected $_redisProviderObj;
	//
	// /**
	//  * @var \Redis $_redisObj
	//  */
	// protected $_redisObj;
	
	// public function initRedis() {
	// 	/** @var Redis $_redisC */
	// 	$_redisC = Container::getInstance()->get(Redis::KEY_CONTAINER_REDIS_ALIAS);
	//
	// 	$this->_redisProviderObj = $_redisC->getRedisProviderObj();
	//
	// 	$this->_redisObj = $_redisC->getRedisObj();
	// }
	
	/**
	 * 从缓存读取
	 */
	public function fromCache() {
	
	}
	
	/**
	 * 写入缓存
	 */
	public function toCache() {
	
	}
	
	/**
	 * 缓存是否存在
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
	
	// /**
	//  * @return \Redis
	//  */
	// public function getRedisObj() {
	// 	// return $this->_redisObj;
	// 	return Container::getInstance()
	// 	                ->get(Redis::KEY_CONTAINER_REDIS_ALIAS)
	// 					->getRedisObj();
	// }
	
	// /**
	//  * @param \Redis $redisObj
	//  *
	//  * @return $this
	//  */
	// public function _setRedisObj($redisObj) {
	// 	$this->_redisObj = $redisObj;
	//
	// 	return $this;
	// }
	
	// /**
	//  * @return RedisProvider
	//  */
	// public function getRedisProviderObj() {
	// 	// return $this->_redisProviderObj;
	// 	return Container::getInstance()
	// 	                ->get(Redis::KEY_CONTAINER_REDIS_ALIAS)
	// 	                ->getRedisProviderObj();
	// }
	
	// /**
	//  * @param RedisProvider $redisProviderObj
	//  *
	//  * @return $this
	//  */
	// public function _setRedisProviderObj($redisProviderObj) {
	// 	$this->_redisProviderObj = $redisProviderObj;
	//
	// 	return $this;
	// }
}