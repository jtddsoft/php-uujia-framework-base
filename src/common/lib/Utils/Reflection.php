<?php

namespace uujia\framework\base\common\lib\Utils;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use uujia\framework\base\common\traits\InstanceBase;

/**
 * Class Reflection
 * 反射工具类
 *
 * @package uujia\framework\base\common\lib\Utils
 */
class Reflection {
	use InstanceBase;
	
	const ANNOTATION_OF_CLASS     = 1;
	const ANNOTATION_OF_METHOD    = 2;
	const ANNOTATION_OF_PROPERTY  = 4;
	
	const METHOD_OF_PUBLIC    = 1;
	const METHOD_OF_PROTECTED = 2;
	const METHOD_OF_PRIVATE   = 4;
	
	/**
	 * @var AnnotationReader
	 */
	protected $_reader = null;
	
	/**
	 * @var string
	 */
	protected $_className;
	
	/**
	 * @var string
	 */
	protected $_methodName;
	
	/**
	 * @var string
	 */
	protected $_propertyName;
	
	/**
	 * @var \ReflectionClass
	 */
	protected $_refClass = null;
	
	/**
	 * @var \ReflectionMethod
	 */
	protected $_refMethod = null;
	
	/**
	 * @var \ReflectionProperty
	 */
	protected $_refProperty = null;
	
	/**
	 * 方法集合
	 * @var \ReflectionMethod[]
	 */
	protected $_refMethods = [];
	
	/**
	 * 方法参数
	 * @var \ReflectionParameter[]
	 */
	protected $_refParameters = [];
	
	/**
	 * 方法集合
	 * @var \ReflectionProperty[]
	 */
	protected $_refPropertys = [];
	
	/**
	 * @var array $_classAnnotations
	 */
	protected $_classAnnotations;
	
	/**
	 * @var array $_methodAnnotations
	 */
	protected $_methodAnnotations;
	
	/**
	 * @var array $_propertyAnnotations
	 */
	protected $_propertyAnnotations;
	
	
	/**
	 * 筛选后的方法对象集合
	 * @var \ReflectionMethod[]
	 */
	protected $_methodObjs = [];
	
	/**
	 * 获取注解所属类型
	 *  分为Class、Method、Property
	 * @var int $_annotationOf
	 */
	protected $_annotationOf = 1;
	
	/**
	 * 解析后的注解Map
	 * @var array
	 */
	protected $_annotationObjs = [];
	
	/**
	 * 反射注入后的对象实例
	 * @var Object $_injectionInstance
	 */
	protected $_injectionInstance = null;
	
	
	/**
	 * Reflection constructor.
	 *
	 * @param string $className 类名
	 * @param string $name      方法名或属性名
	 * @param int    $of        类型所属（1-Class、2-Method、3-Property）
	 */
	public function __construct($className, $name = '', $of = self::ANNOTATION_OF_CLASS) {
		// /** @var Reflection $me */
		// $me = static::getInstance();
		$this->setClassName($className);
		$this->setAnnotationOf($of);
		
		switch ($of) {
			case self::ANNOTATION_OF_METHOD:
				$this->setMethodName($name);
				break;
			
			case self::ANNOTATION_OF_PROPERTY:
				$this->setPropertyName($name);
				break;
		}
		
		// return $me;
	}
	
	/**
	 * 获取或设置注解类型
	 *
	 * @param null|int $value
	 *
	 * @return int|$this
	 */
	public function annotationOf($value = null) {
		if ($value === null) {
			return $this->_annotationOf ?? null;
		} else {
			$this->_annotationOf = $value;
		}
		
		return $this;
	}
	
