<?php

// define('EXT_AMQP_ENABLED', 1);

return [
	'MQTT' => [
		'enabled' => true,              // 启用
		
		'server'          => "59.110.217.60",     // change if necessary
		'port'            => 1883,            // change if necessary
		'username'        => "hello",         // set your username
		'password'        => "123456",        // set your password
		'client_id'       => '',              // make sure this is unique for connecting to sever - you could use uniqid()
		'cafile'          => null,            // 证书
		'topics'          => '',              // 主题
		
		// connect_auto connect
		'clean'           => true,
		'will'            => null,
		
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
		'qos'             => 0,
		// subscribe publish
		'retain'          => 0,
	],
	
	'RabbitMQ' => [
		'enabled' => true,              // 启用
		
		'server'           => 'localhost',//"59.110.217.60",     // change if necessary
		'port'             => 5672,            // change if necessary
		'username'         => "hello",         // set your username
		'password'         => "123456",        // set your password
		
		'vhost'            => '/',
		
		// connect
		'queue'            => 'hello',
		'passive'          => false,
		'durable_exchange' => defined('EXT_AMQP_ENABLED') ? AMQP_DURABLE : true,
		'durable_queue'    => defined('EXT_AMQP_ENABLED') ? AMQP_DURABLE : true,
		'exclusive'        => false,
		'auto_delete'      => false,
		'nowait'           => false,
		'arguments'        => [],
		'ticket'           => null,
		
		// subscribe
		// 'queue'       => 'hello',
		'consumer_tag'     => '',
		'no_local'         => false,
		'no_ack'           => false,
		'ack_flags'        => defined('EXT_AMQP_ENABLED') ? AMQP_AUTOACK : true, // ext flags
		
		// publish
		'internal'         => false,
		'exchange'         => 'amq.topic',
		'exchange_type'    => defined('EXT_AMQP_ENABLED') ? AMQP_EX_TYPE_TOPIC : 'topic',
		'routing_key'      => 'hello.hello',
		'routing_key_bind' => 'hello.*',
		'mandatory'        => true,
		'immediate'        => false,
	],

];
