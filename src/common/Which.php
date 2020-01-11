<?php


namespace uujia\framework\base\common;


class Which {
	
	public static $_WORK_MODE = [
		'unknown' => 0,  // 未知
		'simple'  => 1,  // 单选
		'multi'   => 2,  // 多选
	];
	
	protected $_mode = 0;
	protected $_list = [];
	
	
	
	
	
	/**
	 * 工作模式
	 *  （0-单选 1-多选）
	 *  （单选：只要找到一个匹配值就直接返回，不会再向下找）
	 *  （多选：即使找到匹配值 也仍然有向下继续的可能 需要由每项自主判断是否匹配 如果匹配可调用next继续向下）
	 *
	 * @param null $name
	 *
	 * @return $this|string
	 */
	public function name($name = null) {
		if ($name === null) {
			return $this->_name;
		} else {
			$this->_name = $name;
		}
		
		return $this;
	}
	
}