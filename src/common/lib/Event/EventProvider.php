<?php


namespace uujia\framework\base\common\lib\Event;


use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Tree\TreeFuncData;

/**
 * Class EventProvider
 * 事件监听者供应商
 *  用于将对应事件监听者提供给事件调度
 *
 * @package uujia\framework\base\common\lib\Event
 */
class EventProvider implements ListenerProviderInterface {
	
	
	/**
	 * 配置列表
	 *
	 * @var $_list TreeFunc
	 */
	protected $_list;
	
	
	/**
	 * @inheritDoc
	 */
	public function getListenersForEvent(object $event): iterable {
		// TODO: Implement getListenersForEvent() method.
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
	public function add($key, $listener, $serverName = ServerConst::SERVER_NAME_MAIN, $weight = TreeFunc::DEFAULT_WEIGHT) {
		// 构建触发方法
		$factoryItemFunc = $this->makeTriggerFunc();
		
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
			$subItemFunc = $this->makeListenerFunc($row, $serverName, $_serverConfig);
			
			$this
				// 获取总列表
				->getList()
				// 配置对应事件及添加监听项
				->addKeyNewItemData($key, $subItemFunc, $factoryItemFunc)
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
		}
		
		return $this;
	}
	
	/**
	 * 构建监听项方法
	 *
	 * @param array|\Closure $listener
	 * @param string         $serverName
	 * @param array          $serverConfig
	 * @return \Closure
	 */
	public function makeListenerFunc($listener, $serverName, $serverConfig) {
		$subItemFunc = function ($data, $it, $params) use ($listener, $serverName, $serverConfig) {
			/** @var TreeFuncData $data */
			/** @var TreeFunc $it */
			
			// $_param = $it->getParent()->getParam();
			// $_results = $_param['result'] ?? [];
			//
			// $_lastResult = Arr::from($_results)->last();
			
			/**
			 * $listener 可以是闭包或事件类 也可以是数组包含服务器信息等
			 */
			$_listener = $listener;
			$_serverName = $serverName;
			
			if (is_array($_listener)) {
				// 数组表示含有多个信息 期中listener中为闭包或事件类
				$_listener = $listener['listener'];
				// 服务器名称 通过名称可以查到配置中服务器的详细信息 从而知道监听者是来自本地还是远端
				$_serverName = $listener['serverName'] ?? $_serverName;
			}
			
			// 从服务器配置信息中查到服务器详细信息
			$_server = $serverConfig['server_event'][$_serverName];
			
			if ($_listener instanceof EventHandle) {
				// todo: 事件类来接管处理
				$_evtParams = [
					// 'data' => $data,
					// 'eventItem' => $it,
					'fParams' => $params,
					'name' => $listener,
					'serverName' => $serverName,
					'serverConfig' => $serverConfig,
					'server' => $_server,
				];
				
				/** @var EventHandle $_listener */
				$_listener->_event_listen($_evtParams);
				
				
			} else {
				// 根据类型 知道是本地还是远端
				switch ($_server['type']) {
					case ServerConst::TYPE_LOCAL_NORMAL:
						// 本地服务器
						$_local = $this->getLocalObj();
						
						// 触发事件时执行回调
						// $res = call_user_func_array($_listener, [$params, $_lastResult, $_results]);
						$res = $_local->trigger($_listener, $params);
						
						// // Local返回值复制
						// $this->setLastReturn($_local->getLastReturn());
						//
						// $it->getParent()->addKeyParam('result', $_local->getLastReturn());
						break;
					
					default:
						// 远程服务器
						// todo：MQ通信 POST请求之类
						break;
				}
			}
			
			return $res;
		};
		
		return $subItemFunc;
	}
	
	/**
	 * 构建触发方法
	 */
	public function makeTriggerFunc() {
		$factoryItemFunc = function ($data, $it, $params) {
			// 获取汇总列表中所有配置
			/** @var TreeFunc $it */
			// $it->_param['result'] = [];
			$it->cleanResults();
			
			/**
			 * params会给每个事件监听返回
			 *  results     同一事件所有监听返回值列表
			 *  lastResult  最后一个监听的返回值
			 */
			$it->wForEach(function ($_item, $index, $me, $params) {
				/** @var TreeFunc $_item */
				/** @var TreeFunc $me */
				
				// $_param = $me->getParam();
				// $_results = $_param['result'] ?? [];
				$_results = $me->getResults();
				
				// $_lastResult = Arr::from($_results)->last();
				$_lastResult = $me->getLastReturn();
				
				$params['results'] = $_results;
				$params['lastResult'] = $_lastResult;
				$re = $_item->getData()->get($params, false);
				
				// todo: 如果是事件类就缓存起来 后续触发时要检查是一般回调还是事件类 如果是事件类就执行特定方法
				
				// Local返回值复制
				$_item->getData()->setLastReturn($re);
				
				// 加入到返回值列表
				// $me->addKeyParam('result', $re);
				$me->setLastReturn($re);
				
				if ($_item->getData()->isErr()) {
					return false;
				}
				
				return true;
			}, $params);
			
			// return $this->ok();
			return $it->getLastReturn();
		};
		
		return $factoryItemFunc;
	}
	
	/**
	 * 获取列表
	 *
	 * @return TreeFunc
	 */
	public function getList(): TreeFunc {
		return $this->_list;
	}
	
	/**
	 * 获取列表项
	 *
	 * @param string $key
	 * @return TreeFuncData
	 */
	public function getListData(string $key): TreeFuncData {
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
	 * @return TreeFunc
	 */
	public function getListValue(string $key): TreeFunc {
		return $this->getList()->get($key);
	}
	
}