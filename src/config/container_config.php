<?php

use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Event\Cache\EventCacheData;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\Redis\RedisProvider;

return [
	'container' => [
		'alias' => [
			'redisProvider' => RedisProvider::class
		],
		'as' => [
			CacheDataManagerInterface::class => CacheDataManager::class,
			EventCacheDataInterface::class => EventCacheData::class,
			
		],
	],
	
];
