<?php


namespace uujia\framework\base\common;


use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class Config {
	use NameBase;
	use ResultBase;
	
	// 配置构建工厂 只是文件路径的方法集合
	public $_config_list_path = [
		// 'app_config' => [''],
	];
	
	// 配置列表 文件加载后的内容集合
	public $_config_list = [
		// 'app_config' => [],
	];
	
	// 配置类型 type = app表示name = type . '_config' = 'app_config'
	public $_type = ''; // app
	
	// 如果type是空 name=name 如果type不为空 name=type . '_config'
	public $_name = '';
	
	public function __construct() {
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		$this->initNameInfo();
		
		$this->path(__DIR__ . "./config/app_config.php", 'app');
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '配置类';
	}
	
	/**
	 * 配置类型
	 *
	 * @param null $type
	 *
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
	 * 配置文件名称
	 *
	 * @param null $name
	 *
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
	 * @param        $func
	 * @param string $type
	 * @param string $name
	 *
	 * @return array
	 */
	public function path($path, $type = '', $name = '') {
		$name = $this->getConfigName($type, $name);
		
		$configFactory = $this->getConfigListPath();
		$configFactory[$name] = $path;
		
		return $this->ok();
	}
	
	/**
	 * 加载配置
	 *
	 * @param string $type
	 * @param string $name
	 *
	 * @return $this
	 */
	public function load($type = '', $name = '') {
		$name = $this->getConfigName($type, $name);
		
		
		
		$config = include __DIR__ . "./config/error_code.php";
		
		return $this;
	}
	
	/**
	 * 获取配置名称
	 *  例如：app_config
	 *
	 * @param string $type
	 * @param string $name
	 *
	 * @return string
	 */
	public function getConfigName($type = '', $name = '') {
		if (!empty($type) && !empty($name)) {
			$type = $this->_type;
			$name = $this->_name;
		}
		
		if (!empty($type)) {
			$name = $type . '_config';
		}
		
		return $name;
	}
	
	/**
	 * config list
	 *
	 * @return array
	 */
	public function getConfigList(): array {
		return $this->_config_list;
	}
	
	/**
	 * config list
	 *
	 * @param array $config_list
	 */
	protected function setConfigList(array $config_list) {
		$this->_config_list = $config_list;
	}
	
	/**
	 * @return array
	 */
	public function getConfigListPath(): array {
		return $this->_config_list_path;
	}
	
	/**
	 * @param array $config_list_path
	 */
	public function setConfigListPath(array $config_list_path) {
		$this->_config_list_path = $config_list_path;
	}
	
	
}