<?php
/**
 *
 * author: lz
 * Date: 2020/8/5
 * Time: 14:38
 */

namespace uujia\framework\base\common\lib\Aop\Cache;


use Generator;
use ReflectionMethod;
use uujia\framework\base\common\consts\AopConstInterface;
use uujia\framework\base\common\lib\Annotation\AopTarget;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Reflection\Reflection;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\common\lib\Utils\Json;

/**
 * Class AopProxyCacheDataProvider
 * Date: 2020/8/5
 * Time: 14:39
 *
 * @package uujia\framework\base\common\lib\Aop\cache
 */
class AopProxyCacheDataProvider extends CacheDataProvider {
	
	/**
	 * 反射对象
	 *
	 * @var Reflection;
	 */
	protected $_reflectionObj = null;
	
	/**
	 * key前缀
	 *  Key=app:aop
	 *
	 * @var string
	 */
	protected $_keyPrefixAopProxyClass = '';
	
	/**
	 * 类名
	 *
	 * @var string
	 */
	protected $_className = '';
	
	/**
	 * 生成的代理类命名空间定义
	 *
	 * @var string
	 */
	protected $_proxyClassNameSpace = '';
	
	/**
	 * AopProxyCacheDataProvider constructor.
	 *
	 * @param CacheDataManagerInterface|null $parent
	 * @param RedisProviderInterface|null    $redisProviderObj
	 */
	public function __construct(CacheDataManagerInterface $parent = null,
	                            RedisProviderInterface $redisProviderObj = null,
	                            Reflection $reflectionObj = null) {
		$this->_reflectionObj = $reflectionObj;
		
		parent::__construct($parent, $redisProviderObj);
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = 'Aop代理缓存数据提供商类';
	}
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 写Aop到缓存
	 *
	 * @return $this
	 */
	public function toCacheAop() {
		// Aop类名
		$className = $this->getClassName();
		
		// Aop缓存中的key
		$keyAop = $this->getKeyPrefixAopProxyClass();
		
		// 代理ID 随机码
		$proxyId = uniqid();
		
		// 需要生成的代理类类名
		$aopProxyClassName = $this->getProxyClassNameSpace() . '\\' . str_replace('\\', '_', $className . '_' . $proxyId);
		
		// 写入缓存
		$this->getRedisObj()->hSet($keyAop, str_replace('\\', '/', $className), $aopProxyClassName);
		
		return $this;
	}
	
	/**
	 * 获取缓存中与AopTarget匹配的Aop有序集合列表
	 *
	 * @return Generator
	 */
	public function fromCacheAop() {
		// Aop类名
		$className = $this->getClassName();
		
		// Aop缓存中的key
		$keyAop = $this->getKeyPrefixAopProxyClass();
		
		// 查找哈希表中是否存在AopTarget标识记录
		$aopProxyClass = $this->getRedisObj()->hGet($keyAop, str_replace('\\', '/', $className));
		
		yield $aopProxyClass ?? '';
	}
	
	/**
	 * 清空Aop相关缓存
	 *  1、一个hash列表
	 *  2、一堆key
	 *
	 * @return $this
	 */
	public function clearCacheAop() {
		/** @var \Redis|\Swoole\Coroutine\Redis $redis */
		$redis = $this->getRedisObj();
		
		// 清空hash列表
		
		// 监听者列表缓存中的key
		$keyAop = $this->getKeyPrefixAopProxyClass();
		
		// 清空缓存key
		$redis->del($keyAop);
		
		return $this;
	}
	
	/**************************************************************
	 * Cache
	 **************************************************************/
	
	/**
	 * 构建并获取数据 如果缓存没有就写入缓存
	 */
	public function make() {
		parent::make();
	}
	
	/**
	 * 从缓存读取
	 */
	public function fromCache() {
		$this->make();
		
		yield from $this->fromCacheAop();
	}
	
	/**
	 * 写入缓存
	 */
	public function toCache() {
		// 先清空
		// $this->clearCache();
		
		// 写入缓存Aop
		$this->toCacheAop();
		
		return $this;
	}
	
	/**
	 * 缓存是否存在
	 *
	 * @return bool
	 */
	public function hasCache(): bool {
		// Aop类名
		$className = $this->getClassName();
		
		// 获取
		$keyAop = $this->getKeyPrefixAopProxyClass();
		
		return $this->getRedisObj()->hExists($keyAop, str_replace('\\', '/', $className));
	}
	
	/**
	 * 清空缓存
	 */
	public function clearCache() {
		// 清空Aop
		$this->clearCacheAop();
		
		parent::clearCache();
		
		return $this;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return Reflection
	 */
	public function getReflectionObj(): Reflection {
		if (empty($this->_reflectionObj)) {
			$this->_reflectionObj = new Reflection();
		}
		
		return $this->_reflectionObj;
	}
	
	/**
	 * @param Reflection $reflectionObj
	 *
	 * @return $this
	 */
	public function setReflectionObj(Reflection $reflectionObj) {
		$this->_reflectionObj = $reflectionObj;
		
		return $this;
	}
	
	/**
	 * 主列表key前缀
	 * app:aop -> {#namespace}
	 *
	 * @param array $ks
	 *
	 * @return string
	 */
	public function getKeyPrefixAopProxyClass(array $ks = []): string {
		if (empty($this->_keyPrefixAopProxyClass) || !empty($ks)) {
			// 构建key的层级数组
			// $keys   = [];
			$keys   = $this->getParent()->getCacheKeyPrefix();
			$keys[] = AopConstInterface::CACHE_KEY_PREFIX_AOP_PROXY_CLASS;
			
			// 附加额外key
			$keys = array_merge($keys, $ks);
			
			// key的层级数组转成字符串key
			if (empty($ks)) {
				$this->_keyPrefixAopProxyClass = Arr::arrToStr($keys, ':');
			} else {
				return Arr::arrToStr($keys, ':');
			}
		}
		
		return $this->_keyPrefixAopProxyClass;
	}
	
	/**
	 * @param string $keyPrefixAopProxyClass
	 *
	 * @return $this
	 */
	public function setKeyPrefixAopProxyClass(string $keyPrefixAopProxyClass) {
		$this->_keyPrefixAopProxyClass = $keyPrefixAopProxyClass;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getClassName(): string {
		return $this->_className;
	}
	
	/**
	 * @param string $classNameBuf
	 *
	 * @return $this
	 */
	public function setClassName(string $classNameBuf) {
		$this->_className = $classNameBuf;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getProxyClassNameSpace(): string {
		return $this->_proxyClassNameSpace ?? '';
	}
	
	/**
	 * @param string $proxyClassNameSpace
	 *
	 * @return $this
	 */
	public function setProxyClassNameSpace(string $proxyClassNameSpace) {
		$this->_proxyClassNameSpace = $proxyClassNameSpace;
		
		return $this;
	}
	
	
}