<?php


namespace uujia\framework\base\common\lib\Aop;

/**
 * Interface AopAdviceInterface
 * Date: 2020/8/2 23:37
 *
 * @package uujia\framework\base\common\lib\Aop
 */
interface AopAdviceInterface {

	public function process(\Closure $next);
	
}