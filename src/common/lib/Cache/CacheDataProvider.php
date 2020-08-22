<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\consts\CacheConst;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Exception\ExceptionCache;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\common\lib\Utils\File;
use uujia\framework\base\common\lib\Utils\Str;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class CacheDataProvider
 *
 * @package uujia\framework\base\common\lib\Cache
 */
abstract class CacheDataProvider extends BaseClass implements CacheDataProviderInterface {
	use ResultTrait;
	
	/**
	 * @var CacheDataManagerInterface
	 */
	protected $_parent;
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface
	 */
	protected $_redisProviderObj;
	
	/**
	 * RunnerManager对象
	 *
	 * @var RunnerManagerInterface
	 */
	protected $_runnerManagerObj;
	
	/**
	 * 缓存Key前缀
	 * （此处是来自上层的前缀 本层的真实前缀需要以此为基础拼接
	 *  例如：$_cacheKeyPrefix = ['ev'] 要保存 key = ['ss'] 真实Key应为 'ev:ss'）
	 *
	 * @var array
	 */
	protected $_cacheKeyPrefix = [];
	
	/**
	 * 缓存key
	 *  需要拼接前缀使用
	 *
	 * @var string
	 */
	protected $_key = '';
	
	/**
	 * 标志位key
	 *  用于存储标识当前缓存状态的key 对应的value是哈希表
	 * app:cache:status -> {event => 1} （0-未知 1-缓存中 2-缓存完成 3-缓存出错）
	 *
	 * @var string
	 */
	protected $_statusKey = '';
	
	/**
	 * 是否在收集信息的同时写入缓存
	 *
	 * @var bool
	 */
	protected $_writeCache = true;
	
	/**
	 * 输入参数
	 *
	 * @var array
	 */
	protected $_params = [];
	
	// /**
	//  * 配置项
	//  *
	//  * @var array
	//  */
	// protected $_config = [];
	
	/**
	 * 缓存有效时间
	 *
	 * @var float|int
	 */
	protected $_cache_expires_time = 120 * 1000;
	
