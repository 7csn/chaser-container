<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\container\ContainerInterface;
use chaser\container\exception\ResolvedException;
use chaser\container\resolver\ResolverInterface;

/**
 * 定义接口
 *
 * @package chaser\container\definition
 */
interface DefinitionInterface
{
    /**
     * 获取定义名
     *
     * @return string|null
     */
    public function name(): ?string;

    /**
     * 是否可解析
     *
     * @return bool
     */
    public function isResolvable(): bool;

    /**
     * 获取定义解析器
     *
     * @param ContainerInterface $container
     * @return ResolverInterface
     * @throws ResolvedException
     */
    public function resolver(ContainerInterface $container): ResolverInterface;
}
