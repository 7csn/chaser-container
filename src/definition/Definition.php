<?php

declare(strict_types=1);

namespace chaser\container\definition;

use Reflector;

/**
 * 定义抽象类
 *
 * @package chaser\container\definition
 */
abstract class Definition implements DefinitionInterface
{
    /**
     * 定义名
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * 是否可解析
     *
     * @var bool
     */
    protected bool $isResolvable = false;

    /**
     * 定义有效反射
     *
     * @var Reflector|null
     */
    protected ?Reflector $reflector;

    /**
     * @inheritDoc
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function isResolvable(): bool
    {
        return $this->isResolvable;
    }
}
