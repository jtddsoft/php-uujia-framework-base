<?php

use uujia\framework\base\common\consts\ServerConst;

return [
	'server' => [
		// 服务器名称name
		'main' => [
			// 域名地址
			'host'  => 'localhost',
			
			// 服务类型type
			'type' => [
				'event' => [
					'async' => false,                           // 是否异步
					'type'  => ServerConst::TYPE_LOCAL_NORMAL,  // 请求类型
					'url'   => '',                              // 接口地址（如果需要远程POST请求 对端接口地址）
				],
			],
			
		],
	],

];
