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
	
	// key前缀 —— 主列表 hash
	const CACHE_KEY_PREFIX_AOP = 'aop';
	
	// key前缀 —— 拦截者列表 zset
	const CACHE_KEY_PREFIX_AOP_CLASS = 'aopc';
	
	// key前缀 —— Aop代理类类名表列表 hash
	// （a\b\Hello.php 生成代理类为 proxy\a_b_Hello_1s2d15fd2.php
	//   因为1s2d15fd2是随机数 所以需要缓存记录 不能每次都生成新的）
	const CACHE_KEY_PREFIX_AOP_PROXY_CLASS = 'aoppc';
	
}