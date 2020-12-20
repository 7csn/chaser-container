<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\container\collector\ReflectionCollector;
use chaser\container\ContainerInterface;
use chaser\container\exception\ReflectedException;
use chaser\container\exception\ResolvedException;
use chaser\container\resolver\MethodResolver;
use ReflectionClass;
use ReflectionMethod;
use TypeError;

/**
 * 类函数名定义
 *
 * @package chaser\container\definition
 *
 * @property ?ReflectionMethod $reflector
 */
class MethodDefinition extends Definition
{
    /**
     * 类反射
     *
     * @var ReflectionClass|null
     */
    protected ?ReflectionClass $class;

    /**
     * 定义基础分析
     *
     * @param string $classname
     * @param string $methodName
     */
    public function __construct(string $classname, string $methodName)
    {
        $this->name = $classname . '::' . $methodName;
        try {
            $this->reflector = ReflectionCollector::method($classname, $methodName);
            $this->isResolvable = true;
        } catch (ReflectedException $e) {
        }
    }

    /**
     * @inheritDoc
     */
    public function resolver(ContainerInterface $container): MethodResolver
    {
        try {
            return new MethodResolver($container, $this->reflector);
        } catch (TypeError $e) {
            throw new ResolvedException("Method[{$this->reflector->getDeclaringClass()->name}::{$this->name}] doesn't exists");
        }
    }
}
