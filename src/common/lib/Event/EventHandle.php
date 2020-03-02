<?php

namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

abstract class EventHandle {
	use NameBase;
	use ResultBase;
	
	/**
	 * 唯一标识
	 *  此处的值是Demo 继承类需要重新生成
	 */
	protected $_uuid = 'cdd64cb6-29b8-4663-b1b5-f4f515ed28ca';
	
	
	/**
	 * EventHandle constructor.
	 *
	 * @param $uuid
	 */
	public function __construct($uuid = 'cdd64cb6-29b8-4663-b1b5-f4f515ed28ca') {
		$this->_uuid = $uuid;
		
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
		$this->name_info['intro'] = '事件处理本地模板类';
	}
	
	
	
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return string
	 */
	public function getUuid(): string {
		return $this->_uuid;
	}
	
	/**
	 * @param string $uuid
	 *
	 * @return $this
	 */
	public function setUuid(string $uuid) {
		$this->_uuid = $uuid;
		
		return $this;
	}
	
	
}