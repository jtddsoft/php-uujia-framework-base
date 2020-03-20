<?php


namespace uujia\framework\base\common\traits;


trait NameBase {
	protected $name_info = [
		'name' => '',
		'intro' => '',
		
	];
	
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
	}
	
	public function getNameInfo() {
		return $this->name_info;
	}
}