<?php


namespace uujia\framework\base\common\lib\FactoryCache;

use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\traits\NameBase;

/**
 * Class Pool
 *
 * @package uujia\framework\base\common\lib\FactoryCache
 */
class Pool {
	use NameBase;
	
	/**
	 * 父级FactoryList
	 *
	 * @var $_parent TreeFunc
	 */
	protected $_parent;
	
	/**
	 * 工厂实例化的回调方法 用时才加载
	 *
	 * Example:
	 *  $_items = [
	 *      'app_config' => new ItemData(),
	 *      'sys_config' => new ItemData(),
	 *  ]
	 *
	 */
	protected $_data = [];
	
	
	/**
	 * ItemKeys constructor.
	 *
	 * @param        $parent
	 */
	public function __construct($parent) {
		$this->_parent = $parent;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		$this->initNameInfo();
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '工厂数据列表管理';
	}
	
	/**
	 * 获取and缓存 item
	 *
	 * @param string          $key
	 * @param TreeFuncData $value
	 * @return $this|TreeFuncData
	 */
	public function data(string $key, TreeFuncData $value = null) {
		if ($value === null) {
			return $this->_data[$key] ?? null;
		} else {
			$this->_data[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * 自动缓存
	 *
	 * @return $this|bool
	 */
	public function isAutoCache(): bool {
		return $this->getParent()->isAutoCache();
	}
	
	/**
	 * 获取
	 *
	 * @param string $k
	 * @param array  $param
	 * @return mixed|null
	 */
	public function get(string $k, $param = []) {
		$v = $this->has($k) ? $this->item($k)->get($param) : null;
		
		return $v;
	}
	
	/**
	 * 获取and缓存
	 *
	 * @param string $k
	 * @param array  $param
	 * @return mixed|null
	 */
	public function getAndCache(string $k, $param = []) {
		$v = $this->has($k) ? $this->item($k)->getAndCache($param) : null;
		
		return $v;
	}
	
	/**
	 * 是否存在
	 *
	 * @param string $k
	 * @return bool
	 */
	public function has(string $k): bool {
		return !empty($this->item($k)) && $this->item($k)->has();
	}
	
	/**
	 * 设置
	 *
	 * @param string   $k
	 * @param \Closure $f
	 * @return $this
	 */
	public function set(string $k, \Closure $f) {
		if ($this->has($k)) {
			$this->item($k)->set($f);
		} else {
			$this->item($k, new TreeFuncData($this, $f));
		}
		
		return $this;
	}
	
	/**
	 * 缓存实例
	 *
	 * @param string $k
	 * @param null   $v
	 * @return mixed
	 */
	public function cache(string $k, $v = null) {
		if ($v === null) {
			return $this->has($k) ? $this->item($k)->cache() : null;
		} else {
			$this->has($k) && $this->item($k)->cache($v);
		}
		
		return $this;
	}
	
	/**
	 * 缓存实例是否存在
	 *
	 * @param string $k
	 * @return bool
	 */
	public function hasCache(string $k) {
		return $this->has($k) && $this->item($k)->hasCache();
	}
	
	/**
	 * 删除缓存值
	 *
	 * @param $k
	 * @return TreeNode
	 */
	public function removeCache($k) {
		$this->has($k) && $this->item($k)->removeCache();
		return $this;
	}
	
	/**
	 * @return TreeFunc
	 */
	public function getParent(): TreeFunc {
		return $this->_parent;
	}
	
	
	
}