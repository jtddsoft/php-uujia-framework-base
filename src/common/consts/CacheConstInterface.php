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
	
	
	
}