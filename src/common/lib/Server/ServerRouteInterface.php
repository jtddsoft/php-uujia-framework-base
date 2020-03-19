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
	 * @return $this
	 */
	public function route();
	
	/**
	 * 获取父级
	 */
	public function getParent();
	
	/**
	 * 设置父级
	 *
	 * @param mixed $parent
	 *
	 * @return $this
	 */
	public function _setParent($parent);
}