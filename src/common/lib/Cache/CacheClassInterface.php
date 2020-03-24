<?php

namespace uujia\framework\base\common\lib\Cache;

/**
 * Interface CacheClassInterface
 * 定义类中需要用到缓存的规范
 *
 * @package uujia\framework\base\common\lib\Cache
 */
interface CacheClassInterface {
	
	/**
	 * 从缓存读取
	 */
	public function fromCache();
	
	/**
	 * 写入缓存
	 */
	public function toCache();
	
	/**
	 * 清空缓存
	 */
	public function clearCache();
	
	/**
	 * 是否存在缓存
	 * @return bool
	 */
	public function hasCache(): bool;
	
}