<?php


namespace uujia\framework\base\common\lib\Container;


use ReflectionParameter;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Aop\AopProxyFactory;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Container\ContainerInterface;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Reflection\Reflection;
use uujia\framework\base\common\traits\InstanceTrait;
use uujia\framework\base\common\traits\NameTrait;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class Container
 * 基础容器
 *
 * @package uujia\framework\base\common\lib\Container
 */
class Container extends BaseClass implements ContainerInterface, \Iterator, \ArrayAccess {
	use NameTrait;
	use ResultTrait;
	
	use InstanceTrait;
	
	// private $c = [];
	// // 每次实例化都会存入对象实例 如果已存在就覆盖
	// private $lastObj = [];
	
	/**
	 * @var TreeFunc
	 */
	protected $_list;
	
	/**
	 * key不存在时尝试自动new实例
	 *
	 * @var bool
	 */
	protected $_keyNotExistAutoCreate = true;
	
	/**
	 * Aop是否启用
	 *
	 * @var bool
	 */
	protected $_aopEnabled = false;
	
	/**
	 * Aop是否递归扫描父类
	 *
	 * @var bool
	 */
	protected $_aopScanParent = false;
	
	/**
	 * Aop需要忽略的类
	 *
	 * @var string[]
	 */
	protected $_aopIgnore = [];
	
	/**
	 * ContainerProvider constructor.
	 *
	 * @param TreeFunc|null $list
	 */
	public function __construct(TreeFunc $list = null) {
		$this->_list = $list ?? new TreeFunc();
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		$this->setKeyNotExistAutoCreate(true);
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = static::class;
		$this->name_info['intro'] = '基础容器';
	}
	
	/**************************************************
	 * Iterator 迭代器方法实现
	 **************************************************/
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		$_key = $this->key();
		
