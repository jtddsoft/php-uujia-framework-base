<?php

namespace uujia\framework\base\common\lib\Server;

use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class ServerRoute {
	use NameBase;
	use ResultBase;
	
	// key
	const KEY_HOST = 'host';
	const KEY_TYPE = 'type';
	
	// name
	const NAME_MAIN = 'main';
	
	// type
	const TYPE_EVENT = 'event';
	
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
	 * @var string $_name
	 */
	protected $_name = 'main';
	
	/**
	 * 服务类型
	 *  例如：事件event
	 *
	 * @var string $_type
	 */
	protected $_type = 'event';
	
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
	 * 返回值-主机名或域名
	 * @var string $_host
	 */
	protected $_host = '';
	
	/**
	 * 返回值-对应类型的数据
	 * @var array $_serverData
	 */
	protected $_serverData = [];
	
	/**
	 * Local constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		$this->_config = $config;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		// 初始化
		$this->_name = 'main';
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
	 * 服务器名称
	 *
	 * @param null $name
	 * @return $this|string
	 */
	public function name($name = null) {
		if ($name === null) {
			return $this->_name;
		} else {
			$this->_name = $name;
		}
		
		return $this;
	}
	
	/**
	 * 服务类型
	 *  例如：event
	 *
	 * @param null $type
	 * @return $this|string
	 */
	public function type($type = null) {
		if ($type === null) {
			return $this->_type;
		} else {
			$this->_type = $type;
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
		!empty($name) && $name = $this->name();
		!empty($type) && $type = $this->type();
		
		$_server = $this->getConfig($name);
		
		$_data = $_server[self::KEY_TYPE][$type];
		
		$this->_setHost($_server[self::KEY_HOST]);
		$this->_setServerData($_data);
		
		return $this;
	}
	
	public function route($type) {
	
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @param null|string $name
	 * @return array
	 */
	public function getConfig($name = null) {
		if (empty($name)) {
			return $this->_config;
		}
		
		return $this->_config[$name];
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
	public function getServerData() {
		return $this->_serverData;
	}
	
	/**
	 * @param array $serverData
	 *
	 * @return $this
	 */
	public function _setServerData(array $serverData) {
		$this->_serverData = $serverData;
		
		return $this;
	}
	
	
}