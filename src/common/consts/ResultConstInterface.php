<?php

namespace uujia\framework\base\common\consts;

/**
 * Interface ResultConstInterface
 *
 * @package uujia\framework\base\common\consts
 */
interface ResultConstInterface {
	
	const RESULT_CODE = 'code';
	const RESULT_STATUS = 'status';
	const RESULT_MSG = 'msg';
	const RESULT_DATA = 'data';
	
	// 返回ok模板
	const RESULT_OK
		= [
			'code'   => 200,
			'status' => 'success',
			'msg'    => '操作完成',
			'data'   => [],
		];
	
	// 返回error模板
	const RESULT_ERROR
		= [
			'code'   => 1000,
			'status' => 'error',
			'msg'    => '操作失败',
			'data'   => [],
		];
	
	// 验证正确的依据code = 200
	const OK_CODE = 200;
	
	// 默认权重（返回值队列取优先级）
	const RESULT_WEIGHT_DEFAULT = 100;
	
}