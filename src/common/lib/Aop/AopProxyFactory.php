<?php


namespace uujia\framework\base\common\lib\Aop;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Exception\ExceptionAop;
use uujia\framework\base\common\lib\Reflection\Reflection;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class AopProxyFactory
 * Date: 2020/8/2 19:35
 *
 * @package uujia\framework\base\common\lib\Aop
 */
class AopProxyFactory extends BaseClass {
	use ResultTrait;
	
	/**
	 * 代理的类名（全名）
	 * @var string
	 */
	protected $_className;
	
	/**
	 * 代理的类实例
	 * @var BaseClass
	 */
	protected $_classInstance;
	
	/**
	 * 反射类
	 * @var Reflection
	 */
	protected $_reflectionClass;
	
	/**
	 * AopProxyFactory constructor.
	 */
	public function __construct() {
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '代理类';
	}
	
	/**
	 * 从aop供应商获取拦截类
	 * Date: 2020/8/2 19:35
	 *
	 * @return \Generator
	 */
	public function aopClass() {
		yield null;
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
		$aop = $this->getContainer()->get($aopCurr);
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
				if (method_exists($this->getClassInstance(), $method)) {
					$result = call_user_func_array([$this->getClassInstance(), $method], $args);
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
			$result = $this->aopProcess($generator, $name, $arguments, $this->ok());
		} else {
			if (method_exists($this->getClassInstance(), $name)) {
				$result = call_user_func_array([$this->getClassInstance(), $name], $arguments);
			} else {
				throw new ExceptionAop('方法不存在', 1000);
			}
		}
		
		return $result;
	}
	
	/**
	 * @return string
	 */
	public function getClassName(): string {
		return $this->_className;
	}
	
	/**
	 * @param string $className
	 *
	 * @return AopProxyFactory
	 */
	public function setClassName(string $className) {
		$this->_className = $className;
		
		return $this;
	}
	
	/**
	 * @return object
	 */
	public function getClassInstance() {
		return $this->_classInstance;
	}
	
	/**
	 * @param object $classInstance
	 *
	 * @return AopProxyFactory
	 */
	public function setClassInstance($classInstance) {
		$this->_classInstance = $classInstance;
		
		return $this;
	}
	
	/**
	 * @return Reflection
	 */
	public function getReflectionClass(): Reflection {
		return $this->_reflectionClass;
	}
	
	/**
	 * @param Reflection $reflectionClass
	 *
	 * @return AopProxyFactory
	 */
	public function setReflectionClass(Reflection $reflectionClass) {
		$this->_reflectionClass = $reflectionClass;
		
		return $this;
	}
	
	//private $target;
	//function __construct($tar){
	//	$this->target[] = new $tar();
	//}
	//
	//function __call($name,$args){
	//	foreach ($this->target as $obj) {
	//		$r = new ReflectionClass($obj);
	//		if($method = $r->getMethod($name)){
	//			if($method->isPublic() && !$method->isAbstract()){
	//				$method->invoke($obj,$args);
	//			}
	//		}
	//	}
	//}
}