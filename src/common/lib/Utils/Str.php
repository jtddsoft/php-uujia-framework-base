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
	
	/**
	 * 驼峰转下划线
	 *
	 * @param  string $value
	 * @param  string $delimiter
	 * @return string
	 */
	public static function snake(string $value, string $delimiter = '_'): string
	{
		if (!ctype_lower($value)) {
			$value = preg_replace('/\s+/u', '', $value);
			
			$value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value), 'UTF-8');
		}
		
		return $value;
	}
	
	/**
	 * 下划线转驼峰(首字母小写)
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function camel(string $value): string {
		return lcfirst(static::studly($value));
	}
	
	/**
	 * 下划线转驼峰(首字母大写)
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function studly(string $value): string {
		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		
		return str_replace(' ', '', $value);
	}
	
	/**
	 * 左上斜杠转右上斜杠
	 * \转/
	 *
	 * Date: 2020/8/23
	 * Time: 1:08
	 *
	 * @param string $value
	 * @return string
	 */
	public static function slashLToR(string $value): string {
		return str_replace('\\', '/', $value);
	}
	
	/**
	 * 右上斜杠转左上斜杠
	 * /转\
	 *
	 * Date: 2020/8/23
	 * Time: 1:08
	 *
	 * @param string $value
	 * @return string
	 */
	public static function slashRToL(string $value): string {
		return str_replace('/', '\\', $value);
	}
	
	/**
	 * 获取类名短名（去除命名空间 只剩最后一级类名）
	 *
	 * Date: 2020/8/23
	 * Time: 22:07
	 *
	 * @param string $className
	 * @return string
	 */
	public static function classBasename(string $className): string {
		return basename(self::slashLToR($className));
	}
	
	/**
	 * 获取类名的命名空间部分（去除最后一级类名 只留下前面的命名空间）
	 *
	 * Date: 2020/8/23
	 * Time: 22:18
	 *
	 * @param string $className
	 * @return string
	 */
	public static function classNamespace(string $className): string {
		return self::slashRToL(dirname(self::slashLToR($className)));
	}
	
}