<?php

use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManager;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Event\Cache\EventCacheData;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\MQ\MQCollection;
use uujia\framework\base\common\lib\Redis\RedisProvider;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;

return [
	'container' => [
		'alias' => [
			'redisProvider' => RedisProvider::class
		],
		'as' => [
			ConfigManagerInterface::class => ConfigManager::class,
			CacheDataManagerInterface::class => CacheDataManager::class,
			EventCacheDataInterface::class => EventCacheData::class,
			RunnerManagerInterface::class => RunnerManager::class,
		],
	],
	// todo：还未完成
	'aop' => [
		'enabled' => false,
		
		'ignore' => [
			ConfigManager::class,
			AopProxyFactory::class,
			CacheDataManager::class,
			RedisProvider::class,
			MQCollection::class,
		],
		
	]
];
