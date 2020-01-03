<?php

namespace uujia\framework\base\common;


abstract class AbstractBase {
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