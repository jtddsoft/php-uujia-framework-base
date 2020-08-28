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
use uujia\framework\base\common\lib\Utils\Str;

/**
 * Class AopCacheDataProvider
 * Date: 2020/8/5
 * Time: 14:39
 *
 * @package uujia\framework\base\common\lib\Aop\cache
 */
abstract class AopCacheDataProvider extends CacheDataProvider {
	
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
	protected $_keyPrefixAop = '';
	
	/*******************************
	 * 解析Aop 临时存储
	 *******************************/
	
	/**
	 * 类名
	 *
	 * @var string
	 */
	protected $_classNameBuf = '';
	
	/**
	 * 反射得到所有public方法
	 *
	 * @var ReflectionMethod[]
	 */
	protected $_refMethods = [];
	
	/**
	 * 反射得到注解 AopTarget 集合
	 *
	 * @var AopTarget[]
	 */
	protected $_aopTargets = [];
	
	/**
	 * 需要触发的 AopTarget类名
	 *
	 * @var string
	 */
	protected $_aopTargetClass = '';
	
	/**************************************************************
	 * 缓存
	 **************************************************************/
	
	/**
	 * 临时缓存redis查到的AopClass
	 *
	 * @var array
	 */
	protected $_aopClassListBuf = [];
	
	/**
	 * AopCacheDataProvider constructor.
	 *
	 * @param CacheDataManagerInterface|null $parent
	 * @param RedisProviderInterface|null    $redisProviderObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(CacheDataManagerInterface $parent = null,
	                            RedisProviderInterface $redisProviderObj = null,
	                            Reflection $reflectionObj = null) {
		$this->_reflectionObj = $reflectionObj;
		
		parent::__construct($parent, $redisProviderObj);
	}
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 获取收集Aop类名集合
	 *  yield [];
	 *
	 * @return Generator
	 */
	abstract public function getAops(): Generator;
	
	/**
	 * 加载收集的Aop类集合
	 *
	 * @return $this
	 */
	public function loadAops() {
		foreach ($this->getAops() as $itemAop) {
			$this->parseAop($itemAop)
			     ->toCacheAop();
		}
		
		return $this;
	}
	
	/**
	 * 解析Aop类
	 *
	 * @param $className
	 *
	 * @return $this
	 */
	public function parseAop($className) {
		$refObj = $this->getReflectionObj()
		               ->reset()
		               ->setClassName($className)
		               ->load();
		
		$this->setClassNameBuf($className);
		
		$this->setRefMethods($refObj->methods(Reflection::METHOD_OF_PUBLIC)
		                            ->getMethodObjs());
		
		$this->setAopTargets($refObj->annotation(AopTarget::class)
		                            ->getAnnotationObjs());
		
		return $this;
	}
	
	/**
	 * 写Aop拦截者之后到缓存
	 *
	 * @return Generator
	 */
	public function makeCacheAop() {
		/********************************
		 * 分两部分
		 *  1、只是个列表 方便遍历
		 *  2、String类型的key value。其中key就是监听者要监听的事件名称（可能有通配符模糊匹配）
		 ********************************/
		
		/********************************
		 * 解析class
		 ********************************/
		
		$_aopTargets   = $this->getAopTargets();
		$_methods      = $this->getRefMethods();
		$_aopClassName = $this->getClassNameBuf();
		
		if (empty($_aopTargets)) {
			return [];
		}
		
		// 遍历每一个Aop注解
		foreach ($_aopTargets as $aopTarget) {
			// 要切入的类 目标类
			$aopTargetClassName = $aopTarget->value;
			
			// weight
			$weight = $aopTarget->weight;
			
			if (empty($aopTargetClassName)) {
				continue;
			}
			
			$name = str_replace('\\', '.', $aopTargetClassName);
			
			yield [
				'weight'       => $weight,
				'name'         => $name,
				'aopClassName' => $_aopClassName,
				'aopTarget'    => $aopTargetClassName,
			];
		}
	}
	
