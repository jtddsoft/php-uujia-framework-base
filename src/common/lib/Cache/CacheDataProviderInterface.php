<?php


namespace uujia\framework\base\common\lib\Cache;

/**
 * Interface CacheDataProviderInterface
 * 缓存的提供者需要实现的接口规范
 *  例如：所有的事件为节省资源 事件调度依赖缓存
 *       而这些缓存的来源就来自于收集这些注册事件监听者信息 并将其放入缓存
 *       这里就充当事件监听器的信息收集者
 *       （当然这里只是接口规范 具体收集者可以是Builder 到时由Builder主动注册）
 *
 * @package uujia\framework\base\common\lib\Cache
 */
interface CacheDataProviderInterface {
	
}