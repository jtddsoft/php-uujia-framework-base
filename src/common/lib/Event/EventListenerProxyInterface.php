<?php


namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\lib\Server\ServerParameterInterface;
use uujia\framework\base\common\lib\Server\ServerRouteManager;

interface EventListenerProxyInterface {
	
	/**
	 * 执行触发
	 */
	public function handle();
	
	/**************************************************************
	 * data ServerParameter
	 **************************************************************/
	
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