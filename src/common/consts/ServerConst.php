<?php

namespace uujia\framework\base\common\consts;

class ServerConst {
	
	/**
	 * host
	 */
	const SERVER_HOST_LOCALHOST = 'localhost';
	
	/**
	 * Type 事件类型
	 */
	const REQUEST_TYPE_LOCAL_NORMAL    = 'LOCAL'; // 本地通用事件
	const REQUEST_TYPE_REMOTE_RABBITMQ = 'RabbitMQ'; // 远程RabbitMq事件
	const REQUEST_TYPE_REMOTE_POST     = 'Post'; // 远程POST协议
	
	/**
	 * Server 服务器名称（多服务器分布 用于识别不同服务器的名称）
	 */
	const SERVER_NAME_MAIN = 'main';    // 主服务器
	
	/**
	 * Config中Server的Type
	 */
	const SERVER_CONFIG_KEY = 'server';
	
}