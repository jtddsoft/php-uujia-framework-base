<?php

namespace uujia\framework\base\common\lib\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class EventTrigger
 *
 * @package uujia\framework\base\common\lib\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
class EventTrigger extends Annotation {
	
	/**
	 * 命名空间
	 * @Required()
	 * @var string
	 */
	public $nameSpace = '';
	
	/**
	 * UUID
	 * @var string
	 */
	public $uuid = '';
	
	/**
	 * 事件名
	 * @var array
	 */
	public $evt = [];
	
}