	// /**
	//  * 返回值
	//  *
	//  * @var array
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
	public function __construct(CacheDataManagerInterface $parent = null,
	                            RedisProviderInterface $redisProviderObj = null) {
		$this->_parent = $parent;
		$this->_redisProviderObj = $redisProviderObj;
		// $this->_cacheKeyPrefix = $cacheKeyPrefix;
		// $this->_config = $config;
		// $this->_cache_expires_time = $config['cache_expires_time'] ?? CacheConst::CACHE_EXPIRES_EVENT_TIME;
		$this->_cache_expires_time = CacheConstInterface::CACHE_EXPIRES_EVENT_TIME;
		
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		$this->_writeCache = true;
		
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
	 */
	public function make() {
		$this->resetResult();
		
		if (!$this->hasCache()) {
			// 写状态标记为 正在缓存中
			$this->setCacheStatus(self::CACHE_STATUS_CACHING);
			
			// 不存在缓存 调起缓存数据管理器 收集数据传来
			if ($this->isWriteCache()) {
				$this->toCache();
			}
			
			if ($this->isErr()) {
				// 写状态标记为 错误
				$this->setCacheStatus(self::CACHE_STATUS_ERROR);
				
				throw new ExceptionCache('缓存构建失败', 1000);
			}
			
			// 写状态标记为 完成
			$this->setCacheStatus(self::CACHE_STATUS_OK);
		}
		
		// yield from $this->fromCache();
	}
	
	/**
	 * 从缓存读取
	 */
	public function fromCache() {
		$this->make();
		
		yield [];
	}
	
	/**
	 * 写入缓存
	 */
	public function toCache() {
		$this->ok();
		
		return $this;
	}
	
	/**
	 * 缓存是否存在
	 *
	 * @return bool
	 */
	public function hasCache(): bool {
		$keyStatus = $this->getStatusKey();
		
		$kExist = $this->getRedisObj()->exists($keyStatus);
		if (!$kExist) {
			return false;
		}
		
		// 缓存状态 只有为缓存完成时才算存在 在缓存中报服务器繁忙异常 其他为不存在（0-未知 1-缓存中 2-缓存完成 3-缓存出错）
		// $cacheStatus = $this->getRedisObj()->hGet($keyStatus, $this->getKey());
		$cacheStatus = $this->getRedisObj()->hGet($keyStatus, static::class);
		
		switch ($cacheStatus) {
			case 1:
				throw new ExceptionCache('服务器繁忙，请稍候再试', 1000);
				break;
			
			case 2:
				return true;
				break;
		}
		
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
	 * 设置缓存状态
	 * date: 2020/7/20 16:27
	 *
	 * @param int $status
	 *
	 * @return $this
	 */
	public function setCacheStatus(int $status) {
		$keyStatus = $this->getStatusKey();
		
		// $this->getRedisObj()->hSet($keyStatus, $this->getKey(), $status);
		$this->getRedisObj()->hSet($keyStatus, static::class, $status);
		
		return $this;
	}
	
	/**
	 * 判断文件是否修改
	 *
	 * Date: 2020/8/23
	 * Time: 1:19
	 *
	 * @param string $file
	 * @return bool
	 */
	public function isFileModified(string $file) {
		// 校验文件是否存在
		if (File::isNotExists($file)) {
			return false;
		}
		
		// 文件修改时间
		$fileMTime = File::modifieTime($file);
		
		// 获取缓存记录的文件更新时间
		$cacheFileMTime = $this->getCacheFileMTime($file);
		
		// 比对修改时间 判断是否修改
		return !$cacheFileMTime || $fileMTime != $cacheFileMTime;
	}
	
	/**
	 * 获取缓存记录的文件更新时间
	 *
	 * Date: 2020/8/23
	 * Time: 1:11
	 *
	 * @param string $file
	 * @return mixed|string
	 */
	public function getCacheFileMTime(string $file) {
		// 获取缓存前缀 一般为应用名称
		$keyPrefix = $this->getCacheKeyPrefix();
		
		// 获取文件更新时间缓存key
		$keyCache = CacheConstInterface::CACHE_FILE_LAST_WRITE_TIME_KEY;
		
		// 合并key
		$key = Arr::arrToStr([$keyPrefix, $keyCache], ':');
		
		return $this->getRedisObj()->hGet($key, Str::slashLToR($file));
	}
	
	/**
	 * 更新文件时间
	 *
	 * Date: 2020/8/23
	 * Time: 1:15
	 *
	 * @param string $file
	 * @return mixed|bool|int
	 */
	public function updateCacheFileMTime(string $file) {
		// 校验文件是否存在
		if (File::isNotExists($file)) {
			return false;
		}
		
		// 获取缓存前缀 一般为应用名称
		$keyPrefix = $this->getCacheKeyPrefix();
		
		// 获取文件更新时间缓存key
		$keyCache = CacheConstInterface::CACHE_FILE_LAST_WRITE_TIME_KEY;
		
		// 合并key
		$key = Arr::arrToStr([$keyPrefix, $keyCache], ':');
		
		// 文件修改时间
		$fileMTime = File::modifieTime($file);
		
		return $this->getRedisObj()->hSet($key, Str::slashLToR($file), $fileMTime);
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
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
	
	/**
	 * @return string
	 */
	public function getStatusKey(): string {
		// 查找是否已配置 如果已配置就采用配置的值 如果未配置就采用默认的
		if (empty($this->_statusKey)) {
			$k = array_merge($this->getCacheKeyPrefix(), ['cache', 'status', $this->getKey()]);
			
			return implode(':', $k);
		}
		
		return $this->_statusKey;
	}
	
	/**
	 * @param string $statusKey
	 *
	 * @return $this
	 */
	public function setStatusKey(string $statusKey) {
		$this->_statusKey = $statusKey;
		
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
	 * @return RunnerManagerInterface
	 */
	public function getRunnerManagerObj() {
		return $this->_runnerManagerObj;
	}
	
	/**
	 * @param RunnerManagerInterface $runnerManagerObj
	 *
	 * @return $this
	 */
	public function setRunnerManagerObj(RunnerManagerInterface $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		return $this;
	}
	
	
}