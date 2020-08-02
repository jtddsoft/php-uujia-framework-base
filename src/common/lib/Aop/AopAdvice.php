<?php


namespace uujia\framework\base\common\lib\Aop;

/**
 * Trait AopAdvice
 * Date: 2020/8/2 23:28
 *
 * @package uujia\framework\base\common\lib\Aop
 */
trait AopAdvice {

	public function process(\Closure $next) {
		return $next();
	}
	
}