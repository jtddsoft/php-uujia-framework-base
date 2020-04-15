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
	
	/**
	 * 主机名 域名
	 * @var string
	 */
	protected $_host = '';
	
	/**
	 * 请求类型
	 * @var string
	 */
	protected $_requestType = ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
	
	/**
	 * 是否异步
	 * @var bool
	 */
	protected $_async = false;
	
	/**
	 * 远程接口地址
	 * @var string
	 */
	protected $_url = '';
	
	/**
	 * 参数
	 * @var array
	 */
	protected $_params = [];
	
	/**
	 * 返回值
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
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '服务器参数类';
	}
	
	/**************************************************************
	 * var
	 **************************************************************/
	
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
	 * @return $this
	 */
	public function setHost(string $host) {
		$this->_host = $host;
		
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
	 * @return $this
	 */
	public function setRequestType($requestType) {
		$this->_requestType = $requestType;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->_url;
	}
	
	/**
	 * @param string $url
	 * @return $this
	 */
	public function setUrl(string $url) {
		$this->_url = $url;
		
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
	 * @return bool
	 */
	public function isAsync(): bool {
		return $this->_async;
	}
	
	/**
	 * @param bool $async
	 *
	 * @return $this
	 */
	public function setAsync(bool $async) {
		$this->_async = $async;
		
		return $this;
	}
	
}