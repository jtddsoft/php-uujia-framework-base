<?php
/**
 *
 * author: lz
 * Date: 2020/8/12
 * Time: 9:24
 */

namespace uujia\framework\base\common\lib\Aop\JointPoint;


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
	public $arguments;
	
	/**
	 * @var mixed
	 */
	public $result;
	
	public function process() {
	
	}
	
}