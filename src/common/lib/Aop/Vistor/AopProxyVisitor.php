<?php


namespace uujia\framework\base\common\lib\Aop\Vistor;


use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeFinder;
use uujia\framework\base\common\lib\Aop\AopProxy;
use uujia\framework\base\common\lib\Utils\Str;

class AopProxyVisitor extends NodeVisitorAbstract {
	
	/**
	 * @var string
	 */
	protected $className = '';
	
	/**
	 * @var string
	 */
	protected $proxyClassName = '';
	
	/**
	 * @var array
	 */
	protected $classNode = [
		'classMethod' => [],
		'uses' => [],
	];
	
	/**
	 * 父类方法添加
	 *
	 * @var array
	 */
	protected $addParentNode = [];
	
	public function __construct($className, $proxyClassName, $addParentNode) {
		$this->className      = $className;
		$this->proxyClassName = $proxyClassName;
		$this->addParentNode  = $addParentNode;
	}
	
	public function getProxyClassNameBaseName(): string {
		// return \basename(str_replace('\\', '/', $this->proxyClassName));
		return Str::classBasename($this->proxyClassName);
	}
	
	public function getProxyClassNameDir(): string {
		// return \dirname($this->proxyClassName);
		return Str::classNamespace($this->proxyClassName);
	}
	
	/**
	 * @return \PhpParser\Node\Stmt\TraitUse
	 */
	private function getAopTraitUseNode(): TraitUse {
		// Use AopTrait trait use node
		return new TraitUse([new Name('\\' . AopProxy::class)]);
	}
	
	public function leaveNode(Node $node) {
		// 替换命名空间定义
		if ($node instanceof Namespace_) {
			$_stmts = $node->stmts;
			$_classCount = 0;
			for ($i = count($_stmts) - 1; $i >= 0; $i--) {
				if (!($_stmts[$i] instanceof Use_)) {
					if ($_stmts[$i] instanceof Class_ && $_classCount == 0) {
						$_classCount++;
					} else {
						array_splice($_stmts, $i, 1);
					}
				}
			}
			
			return new Namespace_(new Name($this->getProxyClassNameDir()),
			                      $_stmts, // $node->stmts,
			                      $node->getAttributes());
		}
		
		// 截获uses
		if ($node instanceof Node\Stmt\Use_) {
			foreach ($node->uses as $use) {
				$_use     = $use->name->toString();
				$_useType = $node->type;
				
				$this->classNode['uses'][$_use] = $use;
			}
		}
		
		if ($node instanceof Node\Stmt\GroupUse) {
			$_useGroupPrefix = $node->prefix->toString();
			$_useGroupType   = $node->type;
			
			foreach ($node->uses as $use) {
				$_useItem = $use->name->toString();
				$_useType = ($use->type == Use_::TYPE_UNKNOWN) ? $_useGroupType : $use->type;
				$_use     = $_useGroupPrefix . '\\' . $_useItem;
				
				$this->classNode['uses'][$_use] = $use;
			}
		}
		
		// 替换类定义
		if ($node instanceof Class_) {
			$_stmts = $node->stmts;
			for ($i = count($_stmts) - 1; $i >= 0; $i--) {
				if (!($_stmts[$i] instanceof ClassMethod)) {
					array_splice($_stmts, $i, 1);
				}
			}
			
			// 创建一个代理类 基于目标类
			return new Class_($this->getProxyClassNameBaseName(), [
				'flags'   => $node->flags,
				'stmts'   => $_stmts, // $node->stmts,
				'extends' => new Name('\\' . $this->className),
			]);
		}
		
		// 重写 public 和 protected 方法，不包括静态方法
		if ($node instanceof ClassMethod && !$node->isStatic() && ($node->isPublic() || $node->isProtected())) {
			$methodName = $node->name->toString();
			// Rebuild closure uses, only variable
			$uses = [];
			$args = [];
			foreach ($node->params as $key => $param) {
				if ($param instanceof Param) {
					$uses[$key] = new Param($param->var, null, null, true);
					$args[$key] = new Param($param->var, null, null, $param->byRef);
				}
			}
			$params = [
				// Add method to an closure
				new Closure([
					            'static' => $node->isStatic(),
					            'uses'   => $uses,
					            // 'stmts'  => $node->stmts,
					            'stmts'  => [new Return_(new Node\Expr\StaticCall(new Name('parent'), $methodName, $args))],
				            ]),
				new String_($methodName),
				new FuncCall(new Name('func_get_args')),
			];
			
			$stmts = [
				new Return_(new MethodCall(new Variable('this'), '_aopCall', $params)),
			];
			
			$returnType = $node->getReturnType();
			
			if ($returnType instanceof Name && $returnType->toString() === 'self') {
				$returnType = new Name('\\' . $this->className);
			}
			
			$classMethod = new ClassMethod($methodName, [
				'flags'      => $node->flags,
				'byRef'      => $node->byRef,
				'params'     => $node->params,
				'returnType' => $returnType,
				'stmts'      => $stmts,
			]);
			
			$this->classNode['classMethod'][$methodName] = $classMethod;
			
			return $classMethod;
		}
		
		return null;
	}
	
