<?php


namespace uujia\framework\base\common\lib\Aop;

use uujia\framework\base\common\lib\Aop\JointPoint\ProceedingJoinPoint;

/**
 * Interface AopAdviceInterface
 * Date: 2020/8/2 23:37
 *
 * @package uujia\framework\base\common\lib\Aop
 */
interface AopAdviceInterface {

	public function process(ProceedingJoinPoint $proceedingJoinPoint);
	
}