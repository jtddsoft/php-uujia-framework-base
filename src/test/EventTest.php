<?php

namespace uujia\framework\base\test;


use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Annotation\EventListener;

/**
 * Class EventTest
 *
 * @package uujia\framework\base\test
 *
 * @EventTrigger
 * @EventListener(evt = {
 *     "app.test.eventTest.*.*:*"
 * })
 */
class EventTest extends EventHandle {
	
	/**
	 * @EventName(evt = "app.test.eventTest.add.before")
	 */
	public function addBefore() {
	
	}
	
}