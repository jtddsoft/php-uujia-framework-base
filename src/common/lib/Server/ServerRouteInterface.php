<?php


namespace uujia\framework\base\common\lib\Server;

/**
 * Interface ServerRouteInterface
 * 定义通用Server路由标准（本地或远程各种不同协议 POST WebSocket等等）
 *
 * @package uujia\framework\base\common\lib\Server
 */
interface ServerRouteInterface {
	
	/**
	 * 初始化
	 */
	public function initRoute();
	
	/**
	 * 路由
	 *  自主将请求路由出去 并接收返回值 传给回调
	 *
	 * @return array|false
	 */
	public function route();
	
	/**
	 * 获取父级
	 *
	 * @return ServerRouteManager
	 */
	public function getParent();
	
	/**
	 * 设置父级
	 *
	 * @param ServerRouteManager $parent
	 *
	 * @return $this
	 */
	public function setParent($parent);
	
	// /**
	//  * 获取结果回调
	//  *  如果用于本地 则直接调用回调
	//  *  如果用于远程 调用回调取回数据返回
	//  *
	//  * @return callable
	//  */
	// public function getCallback();
	//
	// /**
	//  * 设置结果回调
	//  *
	//  * @param callable $callback
	//  * @return $this
	//  */
	// public function setCallback($callback);
}