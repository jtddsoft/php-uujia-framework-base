<?php


namespace uujia\framework\base\common;


use phpDocumentor\Reflection\Types\Parent_;
use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Event\EventServer;
use uujia\framework\base\common\lib\FactoryCache\Data;
use uujia\framework\base\common\lib\FactoryCacheTree;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class Event extends Base {
	// use NameBase;
	// use ResultBase;
	
	/** @var Config $_configObj */
	protected $_configObj;
	
	/**
	 * 配置列表
	 *
	 * @var $_list FactoryCacheTree
	 */
	protected $_list;
	
	/**
	 * Event constructor.
	 *  依赖Result、Config
	 *
	 * @param Result $ret
	 * @param Config $configObj
	 */
	public function __construct(Result $ret, Config $configObj) {
		parent::__construct($ret);
		
		$this->_list = new FactoryCacheTree();
		
		$this->_configObj = $configObj;
		
		$this->init();
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
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = '事件管理';
	}
	
	public function trigger($event, $param = []) {
		// $this->getList()->get($event)->_param['result'] = [];
		$re = $this->getList()->getKeyDataValue($event, $param);
		
		return $re ?? $this->ret()->code(10101);
	}
	
	/**
	 * 事件监听
	 *
	 * @param string|array   $event
	 * @param array|\Closure $listener
	 * @param string         $serverName
	 * @param int            $weight
	 * @return $this
	 */
	public function listen($event, $listener, $serverName = ServerConst::SERVER_NAME_MAIN, $weight = FactoryCacheTree::DEFAULT_WEIGHT) {
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
	public function unshift($key, $listener, $serverName = ServerConst::SERVER_NAME_MAIN, $weight = FactoryCacheTree::DEFAULT_WEIGHT) {
		// $factoryItemFunc = function ($data, $it) {
		// 	$_config = [];
		//
		// 	// 获取汇总列表中所有配置
		// 	/** @var FactoryCacheTree $it */
		// 	$it->wForEach(function ($_item, $index, $me) use (&$_config) {
		// 		/** @var FactoryCacheTree $_item */
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
			$_listeners = [$listener];
		} elseif (is_array($listener)) {
			$_listeners = $listener;
		} else {
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
				// 获取最后一次配置的对应事件项
				->getLastSetItem()
				// 获取事件项数据
				->getData()
				// 配置禁用自动缓存（由于仅仅是执行一个闭包 执行后返回的不是具体值 下次还要再执行 因此不能缓存）
				->setIsAutoCache(false)
				// 获取数据Data的父级 就是FactoryCacheTree
				->getParent()
				// 获取事件项最后一次添加的监听项
				->getLastNewItem()
				// 设置权重
				->setWeight($weight)
				// 获取监听项数据
				->getData()
				// 配置监听项禁用缓存（由于仅仅是执行一个闭包 执行后返回的不是具体值 下次还要再执行 因此不能缓存）
				->setIsAutoCache(false);
		}
		
		return $this;
	}
	
	/**
	 * 尾部添加
	 *
	 * @param string|int     $key
	 * @param array|\Closure $listener
	 * @param string         $serverName
	 * @param int            $weight 权重
	 * @return $this
	 */
	public function add($key, $listener, $serverName = ServerConst::SERVER_NAME_MAIN, $weight = FactoryCacheTree::DEFAULT_WEIGHT) {
		$factoryItemFunc = function ($data, $it) {
			$_config = [];
			
			// 获取汇总列表中所有配置
			/** @var FactoryCacheTree $it */
			$it->wForEach(function ($_item, $index, $me) use (&$_config) {
				/** @var FactoryCacheTree $_item */
				$_it_config = $_item->getDataValue();
				$_config    = array_merge($_config, $_it_config);
			});
			
			return $_config;
		};
		
		if (is_string($listener)) {
			$this->getList()->addKeyNewItemData($key,
				function ($data, $it, $params) use ($listener, $type) {
					/** @var Data $data */
					
					// 接受返回值
					$_funcResult = function ($result) use ($data) {
						$data->setKeyOther(Data::OTHER_KEY_RESULT, $result);
					};
					
					// 触发事件时执行回调
					$res = call_user_func_array($listener, [$params, $_funcResult]);
					
					return $res;
				}, $factoryItemFunc
			)->getLastSetItem()->getLastNewItem()->setWeight($weight)->getData()->setIsAutoCache(false);
		} elseif (is_array($listener)) {
			foreach ($listener as $row) {
				$this->getList()->addKeyNewItemData($key,
					function ($data, $it, $params) use ($row, $type) {
						/** @var Data $data */
						
						// 接受返回值
						$_funcResult = function ($result) use ($data) {
							$data->setKeyOther(Data::OTHER_KEY_RESULT, $result);
						};
						
						// 触发事件时执行回调
						$res = call_user_func_array($row, [$params, $_funcResult]);
						
						return $res;
					}, $factoryItemFunc
				)->getLastSetItem()->getLastNewItem()->setWeight($weight)->getData()->setIsAutoCache(false);
			}
		}
		
		return $this;
	}
	
	/**
	 * 获取列表
	 *
	 * @return FactoryCacheTree
	 */
	public function getList(): FactoryCacheTree {
		return $this->_list;
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return Data
	 */
	public function getListData(string $key): Data {
		return $this->getList()->getData();
	}
	
	/**
	 * 获取列表项值
	 *
	 * @param string $key
	 * @return array|string|int|null
	 */
	public function getListDataValue(string $key) {
		return $this->getListValue($key)->getDataValue();
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return FactoryCacheTree
	 */
	public function getListValue(string $key): FactoryCacheTree {
		return $this->getList()->get($key);
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