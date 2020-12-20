<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\ContainerInterface;
use chaser\container\exception\ResolvedException;
use Error;
use ReflectionClass;

/**
 * 类反射解析类
 *
 * @package chaser\container\resolver
 *
 * @property ReflectionClass $reflector
 */
class ClassResolver extends Resolver
{
    /**
     * 参数解析器
     *
     * @var ParametersResolver|null
     */
    protected ?ParametersResolver $parametersResolver;

    /**
     * @inheritDoc
     */
    public function __construct(ContainerInterface $container, ReflectionClass $reflector)
    {
        parent::__construct($container, $reflector);

        if ($reflector->getConstructor()) {
            $this->parametersResolver = new ParametersResolver($container, $reflector->getConstructor());
        }
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->reflector->isInstantiable();
    }

    /**
     * @inheritDoc
     */
    public function action(array $parameters = []): object
    {
        return $this->reflector->getConstructor()
            ? new $this->reflector->name(...$this->parametersResolver->action($parameters))
            : $this->reflector->newInstanceWithoutConstructor();
    }
}
