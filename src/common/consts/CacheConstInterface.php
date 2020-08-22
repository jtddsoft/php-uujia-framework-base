<?php

namespace uujia\framework\base\common\consts;

/**
 * Interface CacheConstInterface
 *
 * @package uujia\framework\base\common\consts
 */
interface CacheConstInterface {
	
	/***********************************
	 * 缓存有效时间
	 *  例如：事件 event
	 ***********************************/
	
	// 事件
	const CACHE_EXPIRES_EVENT_TIME = 120 * 1000;
	
	/***********************************
	 * 缓存数据供应商类型名称
	 *  例如：事件 event
	 ***********************************/
	
	// 事件
	const DATA_PROVIDER_KEY_EVENT = 'event';
	
	// AOP
	const DATA_PROVIDER_KEY_AOP = 'aop';
	
	// AOPProxyClass
	const DATA_PROVIDER_KEY_AOP_PROXY_CLASS = 'aop_proxy_class';
	
	/***********************************
	 * 文件
	 ***********************************/
	
	// 文件最后更新时间缓存key
	const CACHE_FILE_LAST_WRITE_TIME_KEY = 'file:mtime';
	
	
	
}