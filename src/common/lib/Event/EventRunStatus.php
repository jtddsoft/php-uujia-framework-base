<?php

namespace uujia\framework\base\common\lib\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use uujia\framework\base\common\lib\Base\BaseClass;

/**
 * Class EventRunStatus
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventRunStatus extends BaseClass implements StoppableEventInterface {
	
	/**
	 * 是否终止事件队列
	 *  不再触发之后的事件
	 *
	 * @var bool
	 */
	protected $_stopped = false;
	
	/**
	 * EventRunStatus constructor.
	 */
	public function __construct() {
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '服务事件运行状态类';
	}
	
	/**************************************************
	 * interface func
	 **************************************************/
	
	public function isPropagationStopped(): bool {
		return $this->_stopped;
	}
	
	/**************************************************
	 * local func
	 **************************************************/
	
	/**
	 * 从数组分配（导入）
	 * date: 2020/7/21 16:45
	 *
	 * @param $arr
	 *
	 * @return $this
	 */
	public function assignFromArray($arr) {
		$this->setStopped($arr['stopped'] ?? false);
		
		return $this;
	}
	
	/**
	 * 分配到数组（导出）
	 * date: 2020/7/21 16:45
	 *
	 * @return array
	 */
	public function assignToArray() {
		$arr = [];
		$arr['stopped'] = $this->isPropagationStopped();
		
		return $arr;
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return bool
	 */
	public function isStopped(): bool {
		return $this->_stopped;
	}
	
	/**
	 * @param bool $stopped
	 *
	 * @return EventRunStatus
	 */
	public function setStopped(bool $stopped) {
		$this->_stopped = $stopped;
		
		return $this;
	}
	
	
}