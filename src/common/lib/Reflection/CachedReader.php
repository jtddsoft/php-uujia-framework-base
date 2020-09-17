<?php

namespace uujia\framework\base\common\lib\Reflection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;

class CachedReader implements Reader {
	
	const CACHE_KEY = 'cache:reader:annot';
	
	/**
	 * @var Reader
	 */
	private $delegate;
	
	/**
	 * 运行时管理对象
	 *
	 * @var RunnerManagerInterface
	 */
	protected $_runnerManagerObj = null;
	
	/**
	 * @var array
	 */
	private $loadedAnnotations = [];
	
	/**
	 * @var \Redis
	 */
	private $cache;
	
	/**
	 * @var boolean
	 */
	private $debug;
	
	/**
	 * @var string
	 */
	private $cacheKeyBuf = '';
	
	/**
	 * CachedReader constructor.
	 *
	 * @param Reader                      $reader
	 * @param RunnerManagerInterface      $runnerManagerObj
	 * @param RedisProviderInterface|null $redisProviderObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 * @AutoInjection(arg = "reader")
	 */
	public function __construct(?Reader $reader,
	                            RunnerManagerInterface $runnerManagerObj,
	                            RedisProviderInterface $redisProviderObj) {
		$this->delegate = $reader;
		
		$this->cache = $redisProviderObj->getRedisObj();
		
		$this->_runnerManagerObj = $runnerManagerObj;
		$this->debug             = $runnerManagerObj->isDebug();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getClassAnnotations(ReflectionClass $class) {
		$cacheKey = $class->getName();
		
		if (isset($this->loadedAnnotations[$cacheKey])) {
			return $this->loadedAnnotations[$cacheKey];
		}
		
		if (false === ($annots = $this->fetchFromCache($cacheKey, $class))) {
			$annots = $this->delegate->getClassAnnotations($class);
			$this->saveToCache($cacheKey, $annots);
		}
		
		return $this->loadedAnnotations[$cacheKey] = $annots;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getClassAnnotation(ReflectionClass $class, $annotationName) {
		foreach ($this->getClassAnnotations($class) as $annot) {
			if ($annot instanceof $annotationName) {
				return $annot;
			}
		}
		
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getPropertyAnnotations(\ReflectionProperty $property) {
		$class    = $property->getDeclaringClass();
		$cacheKey = $class->getName() . '$' . $property->getName();
		
		if (isset($this->loadedAnnotations[$cacheKey])) {
			return $this->loadedAnnotations[$cacheKey];
		}
		
		if (false === ($annots = $this->fetchFromCache($cacheKey, $class))) {
			$annots = $this->delegate->getPropertyAnnotations($property);
			$this->saveToCache($cacheKey, $annots);
		}
		
		return $this->loadedAnnotations[$cacheKey] = $annots;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName) {
		foreach ($this->getPropertyAnnotations($property) as $annot) {
			if ($annot instanceof $annotationName) {
				return $annot;
			}
		}
		
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getMethodAnnotations(\ReflectionMethod $method) {
		$class    = $method->getDeclaringClass();
		$cacheKey = $class->getName() . '#' . $method->getName();
		
		if (isset($this->loadedAnnotations[$cacheKey])) {
			return $this->loadedAnnotations[$cacheKey];
		}
		
		if (false === ($annots = $this->fetchFromCache($cacheKey, $class))) {
			$annots = $this->delegate->getMethodAnnotations($method);
			$this->saveToCache($cacheKey, $annots);
		}
		
		return $this->loadedAnnotations[$cacheKey] = $annots;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getMethodAnnotation(\ReflectionMethod $method, $annotationName) {
		foreach ($this->getMethodAnnotations($method) as $annot) {
			if ($annot instanceof $annotationName) {
				return $annot;
			}
		}
		
		return null;
	}
	
	public function clearLoadedAnnotations() {
		$this->loadedAnnotations = [];
	}
	
	private function fetchFromCache($cacheKey, ReflectionClass $class) {
		$_cacheKey = str_replace('\\', '/', $cacheKey);
		if ((!$this->debug || $this->isCacheFresh($cacheKey, $class)) && $this->cache->hExists($this->getCacheKey(), $_cacheKey)) {
			return unserialize($this->cache->hGet($this->getCacheKey(), $_cacheKey));
		}
		
		return false;
	}
	
	private function saveToCache($cacheKey, $value) {
		$_cacheKey = str_replace('\\', '/', $cacheKey);
		$this->cache->hSet($this->getCacheKey(), $_cacheKey, serialize($value));
		if ($this->debug) {
			$this->cache->hSet($this->getCacheKey(), '[C]' . $_cacheKey, time());
		}
	}
	
	private function isCacheFresh($cacheKey, ReflectionClass $class) {
		if (null === $lastModification = $this->getLastModification($class)) {
			return true;
		}
		
		$_cacheKey = str_replace('\\', '/', $cacheKey);
		return $this->cache->hGet($this->getCacheKey(), '[C]' . $_cacheKey) >= $lastModification;
	}
	
	private function getLastModification(ReflectionClass $class) {
		$filename = $class->getFileName();
		$parent   = $class->getParentClass();
		
		return max(array_merge(
			           [$filename ? filemtime($filename) : 0],
			           array_map([$this, 'getTraitLastModificationTime'], $class->getTraits()),
			           array_map([$this, 'getLastModification'], $class->getInterfaces()),
			           $parent ? [$this->getLastModification($parent)] : []
		           ));
	}
	
	private function getTraitLastModificationTime(ReflectionClass $reflectionTrait) {
		$fileName = $reflectionTrait->getFileName();
		
		return max(array_merge(
			           [$fileName ? filemtime($fileName) : 0],
			           array_map([$this, 'getTraitLastModificationTime'], $reflectionTrait->getTraits())
		           ));
	}
	
	/**
	 * 获取缓存key
	 *
	 * Date: 2020/9/17
	 * Time: 0:45
	 *
	 * @return string
	 */
	public function getCacheKey() {
		$key = $this->getCacheKeyBuf();
		if (!empty($key)) {
			return $key;
		}
		
		$key = $this->getRunnerManagerObj()->getAppName() . ':' . self::CACHE_KEY;
		$this->setCacheKeyBuf($key);
		
		return $key;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return Reader
	 */
	public function getDelegate(): Reader {
		return $this->delegate;
	}
	
	/**
	 * @param Reader $delegate
	 * @return CachedReader
	 */
	public function setDelegate(Reader $delegate) {
		$this->delegate = $delegate;
		
		return $this;
	}
	
	/**
	 * @return RunnerManagerInterface
	 */
	public function getRunnerManagerObj(): RunnerManagerInterface {
		return $this->_runnerManagerObj;
	}
	
	/**
	 * @param RunnerManagerInterface $runnerManagerObj
	 * @return CachedReader
	 */
	public function setRunnerManagerObj(RunnerManagerInterface $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		return $this;
	}
	
	/**
	 * @return \Redis
	 */
	public function getCache() {
		return $this->cache;
	}
	
	/**
	 * @param \Redis $cache
	 *
	 * @return CachedReader
	 */
	public function setCache(\Redis $cache) {
		$this->cache = $cache;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getCacheKeyBuf(): string {
		return $this->cacheKeyBuf;
	}
	
	/**
	 * @param string $cacheKeyBuf
	 * @return CachedReader
	 */
	public function setCacheKeyBuf(string $cacheKeyBuf) {
		$this->cacheKeyBuf = $cacheKeyBuf;
		
		return $this;
	}
	
}
