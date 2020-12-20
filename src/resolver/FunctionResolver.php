<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\ContainerInterface;
use chaser\container\exception\ResolvedException;
use ReflectionFunction;

/**
 * 方法反射解析类
 *
 * @package chaser\container\resolver
 *
 * @property ReflectionFunction $reflector
 */
class FunctionResolver extends Resolver
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
    public function __construct(ContainerInterface $container, ReflectionFunction $reflector)
    {
        parent::__construct($container, $reflector);

        $this->parametersResolver = new ParametersResolver($container, $reflector);
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return !($this->reflector->isDisabled() || $this->reflector->isDeprecated());
    }

    /**
     * @inheritDoc
     */
    public function action(array $parameters = [])
    {
        return $this->reflector->invokeArgs($this->parametersResolver->action($parameters));
    }
}
