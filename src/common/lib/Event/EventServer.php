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
	 * @param array          $_serverConfig
	 * @return \Closure
	 */
	public function makeListenerFunc($listener, $serverName, $_serverConfig) {
		$subItemFunc = function ($data, $it, $params) use ($listener, $serverName, $_serverConfig) {
			/** @var Data $data */
			/** @var FactoryCacheTree $it */
			
			// $_param = $it->getParent()->getParam();
			// $_results = $_param['result'] ?? [];
			//
			// $_lastResult = Arr::from($_results)->last();
			
			$_listener = $listener;
			$_serverName = $serverName;
			
			if (is_array($_listener)) {
				$_listener = $listener['listener'];
				$_serverName = $listener['serverName'] ?? $_serverName;
			}
			
			$_server = $_serverConfig['server_event'][$_serverName];
			
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
	
	/**
	 * @return Local
	 */
	public function getLocalObj(): Local {
		$this->_localObj === null && $this->_localObj = new Local($this);
		
		return $this->_localObj;
	}
	
	/**
	 * @param Local $localObj
	 * @return $this
	 */
	public function _setLocal(Local $localObj) {
		$this->_localObj = $localObj;
		
		return $this;
	}
	
	
}