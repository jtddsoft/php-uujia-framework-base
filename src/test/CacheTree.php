<?php

namespace uujia\framework\base\common\lib;

use uujia\framework\base\common\lib\Tree\TreeNode;
use uujia\framework\base\traits\NameBase;

/**
 * Class FactoryCacheTree
 *
 * @package uujia\framework\base\common\lib
 */
class CacheTree extends TreeNode {
	use NameBase;
	
	public static $_WORK_MODE = [
		'unknow' => 0,  // 未知
		'key'    => 1,  // key
		'weight' => 2,  // 权重
	];
	
	/**
	 * 构造方法func 需要时才加载
	 *
	 * 工作模式
	 *  1、[key] 有具体key 类似容器中使用 (ItemKeys -> itemData)
	 *  $_l => [
	 *      'default' => [
	 *          '\Base': function($this) {
	 *              return new XXX();
	 *          },
	 *          '\Log': function($this) {
	 *              return new XXX();
	 *          },
	 *      ]
	 *  ];
	 *
	 *  2、[weight] 权重 当存在多个相同子项
	 */
	protected $_l = [
		// 'default' => []
	];
	// _c(cache)每次实例化都会存入对象实例 如果已存在就覆盖
	protected $_c = [
		// 'default' => []
	];
	
	// 为支持多节点
	protected $_name = 'default';
	
	// 自动缓存
	protected $_autoCache = true;
	
	public function __construct() {
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
		$this->name_info['intro'] = '工厂列表管理';
	}
	
	/**
	 * 获取or设置 _l
	 *
	 * @param null $l
	 * @return $this|array
	 */
	public function l($l = null) {
		if ($l === null) {
			return isset($this->_l[$this->name()]) ? $this->_l[$this->name()] : [];
		} else {
			$this->_l[$this->name()] = $l;
		}
		
		return $this;
	}
	
	/**
	 * 获取or设置 _c
	 *
	 * @param null $c
	 * @return $this|array
	 */
	public function c($c = null) {
		if ($c === null) {
			return $this->_c[$this->name()] ?? [];
		} else {
			$this->_c[$this->name()] = $c;
		}
		
		return $this;
	}
	
	/**
	 * 为实现多节点
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
	 * 自动缓存
	 *
	 * @param null $autoCache
	 * @return $this|bool
	 */
	public function autoCache($autoCache = null) {
		if ($autoCache === null) {
			return $this->_autoCache;
		} else {
			$this->_autoCache = $autoCache;
		}
		
		return $this;
	}
	
	/**
	 * 获取
	 * @inheritDoc
	 */
	public function get($k, $param = []) {
		if ($this->hasCache($k)) {
			return $this->cache($k);
		}
		
		if (empty($this->l()[$k]) || !($this->l()[$k] instanceof \Closure)) {
			return null;
		}
		
		// $v = call_user_func_array($this->_l[$this->name()][$k], $param);
		$v = call_user_func_array($this->l()[$k], $param);
		
		if ($this->autoCache()) {
			$this->cache($k, $v);
		}
		
		return $v;
	}
	
	/**
	 * 获取and缓存
	 * @inheritDoc
	 */
	public function getAndCache($k) {
		$v = $this->get($k);
		$this->cache($k, $v);
		
		return $v;
	}
	
	/**
	 * 是否存在
	 * @inheritDoc
	 */
	public function has($k) {
		return array_key_exists($k, $this->l());
	}
	
	/**
	 * 设置
	 *
	 * @param          $k
	 * @param \Closure $f
	 * @return $this
	 */
	public function set($k, \Closure $f) {
		$this->_l[$this->name()][$k] = $f;
		
		return $this;
	}
	
	/**
	 * 缓存实例
	 *
	 * @param      $k
	 * @param null $v
	 * @return mixed
	 */
	public function cache($k, $v = null) {
		if ($v === null) {
			return $this->c()[$k];
		} else {
			$this->_c[$this->name()][$k] = $v;
		}
		
		return $this;
	}
	
	/**
	 * 缓存实例是否存在
	 *
	 * @param $k
	 * @return bool
	 */
	public function hasCache($k) {
		return array_key_exists($k, $this->c());
	}
	
	/**
	 * 删除缓存值
	 *
	 * @param $k
	 * @return FactoryCacheTree
	 */
	public function removeCache($k) {
		unset($this->_c[$this->name()][$k]);
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isAutoCache(): bool {
		return $this->_autoCache;
	}
}