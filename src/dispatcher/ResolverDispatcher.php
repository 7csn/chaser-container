<?php

declare(strict_types=1);

namespace chaser\container\dispatcher;

use chaser\container\ContainerInterface;
use chaser\container\definition\DefinitionInterface;
use chaser\container\exception\ResolvedException;
use chaser\container\resolver\ResolverInterface;

/**
 * 解析调度器
 *
 * @package chaser\container\dispatcher
 */
class ResolverDispatcher
{
    /**
     * 容器
     *
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * 解析库
     *
     * @var array [$definitionName => DefinitionInterface]
     */
    protected array $resolvers = [];

    /**
     * 初始化解析调度器
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 解析定义实体并返回
     *
     * @param DefinitionInterface $definition
     * @param array $parameters
     * @return mixed
     * @throws ResolvedException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        return $this->resolver($definition)->action($parameters);
    }

    /**
     * 获取指定定义的解析对象
     *
     * @param DefinitionInterface $definition
     * @return ResolverInterface
     * @throws ResolvedException
     */
    protected function resolver(DefinitionInterface $definition): ResolverInterface
    {
        $name = $definition->name();

        return $name
            ? $this->resolvers[$name] ??= $definition->resolver($this->container)
            : $definition->resolver($this->container);
    }
}