	/**
	 * 反射获取
	 */
	public function load() {
		try {
			// TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
			AnnotationRegistry::registerLoader('class_exists');
			
			// 根据获取的类型获取注解
			switch ($this->_annotationOf) {
				case self::ANNOTATION_OF_CLASS:
					// 获取类Class
					$this->_setRefClass(new \ReflectionClass($this->getClassName()));
					$this->_setRefMethods($this->getRefClass()->getMethods());
					$this->_setRefPropertys($this->getRefClass()->getProperties());
					
					$this->_setClassAnnotations($this->getReader()->getClassAnnotations($this->getRefClass()));
					break;
					
				case self::ANNOTATION_OF_METHOD:
					// 获取方法Method
					$this->_setRefMethod(new \ReflectionMethod($this->getClassName(), $this->getMethodName()));
					$this->_setRefParameters($this->getRefMethod()->getParameters());
					
					$this->_setMethodAnnotations($this->getReader()->getMethodAnnotations($this->getRefMethod()));
					break;
					
				case self::ANNOTATION_OF_PROPERTY:
					// 获取属性Property
					$this->_setRefProperty(new \ReflectionProperty($this->getClassName(), $this->getPropertyName()));
					
					$this->_setPropertyAnnotations($this->getReader()->getPropertyAnnotations($this->getRefProperty()));
					break;
			}
			
			return $this;
		} catch (\ReflectionException $e) {
			// todo: error
			return null;
		}
	}
	
	/**
	 * 筛选方法
	 *  filter：
	 *  METHOD_OF_PUBLIC
	 *	METHOD_OF_PROTECTED
	 *  METHOD_OF_PRIVATE
	 *
	 * @param int $filter
	 */
	public function methods($filter = self::METHOD_OF_PUBLIC) {
		$this->_methodObjs = [];
		
		foreach ($this->getRefMethods() as $item) {
			if (($filter & self::METHOD_OF_PUBLIC) == self::METHOD_OF_PUBLIC && $item->isPublic()) {
				$this->_methodObjs[] = $item;
			} elseif (($filter & self::METHOD_OF_PROTECTED) == self::METHOD_OF_PROTECTED && $item->isProtected()) {
				$this->_methodObjs[] = $item;
			} elseif (($filter & self::METHOD_OF_PRIVATE) == self::METHOD_OF_PRIVATE && $item->isPrivate()) {
				$this->_methodObjs[] = $item;
			}
		}
		
		return $this;
	}
	
	/**
	 * 筛选注解
	 *
	 * @param string $filter
	 *
	 * @return Reflection
	 */
	public function annotation($filter) {
		$this->_annotationObjs = [];
		
		// 根据获取的类型获取注解
		switch ($this->_annotationOf) {
			case self::ANNOTATION_OF_CLASS:
				// 获取类Class
				foreach ($this->getClassAnnotations() as $item) {
					if ($item instanceof $filter) {
						$this->_annotationObjs[] = $item;
					}
				}
				break;
			
			case self::ANNOTATION_OF_METHOD:
				// 获取方法Method
				foreach ($this->getMethodAnnotations() as $item) {
					if ($item instanceof $filter) {
						$this->_annotationObjs[] = $item;
					}
				}
				break;
			
			case self::ANNOTATION_OF_PROPERTY:
				// 获取属性Property
				foreach ($this->getPropertyAnnotations() as $item) {
					if ($item instanceof $filter) {
						$this->_annotationObjs[] = $item;
					}
				}
				break;
		}
		
		return $this;
	}
	
	/**
	 * 获取属性注解
	 *  遍历获取 回调每一项
	 *
	 * @param \Closure $callback
	 * @param array    $filter
	 */
	public function annotationPropertys(\Closure $callback, $filter = []) {
		if (!is_callable($callback)) {
			return $this;
		}
		
		foreach ($this->getRefPropertys() as $property) {
			/** @var \ReflectionProperty $property */
			
		}
		
		return $this;
	}
	
