<?php


namespace uujia\framework\base\common\lib\Container;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionMethod;
use ReflectionParameter;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\Reflection;
use uujia\framework\base\common\traits\InstanceBase;
use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

/**
 * Class ContainerProvider
 * 基础容器
 *
 * @package uujia\framework\base\common\lib\Container
 */
class Container extends BaseClass implements ContainerInterface, \Iterator, \ArrayAccess {
	use NameBase;
	use ResultBase;
	use InstanceBase;
	
	// private $c = [];
	// // 每次实例化都会存入对象实例 如果已存在就覆盖
	// private $lastObj = [];
	
	/** @var $_list TreeFunc */
	protected $_list;
	
	/**
	 * key不存在时尝试自动new实例
	 * @var bool $_keyNotExistAutoCreate
	 */
	protected $_keyNotExistAutoCreate = true;
	
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
		$this->name_info['name'] = self::class;
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
	 * 获取
	 *
	 * @param $id
	 * @return mixed
	 * @inheritDoc
	 */
	public function get($id) {
		// return $this->$id;
		// return $this->list()->get($id, [$this]);
		
		$_list = $this->list();
		if (!$_list->has($id)) {
			if ($this->isKeyNotExistAutoCreate()) {
				$this->set($id);
			} else {
				return null;
			}
		}
		
		$item = $_list->get($id);
		$data = $item->getData();
		
		if ($data === null) {
			return null;
		}
		
		// 工厂函数$c为空 自动注入
		if ($data->_getFactoryFunc() === null) {
			// 构建工厂
			$_factoryFunc = function (TreeFuncData $data, TreeFunc $it, Container $c) use ($id) {
				$it->getParent()->hasAlias($id) && $id = $it->getParent()->getAlias($id);
				
				if(is_string($id) && class_exists($id)){
					try {
						$className = $id;
						
						if (method_exists($className,  '__construct') === false) {
							// todo: 报错类构造函数未找到
							return null;
						}
						
						// 自动依赖注入
						// todo: 不能用单例
						// $ins = Reflection::from($className, '__construct', Reflection::ANNOTATION_OF_METHOD)
						$refObj = new Reflection($className, '__construct', Reflection::ANNOTATION_OF_METHOD);
						$ins = $refObj
							->load()
							->annotation(AutoInjection::class)
							->injection(function (Reflection $me, ReflectionParameter $param) use ($c) {
								$_arg = null;
								
								/**
								 * 检查是否有注解 AutoInjection
								 * @var AutoInjection[] $anObjs
								 */
								$anObjs = $me->getAnnotationObjs();
								$found = false;
								$autoInjectionItem = '';
								
								if (!empty($anObjs)) {
									foreach ($anObjs as $item) {
										/** @var AutoInjection $item */
										if ($item->arg == $param->getName()) {
											$found = true;
											// $containerKey = $item->name;
											$autoInjectionItem = $item;
											break;
										}
									}
								}
								
								if ($found) {
									// $_arg = $c->get($containerKey);
									switch ($autoInjectionItem->type) {
										case 'c':
											$_arg = $c->get($autoInjectionItem->name);
											break;
											
										case 'v':
											$_arg = $autoInjectionItem->value;
											break;
									}
								} elseif ($param->hasType() && $param->getClass() !== null) {
									// 如果有类型约束 并且是个类 就构建这个依赖
									$newClass = $c->get($param->getClass()->getName());
									$_arg     = $newClass;
								} elseif ($param->isDefaultValueAvailable()) {
									$_arg = $param->getDefaultValue();
								}
								
								return $_arg;
							})
							->getInjectionInstance();
						
						// $ins = Reflection::invokeInjection($className,
						// 	function (ReflectionMethod $refMethod, array $refParams, ReflectionParameter $param) use ($c) {
						// 		$_arg = null;
						//
						// 		// 如果有类型约束 并且是个类 就构建这个依赖
						// 		if ($param->hasType() && $param->getClass() !== null) {
						// 			$newClass = $c->get($param->getClass()->getName());
						// 			$_arg     = $newClass;
						// 		} elseif ($param->isDefaultValueAvailable()) {
						// 			$_arg = $param->getDefaultValue();
						// 		}
						//
						// 		return $_arg;
						// 	});
						
						return $ins;
					} catch (\ReflectionException $e) {
						// todo: 报错反射异常
						return null;
					}
				} else {
					// todo: 报错类未找到
					return null;
				}
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
	 * @param $id
	 * @param \Closure $c
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
	 * @return bool
	 */
	public function isKeyNotExistAutoCreate(): bool {
		return $this->_keyNotExistAutoCreate;
	}
	
	/**
	 * @param bool $keyNotExistAutoCreate
	 * @return $this
	 */
	public function setKeyNotExistAutoCreate(bool $keyNotExistAutoCreate) {
		$this->_keyNotExistAutoCreate = $keyNotExistAutoCreate;
		
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