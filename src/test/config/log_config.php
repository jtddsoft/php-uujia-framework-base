<?php

return [
	'MQ' => [
		'enabled_response' => true,              // 启用
	],
	
	'MQTT' => [
		'enabled' => false,              // 启用
		
		'client_id' => 'Logger2019',              // make sure this is unique for connecting to sever - you could use uniqid()
		'topics'    => 'Logger_2019',              // 主题
		
		'topics_list' => 'Logger_2019_List',
	],
	
	'RabbitMQ' => [
		'enabled'             => false,              // 启用
		
		// connect
		'queue'               => 'Logger_2019.one',
		
		// publish
		'exchange'            => 'amq.topic',
		'routing_key'         => 'Logger_2019.one',
		'routing_key_binding' => 'Logger_2019.one',
		
		'queue_list'               => 'Logger_2019.list',
		'exchange_list'            => 'amq.topic',
		'routing_key_list'         => 'Logger_2019.list',
		'routing_key_binding_list' => 'Logger_2019.list',
	],

];
