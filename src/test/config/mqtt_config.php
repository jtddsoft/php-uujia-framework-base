<?php

return [
	'mqtt' => [
		'enabled' => true,              // 启用
		
		'server'    => "localhost",     // change if necessary
		'port'      => 1883,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
		'client_id' => '',              // make sure this is unique for connecting to sever - you could use uniqid()
		'cafile'    => null,            // 证书
		'topics'    => '',              // 主题
	],
	
];
