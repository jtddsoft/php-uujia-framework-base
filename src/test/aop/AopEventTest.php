<?php

namespace uujia\framework\base\test\aop;


use uujia\framework\base\common\lib\Annotation\AopTarget;
use uujia\framework\base\common\lib\Aop\AopAdviceInterface;
use uujia\framework\base\common\lib\Aop\JointPoint\ProceedingJoinPoint;
use uujia\framework\base\common\lib\Base\BaseClass;

/**
 * Class AopEventTest
 *
 * @package uujia\framework\base\test\aop
 *
 * @AopTarget("uujia\framework\base\test\EventTest")
 */
class AopEventTest extends BaseClass implements AopAdviceInterface {
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = 'EventTest拦截类';
	}
	
	public function process(ProceedingJoinPoint $proceedingJoinPoint) {
		// if ($proceedingJoinPoint->methodName == 'test') {
		// 	return 53214;
		// }
		
		return $proceedingJoinPoint->process();
	}
	
}