<?php


namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Config\ConfigManager;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\Result;

/**
 * Class Config
 *
 * @package uujia\framework\base\common
 */
class Config extends BaseClass {
	
	/**
	 * @var ConfigManagerInterface
	 */
	protected $_configManagerObj;
	
	/**
	 * Config constructor.
	 *
	 * @param ConfigManagerInterface $configManagerObj
	 *
	 * @AutoInjection(arg = "configManagerObj", name = "ConfigManager")
	 */
	public function __construct(ConfigManagerInterface $configManagerObj) {
		$this->_configManagerObj = $configManagerObj;
		
		parent::__construct();
		
		$this->initConfig();
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '配置管理类';
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
	 * 魔术方法
	 *  可直接访问ConfigManager中方法
	 *
	 * Date: 2020/9/28
	 * Time: 1:43
	 *
	 * @param $method
	 * @param $args
	 * @return mixed|object|Config|null
	 */
	public static function __callStatic($method, $args) {
		$di = Container::getInstance();
		$me = $di->get(static::class);
		
		// 从ConfigManager中查找方法
		if (is_callable([$me->getConfigManagerObj(), $method])) {
			return call_user_func_array([$me->getConfigManagerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		$me->getConfigManagerObj()->error('方法不存在', 1000);
		
		return $me;
	}
	
	/**
	 * @return ConfigManagerInterface
	 */
	public function configObj() {
		return $this->getConfigManagerObj();
	}
	
	/**
	 * @return ConfigManagerInterface
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