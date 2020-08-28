<?php

namespace uujia\framework\base\common\lib\Utils;

/**
 * Class File
 *
 * @package uujia\framework\base\common\lib\Utils
 */
class File {
	
	/**
	 * 文件是否存在
	 *
	 * Date: 2020/8/23
	 * Time: 2:55
	 *
	 * @param $file
	 * @return bool
	 */
	public static function isExists($file) {
		return file_exists($file);
	}
	
	/**
	 * 文件是否不存在
	 *
	 * Date: 2020/8/23
	 * Time: 2:55
	 *
	 * @param $file
	 * @return bool
	 */
	public static function isNotExists($file) {
		return !file_exists($file);
	}
	
	/**
	 * 创建多级目录
	 * @param string $dir
	 * @param int    $mode
	 * @return boolean
	 */
	public static function create_dir($dir, $mode = 0777) {
		return is_dir($dir) or (self::create_dir(dirname($dir)) and mkdir($dir, $mode));
	}
	
	/**
	 * 文件内容读取 读取为字符串
	 *
	 * Date: 2020/8/13
	 * Time: 17:26
	 *
	 * @param $file
	 *
	 * @return bool|false|string
	 */
	public static function readToText($file) {
		if (!file_exists($file)) {
			return '';
		}
		
		return file_get_contents($file);
	}
	
	/**
	 * 文件内容写入 将字符串写入文件
	 *
	 * Date: 2020/8/13
	 * Time: 17:26
	 *
	 * @param $file
	 * @param $text
	 *
	 * @return bool|false|string
	 */
	public static function writeFromText($file, $text) {
		if ($fp = @fopen($file, 'wb')) {
			if (PHP_VERSION >= '4.3.0' && function_exists('file_put_contents')) {
				return @file_put_contents($file, $text);
			} else {
				flock($fp, LOCK_EX);
				$bytes = fwrite($fp, $text);
				flock($fp, LOCK_UN);
				fclose($fp);
				return $bytes;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 获取文件修改时间
	 *
	 * Date: 2020/8/23
	 * Time: 0:20
	 *
	 * @param $file
	 * @return false|int
	 */
	public static function modifieTime($file) {
		return filemtime($file);
	}
	
	public static function getClassName($path_to_file) {
		//Grab the contents of the file
		$contents = file_get_contents($path_to_file);
		
		//Start with a blank namespace and class
		$namespace = $class = "";
		
		//Set helper values to know that we have found the namespace/class token and need to collect the string values after them
		$getting_namespace = $getting_class = false;
		
		//Go through each token and evaluate it as necessary
		foreach (token_get_all($contents) as $token) {
			
			//If this token is the namespace declaring, then flag that the next tokens will be the namespace name
			if (is_array($token) && $token[0] == T_NAMESPACE) {
				$getting_namespace = true;
			}
			
			//If this token is the class declaring, then flag that the next tokens will be the class name
			if (is_array($token) && $token[0] == T_CLASS) {
				$getting_class = true;
			}
			
			//While we're grabbing the namespace name...
			if ($getting_namespace === true) {
				
				//If the token is a string or the namespace separator...
				if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
					
					//Append the token's value to the name of the namespace
					$namespace .= $token[1];
					
				} else if ($token === ';') {
					
					//If the token is the semicolon, then we're done with the namespace declaration
					$getting_namespace = false;
					
				}
			}
			
			//While we're grabbing the class name...
			if ($getting_class === true) {
				
				//If the token is a string, it's the name of the class
				if (is_array($token) && $token[0] == T_STRING) {
					
					//Store the token's value as the class name
					$class = $token[1];
					
					//Got what we need, stope here
					break;
				}
			}
		}
		
		//Build the fully-qualified class name and return it
		return $namespace ? $namespace . '\\' . $class : $class;
		
	}
	
	
	
	
}