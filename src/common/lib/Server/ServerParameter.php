<?php


namespace uujia\framework\base\common\lib\Server;


use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Base\BaseClassInterface;

/**
 * Class ServerParameter
 * 远程服务参数定义
 *
 * @package uujia\framework\base\common\lib\Server
 */
class ServerParameter extends BaseClass implements ServerParameterInterface {
	
	/**************************************************
	 * input
	 * name type
	 **************************************************/
	
	/**
	 * 服务器名称
	 *  通过名称查找对应服务器
	 *
	 * @var string
	 */
	protected $_serverName = ''; // main
	
	/**
	 * 服务类型
	 *  例如：事件event
	 *
	 * @var string
	 */
	protected $_serverType = ''; // event
	
	/**************************************************
	 * input
	 * server param
	 **************************************************/
	
	/**
	 * 主机名 域名
	 *
	 * @var string
	 */
	protected $_host = '';
	
	/**
	 * 请求类型
	 *
	 * @var string
	 */
	protected $_requestType = ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
	
	/**
	 * 是否异步
	 *
	 * @var bool
	 */
	protected $_async = false;
	
	/**
	 * 远程接口地址
	 *
	 * @var string
	 */
	protected $_url = '';
	
	/**
	 * 参数
	 *
	 * @var array
	 */
	protected $_params = [];
	
	/**
	 * 返回值
	 *
	 * @var array
	 */
	protected $_ret = [];
	
	/**
	 * 服务回调
	 *  如果用于本地 则直接调用回调 不会再舍近求远的发远程请求
	 *  如果用于远程 调用回调取回数据返回
	 *
	 * @var callable
	 */
	protected $_callback = null;
	
	/**************************************************************
	 * init
	 **************************************************************/
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = '服务器参数类';
	}
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		!in_array('serverName', $exclude) && $this->_serverName = '';
		!in_array('serverName', $exclude) && $this->_serverType = '';
		
		!in_array('host', $exclude) && $this->_host = '';
		!in_array('requestType', $exclude) && $this->_requestType = ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
		!in_array('async', $exclude) && $this->_async = false;
		!in_array('url', $exclude) && $this->_url = '';
		!in_array('params', $exclude) && $this->_params = [];
		!in_array('ret', $exclude) && $this->_ret = [];
		!in_array('callback', $exclude) && $this->_callback = null;
		
		return parent::reset();
	}
	
	/**************************************************************
	 * var
	 **************************************************************/
	
	/**
	 * 服务器名称
	 *
	 * @param null $serverName
	 *
	 * @return $this|string
	 */
	public function serverName($serverName = null) {
		if ($serverName === null) {
			return $this->_serverName;
		} else {
			$this->_serverName = $serverName;
		}
		
		return $this;
	}
	
	/**
	 * 服务类型
	 *  例如：event
	 *
	 * @param null $serverType
	 *
	 * @return $this|string
	 */
	public function serverType($serverType = null) {
		if ($serverType === null) {
			return $this->_serverType;
		} else {
			$this->_serverType = $serverType;
		}
		
		return $this;
	}
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function host($value = null) {
		if ($value === null) {
			return $this->_host;
		} else {
			$this->_host = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function requestType($value = null) {
		if ($value === null) {
			return $this->_requestType;
		} else {
			$this->_requestType = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function async($value = null) {
		if ($value === null) {
			return $this->_async;
		} else {
			$this->_async = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param null $value
	 *
	 * @return $this|string
	 */
	public function url($value = null) {
		if ($value === null) {
			return $this->_url;
		} else {
			$this->_url = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param null   $value
	 *
	 * @return mixed|$this
	 */
	public function params($key, $value = null) {
		if ($value === null) {
			return $this->_params[$key];
		} else {
			$this->_params[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param null   $value
	 *
	 * @return mixed|$this
	 */
	public function ret($key, $value = null) {
		if ($value === null) {
			return $this->_ret[$key];
		} else {
			$this->_ret[$key] = $value;
		}
		
		return $this;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getHost(): string {
		return $this->_host;
	}
	
	/**
	 * @param string $host
	 * @param bool   $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return $this
	 */
	public function setHost(string $host, $notEmptyIgnore = false) {
		if (!$notEmptyIgnore || empty($this->_host)) {
			$this->_host = $host;
		}
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getRequestType() {
		return $this->_requestType;
	}
	
	/**
	 * @param string $requestType
	 * @param bool   $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return $this
	 */
	public function setRequestType($requestType, $notEmptyIgnore = false) {
		if (!$notEmptyIgnore || empty($this->_requestType)) {
			$this->_requestType = $requestType;
		}
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isAsync(): bool {
		return $this->_async;
	}
	
	/**
	 * 设置是否异步 todo: 未支持
	 *
	 * @param bool $async
	 * @param bool $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return $this
	 */
	public function setAsync(bool $async, $notEmptyIgnore = false) {
		$this->_async = $async;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getUrl(): string {
		return $this->_url;
	}
	
	/**
	 * @param string $url
	 * @param bool   $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return $this
	 */
	public function setUrl(string $url, $notEmptyIgnore = false) {
		if (!$notEmptyIgnore || empty($this->_url)) {
			$this->_url = $url;
		}
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}
	
	/**
	 * @param array $params
	 *
	 * @return $this
	 */
	public function _setParams($params) {
		$this->_params = $params;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getRet() {
		return $this->_ret;
	}
	
	/**
	 * @param array $ret
	 *
	 * @return $this
	 */
	public function _setRet($ret) {
		$this->_ret = $ret;
		
		return $this;
	}
	
	/**
	 * @return callable
	 */
	public function getCallback(): callable {
		return $this->_callback;
	}
	
	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function setCallback(callable $callback) {
		$this->_callback = $callback;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getServerName(): string {
		return $this->_serverName;
	}
	
	/**
	 * @param string $serverName
	 * @param bool   $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return $this
	 */
	public function setServerName(string $serverName, $notEmptyIgnore = false) {
		if (!$notEmptyIgnore || empty($this->_serverName)) {
			$this->_serverName = $serverName;
		}
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getServerType(): string {
		return $this->_serverType;
	}
	
	/**
	 * @param string $serverType
	 * @param bool   $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return $this
	 */
	public function setServerType(string $serverType, $notEmptyIgnore = false) {
		if (!$notEmptyIgnore || empty($this->_serverType)) {
			$this->_serverType = $serverType;
		}
		
		return $this;
	}
	
}