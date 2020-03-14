<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Class EventProvider
 * 事件监听者供应商
 *  用于将对应事件监听者提供给事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventProvider implements ListenerProviderInterface {
	
	
	
	/**
	 * @inheritDoc
	 */
	public function getListenersForEvent(object $event): iterable {
		// TODO: Implement getListenersForEvent() method.
	}
}