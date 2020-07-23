<?php

namespace uujia\framework\base\test;


use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Event\EventHandleInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;

/**
 * Class EventTest1
 *
 * @package uujia\framework\base\test
 *
 * @EventListener(
 *     namespace = "app.test.eventTest",
 *     uuid = "*",
 *     evt = {
 *          "add.*"
 *     }
 * )
 */
class EventTest1 extends EventHandle {
	
	public function onAddBefore() {
		return $this->error('2222222');
	}
	
}