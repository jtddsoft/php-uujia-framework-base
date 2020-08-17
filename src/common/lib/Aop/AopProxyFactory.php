<?php


namespace uujia\framework\base\common\lib\Aop;


use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionMethod;
use ReflectionNamedType;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Aop\Cache\AopCacheDataProvider;
use uujia\framework\base\common\lib\Aop\Cache\AopProxyCacheDataProvider;
use uujia\framework\base\common\lib\Aop\Vistor\AopProxyVisitor;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheDataManager;
use uujia\framework\base\common\lib\Cache\CacheDataManagerInterface;
use uujia\framework\base\common\lib\Cache\CacheDataProvider;
use uujia\framework\base\common\lib\Exception\ExceptionAop;
use uujia\framework\base\common\lib\Reflection\CodeParser;
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
	 *
	 * @var string
	 */
	protected $_className = '';
	
	/**
	 * 代理的类实例
	 *
	 * @var BaseClass
	 */
	protected $_classInstance;
	
	/**
	 * 反射类
	 *
	 * @var Reflection
	 */
	protected $_reflectionClass;
	
	/**
	 * 生成的代理类保存路径定义
	 *
	 * @var string
	 */
	protected $_proxyClassFilePath = '';
	
	/**
	 * 生成的代理类命名空间定义
	 *
	 * @var string
	 */
	protected $_proxyClassNameSpace = '';
	
	/**
	 * 代理模板路径 用于生成代理类
	 *
	 * @var string
	 */
	protected $_proxyTemplatePath = '';
	
	/**
	 * 代理模板内容
	 *
	 * @var string
	 */
	protected $_proxyTemplateText = '';
	
	/**
	 * AopProxyFactory constructor.
	 *
	 * @param CacheDataManagerInterface|null $cacheDataManagerObj
	 *
	 * @AutoInjection(arg = "cacheDataManagerObj", name = "CacheDataManager")
	 */
	public function __construct(CacheDataManagerInterface $cacheDataManagerObj = null) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		$this->_proxyTemplatePath   = __DIR__ . '/Template/_AopProxyTemplate.t';
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 *
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
		$this->name_info['name']  = self::class;
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
		$filePath   = $this->getProxyClassFilePath();
		$_namespace = $this->getProxyClassNameSpace();
		if (empty($filePath) || empty($_namespace)) {
			return false;
		}
		
		if (!file_exists($filePath)) {
			// throw new ExceptionAop('路径不存在');
		}
		
		// 读模板文件内容
		$_templateText = $this->getProxyTemplateText();
		if (empty($_templateText)) {
			return false;
		}
		
		// namespace
		// $_namespaceVar = $_namespace;
		
		// class
		// $_class = $this->getProxyClassNameSpace() . '\\' . basename($this->getClassName());
		$_class = $this->getProxyClassFromCache($this->getClassName());
		if (empty($_class)) {
			return false;
		}
		// // class变量替换
		// $_classVar = str_replace('\\', '_', $_class);
		
		// filename
		$_fileName = $filePath . '/' . basename($_class) . '.php';
		
		// extendsClass
		$_extendsClass    = $this->getClassName();
		$_extendsClassVar = '\\' . $_extendsClass;
		
		// method
		$_ref        = $this->getReflectionClass();
		// $_refMethods = $_ref->getRefMethods();
		// $_methodsVar = '';
		
		// 通过类名反射出文件名
		$_sourceFileName = $_ref->getRefClass()->getFileName();
		if (!file_exists($_sourceFileName)) {
			return false;
		}
		
		$_sourceCodeText = File::readToText($_sourceFileName);
		if (empty($_sourceCodeText)) {
			return false;
		}
		
		$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		$ast    = $parser->parse($_sourceCodeText);
		
		$traverser = new NodeTraverser();
		$visitor = new AopProxyVisitor($this->getClassName(), $_class);
		$traverser->addVisitor($visitor);
		$proxyAst = $traverser->traverse($ast);
		if (!$proxyAst) {
			throw new \Exception(sprintf('Class %s AST optimize failure', $_class));
		}
		$printer = new Standard();
		$proxyCode = $printer->prettyPrint($proxyAst);
		
		// $codeParserObj = CodeParser::getInstance();
		// $codeParserObj->reset()
		//               ->setClassFileName($_sourceFileName)
		//               ->loadFile()
		//               ->parse();
		//
		// foreach ($_refMethods as $_refMethodItem) {
		// 	/** @var ReflectionMethod $_refMethodItem */
		// 	if (!$_refMethodItem->isPublic() || $_refMethodItem->isAbstract() || $_refMethodItem->isConstructor()) {
		// 		continue;
		// 	}
		//
		// 	$_methodName = $_refMethodItem->getName();
		// 	$_refParams  = $_refMethodItem->getParameters();
		//
		// 	$_paramsText = [];
		// 	foreach ($_refParams as $_refParamItem) {
		// 		/** @var \ReflectionParameter $_refParamItem */
		//
		// 		$_p = [
		// 			'typeName'       => '',
		// 			'paramName'      => $_refParamItem->getName(),
		// 			'isDefaultValue' => $_refParamItem->isDefaultValueAvailable(),
		// 			'defaultValue'   => $_refParamItem->isDefaultValueAvailable() ? $_refParamItem->getDefaultValue() : '',
		// 		];
		//
		// 		$_pText = '';
		//
		// 		if ($_refParamItem->hasType()) {
		// 			if ($_refParamItem instanceof ReflectionNamedType) {
		// 				$_p['typeName'] = $_refParamItem->getType() . '' ?? '';
		// 			} elseif ($_refParamItem->getClass() !== null) {
		// 				$_p['typeName'] = '\\' . $_refParamItem->getClass()->getName();
		// 			}
		// 		}
		//
		// 		!empty($_p['typeName']) && $_pText .= "{$_p['typeName']} ";
		// 		$_pText .= "\${$_p['paramName']}";
		// 		if ($_p['isDefaultValue']) {
		// 			if (is_string($_p['defaultValue'])) {
		// 				$_pText .= " = '{$_p['defaultValue']}'";
		// 			} elseif (is_array($_p['defaultValue'])) {
		// 				$_arr = [];
		// 				foreach ($_p['defaultValue'] as $item) {
		// 					if (is_string($item)) {
		// 						$_arr[] = "'{$item}'";
		// 					} else {
		// 						$_arr[] = "{$item}";
		// 					}
		// 				}
		// 				$_arrText = implode(',', $_arr);
		// 				$_pText .= " = [{$_arrText}]";
		// 			} else {
		// 				$_pText .= " = {$_p['defaultValue']}";
		// 			}
		// 		}
		//
		// 		$_paramsText[] = $_pText;
		// 	}
		//
		// 	$_paramsVar = implode(', ', $_paramsText);
		//
		// 	$_methodsVar .= "\tpublic function {$_methodName}({$_paramsVar}) {\n";
		// 	$_methodsVar .= "\t\treturn call_user_func_array([\$this, '_aopCall'], ['{$_methodName}', func_get_args()]);\n";
		// 	$_methodsVar .= "\t}\n";
		//
		// 	$_methodsVar .= "\n";
		// }
		//
		// $text = $_templateText;
		// $text = str_replace('%namespace%', $_namespaceVar, $text);
		// $text = str_replace('%class%', $_classVar, $text);
		// $text = str_replace('%extendsClass%', $_extendsClassVar, $text);
		// $text = str_replace('%methods%', $_methodsVar, $text);
		//
		// $res = File::writeFromText($_fileName, $text);
		
		return $proxyCode;
	}
	
	/**
	 * 通过目标类获取代理类类名
	 *
	 * Date: 2020/8/17
	 * Time: 16:39
	 *
	 * @param string $className
	 *
	 * @return mixed|string
	 */
	public function getProxyClassFromCache(string $className) {
		/**
		 * 获取AopProxyClass缓存提供商集合
		 * @var CacheDataProvider[] $cdProviders
		 */
		$cdProviders = $this->getCacheDataProviderAopProxyClass();
		if (empty($cdProviders)) {
			return '';
		}
		
		/** @var TreeFunc $it */
		$it = $cdProviders['it'];
		if ($it->count() == 0) {
			throw new ExceptionAop('未找到AopProxyClass缓存供应商', 1000);
		}
		
		/**
		 * 只取第一个缓存提供商（获取aop代理类属一对一获取 更多的供应商没有意义 暂时只取我提供的）
		 * @var AopProxyCacheDataProvider $aopProvider
		 */
		$aopProvider = $it[0]->getDataValue();
		
		$result = '';
		// 跑循环其实只是搞生成器 由于是复用缓存供应商 而缓存供应商默认是返回生成器 所以虽然只取一个 也跑一下生成器
		// 当然也可以单独写个缓存处理 也可以单独处理生成器 只是循环一下省事 有兴趣可以帮我优化
		foreach ($aopProvider->setClassName($className)
		                     ->setProxyClassNameSpace($this->getProxyClassNameSpace())
		                     ->fromCache() as $item) {
			if (!empty($item)) {
				$result = $item;
				break;
			}
		}
		
		return $result;
	}
	
	/**
	 * 获取AOPProxyClass缓存供应商对象集合
	 * 我只提供一个 但您可以增加多个
	 * 这里返回的是个数组 具体看CacheDataManager中的定义
	 *
	 * @return CacheDataProvider[]|null
	 */
	public function getCacheDataProviderAopProxyClass() {
		$cdMgr       = $this->getCacheDataManagerObj();
		$cdProviders = $cdMgr->getProviderList()->getKeyDataValue(CacheConstInterface::DATA_PROVIDER_KEY_AOP_PROXY_CLASS);
		
		return $cdProviders;
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
		return $this->_proxyClassFilePath ?? '';
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
		return $this->_proxyClassNameSpace ?? '';
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
	
	/**
	 * @return string
	 */
	public function getProxyTemplatePath(): string {
		return $this->_proxyTemplatePath ?? '';
	}
	
	/**
	 * @param string $proxyTemplatePath
	 *
	 * @return AopProxyFactory
	 */
	public function setProxyTemplatePath(string $proxyTemplatePath) {
		$this->_proxyTemplatePath = $proxyTemplatePath;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getProxyTemplateText(): string {
		if (empty($this->_proxyTemplateText) && file_exists($this->getProxyTemplatePath())) {
			$this->_proxyTemplateText = File::readToText($this->getProxyTemplatePath());
		}
		
		return $this->_proxyTemplateText ?? '';
	}
	
	/**
	 * @param string $proxyTemplateText
	 *
	 * @return AopProxyFactory
	 */
	public function setProxyTemplateText(string $proxyTemplateText) {
		$this->_proxyTemplateText = $proxyTemplateText;
		
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