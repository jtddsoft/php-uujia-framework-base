<?php

namespace uujia\framework\base\common\lib\Utils;


class Str {
	
	public static function is($pattern, $value) {
		if ($pattern == $value) return true;
		
		$pattern = preg_quote($pattern, '#');
		$pattern = str_replace('\*', '.*', $pattern) . '\z';
		return (bool) preg_match('#^' . $pattern . '#', $value);
	}
	
}