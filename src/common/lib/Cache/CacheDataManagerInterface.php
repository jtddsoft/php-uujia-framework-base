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
	
	
	
}