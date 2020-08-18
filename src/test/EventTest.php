<?php

namespace uujia\framework\base\test;


use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\{EventTrigger, EventListener};
// use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Event\EventHandleInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
use function uujia\framework\base\test\{a, b};
use const uujia\framework\base\test\{aa, bb};

/**
 * Class EventTest
 *
 * @package uujia\framework\base\test
 *
 * @EventTrigger(namespace = "app.test.eventTest", uuid = "ea6f7e28-1fe4-df41-5535-5be1be9080cc")
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
	public function addBefore($a = [1, 2], $b = 's', $c = 1, $d = true): EventHandle {
		return parent::addBefore();
		// return $this->tm(__FUNCTION__)->getLastReturn();
	}
	
	public function addAfter() {
		return $this->tm(__FUNCTION__)->getLastReturn();
	}
	
	public function onAddBefore() {
		var_dump($this->error(__METHOD__));
		return $this->error(__METHOD__);
	}
	
}