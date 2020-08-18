<?php


namespace uujia\framework\base\common\lib\Aop\Vistor;


use PhpParser\Node\Stmt\Namespace_;
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
	protected $classMethod = [];
	
	/**
	 * 父类方法添加
	 *
	 * @var array
	 */
	protected $addParentClassMethod = [];
	
	public function __construct($className, $proxyClassName, $addParentClassMethod) {
		$this->className            = $className;
		$this->proxyClassName       = $proxyClassName;
		$this->addParentClassMethod = $addParentClassMethod;
	}
	
	public function getProxyClassNameBaseName(): string {
		return \basename(str_replace('\\', '/', $this->proxyClassName));
	}
	
	public function getProxyClassNameDir(): string {
		return \dirname($this->proxyClassName);
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
			return new Namespace_(new Name($this->getProxyClassNameDir()),
			                      $node->stmts,
			                      $node->getAttributes());
		}
		
		// 替换类定义
		if ($node instanceof Class_) {
			// 创建一个代理类 基于目标类
			return new Class_($this->getProxyClassNameBaseName(), [
				'flags'   => $node->flags,
				'stmts'   => $node->stmts,
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
			
			$this->classMethod[$methodName] = $classMethod;
			
			return $classMethod;
		}
		
		return null;
	}
	
	public function afterTraverse(array $nodes) {
		$addEnhancementMethods = true;
		$nodeFinder            = new NodeFinder();
		$nodeFinder->find($nodes, function (Node $node) use (
			&$addEnhancementMethods
		) {
			if ($node instanceof TraitUse) {
				foreach ($node->traits as $trait) {
					// Did AopTrait trait use ?
					if ($trait instanceof Name && $trait->toString() === AopProxy::class) {
						$addEnhancementMethods = false;
						break;
					}
				}
			}
		});
		// Find Class Node and then Add Aop Enhancement Methods nodes and getOriginalClassName() method
		$classNode = $nodeFinder->findFirstInstanceOf($nodes, Class_::class);
		$addEnhancementMethods && array_unshift($classNode->stmts, $this->getAopTraitUseNode());
		
		foreach ($this->addParentClassMethod as $n => $v) {
			/** @var ClassMethod $v */
			
			if (array_key_exists($n, $this->getClassMethod())) {
				continue;
			}
			
			array_push($classNode->stmts, $v);
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
	public function getClassMethod(): array {
		return $this->classMethod;
	}
	
	/**
	 * @param array $classMethod
	 *
	 * @return $this
	 */
	public function setClassMethod(array $classMethod) {
		$this->classMethod = $classMethod;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAddParentClassMethod(): array {
		return $this->addParentClassMethod;
	}
	
	/**
	 * @param array $addParentClassMethod
	 *
	 * @return $this
	 */
	public function setAddParentClassMethod(array $addParentClassMethod) {
		$this->addParentClassMethod = $addParentClassMethod;
		
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