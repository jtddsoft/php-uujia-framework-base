<?php


namespace uujia\framework\base\common\lib\Aop;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Reflection\Reflection;

/**
 * Class AopProxyFactory
 * Date: 2020/8/2 19:35
 *
 * @package uujia\framework\base\common\lib\Aop
 */
class AopProxyFactory extends BaseClass {
	
	/**
	 * 代理的类名（全名）
	 * @var string
	 */
	protected $_className;
	
	/**
	 * 代理的类实例
	 * @var object
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