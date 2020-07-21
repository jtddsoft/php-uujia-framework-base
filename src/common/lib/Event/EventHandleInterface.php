<?php


namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\lib\Event\Name\EventName;
use uujia\framework\base\common\lib\Exception\ExceptionEvent;

interface EventHandleInterface {
	
	// const PCRE_FUNC_NAME = '/^([a-z0-9]+)([A-Z][a-z0-9]*)+?(Before|After|X)?/';
	const PCRE_FUNC_TRIGGER_NAME = '/^([a-zA-Z0-9]+)(Before|After|X|Event|Success|Error)$/';
	
	const PCRE_FUNC_LISTENER_NAME = '/^on([a-zA-Z0-9]+)(Before|After|X|Event|Success|Error)$/';
	
	/**
	 * 事件名称解析
	 *
	 * @param string $triggerName
	 *
	 * @return $this
	 */
	public function parse($triggerName = '');
	
	/**
	 * 事件触发执行
	 *  【内部调用】
	 *
	 * @return $this
	 */
	public function handle();
	
	/**
	 * 事件触发
	 *  在需要触发的地方 启动一个事件
	 *
	 * @param string $triggerName 触发名称 app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
	 * @param array  $param       触发参数 ['data' => [1, 2, ...]]
	 *
	 * @return $this
	 * @throws ExceptionEvent
	 */
	public function triggerEventName($triggerName = '', $param = []);
	
	/**
	 * triggerEventName的简写
	 * date: 2020/7/21 15:10
	 *
	 * @param string $triggerName
	 * @param array  $param
	 *
	 * @return $this
	 * @throws ExceptionEvent
	 */
	public function ten($triggerName = '', $param = []);
	
	/**
	 * 事件触发
	 *  在需要触发的地方 启动一个事件
	 *
	 * @param string $method 触发的方法名
	 * @param array  $param 触发参数 ['data' => [1, 2, ...]]
	 *
	 * @return $this
	 * @throws ExceptionEvent
	 */
	public function triggerMethod($method = '', $param = []);
	
	/**
	 * triggerMethod的简写
	 * date: 2020/7/21 15:10
	 *
	 * @param string $method
	 * @param array  $param
	 *
	 * @return $this
	 * @throws ExceptionEvent
	 */
	public function tm($method = '', $param = []);
	
	/**
	 * @return string
	 */
	public function getUuid(): string;
	
	/**
	 * @param string $uuid
	 *
	 * @return $this
	 */
	public function setUuid(string $uuid);
	
	/**
	 * @return string
	 */
	public function getTriggerName(): string;
	
	/**
	 * @param string $triggerName
	 *
	 * @return $this
	 */
	public function setTriggerName(string $triggerName);
	
	/**
	 * 获取事件名称对象实例
	 *
	 * @return EventName
	 */
	public function getEventNameObj();
	
	/**
	 * @param EventName $eventNameObj
	 *
	 * @return $this
	 */
	public function setEventNameObj(EventName $eventNameObj);
	
	/**
	 * @return array
	 */
	public function getParam();
	
	/**
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setParam(array $param);
	
	
}