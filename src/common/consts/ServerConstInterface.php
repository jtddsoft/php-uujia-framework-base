<?php

namespace uujia\framework\base\common\consts;

interface ServerConstInterface {
	
	/**
	 * host
	 */
	const SERVER_HOST_LOCALHOST = 'localhost';
	
	/**
	 * Type 事件类型
	 */
	const REQUEST_TYPE_LOCAL_NORMAL = 'LOCAL';       // 本地通用事件
	const REQUEST_TYPE_REMOTE_RABBITMQ = 'RabbitMQ'; // 远程RabbitMq事件
	const REQUEST_TYPE_REMOTE_POST = 'Post';         // 远程POST协议
	
	/**
	 * Server 服务器名称（多服务器分布 用于识别不同服务器的名称）
	 */
	const SERVER_NAME_MAIN = 'main';    // 主服务器
	
	/**
	 * Config中Server的Type
	 */
	const SERVER_CONFIG_KEY = 'server';
	
	// key
	const KEY_SERVER_NAME = 'name';
	const KEY_SERVER_DATA = 'server';
	
	const KEY_HOST = 'host';
	const KEY_TYPE = 'type';
	const KEY_REQUEST_TYPE = 'requestType';
	const KEY_ASYNC = 'async';
	const KEY_URL = 'url';
	
	// name
	const NAME_MAIN = 'main';
	
	// type
	const TYPE_EVENT = 'event';
	
	// local
	const HOST_LOCAL = 'localhost';
	
	/**
	 * 路由服务集合名称Key
	 *  本地路由服务
	 *  POST
	 *  MQ
	 */
	const SERVER_ROUTE_NAME_LOCAL = 'server_route_local';
	const SERVER_ROUTE_NAME_POST = 'server_route_post';
	const SERVER_ROUTE_NAME_MQ = 'server_route_mq';
	
}