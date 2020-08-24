<?php


namespace uujia\framework\base\common;


use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\Str as UUStr;

/**
 * Class Event
 *
 * @package uujia\framework\base\common
 */
class Event extends Base {
	// use NameTrait;
	// use ResultTrait;
	
	/** @var Config $_configObj */
	protected $_configObj;
	
	/**
	 * Event constructor.
	 *  依赖Result、Config
	 *
	 * @param Result $ret
	 * @param Config $configObj
	 */
	public function __construct(Result $ret, Config $configObj) {
		$this->_configObj = $configObj;
		
		parent::__construct($ret);
	}
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = static::class;
		$this->name_info['intro'] = '事件管理';
	}
	
	/**
	 * 触发事件
	 *
	 * @param string|array $event
	 * @param array        $param
	 *
	 * @return array|mixed|string
	 */
	public function trigger($event, $param = []) {
		// $this->getList()->get($event)->_param['result'] = [];
		// $re = $this->getList()->getKeyDataValue($event, $param);
		
		$_keys = $this->getList()->wkeys();
		if (empty($_keys)) {
			return $this->ret()->code(10101); // 未找到事件监听者
		}
		
		$_ks = array_filter($_keys, function ($k) use ($event) {
			if ($event == $k || UUStr::is($k, $event . '#')) {
				return true;
			}
			
			return false;
		});
		
		if (empty($_ks)) {
			return $this->ret()->code(10101); // 未找到事件监听者
		}
		
		$_re = null;
		foreach($_ks as $item) {
			$re = $this->getList()->getKeyDataValue($item, $param);
			$re && $_re = $re;
		}
		
		return $_re ?? $this->ret()->code(10101); // 未找到事件监听者
	}
	
	/**
	 * 事件监听
	 *  event
	 *      1、具体事件名 例如：UserLoginAfter
	 *      2、事件类中事件名和uuid精确匹配 例如：UserLoginAfter#184d8c21-9b45-4159-9b88-5cc56abbef0f
	 *      3、匹配所有事件名（包括带有uuid） 例如：UserLoginAfter#*
	 *
	 * @param string|array   $event
	 * @param array|\Closure $listener
	 * @param string         $serverName
	 * @param int            $weight
	 *
	 * @return $this
	 */
	public function listen($event, $listener, $weight = TreeFunc::DEFAULT_WEIGHT, $serverName = ServerConst::SERVER_NAME_MAIN) {
		$_events = [];
		if (is_string($event)) {
			$_events[] = $event;
		} elseif (is_array($event)) {
			$_events = $event;
		}
		
		if (empty($_events)) {
			return $this;
		}
		
		foreach ($_events as $_event) {
			$this->unshift($_event, $listener, $serverName, $weight);
		}
		
		return $this;
	}
	
	/**
	 * 开头插入
	 *
	 * @param string|int     $key
	 * @param array|\Closure $listener
	 * @param string         $serverName
	 * @param int            $weight 权重
	 * @return $this
	 */
	public function unshift($key, $listener, $serverName = ServerConst::SERVER_NAME_MAIN, $weight = TreeFunc::DEFAULT_WEIGHT) {
		// $factoryItemFunc = function ($data, $it) {
		// 	$_config = [];
		//
		// 	// 获取汇总列表中所有配置
		// 	/** @var TreeFunc $it */
		// 	$it->wForEach(function ($_item, $index, $me) use (&$_config) {
		// 		/** @var TreeFunc $_item */
		// 		$_it_config = $_item->getDataValue();
		// 		$_config    = array_merge($_it_config, $_config);
		// 	});
		//
		// 	return $_config;
		// };
		
		// 实例化EventServer
		/** @var EventServer $_eventServerObj */
		$_eventServerObj = EventServer::getInstance();
		
		$factoryItemFunc = $_eventServerObj->init()->makeTriggerFunc();
		
		$_listeners = [];
		
		if (is_callable($listener)) {
			// 单监听者
			$_listeners = [$listener];
		} elseif (is_array($listener)) {
			// 批量监听者
			$_listeners = $listener;
		} else {
			// todo: 监听者格式错误
			return $this;
		}
		
		// 获取Server配置
		$_serverConfig = $this->getConfigObj()->loadValue(ServerConst::SERVER_CONFIG_KEY);
		
		foreach ($_listeners as $row) {
			/** @var \Closure $subItemFunc */
			$subItemFunc = $_eventServerObj->init()->makeListenerFunc($row, $serverName, $_serverConfig);
			
			$this
				// 获取总列表
				->getList()
				// 配置对应事件及添加监听项
				->unshiftKeyNewItemData($key, $subItemFunc, $factoryItemFunc)
				// 获取最后一次配置的对应事件项 获取事件项数据
				->getLastSetItemData()
				// 配置禁用自动缓存（由于仅仅是执行一个闭包 执行后返回的不是具体值 下次还要再执行 因此不能缓存）
				->setIsAutoCache(false)
				// 获取数据Data的父级 就是TreeFunc
				->getParent()
				// 获取事件项最后一次添加的监听项
				->getLastNewItem()
				// 设置权重
				->setWeight($weight);
				// // 获取监听项数据
				// ->getData()
				// // 配置监听项禁用缓存（由于仅仅是执行一个闭包 执行后返回的不是具体值 下次还要再执行 因此不能缓存）
				// ->setIsAutoCache(false);
		}
		
		return $this;
	}
	
	/**
	 * @return Config
	 */
	public function getConfigObj(): Config {
		return $this->_configObj;
	}
	
	/**
	 * @param Config $configObj
	 * @return $this
	 */
	public function _setConfigObj(Config $configObj) {
		$this->_configObj = $configObj;
		
		return $this;
	}
	
}