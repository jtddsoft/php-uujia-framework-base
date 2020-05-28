<?php

namespace uujia\framework\base\test;


use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Event\EventHandleInterface;

/**
 * Class EventTest
 *
 * @package uujia\framework\base\test
 *
 * @EventTrigger
 * @EventListener(
 *     namespace = "app.test.eventTest",
 *     uuid = "*",
 *     evt = {
 *          "add.*"
 *     }
 * )
 */
class EventTest extends EventHandle {
	
	/**
	 * @EventName(evt = "app.test.eventTest.add.before")
	 */
	public function addBefore() {
	
	}
	
}