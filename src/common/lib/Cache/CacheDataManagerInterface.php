<?php


namespace uujia\framework\base\common\lib\Cache;

use uujia\framework\base\common\lib\Tree\TreeFunc;

/**
 * Interface CacheDataManagerInterface
 * 管理所有的缓存提供者 并向需要的对象提供缓存服务
 *
 * @package uujia\framework\base\common\lib\Cache
 */
interface CacheDataManagerInterface {
	
	/**
	 * @return TreeFunc
	 */
	public function getProviderList();
	
	public function getRedisObj();
	
	/**
	 * 设置（添加或修改）缓存数据供应商
	 *
	 * @param $key
	 * @param CacheDataProviderInterface $itemProvider
	 */
	public function regProvider($key, $itemProvider);
	
	/**
	 * 获取缓存key前缀
	 *
	 * @return array
	 */
	public function &getCacheKeyPrefix(): array;
	
	/**
	 * 设置缓存key前缀
	 *
	 * @param array $cacheKeyPrefix
	 *
	 * @return $this
	 */
	public function setCacheKeyPrefix(array $cacheKeyPrefix);
	
}