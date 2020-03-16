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
	
	// type
	const TYPE_EVENT = 'event';
	
	/**
	 * 所有服务器配置
	 * @var array $_serverConfig
	 */
	protected $_config = [];
	
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
	
	protected $_host = '';
	
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
	 * @return array
	 */
	public function load($name = null, $type = null) {
		!empty($name) && $name = $this->name();
		!empty($type) && $type = $this->type();
		
		$_server = $this->getConfig($name);
		
		$_data = $_server[self::KEY_TYPE][$type];
		
		$result = [
			'host' => $_server[self::KEY_HOST],
			'data' => $_data,
		];
		
		return $result;
	}
	
	
	
	
	
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @param null $name
	 * @return array
	 */
	public function getConfig($name = null): array {
		if (empty($name)) {
			return $this->_config;
		}
		
		return $this->_config[$name];
	}
	
	/**
	 * @param array $config
	 */
	public function _setConfig(array $config) {
		$this->_config = $config;
	}
	
	
	
	
}