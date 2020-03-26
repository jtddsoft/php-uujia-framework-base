<?php

namespace uujia\framework\base\common\lib\Annotation;

use Doctrine\Common\Annotations\Annotation;

class AutoInjection extends Annotation {
	
	/**
	 * 参数名
	 * @var string
	 */
	public $arg;
	
	/**
	 * 要注入的容器名称类名（完整类名 包含命名空间）
	 * @var string
	 */
	public $name;
	
}