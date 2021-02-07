<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\collector\ReflectedException;
use chaser\collector\ReflectionCollector;
use chaser\container\exception\DefinedException;
use chaser\container\Resolver;
use ReflectionClass;

/**
 * 类名定义
 *
 * @package chaser\container\definition
 */
class ClassDefinition implements DefinitionInterface
{
    /**
     * 主反射
     *
     * @var ReflectionClass
     */
    private ReflectionClass $reflection;

    /**
     * 定义基础分析
     *
     * @param string $class
     * @throws DefinedException
     */
    public function __construct(string $class)
    {
        try {
            $this->reflection = ReflectionCollector::getClass($class);
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return object
     * @inheritDoc
     */
    public function resolve(Resolver $resolver, array $arguments = []): object
    {
        return $resolver->classAction($this->reflection, $arguments);
    }
}
