<?php

namespace uujia\framework\base\test;


use Generator;
use uujia\framework\base\common\lib\Event\Cache\EventCacheDataProvider;
use uujia\framework\base\test\event\EventRunnerStatusListener;

/**
 * Class EventCacheDataProviderTest
 *
 * @package uujia\framework\base\test
 */
class EventCacheDataProviderTest extends EventCacheDataProvider {
	
	public function getEventClassNames(): Generator {
		foreach ([EventTest::class, EventTest1::class, EventRunnerStatusListener::class] as $item) {
			yield $item;
		}
		
	}
	
}