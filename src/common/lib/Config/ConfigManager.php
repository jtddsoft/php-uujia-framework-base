<?php


namespace uujia\framework\base\common\lib\Config;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\common\lib\Utils\Str;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class ConfigManager
 *
 * @package uujia\framework\base\common\lib\Config
 */
class ConfigManager extends BaseClass implements ConfigManagerInterface {
	use ResultTrait;
	
	// 配置构建工厂 只是文件路径的方法集合
	// public $_config_list_path = [
	// 	// 'app_config' => [''],
	// ];
	//
	// // 配置列表 文件加载后的内容集合
	// public $_config_list = [
	// 	// 'app_config' => [],
	// ];
	
	/**
	 * 配置列表
	 *
	 * @var TreeFunc
	 */
	protected $_list;
	
	/**
	 * 配置类型 type = app表示name = type . '_config' = 'app_config'
	 *
	 * @var string
	 */
	protected $_type = ''; // app
	
	/**
	 * 如果type是空 name=name 如果type不为空 name=type . '_config'
	 *
	 * @var string
	 */
	protected $_name = '';
	
	/**
	 * 最后一次获取load
	 *
	 * @var array|string|int|bool|mixed
	 */
	protected $_lastValue = [];
	
	/**
	 * ConfigManager constructor.
	 */
	public function __construct() {
		$this->_list = new TreeFunc();
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '配置类';
	}
	
	/**
	 * 配置类型
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
	 * 配置文件名称
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
	 * set or add 配置文件路径
	 *
	 *  对于已存在type直接覆盖 不存在的添加
	 *
	 *  例如：配置type = app; 路径为/config/app_config.php
	 *       path('/config/app_config.php', 'app');
	 *
	 * @param string|array $path
	 * @param string       $name
	 * @param int          $weight 权重
	 *
	 * @return $this
	 */
	public function path($path, $name = '', $weight = TreeFunc::DEFAULT_WEIGHT) {
		$_paths = [];
		if (is_string($path)) {
			$_paths[] = $path;
		} elseif (is_array($path)) {
			$_paths = $path;
		}
		
		if (empty($_paths)) {
			return $this;
		}
		
		foreach ($_paths as $_path) {
			// type转换为全名 例如：type = 'app' 转为name = 'app_config'
			// $_name = $this->getConfigName($type, $name);
			
			$_name = $name;
			if (empty($name)) {
				$_name = basename($_path, '.php');
			}
			
			$this->unshift($_name, $_path, $weight);
			
			// $item = new TreeFunc();
			// $item->getData()->set(function ($data, $it) use ($_path, $type, $name) {
			// 	$config = include $_path;
			//
			// 	return $config;
			// });
			//
			// $this->getList()->set($name, $item);
		}
		
		// $configFactory = $this->getConfigListPath();
		// $configFactory[$name] = $_path;
		
		return $this;
	}
	
	/**
	 * 取最后一次获取load的值
	 *
	 * @return array|bool|int|mixed|string
	 */
	public function value() {
		return $this->_lastValue;
	}
	
	/**
	 * 加载配置
	 *
	 * @param string $dotPath 以.分隔的配置结构路径
	 *
	 * @return array|string|null
	 */
	public function load($dotPath = '') {
		$this->_lastValue = $this->loadValue($dotPath);
		
		return $this;
	}
	
	/**
	 * 加载配置并返回值
	 *
	 * @param string $dotPath 以.分隔的配置结构路径
	 *
	 * @return array|string|int|null
	 */
	public function loadValue($dotPath = '') {
		// type转换为全名 例如：type = 'app' 转为name = 'app_config'
		// $_name = $this->getConfigName($type, $name);
		
		// $config = include __DIR__ . "./config/error_code.php";
		
		/** @var array|string|null $config */
		$config = null;
		// $config = $this->getListValue($_name)->getDataValue();
		
		// 以.分隔的配置路径 a.b.c = $config['a']['b']['c']
		if (!empty($dotPath)) {
			$_dots = Arr::strToArr($dotPath, '.');
			
			$_value = null;
			$_first = true;
			foreach ($_dots as $item) {
				if ($_first) {
					// 第一遍循环 查找主键key
					$_name = $item;
					if (Str::rightCompare($_name, '_config') === 0) {
						// 如果是_config结尾 没有找到 直接跳出
						if (!$this->getList()->has($_name)) {
							$_value = null;
							break;
						}
						
						$_value = $this->getListValue($_name)->getDataValue();
					} else {
						// 不是_config结尾
						if ($this->getList()->has($_name)) {
							// 找到
							$_value = $this->getListValue($_name)->getDataValue();
						} elseif ($this->getList()->has($_name . '_config')) {
							// 如果name没有找到 就尝试在name结尾加上_config找 找到了
							$_value = $this->getListValue($_name . '_config')->getDataValue();
						} else {
							// 啥都找不到 跳出
							$_value = null;
							break;
						}
					}
					
					$_first = false;
					continue;
				}
				
				if (empty($_value[$item])) {
					$_value = null;
					break;
				}
				
				$_value = $_value[$item];
			}
			
			$config = $_value;
		}
		
		return $config;
	}
	
