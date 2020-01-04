<?php

namespace uujia\framework\base;

use uujia\framework\base\common\AbstractBase;
use uujia\framework\base\common\AbstractLog;
use uujia\framework\base\common\AbstractMQTT;
use uujia\framework\base\common\ErrorCodeList;
use uujia\framework\base\common\Result;
use uujia\framework\base\common\SimpleContainer;

class Base {
	
	public function __construct() {
		$this->init();
	}
	
	public function init() {
		// 设置对象准实例化 实例化只能调用一次 之后使用直接UU::C(ErrorCodeList::class)->dosomething()
		UU::C(ErrorCodeList::class, function (SimpleContainer $c) {
			$obj = new ErrorCodeList();
			$c->cache(ErrorCodeList::class, $obj);
			return $obj;
		});
		
		// 实例化MQTT
		UU::C(AbstractMQTT::class, function (SimpleContainer $c) {
			$obj = new class() extends AbstractMQTT {
			
			};
			$c->cache(AbstractMQTT::class, $obj);
			return $obj;
		});
		// 实例化Log
		UU::C(AbstractLog::class, function (SimpleContainer $c) {
			$obj = new class($c->get(AbstractMQTT::class)) extends AbstractLog {
			
			};
			$c->cache(AbstractLog::class, $obj);
			return $obj;
		});
		
		// 实例化Result
		UU::C(Result::class, function (SimpleContainer $c) {
			$obj = new Result($c->get(ErrorCodeList::class), $c->get(AbstractLog::class));
			$c->cache(Result::class, $obj);
			return $obj;
		});
		
		// 实例化Base
		UU::C(AbstractBase::class, function (SimpleContainer $c) {
			$obj = new class($c->get(Result::class)) extends AbstractBase {
			
			};
			$c->cache(AbstractBase::class, $obj);
			return $obj;
		});
		
	}
	
}