<?php


namespace uujia\framework\base\common\lib\Aop;

use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Aop\cache\AopCacheDataProvider;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Exception\ExceptionAop;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\Ret;

/**
 * Trait AopProxy
 * Date: 2020/8/10
 * Time: 23:27
 *
 * @package uujia\framework\base\common\lib\Aop
 */
trait AopProxy {
	
	/**
	 * CacheDataManager对象
	 *
	 * @var CacheDataManager
	 */
	protected $_cacheDataManagerObj;
	
	/**
	 * 代理的类名（全名）
	 * @var string
	 */
	protected $_className;
	
	
	/**
	 * 从aop供应商获取拦截类
	 * Date: 2020/8/2 19:35
	 *
	 * @return \Generator
	 * @throws ExceptionAop
	 */
	public function aopClass() {
		foreach ($this->getAopCacheDataProviders() as $item) {
			/** @var AopCacheDataProvider $item */
			yield from $item->setAopTargetClass($this->getClassName())->fromCache();
		}
	}
	
	/**
	 * Date: 2020/8/4 14:25
	 *
	 * @param \Generator $generator
	 * @param string     $method
	 * @param array      $args
	 * @param            $result
	 *
	 * @return mixed
	 * @throws ExceptionAop
	 */
	public function aopProcess($generator, $method, $args, $result) {
		$aopCurr = $generator->current();
		
		/**
		 * 实例化aop
		 * @var AopAdviceInterface $aop
		 */
		$aop = Container::getInstance()->get($aopCurr);
		if (empty($aop)) {
			$generator->next();
			if ($generator->valid()) {
				return $this->aopProcess($generator, $method, $args, $result);
			}
			
			return $result;
		}
		
		// 调用process
		if (method_exists($aop, 'process')) {
			/**
			 * function process($aopProxy, $method, $args, $lastResult, \Closure $callMethod, \Closure $next) {
			 *
			 *      return $next($callMethod());
			 * }
			 */
			$result = call_user_func_array([$aop, 'process'], [$this, $method, $args, $result, function () use ($method, $args) {
				if (method_exists($this, $method)) {
					$result = call_user_func_array([$this, $method], $args);
				} else {
					throw new ExceptionAop('方法不存在', 1000);
				}
				
				return $result;
			}, function ($result) use ($generator, $method, $args) {
				$generator->next();
				return $this->aopProcess($generator, $method, $args, $result);
			}]);
		} else {
			throw new ExceptionAop('AOP方法不存在', 1000);
		}
		
		return $result;
	}
	
	/**
	 * date: 2020/8/4 16:31
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 * @throws ExceptionAop
	 */
	public function __call($name, $arguments) {
		$generator = $this->aopClass();
		$generator->rewind();
		
		$result = false;
		
		if ($generator->valid()) {
			$result = $this->aopProcess($generator, $name, $arguments, Ret::me()->ok());
		} else {
			if (method_exists($this, $name)) {
				$result = call_user_func_array([$this, $name], $arguments);
			} else {
				throw new ExceptionAop('方法不存在', 1000);
			}
		}
		
		return $result;
	}
	
	
	/**
	 * 获取AOP缓存供应商对象集合
	 * 我只提供一个 但您可以增加多个
	 * 这里返回的是个数组 具体看CacheDataManager中的定义
	 *
	 * @return CacheDataProvider[]|null
	 */
	public function getCacheDataProviders() {
		$cdMgr       = $this->getCacheDataManagerObj();
		$cdProviders = $cdMgr->getProviderList()->getKeyDataValue(CacheConstInterface::DATA_PROVIDER_KEY_AOP);
		
		return $cdProviders;
	}
	
	/**
	 * 获取Aop缓存供应商对象
	 *
	 * @return \Generator
	 * @throws ExceptionAop
	 */
	public function getAopCacheDataProviders() {
		$cdProviders = $this->getCacheDataProviders();
		if (empty($cdProviders)) {
			// throw new ExceptionAop('未找到AOP缓存供应商', 1000);
			return [];
		}
		
		/** @var TreeFunc $it */
		$it = $cdProviders['it'];
		if ($it->count() == 0) {
			// throw new ExceptionAop('未找到AOP缓存供应商', 1000);
			return [];
		}
		
		// 遍历寻找AOP缓存供应商 AopCacheDataProvider AOP供应商我只提供一个 但您可以自行增加
		$found = false;
		foreach ($it->wForEachIK() as $i => $item) {
			$data = $item->getDataValue();
			if ($data instanceof AopCacheDataProvider) {
				$found = true;
				yield $data;
			}
		}
		
		if (!$found) {
			throw new ExceptionAop('未找到AOP缓存供应商', 1000);
		}
	}
	
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getClassName(): string {
		return $this->_className;
	}
	
	/**
	 * @param string $className
	 *
	 * @return $this
	 */
	public function setClassName(string $className) {
		$this->_className = $className;
		
		return $this;
	}
	
	/**
	 * @return CacheDataManager
	 */
	public function getCacheDataManagerObj(): CacheDataManager {
		return $this->_cacheDataManagerObj;
	}
	
	/**
	 * @param CacheDataManager $cacheDataManagerObj
	 *
	 * @return $this
	 */
	public function setCacheDataManagerObj(CacheDataManager $cacheDataManagerObj) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		
		return $this;
	}
	
}