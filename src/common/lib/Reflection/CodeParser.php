<?php
/**
 *
 * author: lz
 * Date: 2020/8/14
 * Time: 11:30
 */

namespace uujia\framework\base\common\lib\Reflection;


use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Utils\File;
use uujia\framework\base\common\traits\InstanceTrait;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class CodeParser
 * Date: 2020/8/14
 * Time: 11:30
 *
 * @package uujia\framework\base\common\lib\Reflection
 */
class CodeParser extends BaseClass {
	use InstanceTrait;
	use ResultTrait;
	
	/**
	 * 代码解析
	 * @var Parser
	 */
	protected $_parserObj;
	
	/**
	 * 分类的文件名（带路径）
	 * @var string
	 */
	protected $_classFileName;
	
	/**
	 * 读取的类文件内容代码
	 * @var string
	 */
	protected $_classCodeText;
	
	/**
	 * 代码解析后的数据数组
	 * @var array
	 */
	protected $_codeDataSource = [];
	
	/**
	 * 经过整理后的代码内容成树状
	 * @var array
	 */
	protected $_codeDataTree = [];
	
	/**
	 * 经过整理后的代码内容
	 * @var array
	 */
	protected $_codeData = [];
	
	/**
	 * CodeParser constructor.
	 */
	public function __construct() {
		parent::__construct();
		
		
	}
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		(!in_array('parserFactoryObj', $exclude)) && $this->_parserObj = null;
		(!in_array('classFileName', $exclude)) && $this->_classFileName = '';
		(!in_array('classCodeText', $exclude)) && $this->_classCodeText = '';
		(!in_array('codeDataSource', $exclude)) && $this->_codeDataSource = [];
		(!in_array('codeData', $exclude)) && $this->_codeData = [];
		
