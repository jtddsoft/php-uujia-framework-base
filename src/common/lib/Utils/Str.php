<?php

namespace uujia\framework\base\common\lib\Utils;

/**
 * Class Str
 *
 * @package uujia\framework\base\common\lib\Utils
 */
class Str {
	
	/**
	 * 通配符判断是否匹配
	 *
	 * @param $pattern
	 * @param $value
	 *
	 * @return bool
	 */
	public static function is($pattern, $value) {
		if ($pattern == $value) return true;
		
		$pattern = preg_quote($pattern, '#');
		$pattern = str_replace('\*', '.*', $pattern) . '\z';
		return (bool) preg_match('#^' . $pattern . '#', $value);
	}
	
	/**
	 * 从右截取判断 是否为指定的结尾字符
	 *
	 * @param $str
	 * @param $end
	 *
	 * @return int|\lt
	 */
	public static function rightCompare($str, $end) {
		return substr_compare($str, $end, -strlen($end));
	}
	
}