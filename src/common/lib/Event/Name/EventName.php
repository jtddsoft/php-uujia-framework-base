<?php


namespace uujia\framework\base\common\lib\Event\Name;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\InstanceBase;

/**
 * Class EventName
 * 事件名称分离器
 *
 * 事件定义（首字母小写驼峰）：
 *  addon|plugin|app|sys.{component_name|addon_name|plugin_name}.{event_name}.{behavior_name}:{uuid}
 * 示例：
 *  app.order.goods.addBefore:cdd64cb6-29b8-4663-b1b5-f4f515ed28ca
 *
 * @package uujia\framework\base\common\lib\Event\Name
 */
class EventName extends BaseClass {
	use InstanceBase;
	
	
	
	
	
	
	
}