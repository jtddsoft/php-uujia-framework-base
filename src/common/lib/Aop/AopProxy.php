<?php


namespace uujia\framework\base\common\lib\Aop;

use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Aop\Cache\AopCacheDataProvider;
use uujia\framework\base\common\lib\Aop\JointPoint\ProceedingJoinPoint;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
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
	
	// /**
	//  * 代理的类名（全名）
	//  *
	//  * @var string
	//  */
	// protected $_className;
	
	/**
	 * @var ProceedingJoinPoint
	 */
	protected $_proceedingJoinPointObj;
	
	
	
	public function _aopInit() {
	
	}
	
	/**
	 * 从aop供应商获取拦截类
	 * Date: 2020/8/2 19:35
	 *
	 * @return \Generator
	 * @throws ExceptionAop
	 */
	public function _aopClass() {
		foreach ($this->getAopCacheDataProviders() as $item) {
			/** @var AopCacheDataProvider $item */
			// yield from $item->setAopTargetClass($this->getClassName())->fromCache();
			yield from $item->setAopTargetClass(get_parent_class())->fromCache();
		}
	}
	
	/**
	 * Date: 2020/8/4 14:25
	 *
	 * @param \Generator $generator
	 * @param \Closure   $closure
	 * @param string     $method
	 * @param array      $args
	 *
	 * @return mixed
	 */
	public function _aopProcess(\Generator $generator, \Closure $closure, string $method, array $args) {
		// $callMethod = function () use ($method, $args) {
		// 	if (method_exists($this, $method)) {
		// 		// $result = call_user_func_array([$this, $method], $args);
		// 		$result = parent::$method(...$args);
		// 	} else {
		// 		throw new ExceptionAop('方法不存在', 1000);
		// 	}
		//
		// 	return $result;
		// };
		
		$callMethod = function () use ($closure, $args) {
			// return $closure(...$args);
			return call_user_func_array($closure, $args);
		};
		
		// $this->getProceedingJoinPointObj()->className = $this->getClassName();
		$this->getProceedingJoinPointObj()->className = get_parent_class();
		$this->getProceedingJoinPointObj()->methodName = $method;
		$this->getProceedingJoinPointObj()->args = $args;
		$this->getProceedingJoinPointObj()->result = Ret::me()->ok();
		$this->getProceedingJoinPointObj()->generator = $generator;
		$this->getProceedingJoinPointObj()->methodClosure = $callMethod;
		
		$result = $this->getProceedingJoinPointObj()->process();
		
		return $result;
		
		// $aopCurr = $generator->current();
		
		// /**
		//  * 实例化aop
		//  *
		//  * @var AopAdviceInterface $aop
		//  */
		// $aop = Container::getInstance()->get($aopCurr);
		// if (empty($aop)) {
		// 	return call_user_func_array($callMethod, $args);
		// }
		
		
		
		// // 调用process
		// if (method_exists($aop, 'process')) {
		//
		// 	$result = call_user_func_array([$aop, 'process'], [$this->getProceedingJoinPointObj()]);
		//
		//
		//
		//
		//
		//
		// 	/**
		// 	 * function process($aopProxy, $method, $args, $lastResult, \Closure $callMethod, \Closure $next) {
		// 	 *
		// 	 *      return $next($callMethod());
		// 	 * }
		// 	 */
		// 	$callMethod = function () use ($method, $args) {
		// 		if (method_exists($this, $method)) {
		// 			// $result = call_user_func_array([$this, $method], $args);
		// 			$result = parent::$method(...$args);
		// 		} else {
		// 			throw new ExceptionAop('方法不存在', 1000);
		// 		}
		//
		// 		return $result;
		// 	};
		//
		// 	$next = function ($result) use ($generator, $method, $args) {
		// 		$generator->next();
		// 		return $this->_aopProcess($generator, $method, $args, $result);
		// 	};
		//
		// 	$result = call_user_func_array([$aop, 'process'], [$this, $method, $args, $result, $callMethod, $next]);
		// } else {
		// 	throw new ExceptionAop('AOP方法不存在', 1000);
		// }
		//
		// return $result;
	}
	
	/**
	 * 通用方法
	 *
	 * Date: 2020/8/12
	 * Time: 0:01
	 *
	 * @param \Closure $closure
	 * @param string   $method
	 * @param array    $args
	 *
	 * @return bool|mixed
	 * @throws ExceptionAop
	 */
	public function _aopCall(\Closure $closure, string $method, array $args) {
		$generator = $this->_aopClass();
		$generator->rewind();
		
		$result = false;
		
		if ($generator->valid()) {
			$result = $this->_aopProcess($generator, $closure, $method, $args);
		} else {
			// $result = (...$args);
			$result = call_user_func_array($closure, $args);
		}
		
		return $result;
	}
	
	// /**
	//  * date: 2020/8/4 16:31
	//  *
	//  * @param $name
	//  * @param $arguments
	//  *
	//  * @return mixed
	//  * @throws ExceptionAop
	//  */
	// public function __call($name, $arguments) {
	// 	$generator = $this->_aopClass();
	// 	$generator->rewind();
	//
	// 	$result = false;
	//
	// 	if ($generator->valid()) {
	// 		$result = $this->_aopProcess($generator, $name, $arguments, Ret::me()->ok());
	// 	} else {
	// 		if (method_exists($this, $name)) {
	// 			$result = call_user_func_array([$this, $name], $arguments);
	// 		} else {
	// 			throw new ExceptionAop('方法不存在', 1000);
	// 		}
	// 	}
	//
	// 	return $result;
	// }
	
	
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
	
	// /**
	//  * @return string
	//  */
	// public function getClassName(): string {
	// 	return $this->_className;
	// }
	//
	// /**
	//  * @param string $className
	//  *
	//  * @return $this
	//  */
	// public function setClassName(string $className) {
	// 	$this->_className = $className;
	//
	// 	return $this;
	// }
	
	/**
	 * @return CacheDataManager
	 */
	public function getCacheDataManagerObj(): CacheDataManager {
		if (empty($this->_cacheDataManagerObj)) {
			$this->_cacheDataManagerObj = Container::getInstance()->get(CacheDataManagerInterface::class);
		}
	
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
	
	/**
	 * @return ProceedingJoinPoint
	 */
	public function getProceedingJoinPointObj(): ProceedingJoinPoint {
		if (empty($this->_proceedingJoinPointObj)) {
			$this->_proceedingJoinPointObj = new ProceedingJoinPoint();
		}
		
		return $this->_proceedingJoinPointObj;
	}
	
	/**
	 * @param ProceedingJoinPoint $proceedingJoinPointObj
	 *
	 * @return $this
	 */
	public function setProceedingJoinPointObj(ProceedingJoinPoint $proceedingJoinPointObj) {
		$this->_proceedingJoinPointObj = $proceedingJoinPointObj;
		
		return $this;
	}
	
}