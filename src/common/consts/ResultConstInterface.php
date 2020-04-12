<?php

namespace uujia\framework\base\common\consts;

interface ResultConstInterface {
	
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
	
}