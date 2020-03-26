<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\lib\Base\BaseClass;

class CacheDataProvider extends BaseClass implements CacheDataProviderInterface {
	
	/**
	 * 是否在收集信息的同时写入缓存
	 *
	 * @var bool $_writeCache
	 */
	protected $_writeCache = true;
	
	/**
	 * 输入参数
	 *
	 * @var array $_params
	 */
	protected $_params = [];
	
	// /**
	//  * 返回值
	//  *
	//  * @var array $_results
	//  */
	// protected $_results = [];
	
	/**
	 * CacheDataProvider constructor.
	 *
	 */
	public function __construct() {
		
		
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 构建数据 写入缓存
	 *
	 * @return mixed
	 */
	public function make() {
	
	}
	
	/**
	 * 从缓存读取
	 */
	public function fromCache() {
	
	}
	
	/**
	 * 写入缓存
	 */
	public function toCache() {
	
	}
	
	/**
	 * 缓存是否存在
	 * @return bool
	 */
	public function hasCache(): bool {
		return false;
	}
	
	/**
	 * 清空缓存
	 */
	public function clearCache() {
	
	}
	
	/**
	 * @return bool
	 */
	public function isWriteCache(): bool {
		return $this->_writeCache;
	}
	
	/**
	 * @param bool $writeCache
	 *
	 * @return $this
	 */
	public function setWriteCache(bool $writeCache) {
		$this->_writeCache = $writeCache;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}
	
	/**
	 * @param array $params
	 * @return $this
	 */
	public function setParams($params) {
		$this->_params = $params;
		
		return $this;
	}
	
	// /**
	//  * @return array
	//  */
	// public function getResults() {
	// 	return $this->_results;
	// }
	//
	// /**
	//  * @param array $results
	//  * @return $this
	//  */
	// public function setResults($results) {
	// 	$this->_results = $results;
	//
	// 	return $this;
	// }
	
}