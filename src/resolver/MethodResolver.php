<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\ContainerInterface;
use chaser\container\exception\ContainerException;
use chaser\container\exception\NotFoundException;
use chaser\container\exception\ResolvedException;
use ReflectionMethod;

/**
 * 类函数反射解析类
 *
 * @package chaser\container\resolver
 *
 * @property ReflectionMethod $reflector
 */
class MethodResolver extends Resolver
{
    /**
     * 参数解析器
     *
     * @var ParametersResolver|null
     */
    protected ParametersResolver $parametersResolver;

    /**
     * @inheritDoc
     */
    public function __construct(ContainerInterface $container, ReflectionMethod $reflector)
    {
        parent::__construct($container, $reflector);

        $this->parametersResolver = new ParametersResolver($container, $reflector);
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->reflector->isPublic()
            && !$this->reflector->isAbstract()
            && ($this->reflector->isStatic() || $this->reflector->getDeclaringClass()->isInstantiable());
    }

    /**
     * @inheritDoc
     */
    public function action(array $parameters = [])
    {
        $object = $this->object($parameters);
        $args = $this->parametersResolver->action($parameters);
        return $this->reflector->invokeArgs($object, $args);
    }

    /**
     * 获取对象
     *
     * @param array $parameters
     * @return object|null
     * @throws ResolvedException
     */
    private function object(array &$parameters): ?object
    {
        if ($this->reflector->isStatic()) {
            return null;
        }

        if (isset($parameters['$object'])) {
            $given = $parameters['$object'];
            if ($given instanceof $this->reflector->class) {
                return $given;
            }
        } else {
            $given = [];
        }

        if (is_array($given)) {
            try {
                return $this->container->make($this->reflector->class, $given);
            } catch (ContainerException|NotFoundException $e) {
                throw new ResolvedException("Method[{$this->reflector->class}::{$this->reflector->name}] can't be instantiated");
            }
        }

        throw new ResolvedException("Method[{$this->reflector->class}::{$this->reflector->name}] construction parameter Invalid");
    }
}
