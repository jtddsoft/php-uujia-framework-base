<?php

namespace uujia\framework\base\common\lib\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class EventListener
 *
 * @package uujia\framework\base\common\lib\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
class EventListener extends Annotation {
	
	/**
	 * 命名空间
	 * @Required()
	 * @var string
	 */
	public $namespace = '';
	
	/**
	 * UUID
	 * @var string
	 */
	public $uuid = '';
	
	/**
	 * 事件名
	 * @var string[]
	 */
	public $evt = [];
	
	/**
	 * 权重
	 * @var int
	 */
	public $weight = 100;
	
}