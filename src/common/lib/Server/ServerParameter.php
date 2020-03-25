<?php


namespace uujia\framework\base\common\lib\Server;


use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Base\BaseClass;

/**
 * Class ServerParameter
 * 远程服务参数定义
 *
 * @package uujia\framework\base\common\lib\Server
 */
class ServerParameter extends BaseClass {
	
	/**
	 * 主机名 域名
	 * @var string $_host
	 */
	protected $_host = '';
	
	/**
	 * 请求类型
	 * @var string $_requestType
	 */
	protected $_requestType = ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
	
	/**
	 * 远程接口地址
	 * @var string $_url
	 */
	protected $_url = '';
	
	/**
	 * 参数
	 * @var array $_params
	 */
	public $params = [];
	
	/**
	 * 返回值
	 * @var array $_ret
	 */
	public $ret = [];
	
	
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
	
}