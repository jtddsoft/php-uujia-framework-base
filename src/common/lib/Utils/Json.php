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
	
	/**
	 * 是否json
	 *
	 * @param $string
	 * @return bool
	 */
	public static function isJson($string) {
		json_decode($string);
		return(json_last_error() == JSON_ERROR_NONE);
	}
	
	/**
	 * 解析json串
	 * @param string $json_str
	 * @return bool|array
	 */
	public static function analyJson($json_str) {
		$json_str = str_replace('＼＼', '', $json_str);
		$out_arr = array();
		preg_match('/{.*}/', $json_str, $out_arr);
		if (!empty($out_arr)) {
			$result = json_decode($out_arr[0], TRUE);
		} else {
			return FALSE;
		}
		return $result;
	}
	
	
	
	
}