	/**
	 * 实例化注入
	 *
	 * @param \Closure $callback
	 * @return Reflection|null
	 */
	public function injection(\Closure $callback) {
		$this->_injectionInstance = null;
		
		if (is_callable($callback)) {
			$_args = [];
			
			foreach ($this->getRefParameters() as $key => $param) {
				$_arg = call_user_func_array($callback, [$this, $param]);
				
				$_args[$key] = $_arg;
			}
			
			$reflection = $this->getRefClass();
			$this->_injectionInstance = $reflection->newInstanceArgs($_args); // 传入的是关联数组
			
			return $this;
		}
		
		return null;
	}
	
	/**
	 * invokeInjection
	 * 依赖注入
	 *
	 * @param string         $className
	 * @param array|\Closure $args
	 *
	 * @return false|string
	 */
	public static function invokeInjection($className, $args) {
		try {
			if (is_array($args)) {
				$_args = $args;
			} elseif (is_callable($args)) {
				$_args = [];
				
				// 反射获取类的构造函数
				$refMethod = new \ReflectionMethod($className, '__construct'); // 获取构造函数参数列表
				$refParams = $refMethod->getParameters();
				
				foreach ($refParams as $key => $param) {
					// if ($param->isPassedByReference()) {
					// 	$re_args[$key] = &$args[$key];
					// } else {
					// 	$re_args[$key] = $args[$key];
					// }
					
					$_arg = null;
					
					// 如果有类型约束 并且是个类 就构建这个依赖
					// if ($param->hasType() && $param->getClass() !== null) {
					// 	$newClass = $c->get($param->getClass()->getName());
					// 	$_arg     = $newClass;
					// } elseif ($param->isDefaultValueAvailable()) {
					// 	$_arg = $param->getDefaultValue();
					// }
					
					$_arg = call_user_func_array($args, [$refMethod, $refParams, $param]);
					
					$_args[$key] = $_arg;
				}
			}
			
			$reflection = new \ReflectionClass($className);
			$ins        = $reflection->newInstanceArgs($_args);// 传入的是关联数组
			
			return $ins;
		} catch (\ReflectionException $e) {
			// todo: 异常
			return null;
		}
	}
	
