<?php

// define('EXT_AMQP_ENABLED', 1);

use oliverlorenz\reactphpmqtt\packet\QoS\Levels;

return [
	'MQTT' => [
		'enabled' => true,              // 启用
		
		'server'          => "59.110.217.60",     // change if necessary
		// 'server'          => "mq.tongxinmao.com",     // change if necessary
		'port'            => 1883,            // change if necessary
		'username'        => "hello",         // set your username hello
		'password'        => "123456",        // set your password 123456
		'client_id'       => '1',              // make sure this is unique for connecting to sever - you could use uniqid()
		'cafile'          => null,            // 证书
		'topics'          => 'test',              // 主题
		
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
		
		'broker' => '59.110.217.60:1883',
		// 'broker' => '127.0.0.1:1883',
		'options' => new \oliverlorenz\reactphpmqtt\packet\ConnectionOptions([
			                                                                     'username' => 'hello', // hello
			                                                                     'password' => '123456', // 123456
			                                                                     // 'clientId' => '',
			                                                                     // 'cleanSession' => '',
			                                                                     // 'willTopic' => '',
			                                                                     // 'willMessage' => '',
			                                                                     // 'willQos' => Levels::AT_MOST_ONCE_DELIVERY,
			                                                                     // 'willRetain' => true,
			                                                                     // 'keepAlive' => 0,
		                                                                     ]),
	],
	
	'RabbitMQ' => [
		'enabled' => true,              // 启用
		
		'server'           => '59.110.217.60',//localhost"59.110.217.60",     // change if necessary
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
