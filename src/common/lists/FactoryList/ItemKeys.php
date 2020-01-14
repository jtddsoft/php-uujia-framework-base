<?php


namespace uujia\framework\base\common\lists\FactoryList;

use uujia\framework\base\common\lists\FactoryList;

/**
 * Class ItemKeys
 *
 * @package uujia\framework\base\common\lists\FactoryList
 */
class ItemKeys {
	
	/**
	 * 工厂实例化的回调方法 用时才加载
	 *
	 *  $_factoryFunc => function($me) {
	 *      return new XXX();
	 *  }
	 */
	protected $_factoryFunc = null;
	
	/**
	 * 实例化后的缓存 不用每次访问都实例化
	 */
	protected $_cache = '';
	
	/**
	 * 父级FactoryList
	 * @var $_parent FactoryList
	 */
	protected $_parent;
	
	public function __construct($parent, $factoryFunc = null, $cache = '') {
		$this->_factoryFunc = $factoryFunc;
		$this->_cache = $cache;
	}
	
	
	
}