<?php


namespace uujia\framework\base\common\lib\Event\Name;


/**
 * Interface EventNameInterface
 * 事件名称分离器
 *
 * 事件定义（首字母小写驼峰）：
 *  addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}.[{trigger_timing}]:{uuid}
 * 示例：
 *  app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * @package uujia\framework\base\common\lib\Event\Name
 */
interface EventNameInterface {
	
	// addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}.[{trigger_timing}]:{uuid}
	// const PCRE_NAME = '/^(\w+)\.(\w+)\.(\w+)\.(\w+):{0,1}([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12})?/';
	const PCRE_NAME = '/^(\w+)\.(\w+)\.(\w+)\.(\w+)\.{0,1}(before|after|x|event|success|error)?:{0,1}([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12})?/';
	
	const PCRE_NAME_FULL = '/^(\w+):(evtl|evtt):(\w+)\.(\w+)\.(\w+)\.(\w+)\.{0,1}(before|after|x|event|success|error)?:{0,1}([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12})?:{0,1}(tmp)?/';
	const PCRE_NAME_FULL_LIKE = '/^(\w+):(evtl|evtt):(\w+)\.(\w+)\.(\w+)\.(\w+)\.{0,1}(before|after|x|event|success|error|\*)?:{0,1}([0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}|\*)?:{0,1}(tmp)?/';
	
	/**
	 * 事件名称拆分后可能的数量 4、5或6
	 *  app.order.goods.addBefore                                       (4) 【暂不支持这样格式】
	 *  app.order.goods.addBefore:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca  (5) 【暂不支持这样格式】
	 *  app.order.goods.add.before                                      (5)
	 *  app.order.goods.add.before:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca (6)
	 */
	const PCRE_NAME_SPLIT_COUNT = [8, 9]; // [4, 5, 6];
	
	// 应用名称在事件名称拆分后的位置
	const PCRE_NAME_APPNAME_INDEX = 1;
	
	// 事件模式角色在事件名称拆分后的位置
	const PCRE_NAME_MODENAME_INDEX = 2;
	
	// 事件类型在事件名称拆分后的位置
	const PCRE_NAME_TYPE_INDEX = 3;
	
	// 组件名在事件名称拆分后的位置
	const PCRE_NAME_COM_INDEX = 4;
	
	// 事件名在事件名称拆分后的位置
	const PCRE_NAME_EVENT_INDEX = 5;
	
	// 事件行为在事件名称拆分后的位置
	const PCRE_NAME_BEHAVIOR_INDEX = 6;
	
	// 事件时机在事件名称拆分后的位置
	const PCRE_NAME_TIMING_INDEX = 7;
	
	// 事件UUID在事件名称拆分后的位置
	const PCRE_NAME_UUID_INDEX = 8;
	
	// 临时标识在事件名称拆分后的位置
	const PCRE_NAME_TMP_INDEX = 9;
	
	
	// 临时标识定义
	const EVENT_NAME_TMP_TEXT = 'tmp';
	
	
	/**************************************************************
	 * init
	 **************************************************************/
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []);
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 载入事件名称 拆分事件属性
	 *
	 * @param string $eventName
	 *
	 * @return $this
	 */
	public function parse($eventName = '');
	
	/**
	 * 重组事件名称
	 *
	 * @return $this
	 */
	public function makeEventName();
	
	/**************************************************************
	 * validate
	 **************************************************************/
	
	/**
	 * 校验事件属性
	 *
	 * @return array|\think\response\Json
	 */
	public function validateProperty();
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getEventName(): string;
	
	/**
	 * @param string $eventName
	 *
	 * @return $this
	 */
	public function setEventName(string $eventName);
	
	/**
	 * @return string
	 */
	public function getType(): string;
	
	/**
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType(string $type);
	
	/**
	 * @return string
	 */
	public function getCom(): string;
	
	/**
	 * @param string $com
	 *
	 * @return $this
	 */
	public function setCom(string $com);
	
	/**
	 * @return string
	 */
	public function getEvent(): string;
	
	/**
	 * @param string $event
	 *
	 * @return $this
	 */
	public function setEvent(string $event);
	
	/**
	 * @return string
	 */
	public function getBehavior(): string;
	
	/**
	 * @param string $behavior
	 *
	 * @return $this
	 */
	public function setBehavior(string $behavior);
	
	/**
	 * @return string
	 */
	public function getTiming(): string;
	
	/**
	 * @param string $timing
	 *
	 * @return $this
	 */
	public function setTiming(string $timing);
	
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
	
	
}