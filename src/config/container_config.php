<?php

use uujia\framework\base\common\Base;
use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Config\ConfigManager;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Error\ErrorCodeConfig;
use uujia\framework\base\common\lib\Event\Cache\EventCacheData;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\Log\Logger;
use uujia\framework\base\common\lib\MQ\MQCollection;
use uujia\framework\base\common\lib\Redis\RedisProvider;
use uujia\framework\base\common\lib\Reflection\CachedReader;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;
use uujia\framework\base\common\Result;
use uujia\framework\base\common\Runner;

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
		'enabled' => true,
		'scan' => [
			// 递归扫描父类
			'parent' => false,
		],
		
		'ignore' => [
			// ConfigManager::class,
			// AopProxyFactory::class,
			// CacheDataManager::class,
			// RedisProvider::class,
			MQCollection::class,
			Result::class,
			Logger::class,
			ErrorCodeConfig::class,
			Base::class,
			Runner::class,
			CachedReader::class,
		],
		
	]
];
