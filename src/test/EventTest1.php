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
 *          "add.before"
 *     }
 * )
 */
class EventTest1 extends EventHandle {
	
	public function onAddBefore() {
		var_dump($this->error(__METHOD__));
		return $this->error(__METHOD__);
	}
	
	public function onAddAfter() {
		var_dump($this->error(__METHOD__));
		return $this->error(__METHOD__);
	}
	
}
const aa = 1;
const bb = 1;

function a() {

}

function b() {

}