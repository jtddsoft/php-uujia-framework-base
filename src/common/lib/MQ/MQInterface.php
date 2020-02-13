<?php


namespace uujia\framework\base\common\lib\MQ;

/**
 * Interface MQInterface
 * 定义通用MQ标准（由于MQ各有各的实现形式 故只规范最基本方法）
 *
 * @package uujia\framework\base\common\lib\MQ
 */
interface MQInterface {
	
	/**
	 * 初始化
	 */
	public function initMQ();
	
	/**
	 * 自动连接服务器（连接失败自动重连）
	 */
	public function connect_auto();
	
	/**
	 * 连接服务器
	 */
	public function connect();
	
	/**
	 * 订阅
	 */
	public function subscribe();
	
	/**
	 * 发布
	 *
	 * @param $content
	 */
	public function publish($content);
}