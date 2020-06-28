<?php


namespace uujia\framework\base\common\lib\Container;


use uujia\framework\base\common\lib\Base\BaseClass;

class ProxyClassFactory extends BaseClass {
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