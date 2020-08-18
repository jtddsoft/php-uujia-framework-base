<?php


namespace uujia\framework\base\common\lib\Aop\Vistor;


use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
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

class AopProxyExtendsVisitor extends NodeVisitorAbstract {
	
	protected $className;
	
	protected $returnStmts = [];
	
	public function __construct($className) {
		$this->className = $className;
	}
	
	public function leaveNode(Node $node) {
		// 截获uses
		if ($node instanceof Node\Stmt\Use_) {
		
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
			
			$this->returnStmts[$methodName] = $classMethod;//['classMethod']
			
			return $classMethod;
		}
		
		return null;
	}
	
	// public function afterTraverse(array $nodes) {
	// 	$addEnhancementMethods = true;
	// 	$nodeFinder            = new NodeFinder();
	// 	$nodeFinder->find($nodes, function (Node $node) use (
	// 		&$addEnhancementMethods
	// 	) {
	// 		if ($node instanceof TraitUse) {
	// 			foreach ($node->traits as $trait) {
	// 				// Did AopTrait trait use ?
	// 				if ($trait instanceof Name && $trait->toString() === AopProxy::class) {
	// 					$addEnhancementMethods = false;
	// 					break;
	// 				}
	// 			}
	// 		}
	// 	});
	// 	// Find Class Node and then Add Aop Enhancement Methods nodes and getOriginalClassName() method
	// 	$classNode = $nodeFinder->findFirstInstanceOf($nodes, Class_::class);
	// 	$addEnhancementMethods && array_unshift($classNode->stmts, $this->getAopTraitUseNode());
	//
	// 	return $nodes;
	// }
	
	/**
	 * @return mixed
	 */
	public function getReturnStmts() {
		return $this->returnStmts;
	}
	
	/**
	 * @param mixed $returnStmts
	 *
	 * @return $this
	 */
	public function setReturnStmts($returnStmts) {
		$this->returnStmts = $returnStmts;
		
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