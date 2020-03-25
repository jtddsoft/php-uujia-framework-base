<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use uujia\framework\base\common\lib\Base\BaseClass;

/**
 * Class EventDispatcher
 * 事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventDispatcher extends BaseClass implements EventDispatcherInterface {
	
	/**
	 * @inheritDoc
	 */
	public function dispatch(object $event) {
		// TODO: Implement dispatch() method.
	}
}