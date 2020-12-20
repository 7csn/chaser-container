<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * 调用参数列表解析类
 *
 * @package chaser\container\resolver
 */
class ParametersResolver implements ResolverInterface
{
    /**
     * 参数解析列表
     *
     * @var ParameterResolver[]
     */
    protected array $parameters = [];

    /**
     * 初始化解析器
     *
     * @param ContainerInterface $container
     * @param ReflectionFunctionAbstract $reflector
     */
    public function __construct(ContainerInterface $container, ReflectionFunctionAbstract $reflector)
    {
        $this->parameters = array_map(
            function (ReflectionParameter $parameter) use (&$container) {
                return new ParameterResolver($container, $parameter);
            },
            $reflector->getParameters()
        );
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function action(array $parameters = []): array
    {
        return array_map(function (ParameterResolver $resolver) use (&$parameters) {
            return $resolver->action($parameters);
        }, $this->parameters);

    }
}
