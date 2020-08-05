<?php
/**
 *
 * author: lz
 * Date: 2020/8/5
 * Time: 14:38
 */

namespace uujia\framework\base\common\lib\Aop\cache;


use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;

/**
 * Class AopCacheDataProvider
 * Date: 2020/8/5
 * Time: 14:39
 *
 * @package uujia\framework\base\common\lib\Aop\cache
 */
class AopCacheDataProvider extends CacheDataProvider {
	
	
	/**
	 * AopCacheDataProvider constructor.
	 *
	 * @param CacheDataManagerInterface|null $parent
	 * @param RedisProviderInterface|null    $redisProviderObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(CacheDataManagerInterface $parent = null,
	                            RedisProviderInterface $redisProviderObj = null) {
		parent::__construct($parent, $redisProviderObj);
	}
	
	
	
}