	/**
	 * 开头插入
	 *
	 * @param string|int   $key
	 * @param string|array $path
	 * @param int          $weight  权重
	 * @return $this
	 */
	public function unshift($key, $path, $weight = TreeFunc::DEFAULT_WEIGHT) {
		$factoryItemFunc = function ($data, $it) {
			$_config = [];
			
			// 获取汇总列表中所有配置
			/** @var TreeFunc $it */
			$it->wForEach(function ($_item, $index, $me) use (&$_config) {
				/** @var TreeFunc $_item */
				$_it_config = $_item->getDataValue();
				$_config    = array_merge($_it_config, $_config);
			});
			
			return $_config;
		};
		
		if (is_string($path)) {
			$this->getList()->unshiftKeyNewItemData($key,
				function ($data, $it) use ($path) {
					$_config = include $path;
					
					return $_config;
				}, $factoryItemFunc
			)->getLastSetItem()->getLastNewItem()->setWeight($weight);
		} elseif (is_array($path)) {
			foreach ($path as $row) {
				$this->getList()->unshiftKeyNewItemData($key,
					function ($data, $it) use ($row) {
						$_config = include $row;
						
						return $_config;
					}, $factoryItemFunc
				)->getLastSetItem()->getLastNewItem()->setWeight($weight);
			}
		}
		
		return $this;
	}
	
	/**
	 * 尾部添加
	 *
	 * @param string|int   $key
	 * @param string|array $path
	 * @param int          $weight  权重
	 * @return $this
	 */
	public function add($key, $path, $weight = TreeFunc::DEFAULT_WEIGHT) {
		$factoryItemFunc = function ($data, $it) {
			$_config = [];
			
			// 获取汇总列表中所有配置
			/** @var TreeFunc $it */
			$it->wForEach(function ($_item, $index, $me) use (&$_config) {
				/** @var TreeFunc $_item */
				$_it_config = $_item->getDataValue();
				$_config    = array_merge($_config, $_it_config);
			});
			
			return $_config;
		};
		
		if (is_string($path)) {
			$this->getList()->addKeyNewItemData($key,
				function ($data, $it) use ($path) {
					$_config = include $path;
					
					return $_config;
				}, $factoryItemFunc
			)->getLastSetItem()->getLastNewItem()->setWeight($weight);
		} elseif (is_array($path)) {
			foreach ($path as $row) {
				$this->getList()->addKeyNewItemData($key,
					function ($data, $it) use ($row) {
						$_config = include $row;
						
						return $_config;
					}, $factoryItemFunc
				)->getLastSetItem()->getLastNewItem()->setWeight($weight);
			}
		}
		
		return $this;
	}
	
	// /**
	//  * 获取配置名称
	//  *  例如：app_config
	//  *
	//  * @param string $type
	//  * @param string $name
	//  *
	//  * @return string
	//  */
	// public function getConfigName($type = '', $name = '') {
	// 	if (!empty($type) && !empty($name)) {
	// 		$type = $this->_type;
	// 		$name = $this->_name;
	// 	}
	//
	// 	if (!empty($type)) {
	// 		$name = $type . '_config';
	// 	}
	//
	// 	return $name;
	// }
	
	/**
	 * 获取列表
	 *
	 * @return TreeFunc
	 */
	public function getList(): TreeFunc {
		return $this->_list;
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return TreeFuncData
	 */
	public function getListData(string $key): TreeFuncData {
		return $this->getList()->getData();
	}
	
	/**
	 * 获取列表项值
	 *
	 * @param string $key
	 * @return array|string|int|null
	 */
	public function getListDataValue(string $key) {
		return $this->getListValue($key)->getDataValue();
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return TreeFunc
	 */
	public function getListValue(string $key): TreeFunc {
		return $this->getList()->get($key);
	}
	
}