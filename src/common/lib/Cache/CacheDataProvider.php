<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\consts\CacheConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Runner\RunnerManager;

/**
 * Class CacheDataProvider
 *
 * @package uujia\framework\base\common\lib\Cache
 */
abstract class CacheDataProvider extends BaseClass implements CacheDataProviderInterface {
	
	/**
	 * @var CacheDataManagerInterface $_parent
	 */
	protected $_parent;
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface $_redisProviderObj
	 */
	protected $_redisProviderObj;
	
	/**
	 * RunnerManager对象
	 *
	 * @var RunnerManager $_runnerManagerObj
	 */
	protected $_runnerManagerObj;
	
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
	//  * 配置项
	//  *
	//  * @var array $_config
	//  */
	// protected $_config = [];
	
	/**
	 * 缓存有效时间
	 *
	 * @var float|int $_cache_expires_time
	 */
	protected $_cache_expires_time = 120 * 1000;
	
	// /**
	//  * 返回值
	//  *
	//  * @var array $_results
	//  */
	// protected $_results = [];
	
	/**
	 * CacheDataProvider constructor.
	 *
	 * @param null|CacheDataManagerInterface $parent
	 * @param RedisProviderInterface|null    $redisProviderObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct($parent = null,
	                            RedisProviderInterface $redisProviderObj = null) {
		$this->_parent = $parent;
		$this->_redisProviderObj = $redisProviderObj;
		// $this->_cacheKeyPrefix = $cacheKeyPrefix;
		// $this->_config = $config;
		// $this->_cache_expires_time = $config['cache_expires_time'] ?? CacheConst::CACHE_EXPIRES_EVENT_TIME;
		$this->_cache_expires_time = CacheConst::CACHE_EXPIRES_EVENT_TIME;
		
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 构建缓存Key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function makeCacheKey($key = '') {
		$k = $this->getCacheKeyPrefix();
		$k[] = !empty($key) ? $key : $this->_key;
		
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
	 *
	 * @return $this
	 */
	public function clearCache() {
		return $this;
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
	
	// /**
	//  * @return array
	//  */
	// public function getConfig(): array {
	// 	return $this->_config;
	// }
	//
	// /**
	//  * @param array $config
	//  *
	//  * @return CacheDataProvider
	//  */
	// public function _setConfig(array $config) {
	// 	$this->_config = $config;
	//
	// 	return $this;
	// }
	
	/**
	 * @return float|int
	 */
	public function getCacheExpiresTime() {
		return $this->_cache_expires_time;
	}
	
	/**
	 * @param float|int $cache_expires_time
	 *
	 * @return CacheDataProvider
	 */
	public function setCacheExpiresTime($cache_expires_time) {
		$this->_cache_expires_time = $cache_expires_time;
		
		return $this;
	}
	
	/**
	 * @return CacheDataManagerInterface
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * @param CacheDataManagerInterface $parent
	 *
	 * @return CacheDataProvider
	 */
	public function setParent($parent) {
		$this->_parent = $parent;
		
		return $this;
	}
	
	/**
	 * @return RedisProviderInterface
	 */
	public function getRedisProviderObj(): RedisProviderInterface {
		return $this->_redisProviderObj;
	}
	
	/**
	 * @param RedisProviderInterface $redisProviderObj
	 * @return $this
	 */
	public function setRedisProviderObj(RedisProviderInterface $redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
		
		return $this;
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->getRedisProviderObj()->getRedisObj();
	}
	
	/**
	 * @return RunnerManager
	 */
	public function getRunnerManagerObj(): RunnerManager {
		return $this->_runnerManagerObj;
	}
	
	/**
	 * @param RunnerManager $runnerManagerObj
	 *
	 * @return $this
	 */
	public function setRunnerManagerObj(RunnerManager $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		return $this;
	}
	
	
}