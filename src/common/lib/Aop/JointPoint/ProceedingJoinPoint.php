<?php
/**
 *
 * author: lz
 * Date: 2020/8/12
 * Time: 9:24
 */

namespace uujia\framework\base\common\lib\Aop\JointPoint;


use uujia\framework\base\common\lib\Aop\AopAdviceInterface;
use uujia\framework\base\common\lib\Container\Container;

class ProceedingJoinPoint {
	
	/**
	 * @var string
	 */
	public $className;
	
	/**
	 * @var string
	 */
	public $methodName;
	
	/**
	 * @var mixed[]
	 */
	public $args;
	
	/**
	 * @var mixed
	 */
	public $result;
	
	/**
	 * @var \Generator
	 */
	public $generator;
	
	/**
	 * @var \Closure
	 */
	public $methodClosure;
	
	public function process() {
		if ($this->generator->valid()) {
			$aopAdviceClass = $this->generator->current();
			$this->generator->next();
			
			/**
			 * 实例化aop
			 *
			 * @var AopAdviceInterface $aopAdviceObj
			 */
			$aopAdviceObj = Container::getInstance()->get($aopAdviceClass);
			// todo: 验证是否有process
			return $aopAdviceObj->process($this);
		} else {
			return $this->methodClosure(...$this->args);
		}
	}
	
}