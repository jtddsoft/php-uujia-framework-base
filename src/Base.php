<?php

namespace uujia\framework\base;



class Base {
	public static $_RETURN_TYPE = [
		'arr'  => 1, // 返回数组
		'json' => 2, // 返回json
	];
	
	private $return_type = 1;
	
	public static function call($name) {
		return app('painter_logic_' . $name);
	}
	
	public function __get($name) {
		return $this->$name;
	}
	
	public function __set($name, $value) {
		$this->$name = $value;
	}
	
	public function returnType($return_type = 2) {
		$this->return_type = $return_type;
		return $this;
	}
	
	public function rt($return_type = 2) {
		$this->return_type = $return_type;
		return $this;
	}
	
	public function error($msg = 'error', $code = 1000) {
		switch ($this->return_type) {
			case self::$_RETURN_TYPE['json']:
				return rjErr($msg, $code);
				break;
		}
		
		return rsErr($msg, $code);
	}
	
	public function code($code = 1000) {
		switch ($this->return_type) {
			case self::$_RETURN_TYPE['json']:
				return rjErrCode($code);
				break;
		}
		
		return rsErrCode($code);
	}
	
	public function ok() {
		switch ($this->return_type) {
			case self::$_RETURN_TYPE['json']:
				return rjOk();
				break;
		}
		
		return rsOk();
	}
	
	public function data($data = []) {
		switch ($this->return_type) {
			case self::$_RETURN_TYPE['json']:
				return rjData($data);
				break;
		}
		
		return rsData($data);
	}
}