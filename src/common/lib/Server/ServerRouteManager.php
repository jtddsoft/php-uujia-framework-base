<?php

namespace uujia\framework\base\common\lib\Server;

use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Event\ServerRouteLocal;
use uujia\framework\base\common\lib\Utils\Network;
use uujia\framework\base\common\traits\InstanceBase;
use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

/**
 * Class ServerRouteManager
 *
 * @package uujia\framework\base\common\lib\Server
 */
class ServerRouteManager extends BaseClass {
	use ResultBase;
	use InstanceBase;
	
	/**
	 * 服务器参数类
	 *
	 * @var ServerParameterInterface
	 */
	protected $_serverParameter = null;
	
	/**
	 * 路由服务集合
	 *  本地路由服务、POST服务、MQ服务等等
	 *
	 * @var ServerRouteInterface[]
	 */
	protected $_serverRoutes = [];
	
	/**
	 * 所有服务器配置
	 *
	 * @var array
	 */
	protected $_config = [];
	
	
	/**************************************************
	 * output
	 * value
	 **************************************************/
	
	/**
	 * 本机服务器名称
	 *
	 * @var string
	 */
	protected $_name = '';
	
	/**************************************************
	 * init
	 **************************************************/
	
	/**
	 * ServerRouteManager constructor.
	 *
	 * @param ServerParameterInterface $serverParameter
	 * @param ServerRouteInterface[]   $serverRoutes
	 * @param array                    $config
	 *
	 * @AutoInjection(arg = "serverParameter", type = "v" value = null)
	 */
	public function __construct(ServerParameterInterface $serverParameter = null,
	                            array $serverRoutes = [],
	                            array $config = []) {
		$this->_config          = $config;
		$this->_serverParameter = $serverParameter;
		$this->_serverRoutes    = $serverRoutes;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		// 初始化
		$this->_serverName = 'main';
		$this->_type       = ServerConst::TYPE_EVENT;
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = '服务器配置';
	}
	
	/**
	 * 服务配置
	 *
	 * @param null|array $config
	 *
	 * @return $this|array
	 */
	public function config($config = null) {
		if ($config === null) {
			return $this->_config;
		} else {
			// $this->_config = $config;
			$this->_setConfig($config);
			
			$this->_setName($this->_config[ServerConst::KEY_SERVER_NAME]);
		}
		
		return $this;
	}
	
	/**
	 * 加载配置
	 *
	 * @param null $name
	 * @param null $type
	 * @param bool $notEmptyIgnore 如果不是空就忽略设置
	 *
	 * @return ServerRouteManager
	 */
	public function load($name = null, $type = null, $notEmptyIgnore = false) {
		!empty($name) && $name = $this->getServerParameter()->serverName();
		!empty($type) && $type = $this->getServerParameter()->serverType();
		// !empty($requestType) && $requestType = $this->requestType();
		
		$_config = $this->getConfig();
		$_server = $_config[ServerConst::KEY_SERVER_DATA][$name];
		
		$_host = $_server[ServerConst::KEY_HOST];
		$_data = $_server[ServerConst::KEY_TYPE][$type];
		
		$_url         = $_data[ServerConst::KEY_URL] ?? '';
		$_requestType = $_data[ServerConst::KEY_REQUEST_TYPE] ?? ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
		$_async       = $_data[ServerConst::KEY_ASYNC] ?? false;
		
		// $this->_setHost($_host);
		// $this->_setServerTypeData($_data);
		// $this->_setRequestType($_requestType);
		
		$this->getServerParameter()
		     ->setServerName($name, $notEmptyIgnore)
		     ->setServerType($type, $notEmptyIgnore)
		     ->setHost($_host, $notEmptyIgnore)
		     ->setUrl($_url, $notEmptyIgnore)
		     ->setAsync($_async, $notEmptyIgnore)
		     ->setRequestType($_requestType, $notEmptyIgnore);
		
		return $this;
	}
	
	/**
	 * 是否为本地（无协议 直接调用那种）
	 *
	 * @return bool
	 */
	public function isLocal() {
		return (in_array($this->getServerParameter()->getHost(), ['', ServerConst::SERVER_HOST_LOCALHOST]) ||
		        $this->getServerParameter()->getHost() == Network::getServerIp()) &&
		       $this->getServerParameter()->getRequestType() == ServerConst::REQUEST_TYPE_LOCAL_NORMAL;
	}
	
	/**
	 * 路由
	 */
	public function route() {
		if ($this->isLocal()) {
			return $this->getServerRouteLocal()->route();
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
	 * @return ServerRouteInterface
	 */
	public function getServerRouteLocal() {
		if (empty($this->_serverRoutes[ServerConst::SERVER_ROUTE_NAME_LOCAL])) {
			$this->_serverRoutes[ServerConst::SERVER_ROUTE_NAME_LOCAL] = new ServerRouteLocal($this, $this->getServerParameter());
		}
		return $this->_serverRoutes[ServerConst::SERVER_ROUTE_NAME_LOCAL];
	}
	
	/**
	 * @param ServerRouteLocal $serverRouteLocal
	 *
	 * @return $this
	 */
	public function setServerRouteLocal($serverRouteLocal) {
		$this->_serverRoutes[ServerConst::SERVER_ROUTE_NAME_LOCAL] = $serverRouteLocal;
		
		return $this;
	}
	
	/**
	 * @return ServerParameterInterface
	 */
	public function getServerParameter() {
		if ($this->_serverParameter === null) {
			$this->_serverParameter = new ServerParameter();
		}
		
		return $this->_serverParameter;
	}
	
	/**
	 * @param ServerParameterInterface $serverParameter
	 *
	 * @return $this
	 */
	public function setServerParameter($serverParameter) {
		$this->_serverParameter = $serverParameter;
		
		return $this;
	}
	
	
}