<?php


namespace uujia\framework\base\common\lib\Container;


use uujia\framework\base\common\lib\Reflection\Reflection;
use uujia\framework\base\common\lib\Tree\TreeFunc;

/**
 * interface Container
 * 基础容器
 *
 * @package uujia\framework\base\common\lib\Container
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface {

	/**************************************************
	 * 节点操作
	 **************************************************/

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
	public function findClassNameSpace($className, $useImports = []);
	
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
	public function _newClassAnnotation($className, $injectionType, $useImports = [], $value = null);
	
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
	public function _makeClass($id, $className, $isNew = false);
	
	/**
	 * 获取并自动注入
	 *
	 * @param      $id
	 * @param bool $isNew 是否重新实例化一个新的对象 个别依赖对象不能单例 必须重新new
	 *
	 * @return mixed|object|null
	 * @throws \ReflectionException
	 */
	public function _get($id, $isNew = false);
	
	/**
	 * 设置
	 *
	 * @param               $id
	 * @param \Closure|null $c
	 *
	 * @return $this
	 */
	public function set($id, \Closure $c = null);
	
	/**
	 * 获取or设置 list
	 *
	 * @param null $list
	 *
	 * @return $this|TreeFunc
	 */
	public function list($list = null): TreeFunc;
	
	/**
	 * 实例化一个类 可支持依赖注入
	 * Date: 2020/8/9 22:17
	 *
	 * @param $className
	 *
	 * @return mixed|object|null
	 * @throws \ReflectionException
	 */
	public function invoke($className);
	
	/**
	 * @return bool
	 */
	public function isKeyNotExistAutoCreate(): bool;
	
	/**
	 * @param bool $keyNotExistAutoCreate
	 *
	 * @return $this
	 */
	public function setKeyNotExistAutoCreate(bool $keyNotExistAutoCreate);
	
	/**
	 * @return bool
	 */
	public function isAopEnabled(): bool;
	
	/**
	 * @param bool $aopEnabled
	 * @return Container
	 */
	public function setAopEnabled(bool $aopEnabled);
	
	/**
	 * @return string[]
	 */
	public function &getAopIgnore(): array;
	
	/**
	 * @param string[] $aopIgnore
	 * @return Container
	 */
	public function setAopIgnore(array $aopIgnore);
	
	/**
	 * @return TreeFunc
	 */
	public function getList(): TreeFunc;
	
	/**
	 * @param TreeFunc $list
	 *
	 * @return $this
	 */
	public function setList(TreeFunc $list);
	
	/**
	 * @return Reflection
	 */
	public function newReflectionObj(): Reflection;
	
}
