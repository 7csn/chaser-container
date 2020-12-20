<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\container\collector\ReflectionCollector;
use chaser\container\ContainerInterface;
use chaser\container\exception\ReflectedException;
use chaser\container\exception\ResolvedException;
use chaser\container\resolver\FunctionResolver;
use chaser\container\resolver\ResolverInterface;
use Closure;
use ReflectionFunction;
use TypeError;

/**
 * 闭包定义
 *
 * @package chaser\container\definition
 *
 * @property ?ReflectionFunction $reflector
 */
class ClosureDefinition extends Definition
{
    /**
     * 定义基础分析
     *
     * @param Closure $closure
     */
    public function __construct(Closure $closure)
    {
        try {
            $this->reflector = ReflectionCollector::closure($closure);
            $this->isResolvable = true;
        } catch (ReflectedException $e) {
        }
    }

    /**
     * @inheritDoc
     */
    public function resolver(ContainerInterface $container): FunctionResolver
    {
        try {
            return new FunctionResolver($container, $this->reflector);
        } catch (TypeError $e) {
            throw new ResolvedException("Closure exception");
        }
    }

}
