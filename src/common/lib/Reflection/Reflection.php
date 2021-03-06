<?php

namespace uujia\framework\base\common\lib\Reflection;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\traits\InstanceTrait;

/**
 * Class Reflection
 * 反射工具类
 *
 * @package uujia\framework\base\common\lib\Reflection
 */
class Reflection extends BaseClass {
	use InstanceTrait;
	
	const ANNOTATION_OF_CLASS = 1;
	const ANNOTATION_OF_METHOD = 2;
	const ANNOTATION_OF_PROPERTY = 4;
	
	const METHOD_OF_PUBLIC = 1;
	const METHOD_OF_PROTECTED = 2;
	const METHOD_OF_PRIVATE = 4;
	
	const CACHE_KEY_USE_IMPORTS = 'cache:reader:uses';
	
	/**
	 * @var AnnotationReader|CachedReader
	 */
	protected $_reader = null;
	
	/**
	 * @var AnnotationReader
	 */
	protected $_readerAnnot = null;
	
	/**
	 * @var \ReflectionClass
	 */
	protected $_refReaderClass = null;
	
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
	 *
	 * @var \ReflectionMethod[]
	 */
	protected $_refMethods = [];
	
	/**
	 * 方法参数
	 *
	 * @var \ReflectionParameter[]
	 */
	protected $_refParameters = [];
	
	/**
	 * 方法集合
	 *
	 * @var \ReflectionProperty[]
	 */
	protected $_refPropertys = [];
	
	/**
	 * @var array
	 */
	protected $_classAnnotations = [];
	
	/**
	 * @var array
	 */
	protected $_methodAnnotations = [];
	
	/**
	 * @var array
	 */
	protected $_propertyAnnotations = [];
	
	
	/**
	 * 筛选后的方法对象集合
	 *
	 * @var \ReflectionMethod[]
	 */
	protected $_methodObjs = [];
	
	/**
	 * 获取注解所属类型
	 *  分为Class、Method、Property
	 *
	 * @var int $_annotationOf
	 */
	protected $_annotationOf = 1;
	
	/**
	 * 解析后的注解Map
	 *
	 * @var array
	 */
	protected $_annotationObjs = [];
	
	/**
	 * 反射注入后的对象实例
	 *
	 * @var Object $_injectionInstance
	 */
	protected $_injectionInstance = null;
	
	/**
	 * 引入的use集合
	 *
	 * @var array
	 */
	protected $_useImports = [];
	
	
	/**
	 * Reflection constructor.
	 *
	 * @param string $className 类名
	 * @param string $name      方法名或属性名
	 * @param int    $of        类型所属（1-Class、2-Method、3-Property）
	 */
	public function __construct($className = '', $name = '', $of = self::ANNOTATION_OF_CLASS) {
		parent::__construct();
		
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
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '反射助手类';
	}
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		(!in_array('reader', $exclude)) && $this->_reader = null;
		(!in_array('readerAnnot', $exclude)) && $this->_readerAnnot = null;
		(!in_array('refReaderClass', $exclude)) && $this->_refReaderClass = null;
		(!in_array('className', $exclude)) && $this->_className = '';
		(!in_array('methodName', $exclude)) && $this->_methodName = '';
		(!in_array('propertyName', $exclude)) && $this->_propertyName = '';
		(!in_array('refClass', $exclude)) && $this->_refClass = null;
		(!in_array('refMethod', $exclude)) && $this->_refMethod = null;
		(!in_array('refProperty', $exclude)) && $this->_refProperty = null;
		(!in_array('refMethods', $exclude)) && $this->_refMethods = [];
		(!in_array('refParameters', $exclude)) && $this->_refParameters = [];
		(!in_array('refPropertys', $exclude)) && $this->_refPropertys = [];
		(!in_array('classAnnotations', $exclude)) && $this->_classAnnotations = [];
		(!in_array('methodAnnotations', $exclude)) && $this->_methodAnnotations = [];
		(!in_array('propertyAnnotations', $exclude)) && $this->_propertyAnnotations = [];
		(!in_array('methodObjs', $exclude)) && $this->_methodObjs = [];
		(!in_array('annotationOf', $exclude)) && $this->_annotationOf = self::ANNOTATION_OF_CLASS;
		(!in_array('annotationObjs', $exclude)) && $this->_annotationObjs = [];
		(!in_array('injectionInstance', $exclude)) && $this->_injectionInstance = null;
		(!in_array('useImports', $exclude)) && $this->_useImports = [];
		
		return parent::reset($exclude);
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
	 *
	 * @return $this|null
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
					// var_dump($this->gsPrivateProperty($this->getRefReaderClass(), 'imports', null, $this->getReader()));
					
					$this->_setUseImports($this->getClassUseImports());
					
