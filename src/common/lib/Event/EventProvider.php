<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventProvider implements ListenerProviderInterface {
	
	
	
	/**
	 * @inheritDoc
	 */
	public function getListenersForEvent(object $event): iterable {
		// TODO: Implement getListenersForEvent() method.
	}
}