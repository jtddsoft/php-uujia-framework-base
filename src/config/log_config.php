<?php

return [
	'MQ' => [
		'enabled_response' => false,              // 启用
	],
	
	'MQTT' => [
		'enabled' => false,              // 启用
		
		'client_id' => 'Logger_2019',              // make sure this is unique for connecting to sever - you could use uniqid()
		'topics'    => 'Logger_2019',              // 主题
		
		'topics_list' => 'Logger_2019_List',
	],
	
	'RabbitMQ' => [
		'enabled'     => false,              // 启用
		
		// connect
		'queue'       => 'Logger_2019',
		
		// publish
		'exchange'    => 'Logger_2019',
		'routing_key' => 'Logger_2019',
		
		'queue_list'       => 'Logger_2019_List',
		'exchange_list'    => 'Logger_2019_List',
		'routing_key_list' => 'Logger_2019_List',
	],

];
