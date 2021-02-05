<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\container\exception\{NotFoundException, ResolvedException};
use chaser\container\Resolver;

/**
 * 定义
 *
 * @package chaser\container\definition
 */
interface DefinitionInterface
{
    /**
     * 是否可解析
     *
     * @return bool
     */
    public function isResolvable(): bool;

    /**
     * 定义解析
     *
     * @param Resolver $resolver
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     * @throws ResolvedException
     */
    public function resolve(Resolver $resolver, array $arguments = []): mixed;
}