		return $this->get($_key);
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		$this->list()->next();
	}
	
	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->list()->key();
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		return $this->list()->valid();
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->list()->rewind();
	}
	
	/**************************************************
	 * ArrayAccess 方法实现
	 **************************************************/
	
	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value) {
		return $this->set($offset, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset) {
		return $this->list()->offsetUnset($offset);
	}
	
	/**************************************************
	 * 节点操作
	 **************************************************/
	
	// function __set($k, $c) {
	// 	$this->c[$k] = $c;
	// }
	
	// function __get($k) {
	// 	if ($this->hasCache($k)) {
	// 		return $this->cache($k);
	// 	}
	// 	return $this->c[$k]($this);
	// }
	
	/**
	 * 查找class的命名空间
	 *  如果class只有一个短名 就去尝试找命名空间
	 *  需要提前use引用 不引用那是谁也猜不出你这个类名是谁
	 *
	 *  例如：
	 *      use uu\Apple;
	 *
	 *      $className = 'Apple';
	 *      直接找Apple这个类是不存在的 但你use了他 我就知道你这个Apple的全名是uu\Apple
	 *
	 * @param string $className
	 * @param array  $useImports
	 *
	 * @return string|false
	 */
	public function findClassNameSpace($className, $useImports = []) {
		$_className = $className;
		// 查找别名
		$this->getList()->hasAlias($className) && $_className = $this->getList()->getAlias($className);
		// 查找映射
		$this->getList()->hasAs($className) && $_className = $this->getList()->getAs($className);
		
		if (class_exists($_className)) {
			return $_className;
		}
		
		$loweredClassName = strtolower($_className);
		
		if (isset($useImports[$loweredClassName])) {
			$namespace = $useImports[$loweredClassName];
			
			if (class_exists($namespace)) {
				return $namespace;
			}
		}
		
		if (isset($useImports['__NAMESPACE__'])) {
			$namespace = $useImports['__NAMESPACE__'] . '\\' . $_className;
			
			if (class_exists($namespace)) {
				return $namespace;
			}
		}
		
		return false;
	}
	
	/**
	 * new class
	 *
	 * @param string $className     类名（容器名 容器id）
	 * @param string $injectionType 注入类型
	 * @param array  $useImports    类的use引用列表
	 * @param null   $value         默认赋值
	 *
	 * @return mixed|object|null
	 */
	public function _newClassAnnotation($className, $injectionType, $useImports = [], $value = null) {
		$_arg = null;
		
		switch ($injectionType) {
			case 'c':
			case 'container':
				$classFullName = $this->findClassNameSpace($className, $useImports);
				if ($classFullName === false) {
					return null;
				}
				
				if ($className != $classFullName && !$this->getList()->hasAs($className)) {
					$this->getList()->setAs($className, $classFullName);
				}
				
				// $_arg = $this->get($classFullName);
				$_arg = $this->get($className);
				break;
			
			case 'cc':
			case 'new':
				$classFullName = $this->findClassNameSpace($className, $useImports);
				if ($classFullName === false) {
					return null;
				}
				
				if ($className != $classFullName && !$this->getList()->hasAs($className)) {
					$this->getList()->setAs($className, $classFullName);
				}
				
				// $_arg = $this->_get($classFullName, true);
				$_arg = $this->_get($className, true);
				break;
			
			case 'v':
			case 'value':
				$_arg = $value;
				break;
		}
		
		return $_arg;
	}
	
	/**
	 * 创建类实例
	 *
	 * @param      $id
	 * @param      $className
	 * @param bool $isNew
	 *
	 * @return null|object
	 * @throws \ReflectionException
	 */
	public function _makeClass($id, $className, $isNew = false) {
		// todo: 动态代理实现AOP 全部按接口来实现
		
		if (!is_string($className)) {
			// todo: 报错类未找到
			return null;
		}
		
		if (!class_exists($className)) {
			// todo: 报错类未找到
			return null;
		}
		
		// if (method_exists($className, '__construct') === false) {
		// todo: 报错类构造函数未找到 我们要求必须有构造函数 可以从基类继承
		// return null;
		// }
		
		// 自动依赖注入
		// todo: 不能用单例
		// $ins = Reflection::from($className, '__construct', Reflection::ANNOTATION_OF_METHOD)
		// $refObj = new Reflection($className, '__construct', Reflection::ANNOTATION_OF_METHOD);
		// $refObj = new Reflection($className, '', Reflection::ANNOTATION_OF_CLASS);
		// $refObj = $this->getReflectionObj();
		$refObj = $this->newReflectionObj();
		
		$refObj
			// 设置className
			->setClassName($className)
			
			// 载入
			->load()
			
			// 过滤注解 由于获取注解时是class的 而__construct是默认获取并存入到了method中 因此要获取METHOD
			->annotation(AutoInjection::class, Reflection::ANNOTATION_OF_METHOD);
		
		// 注入实例化闭包
		$funcInjection = function ($refObj, $newInstanceCallBack = null) {
			/** @var Reflection $refObj */
			
			// 注入
			$refObj->injection(
				function (Reflection $me, ReflectionParameter $param, $useImports = []) {
					$_arg = null;
					
					/**
					 * 检查是否有注解 AutoInjection
					 *
					 * @var AutoInjection[] $anObjs
					 */
					$anObjs            = $me->getAnnotationObjs();
					$found             = false;
					$autoInjectionItem = null;
					
					if (!empty($anObjs)) {
						foreach ($anObjs as $item) {
							if ($item->arg == $param->getName()) {
								$found = true;
								// $containerKey = $item->name;
								$autoInjectionItem = $item;
								break;
							}
						}
					}
					
					if ($found) {
						$_arg = null;
						
						// $_class = $autoInjectionItem->name ?? '';
						// if (empty($_class) && $param->hasType() && $param->getClass() !== null) {
						// 	// 如果有类型约束 并且是个类 就构建这个依赖
						// 	$_class = $param->getClass()->getName();
						// }
						
						// 容器id
						$_id = $autoInjectionItem->id ?? '';
						
						// 如果没有指定容器id 就取参数定义的类型约束
						if (empty($_id) && $param->hasType() && $param->getClass() !== null) {
							// 如果有类型约束 并且是个类 就构建这个依赖
							$_id = $param->getClass()->getName();
						}
						
						// 如果定义了要实例化的其他类 就将其加入映射表
						if (!empty($autoInjectionItem->name) && !$this->getList()->hasAs($_id)) {
							$_class = $autoInjectionItem->name;
							
							$classFullName = $this->findClassNameSpace($_class, $useImports);
							if ($classFullName !== false) {
								$this->getList()->setAs($_id, $classFullName);
							}
						}
						
						empty($_id) && $_id = $autoInjectionItem->name ?? '';
						
						if (!empty($_id)) {
							$_arg = $this->_newClassAnnotation($_id,
							                                   $autoInjectionItem->type,
							                                   $useImports, // $me->getClassUseImports(),
							                                   $autoInjectionItem->value);
						}
					} elseif ($param->hasType() && $param->getClass() !== null) {
						// 如果有类型约束 并且是个类 就构建这个依赖
						$newClass = $this->get($param->getClass()->getName());
						$_arg     = $newClass;
					} elseif ($param->isDefaultValueAvailable()) {
						$_arg = $param->getDefaultValue();
					}
					
					return $_arg;
				},
				$newInstanceCallBack
			);
		};
		
		if (!$isNew && $this->isAopEnabled() && !in_array($className, $this->getAopIgnore())) {
			// Aop实现
			
			/** @var AopProxyFactory $aopProxyFactory */
			$aopProxyFactory = $this->get(AopProxyFactory::class);
			$aopProxyFactory->setClassName($className);
			
			// 是否有aop切面拦截 如果有就生成动态代理 如果没有就不用生成了
			$hasAopAdvice = $aopProxyFactory->hasAopAdvice();
			if ($hasAopAdvice) {
				$res = $aopProxyFactory
					// ->setClassInstance($ins)
					->setReflectionClass($refObj)
					->setAopScanParent($this->isAopScanParent())
					->buildProxyClassCacheFile();
				
				if ($res !== false) {
					$proxyClassName = $aopProxyFactory->getProxyClassName();
					
					if (!empty($proxyClassName)) {
						$funcInjection($refObj, function ($args, $refObj) use ($proxyClassName) {
							if (!class_exists($proxyClassName)) {
								return true;
							}
							
							$refObj->_setInjectionInstance((new \ReflectionClass($proxyClassName))->newInstanceArgs($args));
							
							return false;
						});
					} else {
						$funcInjection($refObj, null);
					}
				} else {
					$funcInjection($refObj, null);
				}
			} else {
				$funcInjection($refObj, null);
			}
		} else {
			$funcInjection($refObj, null);
		}
		
		// 处理类属性的注解注入
		$refObj->classPropertys(
			function (Reflection $me, \ReflectionProperty $property, $propertyAnno, $useImports) {
				/** @var AutoInjection[] $propertyAnno */
				
				if (empty($propertyAnno)) {
					return true;
				}
				
				// 遍历注解 取出AutoInjection注入部分 进行注入
				foreach ($propertyAnno as $annot) {
					// $_id    = $annot->name;
					$_type  = $annot->type;
					$_value = $annot->value;
					
					// if (empty($_id)) {
					// 	continue;
					// }
					
					// 容器id
					$_id = $annot->id ?? '';
					
					// 如果定义了要实例化的其他类 就将其加入映射表
					if (!empty($annot->name) && !$this->getList()->hasAs($_id)) {
						$_class = $annot->name;
						
						$classFullName = $this->findClassNameSpace($_class, $useImports);
						if ($classFullName !== false) {
							$this->getList()->setAs($_id, $classFullName);
						}
					}
					
					empty($_id) && $_id = $annot->name ?? '';
					
					if (empty($_id)) {
						continue;
					}
					
					// new class 会处理依赖
					$_arg = $this->_newClassAnnotation($_id,
					                                   $_type,
					                                   $useImports,
					                                   $_value);
					
					if (is_null($_arg)) {
						continue;
					}
					
					// 注入
					$me->gsPrivateProperty($me->getRefClass(),
					                       $property->getName(),
					                       $_arg,
					                       $me->getInjectionInstance());
				}
				
				return true;
			}, [AutoInjection::class]
		);
		
		// 获取实例
		$ins = $refObj->getInjectionInstance();
		
		// 如果存在容器接纳 将自身实例传入
		if (is_callable([$ins, '_setContainer'])) {
			call_user_func_array([$ins, '_setContainer'], [$this]);
		}
		
		// 如果存在反射助手接纳 将反射助手实例传入
		if (is_callable([$ins, '_setReflection'])) {
			call_user_func_array([$ins, '_setReflection'], [$refObj]);
		}
		
		return $ins;
	}
	
	/**
	 * 获取并自动注入
	 *
	 * @param      $id
	 * @param bool $isNew 是否重新实例化一个新的对象 个别依赖对象不能单例 必须重新new
	 *
	 * @return mixed|object|null
	 * @throws \ReflectionException
	 */
	public function _get($id, $isNew = false) {
		$_list = $this->list();
		
		if ($isNew) {
			$className = $id;
			
			// 查找别名
			$this->getList()->hasAlias($id) && $className = $this->getList()->getAlias($id);
			// 查找映射
			$this->getList()->hasAs($id) && $className = $this->getList()->getAs($id);
			
			return $this->_makeClass($id, $className, $isNew);
		} else {
			if (!$_list->has($id)) {
				if ($this->isKeyNotExistAutoCreate()) {
					$this->set($id);
				} else {
					return null;
				}
			}
			
			$item = $_list->get($id);
		}
		
		$data = $item->getData();
		
		if ($data === null) {
			return null;
		}
		
		// 工厂函数$c为空 自动注入
		if ($data->_getFactoryFunc() === null) {
			// 构建工厂
			$_factoryFunc = function (TreeFuncData $data, TreeFunc $it, Container $c) use ($id, $isNew) {
				/**
				 * 别名和映射的区别在于 key键名不同
				 * 设a为真实类的完整名称 b为a的昵称
				 *  别名：key键名为a 使用时用他的昵称b 经过翻译实际用a去访问
				 *  映射：key键名为b 使用时用他的昵称b 映射出a用a去访问
				 *       由于key的键名与实际a不同 因此可以存储a的多份实例 用他不同的映射昵称去区分
				 */
				
				$className = $id;
				
				// 查找别名
				$it->getParent()->hasAlias($id) && $className = $it->getParent()->getAlias($id);
				// 查找映射
				$it->getParent()->hasAs($id) && $className = $it->getParent()->getAs($id);
				
				// if ($id != $className && $this->getList()->has($className)) {
				// 	return $this->getList()->get($className)->getDataValue();
				// }
				
				return $this->_makeClass($id, $className, $isNew);
			};
			
			// 将工厂加入到Data
			$item->getData()->set(function ($data, $it) use ($_factoryFunc) {
				return call_user_func_array($_factoryFunc, [$data, $it, $this]);
			});
		}
		
		if ($item->getData() === null) {
			return null;
		}
		
		return $item->getDataValue();
	}
	
	/**
	 * 获取
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @inheritDoc
	 */
	public function get($id) {
		// return $this->$id;
		// return $this->list()->get($id, [$this]);
		
		return $this->_get($id);
	}
	
	/**
	 * 是否存在
	 *
	 * @param $id
	 *
	 * @return bool
	 * @inheritDoc
	 */
	public function has($id) {
		// return array_key_exists($id, $this->c);
		return $this->list()->has($id);
	}
	
	/**
	 * 设置
	 *
	 * @param               $id
	 * @param \Closure|null $c
	 *
	 * @return $this
	 */
	public function set($id, \Closure $c = null) {
		$item = new TreeFunc();
		
		if ($c !== null && $c instanceof \Closure) {
			$item->getData()->set(function ($data, $it) use ($c) {
				return call_user_func_array($c, [$data, $it, $this]);
			});
		}
		
		$this->list()->set($id, $item);
		
		return $this;
	}
	
	/**
	 * 获取or设置 list
	 *
	 * @param null $list
	 *
	 * @return $this|TreeFunc
	 */
	public function list($list = null): TreeFunc {
		if ($list === null) {
			return $this->_list;
		} else {
			$this->_list = $list;
		}
		
		return $this;
	}
	
	/**
	 * 实例化一个类 可支持依赖注入
	 * Date: 2020/8/9 22:17
	 *
	 * @param $className
	 *
	 * @return mixed|object|null
	 * @throws \ReflectionException
	 */
	public function invoke($className) {
		return $this->_get($className, false);
	}
	
	/**
	 * 实例化一个类 可支持依赖注入
	 * Date: 2020/8/9 22:17
	 *
	 * @param $className
	 *
	 * @return mixed|object|null
	 * @throws \ReflectionException
	 */
	public function invokeNew($className) {
		return $this->_get($className, true);
	}
	
	/**
	 * @return bool
	 */
	public function isKeyNotExistAutoCreate(): bool {
		return $this->_keyNotExistAutoCreate;
	}
	
	/**
	 * @param bool $keyNotExistAutoCreate
	 *
	 * @return $this
	 */
	public function setKeyNotExistAutoCreate(bool $keyNotExistAutoCreate) {
		$this->_keyNotExistAutoCreate = $keyNotExistAutoCreate;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isAopEnabled(): bool {
		return $this->_aopEnabled;
	}
	
	/**
	 * @param bool $aopEnabled
	 * @return $this
	 */
	public function setAopEnabled(bool $aopEnabled) {
		$this->_aopEnabled = $aopEnabled;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isAopScanParent(): bool {
		return $this->_aopScanParent;
	}
	
	/**
	 * @param bool $aopScanParent
	 * @return $this
	 */
	public function setAopScanParent(bool $aopScanParent) {
		$this->_aopScanParent = $aopScanParent;
		
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	public function &getAopIgnore(): array {
		return $this->_aopIgnore;
	}
	
	/**
	 * @param string[] $aopIgnore
	 * @return $this
	 */
	public function setAopIgnore(array $aopIgnore) {
		$this->_aopIgnore = $aopIgnore;
		
		return $this;
	}
	
	/**
	 * 添加一条Aop忽略类名
	 * Date: 2020/8/23
	 * Time: 17:51
	 *
	 * @param string $className
	 * @return $this
	 */
	public function addAopIgnore(string $className) {
		if (in_array($className, $this->_aopIgnore)) {
			return $this;
		}
		
		$this->_aopIgnore[] = $className;
		
		return $this;
	}
	
	// public function __call($method, $args) {
	// 	if ($this->isErr()) { return $this->return_error(); }
	//
	// 	// 从list中查找方法
	// 	if (is_callable([$this->list(), $method])) {
	// 		return call_user_func_array([$this->list(), $method], $args);
	// 	}
	//
	// 	// 方法不存在
	// 	$this->error('方法不存在', 1000);
	// 	return $this;
	// }
	
	// /**
	//  * 设置
	//  *
	//  * @param $id
	//  * @param $c
	//  *
	//  * @return $this
	//  */
	// public function set($id, $c) {
	// 	$this->$id = $c;
	// 	return $this;
	// }
	//
	// /**
	//  * 缓存实例
	//  *
	//  * @param $id
	//  * @param $obj
	//  *
	//  * @return mixed
	//  */
	// public function cache($id, $obj = null) {
	// 	if ($obj === null) {
	// 		return $this->lastObj[$id];
	// 	}
	//
	// 	$this->lastObj[$id] = $obj;
	// 	return $obj;
	// }
	//
	// /**
	//  * 缓存实例是否存在
	//  *
	//  * @param $id
	//  *
	//  * @return bool
	//  */
	// public function hasCache($id) {
	// 	return array_key_exists($id, $this->lastObj);
	// }
	//
	// /**
	//  * 删除缓存值
	//  *
	//  * @param $id
	//  */
	// public function removeCache($id) {
	// 	unset($this->lastObj[$id]);
	// }
	
	/**
	 * @return TreeFunc
	 */
	public function getList(): TreeFunc {
		return $this->_list;
	}
	
	/**
	 * @param TreeFunc $list
	 *
	 * @return $this
	 */
	public function setList(TreeFunc $list) {
		$this->_list = $list;
		
		return $this;
	}
	
	/**
	 * @return Reflection
	 */
	public function newReflectionObj(): Reflection {
		return new Reflection();
	}
	
}

// demo
// $class = new Container();
//
// $class->c = function () {
// 	return new C();
// };
// $class->b = function ($class) {
// 	return new B($class->c);
// };
// $class->a = function ($class) {
// 	return new A($class->b);
// };