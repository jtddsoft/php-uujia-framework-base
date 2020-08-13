<?php


namespace uujia\framework\base\common\lib\Aop;


use ReflectionMethod;
use ReflectionNamedType;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Aop\Cache\AopCacheDataProvider;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Exception\ExceptionAop;
use uujia\framework\base\common\lib\Reflection\Reflection;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\File;
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
	 * 生成的代理类保存路径定义
	 * @var string
	 */
	protected $_proxyClassFilePath;
	
	/**
	 * 生成的代理类命名空间定义
	 * @var string
	 */
	protected $_proxyClassNameSpace;
	
	/**
	 * AopProxyFactory constructor.
	 *
	 * @param CacheDataManagerInterface|null $cacheDataManagerObj
	 *
	 * @AutoInjection(arg = "cacheDataManagerObj", name = "CacheDataManager")
	 */
	public function __construct(CacheDataManagerInterface $cacheDataManagerObj = null) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		
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
	
	/**************************************************************
	 * build
	 **************************************************************/
	
	/**
	 * 构建生成代理类 写入代理缓存文件
	 *
	 * Date: 2020/8/13
	 * Time: 17:53
	 *
	 * @return bool|false|string
	 * @throws \ReflectionException
	 */
	public function buildProxyClassCacheFile() {
		$filePath = $this->getProxyClassFilePath();
		if (!file_exists($filePath)) {
			// throw new ExceptionAop('路径不存在');
		}
		
		// namespace
		$_namespace = $this->getProxyClassNameSpace();
		$_namespaceVar = $_namespace;
		
		// class
		$_class = $this->getProxyClassNameSpace() . '\\' . basename($this->getClassName());
		// class变量替换
		$_classVar = str_replace('\\', '_', $_class);
		
		// filename
		$_fileName = $filePath . '\\' . $_classVar . '.php';
		
		// extendsClass
		$_extendsClass = $this->getClassName();
		$_extendsClassVar = '\\' . $_extendsClass;
		
		// method
		$_ref = $this->getReflectionClass();
		$_refMethods = $_ref->getRefMethods();
		$_methodsVar = '';
		
		foreach ($_refMethods as $_refMethodItem) {
			/** @var ReflectionMethod $_refMethodItem */
			if (!$_refMethodItem->isPublic() || $_refMethodItem->isAbstract()) {
				continue;
			}
			
			$_methodName = $_refMethodItem->getName();
			$_refParams = $_refMethodItem->getParameters();
			
			$_paramsText = [];
			foreach ($_refParams as $_refParamItem) {
				$_p = [
					'typeName' => '',
					'paramName' => $_refParamItem->getName(),
					'defaultValue' => $_refParamItem->getDefaultValue() ?? '',
				];
				
				$_pText = '';
				
				if ($_refParamItem->hasType()) {
					if ($_refParamItem instanceof ReflectionNamedType) {
						$_p['typeName'] = $_refParamItem->getType() . '' ?? '';
					} elseif ($_refParamItem->getClass() !== null) {
						$_p['typeName'] = $_refParamItem->getClass()->getName();
					}
				}
				
				!empty($_p['typeName']) && $_pText .= "{$_p['typeName']} ";
				$_pText .= "{$_p['paramName']}";
				!empty($_p['defaultValue']) && $_pText .= " = {$_p['defaultValue']}";
				
				$_paramsText[] = $_pText;
			}
			
			$_paramsVar = implode(', ', $_paramsText);
			
			$_methodsVar = "public function {$_methodName}({$_paramsVar}) {\n";
			$_methodsVar .= "\treturn call_user_func_array([\$this, '{$_methodName}'], func_get_args());\n";
			$_methodsVar .= "}\n";
			
			$_methodsVar .= "\n";
		}
		
		$text = File::readToText($_fileName);
		$text = str_replace('%namespace%', $_namespaceVar, $text);
		$text = str_replace('%class%', $_classVar, $text);
		$text = str_replace('%extendsClass%', $_extendsClassVar, $text);
		$text = str_replace('%methods%', $_methodsVar, $text);
		
		$res = File::writeFromText($_fileName, $text);
		
		return $res;
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
	
	/**
	 * @return CacheDataManager
	 */
	public function getCacheDataManagerObj(): CacheDataManager {
		return $this->_cacheDataManagerObj;
	}
	
	/**
	 * @param CacheDataManager $cacheDataManagerObj
	 *
	 * @return AopProxyFactory
	 */
	public function setCacheDataManagerObj(CacheDataManager $cacheDataManagerObj) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getProxyClassFilePath(): string {
		return $this->_proxyClassFilePath;
	}
	
	/**
	 * @param string $proxyClassFilePath
	 *
	 * @return AopProxyFactory
	 */
	public function setProxyClassFilePath(string $proxyClassFilePath) {
		$this->_proxyClassFilePath = $proxyClassFilePath;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getProxyClassNameSpace(): string {
		return $this->_proxyClassNameSpace;
	}
	
	/**
	 * @param string $proxyClassNameSpace
	 *
	 * @return AopProxyFactory
	 */
	public function setProxyClassNameSpace(string $proxyClassNameSpace) {
		$this->_proxyClassNameSpace = $proxyClassNameSpace;
		
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