	/**
	 * 写Aop到缓存
	 *
	 * @return $this
	 */
	public function toCacheAop() {
		// Aop类名
		$className = $this->getClassNameBuf();
		
		// Aop列表缓存中的key
		$keyAop = $this->makeKeyPrefixAop();
		
		foreach ($this->makeCacheAop() as $item) {
			$_weight    = $item['weight'] ?? 100;
			$_name      = $item['name'] ?? '';
			$_aopTarget = $item['aopTarget'] ?? '';
			
			if (empty($_name)) {
				continue;
			}
			
			// 写入Aop类列表到缓存
			// 格式：app:aop -> hash表 app\hello\X -> '["app\hello\AopXA", "app\hello\AopXB"]'
			$classNames = $this->getRedisObj()->hGet($keyAop, Str::slashLToR($_aopTarget));
			if (!empty($classNames)) {
				$classNames = Json::jd($classNames);
			} else {
				$classNames = [];
			}
			
			if (!in_array($className, $classNames)) {
				$classNames[] = $className;
			}
			
			$this->getRedisObj()->hSet($keyAop, Str::slashLToR($_aopTarget), Json::je($classNames));
			
			// 写AopTarget对应Aop有序集合
			// 格式：app:aopc:app.hello.X -> zset表 app\hello\AopXA -> 100
			//                                     app\hello\AopXB -> 100
			$keyAopC = $this->makeKeyPrefixAopClass([$_name]);
			
			// $this->getRedisObj()->zAdd($keyAopC, $_weight, Str::slashLToR($_aopTarget));
			$this->getRedisObj()->zAdd($keyAopC, $_weight, Str::slashLToR($className));
		}
		
		return $this;
	}
	
