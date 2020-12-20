<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\container\collector\ReflectionCollector;
use chaser\container\ContainerInterface;
use chaser\container\exception\ReflectedException;
use chaser\container\exception\ResolvedException;
use chaser\container\resolver\ClassResolver;
use ReflectionClass;
use TypeError;

/**
 * 类名定义
 *
 * @package chaser\container\definition
 *
 * @property ?ReflectionClass $reflector
 */
class ClassDefinition extends Definition
{
    /**
     * 定义基础分析
     *
     * @param string $classname
     */
    public function __construct(string $classname)
    {
        $this->name = $classname;
        try {
            $this->reflector = ReflectionCollector::class($classname);
            $this->isResolvable = true;
        } catch (ReflectedException $e) {
        }
    }

    /**
     * @inheritDoc
     */
    public function resolver(ContainerInterface $container): ClassResolver
    {
        try {
            return new ClassResolver($container, $this->reflector);
        } catch (TypeError $e) {
            throw new ResolvedException("Class[{$this->name}] does not exists");
        }
    }
}
