<?php
/**
 *
 * author: lz
 * Date: 2020/8/13
 * Time: 12:10
 */

namespace uujia\framework\base\test\cache\proxy;


class uujia_framework_base_test_cache_proxy_EventTest extends \uujia\framework\base\test\EventTest {
	use \uujia\framework\base\common\lib\Aop\AopProxy;

	public function addBefore($a = Array) {
		return call_user_func_array([$this, '_aopCall'], ['addBefore', func_get_args()]);
	}

	public function addAfter() {
		return call_user_func_array([$this, '_aopCall'], ['addAfter', func_get_args()]);
	}

	public function onAddBefore() {
		return call_user_func_array([$this, '_aopCall'], ['onAddBefore', func_get_args()]);
	}

	public function init() {
		return call_user_func_array([$this, '_aopCall'], ['init', func_get_args()]);
	}

	public function initNameInfo() {
		return call_user_func_array([$this, '_aopCall'], ['initNameInfo', func_get_args()]);
	}

	public function parse($triggerName = '') {
		return call_user_func_array([$this, '_aopCall'], ['parse', func_get_args()]);
	}

	public function _trigger() {
		return call_user_func_array([$this, '_aopCall'], ['_trigger', func_get_args()]);
	}

	public function handle() {
		return call_user_func_array([$this, '_aopCall'], ['handle', func_get_args()]);
	}

	public function triggerEventName($triggerName = '', $param = Array) {
		return call_user_func_array([$this, '_aopCall'], ['triggerEventName', func_get_args()]);
	}

	public function ten($triggerName = '', $param = Array) {
		return call_user_func_array([$this, '_aopCall'], ['ten', func_get_args()]);
	}

	public function triggerMethod($method = '', $param = Array) {
		return call_user_func_array([$this, '_aopCall'], ['triggerMethod', func_get_args()]);
	}

	public function tm($method = '', $param = Array) {
		return call_user_func_array([$this, '_aopCall'], ['tm', func_get_args()]);
	}

	public function getUuid() {
		return call_user_func_array([$this, '_aopCall'], ['getUuid', func_get_args()]);
	}

	public function setUuid($uuid) {
		return call_user_func_array([$this, '_aopCall'], ['setUuid', func_get_args()]);
	}

	public function getTriggerName() {
		return call_user_func_array([$this, '_aopCall'], ['getTriggerName', func_get_args()]);
	}

	public function setTriggerName($triggerName) {
		return call_user_func_array([$this, '_aopCall'], ['setTriggerName', func_get_args()]);
	}

	public function getRunnerManagerObj() {
		return call_user_func_array([$this, '_aopCall'], ['getRunnerManagerObj', func_get_args()]);
	}

	public function setRunnerManagerObj(\uujia\framework\base\common\lib\Runner\RunnerManagerInterface $runnerManagerObj) {
		return call_user_func_array([$this, '_aopCall'], ['setRunnerManagerObj', func_get_args()]);
	}

	public function getEventNameObj() {
		return call_user_func_array([$this, '_aopCall'], ['getEventNameObj', func_get_args()]);
	}

	public function setEventNameObj(\uujia\framework\base\common\lib\Event\Name\EventName $eventNameObj) {
		return call_user_func_array([$this, '_aopCall'], ['setEventNameObj', func_get_args()]);
	}

	public function getParam() {
		return call_user_func_array([$this, '_aopCall'], ['getParam', func_get_args()]);
	}

	public function setParam($param) {
		return call_user_func_array([$this, '_aopCall'], ['setParam', func_get_args()]);
	}

	public function isPropagationStopped() {
		return call_user_func_array([$this, '_aopCall'], ['isPropagationStopped', func_get_args()]);
	}

	public function assignFromArray($arr) {
		return call_user_func_array([$this, '_aopCall'], ['assignFromArray', func_get_args()]);
	}

	public function assignToArray() {
		return call_user_func_array([$this, '_aopCall'], ['assignToArray', func_get_args()]);
	}

	public function isStopped() {
		return call_user_func_array([$this, '_aopCall'], ['isStopped', func_get_args()]);
	}

	public function setStopped($stopped) {
		return call_user_func_array([$this, '_aopCall'], ['setStopped', func_get_args()]);
	}

	public function assign($obj) {
		return call_user_func_array([$this, '_aopCall'], ['assign', func_get_args()]);
	}

	public function reset($exclude = Array) {
		return call_user_func_array([$this, '_aopCall'], ['reset', func_get_args()]);
	}

