<?php

namespace uujia\framework\base\common\lib\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class AopTarget
 *
 * @package uujia\framework\base\common\lib\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
class AopTarget extends Annotation {
	
	/**
	 * 权重
	 * @var int
	 */
	public $weight = 100;
	
}