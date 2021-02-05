<?php

declare(strict_types=1);

namespace chaser\container;

use chaser\container\exception\{DefinedException, NotFoundException, ResolvedException};
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * IoC 容器
 *
 * @package chaser\container
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * 绑定定义到标识符
     *
     * @param string $id
     * @param callable|string $source
     * @throws DefinedException
     */
    public function define(string $id, callable|string $source): void;

    /**
     * 移除指定或全部标识符定义
     *
     * @param string|null $id
     */
    public function removeDefinition(string $id = null): void;

    /**
     * 从容器解析指定标识符实体并返回；若未提供参数，优先取标识符缓存实体
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws NotFoundException
     * @throws ResolvedException
     */
    public function make(string $id, array $parameters = []): mixed;

    /**
     * 从容器解析指定标识符实体并返回
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws NotFoundException
     * @throws ResolvedException
     */
    public function resolve(string $id, array $parameters = []): mixed;

    /**
     * 获取解析堆栈
     *
     * @return string[]
     */
    public function getResolveStack(): array;

    /**
     * 设置标识符实体
     *
     * @param string $id
     * @param mixed $entry
     */
    public function set(string $id, mixed $entry): void;

    /**
     * 移除指定标识符或全部实体
     *
     * @param string|null $id
     */
    public function unset(string $id = null): void;
}
