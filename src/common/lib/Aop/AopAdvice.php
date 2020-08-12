<?php


namespace uujia\framework\base\common\lib\Aop;

use uujia\framework\base\common\lib\Aop\JointPoint\ProceedingJoinPoint;

/**
 * Trait AopAdvice
 * Date: 2020/8/2 23:28
 *
 * @package uujia\framework\base\common\lib\Aop
 */
trait AopAdvice {

	public function process(ProceedingJoinPoint $proceedingJoinPoint) {
		return $proceedingJoinPoint->process();
	}
	
}