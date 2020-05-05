<?php


namespace uujia\framework\base\common\lib\Tree;

use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\ResultBase;

/**
 * Class TreeFuncData
 *
 * @package uujia\framework\base\common\lib\Tree
 */
class TreeFuncData extends BaseClass {
	use ResultBase;
	
	const OTHER_KEY_RESULT = 'result';
	
	/**
	 * 父级
	 *
	 * @var TreeFunc $_parent
	 */
	protected $_parent;
	
	/**
	 * 工厂实例化的回调方法 用时才加载
	 *
	 * Example:
	 *  $_factoryFunc => function($me) {
	 *      return new XXX();
	 *  }
	 *
	 * @var \Closure $_factoryFunc
	 */
	protected $_factoryFunc = null;
	
	/**
	 * 实例化后的缓存 不用每次访问都实例化
	 */
	protected $_cache = null;
	
	/**
	 * 自动缓存
	 *
	 * @var bool $_autoCache
	 */
	protected $_autoCache = true;
	
	/**
	 * 自动加载缓存
	 *  true - get获取时会自动查找是否存在缓存
	 *  false - get将不再加载缓存
	 *
	 * @var bool $_loadCache
	 */
	protected $_loadCache = true;
	
	/**
	 * 其他附加属性或返回值等等 自由使用
	 *
	 * @var array $_other
	 */
	protected $_other = [];
	
	
	/**
	 * TreeFuncData constructor.
	 *
	 * @param      $parent
	 * @param null $factoryFunc
	 * @param null $cache
	 */
	public function __construct($parent, $factoryFunc = null, $cache = null) {
		$this->_parent      = $parent;
		$this->_factoryFunc = $factoryFunc;
		$this->_cache       = $cache;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 *
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
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = '列表项数据管理';
	}
	
	/**
	 * 获取
	 *
	 * @param array $param
	 * @param bool  $doNotCache     不保存到缓存
	 * @param bool  $doNotLoadCache 不加载缓存
	 *
	 * @return mixed|null
	 */
	public function get($param = [], $doNotCache = false, $doNotLoadCache = false) {
		if (!$doNotLoadCache && $this->isLoadCache() && $this->hasCache()) {
			return $this->cache();
		}
		
		if (empty($this->_factoryFunc) || !($this->_factoryFunc instanceof \Closure)) {
			return null;
		}
		
		//$param = array_merge([$this, $this->_parent], $param);
		$_param = [$this, $this->_parent, $param];
		
		$v = call_user_func_array($this->_factoryFunc, $_param);
		
		if (!$doNotCache && $this->isAutoCache()) {
			$this->cache($v);
		}
		
		return $v;
	}
	
	/**
	 * 获取and缓存
	 *
	 * @param array $param
	 *
	 * @return mixed|null
	 */
	public function getAndCache($param = []) {
		$v = $this->get($param);
		!$this->isAutoCache() && $this->cache($v);
		
		return $v;
	}
	
	/**
	 * 是否存在
	 *
	 * @return bool
	 */
	public function has(): bool {
		return $this->_factoryFunc !== null;
	}
	
	/**
	 * 设置
	 *  set(function ($data, $item) {
	 *
	 *  });
	 *
	 * @param \Closure $f
	 *
	 * @return $this
	 */
	public function set(\Closure $f): TreeFuncData {
		$this->_factoryFunc = $f;
		$this->removeCache();
		
		return $this;
	}
	
	/**
	 * 缓存实例
	 *
	 * @param null $v
	 *
	 * @return mixed
	 */
	public function cache($v = null) {
		if ($v === null) {
			return $this->_cache;
		} else {
			$this->_cache = $v;
		}
		
		return $this;
	}
	
	/**
	 * 缓存实例是否存在
	 *
	 * @return bool
	 */
	public function hasCache(): bool {
		return $this->_cache !== null;
	}
	
	/**
	 * 删除缓存值
	 *
	 * @return TreeFuncData
	 */
	public function removeCache(): TreeFuncData {
		$this->_cache = null;
		return $this;
	}
	
	/**
	 * 父级
	 *
	 * @return TreeFunc
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * 自动缓存
	 *
	 * @return $this|bool
	 */
	public function isAutoCache(): bool {
		return $this->_autoCache;
	}
	
	/**
	 * @param bool $isAutoCache
	 *
	 * @return $this
	 */
	public function setAutoCache(bool $isAutoCache) {
		$this->_autoCache = $isAutoCache;
		
		return $this;
	}
	
	/**
	 * 自动加载缓存
	 *  true - get获取时会自动查找是否存在缓存
	 *  false - get将不再加载缓存
	 *
	 * @return bool
	 */
	public function isLoadCache(): bool {
		return $this->_loadCache;
	}
	
	/**
	 * @param bool $isLoadCache
	 *
	 * @return $this
	 */
	public function setLoadCache(bool $isLoadCache) {
		$this->_loadCache = $isLoadCache;
		
		return $this;
	}
	
	/**
	 * @return \Closure
	 */
	public function _getFactoryFunc() {
		return $this->_factoryFunc;
	}
	
	/**
	 * @param \Closure $factoryFunc
	 *
	 * @return $this
	 */
	public function _setFactoryFunc($factoryFunc) {
		$this->_factoryFunc = $factoryFunc;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getOther(): array {
		return $this->_other;
	}
	
	/**
	 * @param array $other
	 *
	 * @return $this
	 */
	public function _setOther(array $other) {
		$this->_other = $other;
		
		return $this;
	}
	
	/**
	 * 设置附加数据
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function setKeyOther($key, $value) {
		$this->_other[$key] = $value;
		
		return $this;
	}
	
	
}