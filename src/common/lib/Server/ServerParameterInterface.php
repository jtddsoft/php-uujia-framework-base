<?php


namespace uujia\framework\base\common\lib\Server;


/**
 * interface ServerParameterInterface
 * 远程服务参数定义
 *
 * @package uujia\framework\base\common\lib\Server
 */
interface ServerParameterInterface {
	
	/**************************************************************
	 * var
	 **************************************************************/
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function host($value = null);
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function requestType($value = null);
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function async($value = null);
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function url($value = null);
	
	/**
	 * @param string $key
	 * @param null   $value
	 *
	 * @return mixed|$this
	 */
	public function params($key, $value = null);
	
	/**
	 * @param string $key
	 * @param null   $value
	 *
	 * @return mixed|$this
	 */
	public function ret($key, $value = null);
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getHost(): string;
	
	/**
	 * @param string $host
	 * @return $this
	 */
	public function setHost(string $host);
	
	/**
	 * @return string
	 */
	public function getRequestType();
	
	/**
	 * @param string $requestType
	 * @return $this
	 */
	public function setRequestType($requestType);
	
	/**
	 * @return string
	 */
	public function getUrl();
	
	/**
	 * @param string $url
	 * @return $this
	 */
	public function setUrl(string $url);
	
	/**
	 * @return array
	 */
	public function getParams();
	
	/**
	 * @param array $params
	 *
	 * @return $this
	 */
	public function _setParams($params);
	
	/**
	 * @return array
	 */
	public function getRet();
	
	/**
	 * @param array $ret
	 *
	 * @return $this
	 */
	public function _setRet($ret);
	
	/**
	 * @return callable
	 */
	public function getCallback(): callable;
	
	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function setCallback(callable $callback);
	
	/**
	 * @return bool
	 */
	public function isAsync(): bool;
	
	/**
	 * @param bool $async
	 *
	 * @return $this
	 */
	public function setAsync(bool $async);
	
}