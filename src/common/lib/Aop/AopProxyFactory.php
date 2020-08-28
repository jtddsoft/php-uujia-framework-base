<?php


namespace uujia\framework\base\common\lib\Aop;


use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionMethod;
use ReflectionNamedType;
use uujia\framework\base\common\consts\CacheConstInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Aop\Cache\AopCacheDataProvider;
use uujia\framework\base\common\lib\Aop\Cache\AopProxyCacheDataProvider;
use uujia\framework\base\common\lib\Aop\Vistor\AopProxyExtendsVisitor;
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
use uujia\framework\base\common\lib\Utils\Str;
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
	
	// /**
	//  * 代理模板路径 用于生成代理类
	//  *
	//  * @var string
	//  */
	// protected $_proxyTemplatePath = '';
	
	// /**
	//  * 代理模板内容
	//  *
	//  * @var string
	//  */
	// protected $_proxyTemplateText = '';
	
	/**
	 * 代理类名（从缓存代理得来）
	 *
	 * @var string
	 */
	protected $_proxyClassName = '';
	
	/**
	 * 临时缓存（获取一次就会暂存进来）
	 *
	 * @var AopProxyCacheDataProvider
	 */
	protected $_aopProxyCacheDataProviderTmp = null;
	
	/**
	 * Aop是否递归扫描父类
	 *
	 * @var bool
	 */
	protected $_aopScanParent = false;
	
	
	/**
	 * AopProxyFactory constructor.
	 *
	 * @param CacheDataManagerInterface|null $cacheDataManagerObj
	 *
	 * @AutoInjection(arg = "cacheDataManagerObj", name = "CacheDataManager")
	 */
	public function __construct(CacheDataManagerInterface $cacheDataManagerObj = null) {
		$this->_cacheDataManagerObj = $cacheDataManagerObj;
		// $this->_proxyTemplatePath   = __DIR__ . '/Template/_AopProxyTemplate.t';
		
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
		$this->name_info['name']  = static::class;
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
	 * @throws ExceptionAop
	 */
	public function buildProxyClassCacheFile() {
		$filePath   = $this->getProxyClassFilePath();
		$_namespace = $this->getProxyClassNameSpace();
		if (empty($filePath) || empty($_namespace)) {
			return false;
		}
		
		//if (!file_exists($filePath)) {
			// throw new ExceptionAop('路径不存在');
		//}
		
		// 读模板文件内容
		// $_templateText = $this->getProxyTemplateText();
		// if (empty($_templateText)) {
		// 	return false;
		// }
		
		// namespace
		// $_namespaceVar = $_namespace;
		
		// class
		// $_class = $this->getProxyClassNameSpace() . '\\' . basename($this->getClassName());
		
		$_class = $this->getProxyClassFromCache($this->getClassName());
		$this->setProxyClassName($_class);
		
		if (empty($_class)) {
			return false;
		}
		// // class变量替换
		// $_classVar = str_replace('\\', '_', $_class);
		
		// filename
		$_classBasename = Str::classBasename($_class);
		$_fileName = $filePath . '/' . $_classBasename . '.php';
		
		// extendsClass
		// $_extendsClass = $this->getClassName();
		// $_extendsClassVar = '\\' . $_extendsClass;
		
		// method
		$_ref = $this->getReflectionClass();
		
		// $_refMethods = $_ref->getRefMethods();
		// $_methodsVar = '';
		
		// 通过类名反射出文件名
		$_sourceFileName = $_ref->getRefClass()->getFileName();
		if (!file_exists($_sourceFileName)) {
			return false;
		}
		
		// 校验是否不需要生成
		if (file_exists($_fileName) && !$this->isFileModified($_sourceFileName)) {
			return true;
		}
		
		$_sourceCodeText = File::readToText($_sourceFileName);
		if (empty($_sourceCodeText)) {
			return false;
		}
		
		$stmtsParentNode = [
			'uses' => [],
			'classMethod' => [],
		];
		
		if ($this->isAopScanParent()) {
			// 递归获取父类到数组
			$_refParentClasses = $_ref->getClassExtends($_ref->getRefClass(), []);
			
			// 递归扫描父类
			foreach ($_refParentClasses as $c => $f) {
				// c -- class   f -- filename
				$_code = File::readToText($f);
				if (empty($_code)) {
					continue;
				}
				
				$_parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
				$_ast    = $_parser->parse($_code);
				
				$_traverser = new NodeTraverser();
				$_visitor   = new AopProxyExtendsVisitor($c);
				$_traverser->addVisitor($_visitor);
				$_proxyAst = $_traverser->traverse($_ast);
				if (!$_proxyAst) {
					break;
				}
				
				foreach ($_visitor->getReturnStmts()['classMethod'] as $n => $v) {
					/** @var ClassMethod $v */
					
					if (array_key_exists($n, $stmtsParentNode['classMethod'])) {
						continue;
					}
					
					$stmtsParentNode['classMethod'][$n] = $v;
				}
				
				foreach ($_visitor->getReturnStmts()['uses'] as $n => $v) {
					/** @var Use_ $v */
					
					if (array_key_exists($n, $stmtsParentNode['uses'])) {
						continue;
					}
					
					$stmtsParentNode['uses'][$n] = $v;
				}
			}
		}
		
		$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		$ast    = $parser->parse($_sourceCodeText);
		
		$traverser = new NodeTraverser();
		$visitor   = new AopProxyVisitor($this->getClassName(), $_class, $stmtsParentNode);
		$traverser->addVisitor($visitor);
		$proxyAst = $traverser->traverse($ast);
		if (!$proxyAst) {
			return false;
		}
		$printer   = new Standard();
		$proxyCode = $printer->prettyPrint($proxyAst);
		
		File::create_dir($filePath);
		$res = File::writeFromText($_fileName, "<?php \n" . $proxyCode);
		
		// 更新缓存中的文件更新时间
		$this->updateCacheFileMTime($_sourceFileName);
		
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
		
		return $res;
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
	 * @throws ExceptionAop
	 */
	public function getProxyClassFromCache(string $className) {
		/**
		 * 只取第一个缓存提供商（获取aop代理类属一对一获取 更多的供应商没有意义 暂时只取我提供的）
		 *
		 * @var AopProxyCacheDataProvider $aopProvider
		 */
		$aopProvider = $this->getAopProxyCacheDataProvider();
		
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
	 * 判断文件是否修改
	 *
	 * Date: 2020/8/23
	 * Time: 1:19
	 *
	 * @param string $file
	 * @return bool
	 * @throws ExceptionAop
	 */
	public function isFileModified(string $file) {
		/**
		 * 只取第一个缓存提供商（获取aop代理类属一对一获取 更多的供应商没有意义 暂时只取我提供的）
		 *
		 * @var AopProxyCacheDataProvider $aopProvider
		 */
		$aopProvider = $this->getAopProxyCacheDataProvider();
		
		return $aopProvider->isFileModified($file);
	}
	
	/**
	 * 更新文件时间
	 *
	 * Date: 2020/8/23
	 * Time: 1:15
	 *
	 * @param string $file
	 * @return mixed|bool|int
	 * @throws ExceptionAop
	 */
	public function updateCacheFileMTime(string $file) {
		/**
		 * 只取第一个缓存提供商（获取aop代理类属一对一获取 更多的供应商没有意义 暂时只取我提供的）
		 *
		 * @var AopProxyCacheDataProvider $aopProvider
		 */
		$aopProvider = $this->getAopProxyCacheDataProvider();
		
		return $aopProvider->updateCacheFileMTime($file);
	}
	
	/**
	 * 是否存在切面
	 *
	 * Date: 2020/8/28
	 * Time: 23:15
	 *
	 * @return bool
	 * @throws ExceptionAop
	 */
	public function hasAopAdvice() {
		foreach ($this->getAopCacheDataProviders() as $item) {
			/** @var AopCacheDataProvider $item */
			$exists = $item->setAopTargetClass($this->getClassName())->hasAopAdvice();
			if ($exists) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * 获取aop拦截类供应商
	 * 例如：新建一个切面uujia\framework\base\test\aop\AopEventTest
	 *      要切uujia\framework\base\test\EventTest类的对应关系
	 *
	 * Date: 2020/8/29
	 * Time: 0:02
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
			// throw new ExceptionAop('未找到AOP缓存供应商', 1000);
			return [];
		}
	}
	
	/**
	 * 获取AOPProxyClass缓存供应商对象集合
	 * 我只提供一个 但您可以增加多个
	 * （注意这是一个特殊供应商类型 即使你添加多个 默认我只取第一个
	 *   因为这是一对一关系 一个类对应一个代理类 你添加的其他供应商你只能自行使用）
	 * 这里返回的是个数组 具体看CacheDataManager中的定义
	 *
	 * 例如：uujia\framework\base\test\EventTest
	 *      应生成的动态代理为uujia\framework\base\test\cache\proxy\uujia_framework_base_test_EventTest_5f487c92d9a4f
	 *
	 * @return CacheDataProvider[]|null
	 */
	public function getProxyCacheDataProviders() {
		$cdMgr       = $this->getCacheDataManagerObj();
		$cdProviders = $cdMgr->getProviderList()->getKeyDataValue(CacheConstInterface::DATA_PROVIDER_KEY_AOP_PROXY_CLASS);
		
		return $cdProviders;
	}
	
	/**
	 * 获取AOPProxyClass缓存供应商对象
	 *
	 * Date: 2020/8/23
	 * Time: 2:36
	 *
	 * @return string|AopProxyCacheDataProvider
	 * @throws ExceptionAop
	 */
	public function getAopProxyCacheDataProvider() {
		if (!empty($this->_aopProxyCacheDataProviderTmp)) {
			return $this->_aopProxyCacheDataProviderTmp;
		}
		
		/**
		 * 获取AopProxyClass缓存提供商集合
		 *
		 * @var CacheDataProvider[] $cdProviders
		 */
		$cdProviders = $this->getProxyCacheDataProviders();
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
		 *
		 * @var AopProxyCacheDataProvider $aopProvider
		 */
		$aopProvider = $it[0]->getDataValue();
		
		// 计入临时变量缓存一下
		$this->_aopProxyCacheDataProviderTmp = $aopProvider;
		
		return $aopProvider;
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
	
	// /**
	//  * @return string
	//  */
	// public function getProxyTemplatePath(): string {
	// 	return $this->_proxyTemplatePath ?? '';
	// }
	//
	// /**
	//  * @param string $proxyTemplatePath
	//  *
	//  * @return AopProxyFactory
	//  */
	// public function setProxyTemplatePath(string $proxyTemplatePath) {
	// 	$this->_proxyTemplatePath = $proxyTemplatePath;
	//
	// 	return $this;
	// }
	
	// /**
	//  * @return string
	//  */
	// public function getProxyTemplateText(): string {
	// 	if (empty($this->_proxyTemplateText) && file_exists($this->getProxyTemplatePath())) {
	// 		$this->_proxyTemplateText = File::readToText($this->getProxyTemplatePath());
	// 	}
	//
	// 	return $this->_proxyTemplateText ?? '';
	// }
	//
	// /**
	//  * @param string $proxyTemplateText
	//  *
	//  * @return AopProxyFactory
	//  */
	// public function setProxyTemplateText(string $proxyTemplateText) {
	// 	$this->_proxyTemplateText = $proxyTemplateText;
	//
	// 	return $this;
	// }
	
	/**
	 * @return string
	 */
	public function getProxyClassName(): string {
		return $this->_proxyClassName;
	}
	
	/**
	 * @param string $proxyClassName
	 *
	 * @return AopProxyFactory
	 */
	public function setProxyClassName(string $proxyClassName) {
		$this->_proxyClassName = $proxyClassName;
		
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
	 * @return AopProxyFactory
	 */
	public function setAopScanParent(bool $aopScanParent) {
		$this->_aopScanParent = $aopScanParent;
		
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