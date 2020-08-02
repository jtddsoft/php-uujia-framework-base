<?php

namespace uujia\framework\base\common\interfaces;


use uujia\framework\base\common\lib\Error\ErrorCodeConfig;

interface ResultInterface extends ResultBaseInterface {
	
	/**
	 * 返回类型
	 *  1 - 数组 内部使用
	 *  2 - json 直接返回ajax前端
	 *
	 * @param int $return_type
	 *
	 * @return $this
	 */
	public function returnType($return_type = 2);
	
	public function rt($return_type = 2);
	
	/**
	 * 是否在返回错误时直接exit
	 *
	 * @param bool $die
	 *
	 * @return $this
	 */
	public function die($die = true);
	
	/**
	 * 获取返回类型（1 - 内部使用数组 2 - 直接输出的json）
	 * @return int
	 */
	public function getReturnType(): int;
	
	/**
	 * 设置返回类型
	 * @param int $return_type
	 */
	public function setReturnType(int $return_type);
	
	/**
	 * 获取出错时是否终止运行
	 * @return bool
	 */
	public function isReturnDie(): bool;
	
	/**
	 * 设置出错时是否终止运行
	 * @param bool $return_die
	 */
	public function setReturnDie(bool $return_die);
	
	/**
	 * 获取配置对象
	 *  期中保存多组错误代码（不是多个，是多组。每组包含一个数组，里面是多个错误代码，可同时支持多个组件自身的错误组。）
	 *
	 * @return ErrorCodeConfig
	 */
	public function getErrObj();
	
	/**
	 * 设置配置对象（一般不要更改）*
	 *
	 * @param ErrorCodeConfig $errObj
	 */
	public function _setErrObj($errObj);
	
	
}