					// 将构造函数解析反射
					if ($this->getRefClass()->hasMethod('__construct')) {
						// 	$this->_setRefMethod($this->getRefClass()->getMethod('__construct'));
						$this->_setRefMethod($this->getRefClass()->getConstructor());
						$this->_setRefParameters($this->getRefMethod()->getParameters());
						
						$this->_setMethodAnnotations($this->getReader()->getMethodAnnotations($this->getRefMethod()));
					}
					
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
	 *    METHOD_OF_PROTECTED
	 *  METHOD_OF_PRIVATE
	 *
	 * @param int $filter
	 *
	 * @return Reflection
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
	 * @param string   $filter
	 * @param null|int $annotationOf
	 *
	 * @return Reflection
	 */
	public function annotation($filter, $annotationOf = null) {
		$this->_annotationObjs = [];
		
		is_null($annotationOf) && $annotationOf = $this->_annotationOf;
		
		// 根据获取的类型获取注解
		switch ($annotationOf) {
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
	
	/**************************************************
	 * 类反射后 循环获取属性
	 **************************************************/
	
	/**
	 * 获取类use引用列表
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public function getClassUseImports() {
		$_redisObj = null;
		if (!empty(Container::getInstance()->getList()->getKeyDataCache(CachedReader::class))) {
			if ($this->_reader && $this->_reader instanceof CachedReader) {
				$_redisObj = $this->_reader->getCache();
			}
		}
		
		if (!empty($_redisObj)) {
			$key = $this->_reader->getRunnerManagerObj()->getAppName() . ':' . self::CACHE_KEY_USE_IMPORTS;
			$_cacheKey = str_replace('\\', '/', $this->getClassName());
			$res = $_redisObj->hGet($key, $_cacheKey);
			if ($res !== false) {
				$useImports = unserialize($res);
				return $useImports ?? [];
			}
		}
		
		$useImports = $this->gsPrivateProperty($this->getRefReaderClass(),
		                                       'imports',
		                                       null,
		                                       $this->getReaderAnnot());
		
		if (!empty($_redisObj)) {
			$_redisObj->hSet($key, $_cacheKey, serialize($useImports[$this->getClassName()] ?? []));
		}
		
		return $useImports[$this->getClassName()] ?? [];
	}
	
	/**
	 * 获取属性注解
	 *  遍历获取 回调每一项
	 *
	 * @param \Closure $callback       回调
	 * @param array    $filter         过滤注解
	 * @param array    $filterProperty 过滤属性
	 *
	 * @return Reflection
	 * @throws \ReflectionException
	 */
	public function classPropertys(\Closure $callback, $filter = [], $filterProperty = []) {
		if (!is_callable($callback)) {
			return $this;
		}
		
		// $useImports = $this->getClassUseImports();
		$useImports = $this->getUseImports();
		
		foreach ($this->getRefPropertys() as $property) {
			if (!empty($filterProperty) && !in_array($property->getName(), $filterProperty)) {
				continue;
			}
			
			$propertyAnno = $this->getReader()->getPropertyAnnotations($property);
			if (empty($propertyAnno)) {
				continue;
			}
			
			$_propertyAnno = $propertyAnno;
			
			if (!empty($filter)) {
				$_propertyAnno = array_filter($propertyAnno, function ($var) use ($filter) {
					return in_array($var, $filter);
				}, ARRAY_FILTER_USE_KEY);
			}
			
			$ret = call_user_func_array($callback, [$this, $property, $_propertyAnno, $useImports]);
			if ($ret === false) {
				break;
			}
		}
		
		return $this;
	}
	
	/**************************************************
	 * 类
	 **************************************************/
	
	/**
	 * 递归获取父类 直到没有父类为止
	 *
	 * Date: 2020/8/18
	 * Time: 9:25
	 *
	 * @param \ReflectionClass|null $refClassObj
	 * @param array                 $extends
	 *
	 * @return array
	 */
	public function getClassExtends(\ReflectionClass $refClassObj = null, $extends = []) {
		if (is_null($refClassObj)) {
			$refClassObj = $this->getRefClass();
		}
		
		$_refClassObj = $refClassObj->getParentClass();
		if ($_refClassObj === false) {
			return $extends;
		}
		
		$extends[$_refClassObj->getName()] = $_refClassObj->getFileName();
		return $this->getClassExtends($_refClassObj, $extends);
	}
	
	/**************************************************
	 * 依赖注入会用到的相关通用方法
	 **************************************************/
	
	/**
	 * 实例化注入
	 *
	 * @param \Closure      $callback
	 * @param \Closure|null $newInstanceCallBack
	 *
	 * @return Reflection|null
	 */
	public function injection(\Closure $callback, ?\Closure $newInstanceCallBack = null) {
		$this->_injectionInstance = null;
		
		if (is_callable($callback)) {
			$_args = [];
			
			foreach ($this->getRefParameters() as $key => $param) {
				$_arg = call_user_func_array($callback, [$this, $param, $this->getUseImports()]);
				
				$_args[$key] = $_arg;
			}
			
			if (!empty($newInstanceCallBack)) {
				$res = call_user_func_array($newInstanceCallBack, [$_args, $this]);
				if (!$res) {
					return $this;
				}
			}
			
			$reflection               = $this->getRefClass();
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
	
	/**************************************************
	 * 访问类的私有属性或方法
	 **************************************************/
	
	/**
	 * 执行类的私有方法
	 *
	 * @param \ReflectionClass|string $class         类名或类的反射实例
	 * @param string                  $method        方法名
	 * @param array                   $params        参数
	 * @param null|object             $classInstance 类的实例（如果之前已经实例化）
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function callPrivateMethod($class, $method, $params = [], $classInstance = null) {
		if (is_string($class)) {
			//通过类名MyClass进行反射
			$ref_class = new \ReflectionClass($class);
		} else {
			$ref_class = $class;
		}
		
		//通过反射类进行实例化
		if (is_null($classInstance)) {
			$instance = $ref_class->newInstance();
		} else {
			$instance = $classInstance;
		}
		
		//通过方法名myFun获取指定方法
		$ref_method = $ref_class->getmethod($method);
		
		//设置可访问性
		$ref_method->setAccessible(true);
		
		//执行方法
		return $ref_method->invoke($instance, $params);
	}
	
	/**
	 * get set执行类的私有属性
	 *
	 * @param \ReflectionClass|string $class         类名或类的反射实例
	 * @param string                  $property      属性名
	 * @param null|mixed              $value         如果null为获取 不为null则设置
	 * @param null|object             $classInstance 类的实例（如果之前已经实例化）
	 *
	 * @return mixed|$this
	 * @throws \ReflectionException
	 */
	public function gsPrivateProperty($class, $property, $value = null, $classInstance = null) {
		if (is_string($class)) {
			//通过类名MyClass进行反射
			$ref_class = new \ReflectionClass($class);
		} else {
			$ref_class = $class;
		}
		
		//通过反射类进行实例化
		if (is_null($classInstance)) {
			$instance = $ref_class->newInstance();
		} else {
			$instance = $classInstance;
		}
		
		//通过属性
		$ref_property = $ref_class->getProperty($property);
		
		//设置可访问性
		$ref_property->setAccessible(true);
		
		if (is_null($value)) {
			//读取属性值
			return $ref_property->getValue($instance);
		}
		
		// 设置属性值
		$ref_property->setValue($instance, $value);
		
		return $this;
	}
	
	/**************************************************
	 * get set
	 **************************************************/
	
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
	 * @param \ReflectionMethod $refMethod
	 *
	 * @return Reflection
	 */
	public function _setRefMethod(\ReflectionMethod $refMethod) {
		$this->_refMethod = $refMethod;
		
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
	 * @return AnnotationReader|CachedReader
	 */
	public function getReader() {
		try {
			if (is_null($this->_reader)) {
				// $this->_reader = new AnnotationReader();
				if (!empty(Container::getInstance()->getList()->getKeyDataCache(CachedReader::class))) {
					$this->_readerAnnot = $this->getRefReaderClass()->newInstance();
					$this->_reader = Container::getInstance()->get(CachedReader::class);
					$this->_reader->setDelegate($this->_readerAnnot);
					// $this->_readerAnnot = $this->_reader->getDelegate();
				} else {
					$this->_reader = $this->getRefReaderClass()->newInstance();
					$this->_readerAnnot = $this->_reader;
				}
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
	 * @return AnnotationReader
	 */
	public function getReaderAnnot() {
		try {
			if (is_null($this->_readerAnnot)) {
				if (Container::getInstance()->getList()->getKeyDataCache(CachedReader::class)) {
					$this->_readerAnnot = $this->getRefReaderClass()->newInstance();
					$this->_reader = Container::getInstance()->get(CachedReader::class);
					$this->_reader->setDelegate($this->_readerAnnot);
					// $this->_readerAnnot = $this->_reader->getDelegate();
				} else {
					$this->_reader = $this->getRefReaderClass()->newInstance();
					$this->_readerAnnot = $this->_reader;
				}
			}
			
			return $this->_readerAnnot;
		} catch (AnnotationException $e) {
			return null;
		}
	}
	
	/**
	 * @param AnnotationReader $readerAnnot
	 */
	public function _setReaderAnnot(AnnotationReader $readerAnnot) {
		$this->_readerAnnot = $readerAnnot;
		
		return $this;
	}
	
	/**
	 * @return \ReflectionClass
	 */
	public function getRefReaderClass(): \ReflectionClass {
		if (is_null($this->_refReaderClass)) {
			$this->_refReaderClass = new \ReflectionClass(AnnotationReader::class);
		}
		
		return $this->_refReaderClass;
	}
	
	/**
	 * @param \ReflectionClass $refReaderClass
	 */
	public function setRefReaderClass(\ReflectionClass $refReaderClass) {
		$this->_refReaderClass = $refReaderClass;
		
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
	 *
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
	 *
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
	 *
	 * @return Reflection
	 */
	public function _setInjectionInstance($injectionInstance) {
		$this->_injectionInstance = $injectionInstance;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getUseImports(): array {
		return $this->_useImports;
	}
	
	/**
	 * @param array $useImports
	 *
	 * @return Reflection
	 */
	public function _setUseImports(array $useImports) {
		$this->_useImports = $useImports;
		
		return $this;
	}
	
	
}