<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\ContainerInterface;
use Reflector;

/**
 * 反射解析抽象类
 *
 * @package chaser\container\resolver
 */
abstract class Resolver implements ResolverInterface
{
    /**
     * 容器
     *
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * 反射
     *
     * @var Reflector
     */
    protected Reflector $reflector;

    /**
     * 初始化解析器
     *
     * @param ContainerInterface $container
     * @param Reflector $reflector
     */
    public function __construct(ContainerInterface $container, Reflector $reflector)
    {
        $this->container = $container;
        $this->reflector = $reflector;
    }
}