	public function di() {
		return call_user_func_array([$this, '_aopCall'], ['di', func_get_args()]);
	}

	public function getDI() {
		return call_user_func_array([$this, '_aopCall'], ['getDI', func_get_args()]);
	}

	public function getContainer() {
		return call_user_func_array([$this, '_aopCall'], ['getContainer', func_get_args()]);
	}

	public function _setContainer(\Psr\Container\ContainerInterface $container) {
		return call_user_func_array([$this, '_aopCall'], ['_setContainer', func_get_args()]);
	}

	public function getReflection() {
		return call_user_func_array([$this, '_aopCall'], ['getReflection', func_get_args()]);
	}

	public function _setReflection(\uujia\framework\base\common\lib\Reflection\Reflection $reflection) {
		return call_user_func_array([$this, '_aopCall'], ['_setReflection', func_get_args()]);
	}

	public function getNameInfo() {
		return call_user_func_array([$this, '_aopCall'], ['getNameInfo', func_get_args()]);
	}

	public function resetResult() {
		return call_user_func_array([$this, '_aopCall'], ['resetResult', func_get_args()]);
	}

	public function assignLastReturn($lastReturn = Array, $isCleanResults = 1) {
		return call_user_func_array([$this, '_aopCall'], ['assignLastReturn', func_get_args()]);
	}

	public function code($code = 1000, $data = Array) {
		return call_user_func_array([$this, '_aopCall'], ['code', func_get_args()]);
	}

	public function error($msg = 'error', $code = 1000, $data = Array) {
		return call_user_func_array([$this, '_aopCall'], ['error', func_get_args()]);
	}

	public function ok() {
		return call_user_func_array([$this, '_aopCall'], ['ok', func_get_args()]);
	}

	public function data($data = Array) {
		return call_user_func_array([$this, '_aopCall'], ['data', func_get_args()]);
	}

	public function return_error() {
		return call_user_func_array([$this, '_aopCall'], ['return_error', func_get_args()]);
	}

	public function isOk($ret = Array) {
		return call_user_func_array([$this, '_aopCall'], ['isOk', func_get_args()]);
	}

	public function isErr($ret = Array) {
		return call_user_func_array([$this, '_aopCall'], ['isErr', func_get_args()]);
	}

	public function getCode() {
		return call_user_func_array([$this, '_aopCall'], ['getCode', func_get_args()]);
	}

	public function setCode($code) {
		return call_user_func_array([$this, '_aopCall'], ['setCode', func_get_args()]);
	}

	public function getStatus() {
		return call_user_func_array([$this, '_aopCall'], ['getStatus', func_get_args()]);
	}

	public function setStatus($status) {
		return call_user_func_array([$this, '_aopCall'], ['setStatus', func_get_args()]);
	}

	public function getMsg() {
		return call_user_func_array([$this, '_aopCall'], ['getMsg', func_get_args()]);
	}

	public function setMsg($msg) {
		return call_user_func_array([$this, '_aopCall'], ['setMsg', func_get_args()]);
	}

	public function getData() {
		return call_user_func_array([$this, '_aopCall'], ['getData', func_get_args()]);
	}

	public function setData($data) {
		return call_user_func_array([$this, '_aopCall'], ['setData', func_get_args()]);
	}

	public function getLastReturn() {
		return call_user_func_array([$this, '_aopCall'], ['getLastReturn', func_get_args()]);
	}

	public function setLastReturn($last_return) {
		return call_user_func_array([$this, '_aopCall'], ['setLastReturn', func_get_args()]);
	}

	public function getResults() {
		return call_user_func_array([$this, '_aopCall'], ['getResults', func_get_args()]);
	}

	public function _setResults($results) {
		return call_user_func_array([$this, '_aopCall'], ['_setResults', func_get_args()]);
	}

	public function cleanResults() {
		return call_user_func_array([$this, '_aopCall'], ['cleanResults', func_get_args()]);
	}

	public function addResults($result) {
		return call_user_func_array([$this, '_aopCall'], ['addResults', func_get_args()]);
	}

	public function getInstance() {
		return call_user_func_array([$this, '_aopCall'], ['getInstance', func_get_args()]);
	}

	public function factory() {
		return call_user_func_array([$this, '_aopCall'], ['factory', func_get_args()]);
	}

	public function me() {
		return call_user_func_array([$this, '_aopCall'], ['me', func_get_args()]);
	}


}