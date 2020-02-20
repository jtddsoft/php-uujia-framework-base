<?php

namespace uujia\framework\base\common\lib\Utils;


use ReflectionMethod;

class Reflection {
	
	/**
	 * invokeInjection
	 * 依赖注入
	 *
	 * @param $value
	 *
	 * @return false|string
	 */
	public static function invokeInjection($className, $args) {
		try {
			// 反射获取类的构造函数
			$refMethod = new ReflectionMethod($className, '__construct'); // 获取构造函数参数列表
			$refParams = $refMethod->getParameters();
			
			if (is_array($args)) {
				$_args = $args;
			} elseif (is_callable($args)) {
				$_args = [];
				
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
	
	
}