<?php

namespace uujia\framework\base;

use uujia\framework\base\common\Base;
use uujia\framework\base\common\SimpleLog;
use uujia\framework\base\common\SimpleMQTT;
use uujia\framework\base\common\ErrorCodeList;
use uujia\framework\base\common\Result;
use uujia\framework\base\common\SimpleContainer;

class BaseService {
	
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
		UU::C(SimpleMQTT::class, function (SimpleContainer $c) {
			$obj = new SimpleMQTT();
			$c->cache(SimpleMQTT::class, $obj);
			return $obj;
		});
		// 实例化Log
		UU::C(SimpleLog::class, function (SimpleContainer $c) {
			$obj = new SimpleLog($c->get(SimpleMQTT::class));
			$c->cache(SimpleLog::class, $obj);
			return $obj;
		});
		
		// 实例化Result
		UU::C(Result::class, function (SimpleContainer $c) {
			$obj = new Result($c->get(ErrorCodeList::class), $c->get(SimpleLog::class));
			$c->cache(Result::class, $obj);
			return $obj;
		});
		
		// 实例化Base
		UU::C(Base::class, function (SimpleContainer $c) {
			$obj = new Base($c->get(Result::class));
			$c->cache(Base::class, $obj);
			return $obj;
		});
		
	}
	
	/**
	 * @return ErrorCodeList
	 */
	public function getErrorCodeList(): ErrorCodeList {
		return UU::C(ErrorCodeList::class);
	}
	
	/**
	 * @return SimpleMQTT
	 */
	public function getSimpleMQTT(): SimpleMQTT {
		return UU::C(SimpleMQTT::class);
	}
	
	/**
	 * @return SimpleLog
	 */
	public function getSimpleLog(): SimpleLog {
		return UU::C(SimpleLog::class);
	}
	
	/**
	 * @return Result
	 */
	public function getResult(): Result {
		return UU::C(Result::class);
	}
	
	/**
	 * @return Base
	 */
	public function getBase(): Base {
		return UU::C(Base::class);
	}
	
	
}