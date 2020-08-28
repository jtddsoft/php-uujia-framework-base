<?php

namespace uujia\framework\base\test\aop;


use Generator;
use uujia\framework\base\common\lib\Aop\Cache\AopCacheDataProvider;

/**
 * Class AopCacheDataProviderTest
 *
 * @package uujia\framework\base\test
 */
class AopCacheDataProviderTest extends AopCacheDataProvider {
	
	public function getAops(): Generator {
		// TODO: Implement getAops() method.
		foreach ([AopEventTest::class, AopEventTest1::class] as $item) {
			yield $item;
		}
	}
}