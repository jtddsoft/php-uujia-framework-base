<?php


namespace uujia\framework\base\common\lib\FactoryCache;

use uujia\framework\base\traits\NameBase;

/**
 * Class Data
 *
 * @package uujia\framework\base\common\lib\FactoryCache
 */
class Data {
	use NameBase;
	
	/**
	 * 父级
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
	 */
	protected $_factoryFunc = null;
	
	/**
	 * 实例化后的缓存 不用每次访问都实例化
	 */
	protected $_cache = null;
	
	/**
	 * 自动缓存
	 *
	 * @var bool $_isAutoCache
	 */
	protected $_isAutoCache = true;
	
	
	
	/**
	 * ItemData constructor.
	 *
	 * @param      $parent
	 * @param null $factoryFunc
	 * @param null $cache
	 */
	public function __construct($parent, $factoryFunc = null, $cache = null) {
		$this->_parent = $parent;
		$this->_factoryFunc = $factoryFunc;
		$this->_cache = $cache;
		
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
		$this->name_info['intro'] = '列表项数据管理';
	}
	
	/**
	 * 获取
	 *
	 * @param array $param
	 * @param bool  $doNotCache
	 * @return mixed|null
	 */
	public function get($param = [], $doNotCache = false) {
		if ($this->hasCache()) {
			return $this->cache();
		}
		
		if (empty($this->_factoryFunc) || !($this->_factoryFunc instanceof \Closure)) {
			return null;
		}
		
		$param = array_merge([$this, $this->_parent], $param);
		
		$v = call_user_func_array($this->_factoryFunc, $param);
		
		if (!$doNotCache && $this->isAutoCache()) {
			$this->cache($v);
		}
		
		return $v;
	}
	
	/**
	 * 获取and缓存
	 *
	 * @param array $param
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
	 * @return $this
	 */
	public function set(\Closure $f): Data {
		$this->_factoryFunc = $f;
		$this->removeCache();
		
		return $this;
	}
	
	/**
	 * 缓存实例
	 *
	 * @param null $v
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
	 * @return Data
	 */
	public function removeCache(): Data {
		$this->_cache = null;
		return $this;
	}
	
	/**
	 * 父级
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
		return $this->_isAutoCache;
	}
	
	/**
	 * @param bool $isAutoCache
	 */
	public function setIsAutoCache(bool $isAutoCache) {
		$this->_isAutoCache = $isAutoCache;
	}
	
	
}