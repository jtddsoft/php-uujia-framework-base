<?php

namespace uujia\framework\base\common\lib\Reflection;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;

class CachedReader implements Reader
{
    /**
     * @var Reader
     */
    private $delegate;
    
    /**
     * @var array
     */
    private $loadedAnnotations = [];

    /**
     * @var Driver
     */
    private $cache;

    /**
     * @var boolean
     */
    private $debug;
	
	/**
	 * CachedReader constructor.
	 *
	 * @param Reader                      $reader
	 * @param RunnerManagerInterface      $runnerManagerObj
	 * @param RedisProviderInterface|null $redisProviderObj
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
    public function __construct(Reader $reader,
                                RunnerManagerInterface $runnerManagerObj,
                                RedisProviderInterface $redisProviderObj) {
        $this->delegate = $reader;

        $this->cache = $redisProviderObj->getRedisObj();

        $this->debug = $runnerManagerObj->isDebug();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
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
    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
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
    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
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
    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
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
    public function getMethodAnnotations(\ReflectionMethod $method)
    {
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
    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    public function clearLoadedAnnotations()
    {
        $this->loadedAnnotations = [];
    }

    private function fetchFromCache($cacheKey, ReflectionClass $class)
    {
        if ((!$this->debug || $this->isCacheFresh($cacheKey, $class)) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey, false);
        }

        return false;
    }

    private function saveToCache($cacheKey, $value)
    {
        $this->cache->set($cacheKey, $value);
        if ($this->debug) {
            $this->cache->set('[C]' . $cacheKey, time());
        }
    }

    private function isCacheFresh($cacheKey, ReflectionClass $class)
    {
        if (null === $lastModification = $this->getLastModification($class)) {
            return true;
        }

        return $this->cache->get('[C]' . $cacheKey) >= $lastModification;
    }

    private function getLastModification(ReflectionClass $class)
    {
        $filename = $class->getFileName();
        $parent   = $class->getParentClass();

        return max(array_merge(
            [$filename ? filemtime($filename) : 0],
            array_map([$this, 'getTraitLastModificationTime'], $class->getTraits()),
            array_map([$this, 'getLastModification'], $class->getInterfaces()),
            $parent ? [$this->getLastModification($parent)] : []
        ));
    }

    private function getTraitLastModificationTime(ReflectionClass $reflectionTrait)
    {
        $fileName = $reflectionTrait->getFileName();

        return max(array_merge(
            [$fileName ? filemtime($fileName) : 0],
            array_map([$this, 'getTraitLastModificationTime'], $reflectionTrait->getTraits())
        ));
    }
}