	/**
	 * 获取缓存中与AopTarget匹配的Aop有序集合列表
	 *
	 * @param string $aopTargetClass Aop目标类
	 * @return \Generator
	 */
	public function fromCacheAop($aopTargetClass) {
		if (isset($this->_aopClassListBuf[$aopTargetClass])) {
			$zAopClassList = $this->_aopClassListBuf[$aopTargetClass] ?? [];
		} else {
			$keyAop = $this->makeKeyPrefixAop();
			
			// 查找哈希表中是否存在AopTarget标识记录
			$_aopTargetClass = Str::slashLToR($aopTargetClass);
			$hExist = $this->getRedisObj()->hExists($keyAop, $_aopTargetClass);
			
			// 如果不存在 返回
			if (!$hExist) {
				return [];
			}
			
			// 如果存在获取内容 返回
			$_name = str_replace('\\', '.', $aopTargetClass);
			
			$keyAopC = $this->makeKeyPrefixAopClass([$_name]);
			
			// 判断是否存在
			$kExist = $this->getRedisObj()->exists($keyAopC);
			
			// 如果不存在 返回空
			if (!$kExist) {
				return [];
			}
			
			// 如果存在 读取监听列表（key是触发者的标识名 value是有序集合存储的是从监听列表中匹配的服务配置json）
			$zAopClassList = $this->getRedisObj()->zRange($keyAopC, 0, -1, true);
			
			// 缓存
			$this->_aopClassListBuf[$aopTargetClass] = $zAopClassList;
		}
		
		foreach ($zAopClassList as $zValue => $zScore) {
			$_className = Str::slashRToL($zValue);
			// yield $_className => $zScore;
			yield $_className;
		}
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
		
		// 1、清空hash列表
		
		// 监听者列表缓存中的key
		$keyAop = $this->makeKeyPrefixAop();
		
		// 清空缓存key
		$redis->del($keyAop);
		
		// 2、清空一堆key
		
		// 搜索key
		$k = $this->makeKeyPrefixAopClass(['*']);
		
		$iterator = null;
		
		while(false !== ($keys = $redis->scan($iterator, $k, 20))) {
			if (empty($keys)) {
				continue;
			}
			
			$redis->del($keys);
		}
		
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
	 *
	 * Date: 2020/8/27
	 * Time: 0:55
	 *
	 * @return array|Generator
	 */
	public function fromCache() {
		$this->make();
		
		// 获取要触发的AopTarget类
		$_aopTargetClass = $this->getAopTargetClass();
		
		if (empty($_aopTargetClass)) {
			return [];
		}
		
		yield from $this->fromCacheAop($_aopTargetClass);
	}
	
	/**
	 * 写入缓存
	 */
	public function toCache() {
		// 先清空
		$this->clearCache();
		
		// 加载Aop
		$this->loadAops();
		
		return $this;
	}
	
	/**
	 * 缓存是否存在
	 *
	 * @return bool
	 */
	public function hasCache(): bool {
		// 获取
		$keyAop = $this->makeKeyPrefixAop();
		
		return $this->getRedisObj()->exists($keyAop);
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
	
	/**
	 * 主列表key前缀
	 * app:aop -> {#namespace}
	 *
	 * @param array $ks
	 *
	 * @return string
	 */
	public function makeKeyPrefixAop(array $ks = []): string {
		if (empty($this->_keyPrefixAop) || !empty($ks)) {
			// 构建key的层级数组
			// $keys   = [];
			$keys   = $this->getParent()->getCacheKeyPrefix();
			$keys[] = AopConstInterface::CACHE_KEY_PREFIX_AOP;
			
			// 附加额外key
			$keys = array_merge($keys, $ks);
			
			// key的层级数组转成字符串key
			if (empty($ks)) {
				$this->_keyPrefixAop = Arr::arrToStr($keys, ':');
			} else {
				return Arr::arrToStr($keys, ':');
			}
		}
		
		return $this->_keyPrefixAop;
	}
	
	/**
	 * 拦截者列表key前缀
	 * app:aopc ->
	 *
	 * @param array $ks
	 *
	 * @return string
	 */
	public function makeKeyPrefixAopClass(array $ks = []): string {
		// 构建key的层级数组
		// $keys   = [];
		$keys   = $this->getParent()->getCacheKeyPrefix();
		$keys[] = AopConstInterface::CACHE_KEY_PREFIX_AOP_CLASS;
		
		// 附加额外key
		!empty($ks) && $keys = array_merge($keys, $ks);
		
		// key的层级数组转成字符串key
		return Arr::arrToStr($keys, ':');
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
	 * @return string
	 */
	public function getKeyPrefixAop(): string {
		return $this->_keyPrefixAop;
	}
	
	/**
	 * @param string $keyPrefixAop
	 */
	public function setKeyPrefixAop(string $keyPrefixAop) {
		$this->_keyPrefixAop = $keyPrefixAop;
	}
	
	/**
	 * @return string
	 */
	public function getClassNameBuf(): string {
		return $this->_classNameBuf;
	}
	
	/**
	 * @param string $classNameBuf
	 *
	 * @return $this
	 */
	public function setClassNameBuf(string $classNameBuf) {
		$this->_classNameBuf = $classNameBuf;
		
		return $this;
	}
	
	/**
	 * @return ReflectionMethod[]
	 */
	public function &getRefMethods(): array {
		return $this->_refMethods;
	}
	
	/**
	 * @param ReflectionMethod[] $refMethods
	 *
	 * @return $this
	 */
	public function setRefMethods(array $refMethods) {
		$this->_refMethods = $refMethods;
		
		return $this;
	}
	
	/**
	 * @return AopTarget[]
	 */
	public function &getAopTargets(): array {
		return $this->_aopTargets;
	}
	
	/**
	 * @param AopTarget[] $aopTargets
	 *
	 * @return $this
	 */
	public function setAopTargets(array $aopTargets) {
		$this->_aopTargets = $aopTargets;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getAopTargetClass(): string {
		return $this->_aopTargetClass;
	}
	
	/**
	 * @param string $aopTargetClass
	 *
	 * @return $this
	 */
	public function setAopTargetClass(string $aopTargetClass) {
		$this->_aopTargetClass = $aopTargetClass;
		
		return $this;
	}
	
	
}