		return parent::reset($exclude);
	}
	
	/**
	 * 读取文件
	 * Date: 2020/8/14
	 * Time: 11:50
	 *
	 * @return $this
	 */
	public function loadFile() {
		$this->setClassCodeText(File::readToText($this->getClassFileName()));
		
		return $this;
	}
	
	/**
	 * 解析代码
	 * Date: 2020/8/14
	 * Time: 13:51
	 *
	 * @return $this
	 */
	public function parse() {
		if (empty($this->getClassCodeText())) {
			$this->error('代码不能为空');
			return $this;
		}
		
		$data = $this->getParserObj()->parse($this->getClassCodeText());
		$this->setCodeDataSource($data);
		
		// 解析树状
		$this->parseDataTree($data, $this->getCodeDataTree());
		
		return $this;
	}
	
	public function parseDataTree($data, &$tree) {
		if (empty($data)) {
			return $this;
		}
		
		foreach ($data as $item) {
			switch ($item->getType()) {
				case 'Stmt_Namespace':
					if (!isset($tree['namespace'])) {
						$tree['namespace'] = [];
					}
					
					$namespace = [
						'data' => [],
						'children' => [],
					];
					
					$namespace['data']['arr'] = $item->name->parts ?? [];
					$namespace['data']['text'] = implode('\\', $item->name->parts ?? []);
					
					if (empty($item->stmts)) {
						continue 2;
					}
					
					$tree['namespace'][] = $namespace;
					
					$this->parseDataTree($item->stmts, $tree['namespace'][count($tree['namespace']) - 1]['children']);
					break;
				
				case 'Stmt_Use':
					if (empty($item->uses)) {
						continue 2;
					}
					
					if (!isset($tree['use'])) {
						$tree['use'] = [];
					}
					
					//$use = [];
					
					//$tree['use'][] = $use;
					
					$this->parseDataTree($item->uses, $tree['use']);
					break;
					
				case 'Stmt_UseUse':
					$use = [];
					
					$use['arr'] = $item->name->parts ?? [];
					$use['text'] = implode('\\', $item->name->parts ?? []);
					
					$tree[] = $use;
					break;
				
				case 'Stmt_Class':
					if (!isset($tree['class'])) {
						$tree['class'] = [];
					}
					
					$class = [
						'data' => [],
						'children' => [],
					];
					
					$class['data']['extands_arr'] = $item->extends->parts ?? [];
					$class['data']['extands_text'] = implode('\\', $item->extends->parts ?? []);
					$class['data']['name'] = $item->name->name;
					
					if (empty($item->stmts)) {
						continue 2;
					}
					
					$tree['class'] = $class;
					
					$this->parseDataTree($item->stmts, $tree['class']['children']);
					break;
				
				case 'Stmt_ClassMethod':
					$method = [
						'name' => '',
						'params' => [],
						'returnType' => null,
					];
					
					$method['name'] = $item->name->__toString();
					$method['returnType'] = $item->returnType->__toString();
					
					$tree[] = $method;
					
					$this->parseDataTree($item->params, $tree[count($tree) - 1]['params']);
					break;
					
				case 'Param':
					$param = [
						'name' => '',
						'byRef' => false,
						'default' => [
							'type' => '',
							'value' => [],
						],
					];
					
					$param['name'] = $item->var->name;
					$param['byRef'] = $item->byRef;
					
					switch ($item->default->getType()) {
						case 'Expr_Array':
							$param['default']['type'] = 'array';
							$this->parseDataTree($item->default->items, $param['default']['value']);
							$tree[] = $param;
							break;
							
						case 'Scalar_String':
							$param['default']['type'] = 'string';
							$param['default']['value'] = $item->default->value;
							break;
						
						case 'Scalar_LNumber':
							$param['default']['type'] = 'number';
							$param['default']['value'] = $item->default->value;
							break;
						
						case 'Expr_ConstFetch':
							if (!empty($item->default->name->parts) && in_array($item->default->name->parts[0], ['true', 'false'])) {
								$param['default']['type'] = 'bool';
								$param['default']['value'] = $item->default->name->parts[0];
							} else {
								// todo: 其他类型 常量
							}
							
							$tree[] = $param;
							break;
					}
					break;
					
				case 'Expr_ArrayItem':
					$tree[] = $item->value->value;
					break;
			}
		}
		
		
	}
	
	/**************************************************
	 * get set
	 **************************************************/
	
	/**
	 * @return Parser
	 */
	public function getParserObj(): Parser {
		if (empty($this->_parserObj)) {
			$this->_parserObj = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		}
		
		return $this->_parserObj;
	}
	
	/**
	 * @param ParserFactory $parserFactoryObj
	 *
	 * @return CodeParser
	 */
	public function setParserObj(ParserFactory $parserFactoryObj) {
		$this->_parserObj = $parserFactoryObj;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getClassFileName(): string {
		return $this->_classFileName;
	}
	
	/**
	 * @param string $classFileName
	 *
	 * @return CodeParser
	 */
	public function setClassFileName(string $classFileName) {
		$this->_classFileName = $classFileName;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getClassCodeText(): string {
		return $this->_classCodeText;
	}
	
	/**
	 * @param string $classCodeText
	 *
	 * @return CodeParser
	 */
	public function setClassCodeText(string $classCodeText) {
		$this->_classCodeText = $classCodeText;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function &getCodeDataSource(): array {
		return $this->_codeDataSource;
	}
	
	/**
	 * @param array $codeDataSource
	 */
	public function setCodeDataSource(array $codeDataSource) {
		$this->_codeDataSource = $codeDataSource;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function &getCodeDataTree(): array {
		return $this->_codeDataTree;
	}
	
	/**
	 * @param array $codeDataTree
	 *
	 * @return CodeParser
	 */
	public function setCodeDataTree(array $codeDataTree) {
		$this->_codeDataTree = $codeDataTree;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function &getCodeData(): array {
		return $this->_codeData;
	}
	
	/**
	 * @param array $codeData
	 *
	 * @return CodeParser
	 */
	public function setCodeData(array $codeData) {
		$this->_codeData = $codeData;
		
		return $this;
	}
	
	
}