<?php


namespace uujia\framework\base\common\lib\Event;


interface EventHandleInterface {
	
	public function t($triggerName = '');
	public function handle($triggerName = '');
	
	public function on($params);
}