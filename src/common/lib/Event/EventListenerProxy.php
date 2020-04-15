<?php


namespace uujia\framework\base\common\lib\Event;

use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Cache\CacheClassInterface;
use uujia\framework\base\common\lib\Cache\CacheClassTrait;
use uujia\framework\base\common\lib\Server\ServerParameter;
use uujia\framework\base\common\lib\Server\ServerParameterInterface;

/**
 * Class EventListenerProxy
 * 事件监听代理（接口实现）
 *  负责为不同类型监听者提供与事件供应商的通讯
 *  1、标准事件类 代理保存事件类的类名（包括完整命名空间）。需要触发时会访问容器自动为其实例化，由代理调用预定方法。
 *  2、闭包 代理
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventListenerProxy extends BaseClass implements EventListenerProxyInterface {
	
	/**
	 * 服务器参数
	 * @var ServerParameterInterface
	 */
	protected $_serverParameter;
	
	
	/**
	 * EventListenerProxy constructor.
	 *
	 * @param ServerParameterInterface $serverParameterObj
	 */
	public function __construct($serverParameterObj = null) {
		$this->_serverParameter = $serverParameterObj;
		
		parent::__construct();
	}
	
	/**
	 * 执行触发
	 */
	public function handle() {
	
	}
	
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return ServerParameterInterface
	 */
	public function getServerParameter() {
		return $this->_serverParameter;
	}
	
	/**
	 * @param ServerParameterInterface $serverParameter
	 *
	 * @return EventListenerProxy
	 */
	public function setServerParameter($serverParameter) {
		$this->_serverParameter = $serverParameter;
		
		return $this;
	}
	
	
	
}