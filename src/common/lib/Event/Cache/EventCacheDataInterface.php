<?php


namespace uujia\framework\base\common\lib\Event\Cache;

/**
 * interface EventCacheDataInterface
 *
 * @package uujia\framework\base\common\lib\Event\Cache
 */
interface EventCacheDataInterface {
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 载入缓存数据
	 *
	 * @param array $cacheData
	 *
	 * @return $this
	 */
	public function load($cacheData = []);
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getServerName(): string;
	
	/**
	 * @param string $serverName
	 *
	 * @return $this
	 */
	public function setServerName(string $serverName);
	
	/**
	 * @return string
	 */
	public function getServerType(): string;
	
	/**
	 * @param string $serverType
	 *
	 * @return $this
	 */
	public function setServerType(string $serverType);
	
	/**
	 * @return array
	 */
	public function getParam(): array;
	
	/**
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setParam(array $param);
	
	/**
	 * @return string
	 */
	public function getClassNameSpace(): string;
	
	/**
	 * @param string $classNameSpace
	 *
	 * @return $this
	 */
	public function setClassNameSpace(string $classNameSpace);
	
	
}