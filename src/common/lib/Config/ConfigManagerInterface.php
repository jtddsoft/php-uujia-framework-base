<?php


namespace uujia\framework\base\common\lib\Config;

use uujia\framework\base\common\interfaces\ResultBaseInterface;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;

/**
 * interface ConfigManager
 *
 * @package uujia\framework\base\common\lib\Config
 */
interface ConfigManagerInterface extends ResultBaseInterface {
	
	/**
	 * 配置类型
	 *
	 * @param null $type
	 * @return $this|string
	 */
	public function type($type = null);
	
	/**
	 * 配置文件名称
	 *
	 * @param null $name
	 * @return $this|string
	 */
	public function name($name = null);
	
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
	public function path($path, $name = '', $weight = TreeFunc::DEFAULT_WEIGHT);
	
	/**
	 * 取最后一次获取load的值
	 *
	 * @return array|bool|int|mixed|string
	 */
	public function value();
	
	/**
	 * 加载配置
	 *
	 * @param string $dotPath 以.分隔的配置结构路径
	 *
	 * @return array|string|null
	 */
	public function load($dotPath = '');
	
	/**
	 * 加载配置并返回值
	 *
	 * @param string $dotPath 以.分隔的配置结构路径
	 *
	 * @return array|string|int|null
	 */
	public function loadValue($dotPath = '');
	
	/**
	 * 开头插入
	 *
	 * @param string|int   $key
	 * @param string|array $path
	 * @param int          $weight  权重
	 * @return $this
	 */
	public function unshift($key, $path, $weight = TreeFunc::DEFAULT_WEIGHT);
	
	/**
	 * 尾部添加
	 *
	 * @param string|int   $key
	 * @param string|array $path
	 * @param int          $weight  权重
	 * @return $this
	 */
	public function add($key, $path, $weight = TreeFunc::DEFAULT_WEIGHT);
	
	/**
	 * 获取列表
	 *
	 * @return TreeFunc
	 */
	public function getList(): TreeFunc;
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return TreeFuncData
	 */
	public function getListData(string $key): TreeFuncData;
	
	/**
	 * 获取列表项值
	 *
	 * @param string $key
	 * @return array|string|int|null
	 */
	public function getListDataValue(string $key);
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return TreeFunc
	 */
	public function getListValue(string $key): TreeFunc;
	
}