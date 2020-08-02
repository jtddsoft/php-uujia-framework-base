<?php

namespace uujia\framework\base\common\interfaces;


interface ResultBaseInterface {
	
	/**************************************************************
	 * 返回输出
	 **************************************************************/
	
	/**
	 * 返回错误
	 *
	 * @param string $msg
	 * @param int    $code
	 *
	 * @return array|\think\response\Json
	 */
	public function error($msg = 'error', $code = 1000);
	
	/**
	 * 返回错误码 自动解析错误msg
	 *
	 * @param int $code
	 *
	 * @return array|mixed|string
	 */
	public function code($code = 1000);
	
	public function ok();
	
	public function data($data = []);
	
	public function return_error();
	
	
}