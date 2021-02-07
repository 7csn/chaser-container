<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\collector\ReflectedException;
use chaser\collector\ReflectionCollector;
use chaser\container\exception\DefinedException;
use chaser\container\Resolver;
use ReflectionMethod;

/**
 * 类方法名定义
 *
 * @package chaser\container\definition
 */
class MethodDefinition implements DefinitionInterface
{
    /**
     * 主反射
     *
     * @var ReflectionMethod
     */
    private ReflectionMethod $reflection;

    /**
     * 定义基础分析
     *
     * @param string $class
     * @param string $method
     * @throws DefinedException
     */
    public function __construct(string $class, string $method)
    {
        try {
            $this->reflection = ReflectionCollector::getMethod($class, $method);
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function resolve(Resolver $resolver, array $arguments = []): mixed
    {
        return $resolver->methodAction($this->reflection, $arguments);
    }
}
