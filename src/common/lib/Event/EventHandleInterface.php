<?php


namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\lib\Event\Name\EventName;

interface EventHandleInterface {
	
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
	 *
	 * @param string $triggerName
	 * @param array  $param
	 *
	 * @return $this
	 */
	public function t($triggerName = '', $param = []);
	
	public function on($params);
	
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