<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\collector\ReflectedException;
use chaser\collector\ReflectionCollector;
use chaser\container\exception\DefinedException;
use chaser\container\Resolver;
use Closure;
use ReflectionFunction;

/**
 * 函数名/闭包定义
 *
 * @package chaser\container\definition
 */
class FunctionDefinition implements DefinitionInterface
{
    /**
     * 主反射
     *
     * @var ReflectionFunction
     */
    private ReflectionFunction $reflection;

    /**
     * 定义基础分析
     *
     * @param Closure|string $function
     * @throws DefinedException
     */
    public function __construct(Closure|string $function)
    {
        try {
            $this->reflection = ReflectionCollector::getFunction($function);
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function resolve(Resolver $resolver, array $arguments = []): mixed
    {
        return $resolver->functionAction($this->reflection, $arguments);
    }
}