	public function afterTraverse(array $nodes) {
		$isAddMethods = true;
		$nodeFinder   = new NodeFinder();
		$nodeFinder->find($nodes, function (Node $node) use (
			&$isAddMethods
		) {
			if ($node instanceof TraitUse) {
				foreach ($node->traits as $trait) {
					// Did AopTrait trait use ?
					if ($trait instanceof Name && $trait->toString() === AopProxy::class) {
						$isAddMethods = false;
						break;
					}
				}
			}
		});
		// Find Class Node and then Add Aop Enhancement Methods nodes and getOriginalClassName() method
		$classNode = $nodeFinder->findFirstInstanceOf($nodes, Class_::class);
		$isAddMethods && array_unshift($classNode->stmts, $this->getAopTraitUseNode());
		
		foreach ($this->addParentNode['classMethod'] as $n => $v) {
			/** @var ClassMethod $v */
			
			if (array_key_exists($n, $this->getClassNode()['classMethod'])) {
				continue;
			}
			
			array_push($classNode->stmts, $v);
		}
		
		$namespaceNode = $nodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
		
		foreach ($this->addParentNode['uses'] as $n => $v) {
			/** @var ClassMethod $v */
			
			if (array_key_exists($n, $this->getClassNode()['uses'])) {
				continue;
			}
			
			array_unshift($namespaceNode->stmts, $v);
		}
		
		return $nodes;
	}
	
	/**
	 * @return string
	 */
	public function getClassName(): string {
		return $this->className;
	}
	
	/**
	 * @param string $className
	 */
	public function setClassName(string $className): void {
		$this->className = $className;
	}
	
	/**
	 * @return string
	 */
	public function getProxyClassName(): string {
		return $this->proxyClassName;
	}
	
	/**
	 * @param string $proxyClassName
	 *
	 * @return AopProxyVisitor
	 */
	public function setProxyClassName(string $proxyClassName) {
		$this->proxyClassName = $proxyClassName;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getClassNode(): array {
		return $this->classNode;
	}
	
	/**
	 * @param array $classNode
	 *
	 * @return $this
	 */
	public function setClassNode(array $classNode) {
		$this->classNode = $classNode;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAddParentNode(): array {
		return $this->addParentNode;
	}
	
	/**
	 * @param array $addParentNode
	 *
	 * @return $this
	 */
	public function setAddParentNode(array $addParentNode) {
		$this->addParentNode = $addParentNode;
		
		return $this;
	}
}

// trait AopTrait {
// 	/**
// 	 * AOP proxy call method
// 	 *
// 	 * @param \Closure $closure
// 	 * @param string   $method
// 	 * @param array    $params
// 	 * @return mixed|null
// 	 * @throws \Throwable
// 	 */
// 	public function __proxyCall(\Closure $closure, string $method, array $params) {
// 		return $closure(...$params);
// 	}
// }