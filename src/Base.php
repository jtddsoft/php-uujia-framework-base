<?php

namespace uujia\framework\base;

use uujia\framework\base\common\Result;

class Base {
	/** @var $ret Result */
	protected $ret;
	
	/**
	 * Base constructor.
	 * 依赖Result
	 *
	 * @param Result $ret
	 */
	public function __construct(Result $ret) {
		$this->ret = $ret;
	}
	
	public function __call($method, $args) {
		if ($this->getRet()->isErr()) { return $this->getRet()->return_error(); }
		
		// 从ret中查找方法
		if (is_callable([$this->getRet(), $method])) {
			return call_user_func_array([$this->getRet(), $method], $args);
		}
		
		// 方法不存在
		$this->getRet()->error('方法不存在', 1000);
		return $this;
	}
	
	/**
	 * 获取返回值
	 * @return Result
	 */
	public function getRet(): Result {
		return $this->ret;
	}
	
	
	
}