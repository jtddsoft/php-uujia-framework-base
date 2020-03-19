<?php

namespace uujia\framework\base\common\lib\Server;

use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Event\ServerRouteLocal;
use uujia\framework\base\traits\InstanceBase;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class ServerRoute {
	use NameBase;
	use ResultBase;
	use InstanceBase;
	
	// key
	const KEY_SERVER_NAME = 'name';
	const KEY_SERVER_DATA = 'server';
	
	const KEY_HOST = 'host';
	const KEY_TYPE = 'type';
	const KEY_REQUEST_TYPE = 'requestType';
	
	// name
	const NAME_MAIN = 'main';
	
	// type
	const TYPE_EVENT = 'event';
	
	// local
	const HOST_LOCAL = 'localhost';
	
	/**
	 * 本地服务路由
	 * @var ServerRouteLocal $_serverRouteLocal
	 */
	protected $_serverRouteLocal = null;
	
	
	
	/**
	 * 所有服务器配置
	 * @var array $_serverConfig
	 */
	protected $_config = [];
	
	/**************************************************
	 * input
	 * name type
	 **************************************************/
	
	/**
	 * 服务器名称
	 *  通过名称查找对应服务器
	 * @var string $_serverName
	 */
	protected $_serverName = 'main';
	
	/**
	 * 服务类型
	 *  例如：事件event
	 *
	 * @var string $_serverType
	 */
	protected $_serverType = 'event';
	
	/**************************************************
	 * input
	 * param
	 **************************************************/
	
	/**
	 * route参数
	 *  例如：->param([...])->route();
	 *
	 * @var string $_param
	 */
	protected $_param = 'param';
	
	/**
	 * route回调
	 *  例如：->param([...])->callback(function () {})->route();
	 *
	 * @var callable $_callback
	 */
	protected $_callback = null;
	
	/**************************************************
	 * output
	 * value
	 **************************************************/
	
	/**
	 * 本机服务器名称
	 * @var string $_name
	 */
	protected $_name = '';
	
	/**
	 * 配置中某服务器对应的主机名或域名
	 * @var string $_host
	 */
	protected $_host = ServerConst::SERVER_HOST_LOCALHOST;
	
	/**
	 * 配置中某服务器对应类型的数据
	 *  例如：event类型的 可能会有url、requestType
	 * @var array $_serverTypeData
	 */
	protected $_serverTypeData = [];
	
	/**
	 * 请求类型
	 *  （例如：本地local、Rabbitmq、Post等等）
	 * @var string $_requestType
	 */
	protected $_requestType = ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
	
	/**************************************************
	 * init
	 **************************************************/
	
	/**
	 * Local constructor.
	 */
	public function __construct() {
		// $this->_config = $config;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		// 初始化
		$this->_serverName = 'main';
		$this->_type = self::TYPE_EVENT;
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '服务器配置';
	}
	
	/**
	 * 服务配置
	 *
	 * @param null|array $config
	 * @return $this|array
	 */
	public function config($config = null) {
		if ($config === null) {
			return $this->_config;
		} else {
			// $this->_config = $config;
			$this->_setConfig($config);
			
			$this->_setName($this->_config[self::KEY_SERVER_NAME]);
		}
		
		return $this;
	}
	
	/**
	 * 服务器名称
	 *
	 * @param null $serverName
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
	 * 加载配置
	 *
	 * @param null $name
	 * @param null $type
	 *
	 * @return ServerRoute
	 */
	public function load($name = null, $type = null) {
		!empty($name) && $name = $this->serverName();
		!empty($type) && $type = $this->serverType();
		// !empty($requestType) && $requestType = $this->requestType();
		
		$_config = $this->getConfig();
		$_server = $_config[self::KEY_SERVER_DATA][$name];
		
		$_host = $_server[self::KEY_HOST];
		$_data = $_server[self::KEY_TYPE][$type];
		
		$_requestType = $_data[self::KEY_REQUEST_TYPE];
		
		$this->_setHost($_host);
		$this->_setServerTypeData($_data);
		$this->_setRequestType($_requestType);
		
		return $this;
	}
	
	/**
	 * 是否为本地（无协议 直接调用那种）
	 *
	 * @return bool
	 */
	public function isLocal() {
		return in_array($this->getHost(), ['', ServerConst::SERVER_HOST_LOCALHOST]) &&
		       $this->getRequestType() == ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
	}
	
	/**
	 * 路由
	 */
	public function route() {
		if ($this->isLocal()) {
			$this->getServerRouteLocal()->setCallback($this->getCallback())->route();
		} else {
			// todo: 远程或有协议（post之类）
		}
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return array
	 */
	public function getConfig() {
		return $this->_config;
	}
	
	/**
	 * @param array $config
	 *
	 * @return $this
	 */
	public function _setConfig(array $config) {
		$this->_config = $config;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->_name;
	}
	
	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function _setName(string $name) {
		$this->_name = $name;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getHost() {
		return $this->_host;
	}
	
	/**
	 * @param string $host
	 *
	 * @return $this
	 */
	public function _setHost(string $host) {
		$this->_host = $host;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getServerTypeData() {
		return $this->_serverTypeData;
	}
	
	/**
	 * @param array $serverTypeData
	 *
	 * @return $this
	 */
	public function _setServerTypeData(array $serverTypeData) {
		$this->_serverTypeData = $serverTypeData;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getRequestType(): string {
		return $this->_requestType;
	}
	
	/**
	 * @param string $requestType
	 *
	 * @return $this
	 */
	public function _setRequestType(string $requestType) {
		$this->_requestType = $requestType;
		
		return $this;
	}
	
	/**
	 * @return ServerRouteLocal
	 */
	public function getServerRouteLocal(): ServerRouteLocal {
		if ($this->_serverRouteLocal === null) {
			$this->_serverRouteLocal = new ServerRouteLocal($this);
		}
		return $this->_serverRouteLocal;
	}
	
	/**
	 * @param ServerRouteLocal $serverRouteLocal
	 *
	 * @return $this
	 */
	public function setServerRouteLocal(ServerRouteLocal $serverRouteLocal) {
		$this->_serverRouteLocal = $serverRouteLocal;
		
		return $this;
	}
	
	/**
	 * @return callable|array
	 */
	public function getCallback() {
		return $this->_callback;
	}
	
	/**
	 * @param callable|array $callback
	 *
	 * @return $this
	 */
	public function setCallback($callback) {
		$this->_callback = $callback;
		
		return $this;
	}
	
	
}