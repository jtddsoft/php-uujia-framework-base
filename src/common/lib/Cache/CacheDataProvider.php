<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\lib\Base\BaseClass;

class CacheDataProvider extends BaseClass implements CacheDataProviderInterface {
	
	/**
	 * 缓存Key前缀
	 * （此处是来自上层的前缀 本层的真实前缀需要以此为基础拼接
	 *  例如：$_cacheKeyPrefix = ['ev'] 要保存 key = ['ss'] 真实Key应为 'ev:ss'）
	 *
	 * @var array $_cacheKeyPrefix
	 */
	protected $_cacheKeyPrefix = [];
	
	/**
	 * 缓存key
	 *  需要拼接前缀使用
	 *
	 * @var string $_key
	 */
	protected $_key = '';
	
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
	 * @param array $cacheKeyPrefix
	 */
	public function __construct($cacheKeyPrefix = []) {
		$this->_cacheKeyPrefix = $cacheKeyPrefix;
		
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 构建缓存Key
	 *
	 * @return string
	 */
	public function makeCacheKey() {
		$k = $this->getCacheKeyPrefix();
		$k[] = $this->_key;
		
		return implode(':', $k);
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
	 * 获取拼接后的缓存key
	 *
	 * @param string $currKey 当前key
	 * @return string
	 */
	public function getCacheKey($currKey = '') {
		// 前缀 + 起始key + 当前key = 最终使用key
		$k = array_merge($this->getCacheKeyPrefix(), [$this->getKey(), $currKey]);
		
		return implode(':', $k);
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
	
	/**
	 * @return array
	 */
	public function getCacheKeyPrefix() {
		return $this->_cacheKeyPrefix;
	}
	
	/**
	 * @param array $cacheKeyPrefix
	 *
	 * @return CacheDataProvider
	 */
	public function setCacheKeyPrefix($cacheKeyPrefix) {
		$this->_cacheKeyPrefix = $cacheKeyPrefix;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getKey(): string {
		return $this->_key;
	}
	
	/**
	 * @param string $key
	 *
	 * @return CacheDataProvider
	 */
	public function setKey(string $key) {
		$this->_key = $key;
		
		return $this;
	}
	
}