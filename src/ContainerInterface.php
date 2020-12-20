<?php

declare(strict_types=1);

namespace chaser\container;

use chaser\container\exception\ContainerException;
use chaser\container\exception\DefinedException;
use chaser\container\exception\NotFoundException;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * 绑定定义到标识符
     *
     * @param string $id
     * @param mixed $source
     * @throws DefinedException
     */
    public function define(string $id, $source);

    /**
     * 从容器解析指定标识符实体并返回
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function make(string $id, array $parameters = []);

    /**
     * 设置标识符实体
     *
     * @param string $id
     * @param mixed $entry
     */
    public function set(string $id, $entry);
}
