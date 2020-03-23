<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\traits\NameBase;

class Redis {
	use NameBase;
	
	/** @var $_redisProviderObj RedisProviderInterface */
	protected $_redisProviderObj;
	
	/**
	 * Base constructor.
	 * 依赖Result
	 *
	 * @param Result $ret
	 */
	public function __construct(Result $ret) {
		$this->_ret = $ret;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = 'Redis服务';
	}
	
	
	
}