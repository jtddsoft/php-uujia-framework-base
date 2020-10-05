<?php


namespace uujia\framework\base\common\lib\Decrypt;

/**
 * Class Aes
 * Date: 2020/10/6
 * Time: 1:18
 *
 * @package uujia\framework\base\common\lib\Decrypt
 */
class Aes {
	/**
	 * @var string 加解密方法，可通过openssl_get_cipher_methods()获得
	 */
	protected $_method;
	
	/**
	 * @var string 加解密的密钥
	 */
	protected $_secret_key;
	
	/**
	 * @var string 加解密的向量，有些方法需要设置比如CBC
	 */
	protected $_iv;
	
	/**
	 * @var int
	 */
	protected $_options;
	
	/**
	 * 构造函数
	 *
	 * @param string $key     密钥
	 * @param string $method  加密方式
	 * @param string $iv      iv向量
	 * @param mixed  $options 还不是很清楚
	 */
	public function __construct($key, $method = 'AES-128-ECB', $iv = '', $options = 0) {
		// key是必须要设置的
		$this->_secret_key = isset($key) ? $key : 'uujia.net';
		
		$this->_method = $method;
		
		$this->_iv = $iv;
		
		$this->_options = $options;
	}
	
	/**
	 * 加密方法，对数据进行加密，返回加密后的数据
	 *
	 * @param string $data 要加密的数据
	 *
	 * @return string
	 */
	public function encrypt($data) {
		return openssl_encrypt($data, $this->_method, $this->_secret_key, $this->_options, $this->_iv);
	}
	
	/**
	 * 解密方法，对数据进行解密，返回解密后的数据
	 *
	 * @param string $data 要解密的数据
	 *
	 * @return string
	 */
	public function decrypt($data) {
		return openssl_decrypt($data, $this->_method, $this->_secret_key, $this->_options, $this->_iv);
	}
	
	/**
	 * @return string
	 */
	public function getMethod(): string {
		return $this->_method;
	}
	
	/**
	 * @param string $method
	 * @return Aes
	 */
	public function setMethod(string $method) {
		$this->_method = $method;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getSecretKey(): string {
		return $this->_secret_key;
	}
	
	/**
	 * @param string $secret_key
	 * @return Aes
	 */
	public function setSecretKey(string $secret_key) {
		$this->_secret_key = $secret_key;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getIv(): string {
		return $this->_iv;
	}
	
	/**
	 * @param string $iv
	 * @return Aes
	 */
	public function setIv(string $iv) {
		$this->_iv = $iv;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getOptions(): int {
		return $this->_options;
	}
	
	/**
	 * @param int $options
	 * @return Aes
	 */
	public function setOptions(int $options) {
		$this->_options = $options;
		
		return $this;
	}
	
}