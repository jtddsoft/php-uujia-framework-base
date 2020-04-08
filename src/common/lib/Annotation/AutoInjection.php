<?php

namespace uujia\framework\base\common\lib\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class AutoInjection
 *
 * @package uujia\framework\base\common\lib\Annotation
 * @Annotation
 * @Target({"METHOD"})
 */
class AutoInjection extends Annotation {
	
	/**
	 * 参数名
	 * @var string
	 */
	public $arg = '';
	
	/**
	 * 要注入的容器名称类名（完整类名 包含命名空间）
	 * @var string
	 */
	public $name = '';
	
	/**
	 * 类型（c-容器名称 v-赋值）
	 * @var string
	 */
	public $type = 'c';
	
	/**
	 * 赋值
	 * @var string|int|float|bool|array|null
	 */
	public $value = '';
	
}