<?php

namespace uujia\framework\base\common\lib\Utils;


class Json {
	
	/**
	 * json_encode
	 *
	 * @param $value
	 *
	 * @return false|string
	 */
	public static function je($value) {
		return json_encode($value, JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * json_encode
	 *
	 * @param $value
	 *
	 * @return false|string
	 */
	public static function encode($value) {
		return json_encode($value, JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * json_decode
	 *
	 * @param $json
	 *
	 * @return mixed
	 */
	public static function jd($json) {
		return json_decode($json, true);
	}
	
	/**
	 * json_decode
	 *
	 * @param $json
	 *
	 * @return mixed
	 */
	public static function decode($json) {
		return json_decode($json, true);
	}
	
	
	
}