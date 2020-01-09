<?php


namespace uujia\framework\base\traits;


trait NameBase {
	protected $name_info = [
		'name' => '',
		'intro' => '',
		
	];
	
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
	}
}