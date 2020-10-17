<?php

namespace uujia\framework\base\common\event;


use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\EventTrigger;

/**
 * Class EventRunnerStatus
 * Date: 2020/10/17
 * Time: 13:43
 *
 * @package uujia\framework\base\common\event
 *
 * @EventTrigger(namespace = "sys.runner.status", uuid = "7a34d347-c3f2-4e27-b3bb-7ada64abac62")
 */
class EventRunnerStatus extends EventHandle {
	
	public function bootAfter() {
		return $this->tm(__FUNCTION__)->getLastReturn();
	}
	
}