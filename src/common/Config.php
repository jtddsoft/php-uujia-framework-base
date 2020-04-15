<?php


namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Config\ConfigManager;

class Config {
	
	/**
	 * @var ConfigManager
	 */
	protected $_configManagerObj;
	
	/**
	 * Config constructor.
	 *
	 * @param ConfigManager $configManagerObj
	 */
	public function __construct(ConfigManager $configManagerObj) {
		$this->_configManagerObj = $configManagerObj;
		
		$this->initConfig();
	}
	
	/**
	 * 加载初始化配置文件
	 * @return $this
	 */
	public function initConfig() {
		$paths = glob(__DIR__ . "/../config/*_config.php", GLOB_BRACE);
		$this->getConfigManagerObj()->path($paths);
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问ConfigManager中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从ConfigManager中查找方法
		if (is_callable([$this->getConfigManagerObj(), $method])) {
			return call_user_func_array([$this->getConfigManagerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		$this->getConfigManagerObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * @return ConfigManager
	 */
	public function configObj() {
		return $this->getConfigManagerObj();
	}
	
	/**
	 * @return ConfigManager
	 */
	public function getConfigManagerObj() {
		return $this->_configManagerObj;
	}
	
	/**
	 * @param mixed $configManagerObj
	 *
	 * @return $this
	 */
	public function _setConfigManagerObj($configManagerObj) {
		$this->_configManagerObj = $configManagerObj;
		
		return $this;
	}
	
}