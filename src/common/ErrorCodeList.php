<?php


namespace uujia\framework\base\common;


class ErrorCodeList {
	/**
	 * 加入多组error_code
	 *  每组格式：[
	 *      10000 => '用户名不能为空',
	 *      10001 => '用户名密码错误',
	 *  ]
	 * @var array
	 */
	public static $errCodeList = [];
	
	public function __construct($err) {
		if (!empty($err)) {
			self::$errCodeList[] = $err;
		}
	}
	
	/**
	 * 开头插入
	 */
	public static function unshift($data) {
		array_unshift(self::$errCodeList, $data);
	}
	
	/**
	 * 查找时从第一组开始 只要找到直接返回
	 *
	 * @param $code
	 *
	 * @return mixed|string
	 */
	public static function find($code) {
		$msg = '未知异常';
		
		foreach (self::$errCodeList as $errItem) {
			if (in_array($code, $errItem)) {
				$msg = $errItem[$code];
				break;
			}
		}
		
		return $msg;
	}
}