<?php

namespace uujia\framework\base\common\consts;

/**
 * Interface EventConstInterface
 *
 * @package uujia\framework\base\common\consts
 */
interface EventConstInterface {
	
	/***********************************
	 * 缓存key名定义
	 ***********************************/
	
	// 监听key前缀
	const CACHE_KEY_PREFIX_LISTENER = 'evtl';
	
	// 触发key前缀
	const CACHE_KEY_PREFIX_TRIGGER = 'evtt';
	
	// 监听和触发key前缀
	const CACHE_KEY_PREFIX_TRIGGER_LISTENER = 'evttl';
	
	/***********************************
	 * 缓存有序集合json数据中字段名定义
	 *  例如：服务器名 serverName
	 * SP = ServerParameter
	 ***********************************/
	
	// 服务器名
	const CACHE_SP_SERVERNAME = 'serverName';
	
	// 服务类型
	const CACHE_SP_SERVERTYPE = 'serverType';
	
	// 本地执行的类名
	const CACHE_SP_CLASSNAMESPACE = 'classNameSpace';
	
	// 附加参数
	const CACHE_SP__PARAM = '_param';
	
	
}