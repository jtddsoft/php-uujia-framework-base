<?php

return [
	'MQTT' => [
		'enabled' => true,              // 启用
		
		'server'    => "59.110.217.60",     // change if necessary
		'port'      => 1883,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
		'client_id' => '',              // make sure this is unique for connecting to sever - you could use uniqid()
		'cafile'    => null,            // 证书
		'topics'    => '',              // 主题
		
		// connect_auto connect
		'clean' => true,
		'will' => null,
		
		// subscribe
		'topics_callback' => null,
		// 'topics_callback' => [
		// 	'[topic1]' => [
		// 		'function' => function ($topic, $msg) {},
		// 		'qos' => 0,
		// 	],
		// 	'[topic2]' => [
		// 		'function' => function ($topic, $msg) {},
		// 		'qos' => 0,
		// 	],
		// ],
		'qos'    => 0,
		// subscribe publish
		'retain' => 0,
	],
	
	'RabbitMQ' => [
		'enabled' => false,              // 启用
		
		'server'    => "localhost",     // change if necessary
		'port'      => 5672,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
		
		// connect
		'queue'       => 'hello',
		'passive'     => false,
		'durable'     => true,
		'exclusive'   => false,
		'auto_delete' => false,
		'nowait'      => false,
		'arguments'   => [],
		'ticket'      => null,
		
		// subscribe
		// 'queue'       => 'hello',
		'consumer_tag' => '',
		'no_local'     => false,
		'no_ack'       => false,
		
		// publish
		'exchange'    => '',
		'routing_key' => 'hello',
		'mandatory'   => true,
		'immediate'   => false,
	],
	
];
