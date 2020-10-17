<?php

namespace uujia\framework\base;


use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\traits\NameTrait;

class BaseService {
	use NameTrait;
	
	public function __construct() {
		$this->init();
	}
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
		
		// // 实例化Config
		// UU::C(Config::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Config();
		// 	// $c->cache(ErrorCodeList::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 设置对象准实例化 实例化只能调用一次 之后使用直接UU::C(ErrorCodeList::class)->dosomething()
		// UU::C(ErrorCodeList::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new ErrorCodeList($c->get(Config::class));
		// 	// $c->cache(ErrorCodeList::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 实例化MQTT
		// UU::C(MQCollection::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new MQCollection($c->get(Config::class));
		// 	// $c->cache(MQTT::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		// // 实例化Log
		// UU::C(Log::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Log($c->get(Config::class), $c->get(MQCollection::class));
		// 	// $c->cache(Log::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 实例化Result
		// UU::C(Result::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Result($c->get(ErrorCodeList::class), $c->get(Log::class));
		// 	// $c->cache(Result::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		//
		// // 实例化Base
		// UU::C(Base::class, function (Data $data, TreeFunc $it, Container $c) {
		// 	$obj = new Base($c->get(Result::class));
		// 	// $c->cache(Base::class, $obj);
		// 	// $data->cache($obj);
		// 	return $obj;
		// });
		
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = static::class;
		$this->name_info['intro'] = '基础服务类';
	}
	
	/**
	 * @return Container
	 */
	public function getContainer(): Container {
		return UU::getInstance()->getContainer();
	}
	
	public function t() {
		return 112233;
	}
}