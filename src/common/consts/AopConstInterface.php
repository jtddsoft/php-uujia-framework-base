<?php

namespace uujia\framework\base\common\consts;

/**
 * Interface AopConstInterface
 *
 * @package uujia\framework\base\common\consts
 */
interface AopConstInterface {
	
	/***********************************
	 * 缓存key名定义
	 ***********************************/
	
	// key前缀 —— 主列表
	const CACHE_KEY_PREFIX_AOP = 'aop';
	
	// key前缀 —— 拦截者列表
	const CACHE_KEY_PREFIX_AOP_CLASS = 'aopc';
	
	
}