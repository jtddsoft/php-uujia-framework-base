<?php


namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\lib\Event\Cache\EventCacheDataInterface;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;

interface EventListenerProxyInterface {
	
	/**************************************************************
	 * 构建 触发
	 **************************************************************/
	
	/**
	 * 构建
	 */
	public function make();
	
	/**
	 * 执行触发
	 */
	public function handle();
	
	/**************************************************************
	 * data ServerParameter
	 **************************************************************/
	
	/**
	 * 载入缓存数据
	 *
	 * @param EventCacheDataInterface $cacheDataObj
	 *
	 * @return $this
	 */
	public function loadCache(EventCacheDataInterface $cacheDataObj);
	
	/**
	 * 重置ServerParameter
	 *
	 * @return $this
	 */
	public function resetSP();
	
	/**
	 * 清空ServerParameter返回值
	 *
	 * @return $this
	 */
	public function clearSPRet();
	
	/**
	 * 设置服务名称
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setSPServerName($name = '');
	
	/**
	 * 设置服务类型
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setSPServerType($type = '');
	
	/**
	 * 设置服务回调
	 *
	 * @param \Closure $callback
	 *
	 * @return $this
	 */
	public function setSPCallBack(\Closure $callback);
	
	/**
	 * 设置ServerParameter执行时附加参数
	 *
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setSPParam($param = []);
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return ServerParameterInterface
	 */
	public function getServerParameter();
	
	/**
	 * @param ServerParameterInterface $serverParameter
	 *
	 * @return $this
	 */
	public function setServerParameter($serverParameter);
	
	/**
	 * @return ServerRouteManager
	 */
	public function getServerRouteManagerObj();
	
	/**
	 * @param ServerRouteManager $serverRouteManagerObj
	 *
	 * @return $this
	 */
	public function setServerRouteManagerObj($serverRouteManagerObj);
	
}