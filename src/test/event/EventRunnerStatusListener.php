<?php

namespace uujia\framework\base\test\event;


use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\EventListener;

/**
 * Class EventRunnerStatusListener
 * Date: 2020/10/17
 * Time: 13:43
 *
 * @package uujia\framework\base\common\event
 *
 * @EventListener(
 *     namespace = "sys.runner.status",
 *     uuid = "7a34d347-c3f2-4e27-b3bb-7ada64abac62",
 *     evt = {
 *          "boot.*"
 *     }
 * )
 */
class EventRunnerStatusListener extends EventHandle {
	
	public function onBootAfter() {
		echo __METHOD__ . "\n";
		return $this->ok();
	}
	
}