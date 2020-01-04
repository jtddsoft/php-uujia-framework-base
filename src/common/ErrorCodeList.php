<?php


namespace uujia\framework\base\common;


class ErrorCodeList {
	public static $_ERROR_CODE_NAME = 'error_code';
	
	/**
	 * 加入多组error_code
	 *  每组格式：[
	 *      10000 => '用户名不能为空',
	 *      10001 => '用户名密码错误',
	 *  ]
	 * @var array
	 */
	public static $errCodeList = [];
	
	public function __construct($err = []) {
		// 自身code
		self::$errCodeList[] = include __DIR__ . "./config/error_code.php";
		
		if (!empty($err)) {
			self::unshift($err);
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
			$_err = $errItem[self::$_ERROR_CODE_NAME];
			if (in_array($code, $_err)) {
				$msg = $_err[$code];
				break;
			}
		}
		
		return $msg;
	}
}