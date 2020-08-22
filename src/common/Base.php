<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\NameTrait;
use uujia\framework\base\common\Result;

/**
 * Class Base
 *
 * @package uujia\framework\base\common
 */
class Base extends BaseClass {
	
	/** @var $_ret Result */
	protected $_ret;
	
	/**
	 * Base constructor.
	 * 依赖Result
	 *
	 * @param Result $ret
	 */
	public function __construct(Result $ret) {
		$this->_ret = $ret;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '基础类';
	}
	
	public function __call($method, $args) {
		if ($this->ret()->isErr()) { return $this->ret()->return_error(); }
		
		// 从ret中查找方法
		if (is_callable([$this->ret(), $method])) {
			return call_user_func_array([$this->ret(), $method], $args);
		}
		
		// 方法不存在
		$this->ret()->error('方法不存在', 1000);
		return $this;
	}
	
	/**
	 * 获取返回值
	 * @return Result
	 */
	public function ret(): Result {
		return $this->_ret;
	}
	
	
	
}