	/**
	 * @return string
	 */
	public function getClassName() {
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
	 * @return string
	 */
	public function getMethodName(): string {
		return $this->_methodName;
	}
	
	/**
	 * @param string $methodName
	 */
	public function setMethodName(string $methodName): void {
		$this->_methodName = $methodName;
	}
	
	/**
	 * @return string
	 */
	public function getPropertyName(): string {
		return $this->_propertyName;
	}
	
	/**
	 * @param string $propertyName
	 */
	public function setPropertyName(string $propertyName): void {
		$this->_propertyName = $propertyName;
	}
	
	/**
	 * @return \ReflectionClass
	 */
	public function getRefClass() {
		try {
			if (is_null($this->_refClass)) {
				$this->_setRefClass(new \ReflectionClass($this->getClassName()));
			}
			
			return $this->_refClass;
		} catch (\ReflectionException $e) {
			return null;
		}
	}
	
	/**
	 * @param \ReflectionClass $refClass
	 *
	 * @return $this
	 */
	public function _setRefClass(\ReflectionClass $refClass) {
		$this->_refClass = $refClass;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionMethod
	 */
	public function getRefMethod() {
		try {
			if (is_null($this->_refMethod)) {
				$this->_setRefMethod(new \ReflectionMethod($this->getClassName(), $this->getMethodName()));
			}
			
			return $this->_refMethod;
		} catch (\ReflectionException $e) {
			return null;
		}
	}
	
	/**
	 * @param \ReflectionMethod $refMethod
	 *
	 * @return Reflection
	 */
	public function _setRefMethod(\ReflectionMethod $refMethod) {
		$this->_refMethod = $refMethod;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionProperty|null
	 */
	public function getRefProperty() {
		try {
			if (is_null($this->_refProperty)) {
				$this->_setRefProperty(new \ReflectionProperty($this->getClassName(), $this->getPropertyName()));
			}
			
			return $this->_refProperty;
		} catch (\ReflectionException $e) {
			return null;
		}
	}
	
	/**
	 * @param \ReflectionProperty $refProperty
	 *
	 * @return Reflection
	 */
	public function _setRefProperty(\ReflectionProperty $refProperty) {
		$this->_refProperty = $refProperty;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionMethod[]
	 */
	public function getRefMethods(): array {
		return $this->_refMethods;
	}
	
	/**
	 * @param \ReflectionMethod[] $refMethods
	 *
	 * @return Reflection
	 */
	public function _setRefMethods(array $refMethods) {
		$this->_refMethods = $refMethods;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionParameter[]
	 */
	public function getRefParameters(): array {
		try {
			if (is_null($this->_refParameters)) {
				$this->_setRefParameters($this->getRefMethod()->getParameters());
			}
			
			return $this->_refParameters;
		} catch (\ReflectionException $e) {
			return [];
		}
	}
	
	/**
	 * @param \ReflectionParameter[] $refParameters
	 *
	 * @return Reflection
	 */
	public function _setRefParameters(array $refParameters) {
		$this->_refParameters = $refParameters;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionProperty[]
	 */
	public function getRefPropertys(): array {
		return $this->_refPropertys;
	}
	
	/**
	 * @param \ReflectionProperty[] $refPropertys
	 *
	 * @return Reflection
	 */
	public function _setRefPropertys(array $refPropertys) {
		$this->_refPropertys = $refPropertys;
		
		return $this;
	}
	
	/**
	 * @return AnnotationReader
	 */
	public function getReader() {
		try {
			if (is_null($this->_reader)) {
				$this->_reader = new AnnotationReader();
			}
			
			return $this->_reader;
		} catch (AnnotationException $e) {
			return null;
		}
	}
	
	/**
	 * @param AnnotationReader $reader
	 *
	 * @return Reflection
	 */
	public function _setReader($reader) {
		$this->_reader = $reader;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getClassAnnotations() {
		return $this->_classAnnotations;
	}
	
	/**
	 * @param array $classAnnotations
	 *
	 * @return Reflection
	 */
	public function _setClassAnnotations($classAnnotations) {
		$this->_classAnnotations = $classAnnotations;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getMethodAnnotations() {
		return $this->_methodAnnotations;
	}
	
	/**
	 * @param array $methodAnnotations
	 *
	 * @return Reflection
	 */
	public function _setMethodAnnotations($methodAnnotations) {
		$this->_methodAnnotations = $methodAnnotations;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getPropertyAnnotations() {
		return $this->_propertyAnnotations;
	}
	
	/**
	 * @param array $propertyAnnotations
	 *
	 * @return Reflection
	 */
	public function _setPropertyAnnotations($propertyAnnotations) {
		$this->_propertyAnnotations = $propertyAnnotations;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionMethod[]
	 */
	public function getMethodObjs(): array {
		return $this->_methodObjs;
	}
	
	/**
	 * @param \ReflectionMethod[] $methodObjs
	 *
	 * @return Reflection
	 */
	public function _setMethodObjs(array $methodObjs) {
		$this->_methodObjs = $methodObjs;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getAnnotationOf(): int {
		return $this->_annotationOf;
	}
	
	/**
	 * @param int $annotationOf
	 * @return Reflection
	 */
	public function setAnnotationOf(int $annotationOf) {
		$this->_annotationOf = $annotationOf;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAnnotationObjs(): array {
		return $this->_annotationObjs;
	}
	
	/**
	 * @param array $annotationObjs
	 * @return Reflection
	 */
	public function _setAnnotationObjs(array $annotationObjs) {
		$this->_annotationObjs = $annotationObjs;
		
		return $this;
	}
	
	/**
	 * @return Object
	 */
	public function getInjectionInstance() {
		return $this->_injectionInstance;
	}
	
	/**
	 * @param Object $injectionInstance
	 * @return Reflection
	 */
	public function _setInjectionInstance($injectionInstance) {
		$this->_injectionInstance = $injectionInstance;
		
		return $this;
	}
	
	
}