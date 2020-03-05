<?php

namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\FactoryCache\Data;
use uujia\framework\base\common\lib\FactoryCacheTree;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\traits\InstanceBase;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class EventServer {
	use NameBase;
	use ResultBase;
	use InstanceBase;
	
	/** @var Local $_local */
	protected $_localObj = null;
	
	// todo: POST
	protected $_postObj = null;
	
	/**
	 * EventServer constructor.
	 *
	 */
	public function __construct() {
		
		
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
		$this->name_info['intro'] = '事件处理服务';
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
			/** @var Data $data */
			/** @var FactoryCacheTree $it */
			
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
			
			
			
			// // 根据类型 知道是本地还是远端
			// switch ($_server['type']) {
			// 	case ServerConst::TYPE_LOCAL_NORMAL:
			// 		// 本地服务器
			// 		$_local = $this->getLocalObj();
			//
			// 		// 触发事件时执行回调
			// 		// $res = call_user_func_array($_listener, [$params, $_lastResult, $_results]);
			// 		$res = $_local->trigger($_listener, $params);
			//
			// 		// // Local返回值复制
			// 		// $this->setLastReturn($_local->getLastReturn());
			// 		//
			// 		// $it->getParent()->addKeyParam('result', $_local->getLastReturn());
			// 		break;
			//
			// 	default:
			// 		// 远程服务器
			// 		// todo：MQ通信 POST请求之类
			// 		break;
			// }
			
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
			/** @var FactoryCacheTree $it */
			// $it->_param['result'] = [];
			$it->cleanResults();
			
			/**
			 * params会给每个事件监听返回
			 *  results     同一事件所有监听返回值列表
			 *  lastResult  最后一个监听的返回值
			 */
			$it->wForEach(function ($_item, $index, $me, $params) {
				/** @var FactoryCacheTree $_item */
				/** @var FactoryCacheTree $me */
				
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
	
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	
}