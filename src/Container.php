<?php

declare(strict_types=1);

namespace chaser\container;

use chaser\container\definition\{ClassDefinition, FunctionDefinition, MethodDefinition};
use chaser\container\exception\NotFoundException;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use TypeError;

/**
 * IoC 容器
 *
 * @package chaser\container
 */
class Container implements ContainerInterface
{
    /**
     * 定义解析调度器
     *
     * @var Resolver
     */
    private Resolver $resolver;

    /**
     * 共享实体库
     *
     * @var array
     */
    private array $entries = [];

    /**
     * 定义库
     *
     * @var array
     */
    private array $definitions = [];

    /**
     * 解析实体标识符堆栈
     *
     * @var array
     */
    private array $resolveStack = [];

    /**
     * @inheritDoc
     */
    public function define(string $id, callable|string $source): void
    {
        $this->definitions[$id] = DefinitionCollector::make($source);
        unset($this->entries[$id]);
    }

    /**
     * @inheritDoc
     */
    public function removeDefinition(string $id = null): void
    {
        if ($id === null) {
            $this->definitions = [];
        } else {
            unset($this->definitions[$id]);
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $id, mixed $entry): void
    {
        $this->entries[$id] = $entry;
    }

    /**
     * @inheritDoc
     */
    public function unset(string $id = null): void
    {
        if ($id === null) {
            $this->entries = [];
        } else {
            unset($this->entries[$id]);
        }
    }

    /**
     * @inheritDoc
     */
    public function make(string $id, array $parameters = []): mixed
    {
        if (empty($parameters) && (isset($this->entries[$id]) || key_exists($id, $this->entries))) {
            return $this->entries[$id];
        }

        return $this->resolve($id, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $id, array $parameters = []): mixed
    {
        $definition = $this->getDefinition($id);

        $this->resolveStack[] = $id;

        if ($definition === false) {
            throw new NotFoundException($this->getResolving());
        }

        $entry = $definition->resolve($this->resolver, $parameters);

        array_pop($this->resolveStack);

        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function getResolving(): string
    {
        return join(' > ', $this->resolveStack);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): mixed
    {
        return isset($this->entries[$id]) || key_exists($id, $this->entries)
            ? $this->entries[$id]
            : $this->entries[$id] = $this->resolve($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->entries[$id])
            || key_exists($id, $this->entries)
            || isset($this->definitions[$id])
            || $this->getDefinition($id);
    }

    /**
     * 共享容器自身，初始化定义
     *
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->resolver = new Resolver($this);

        $this->entries = [
            PsrContainerInterface::class => $this,
            ContainerInterface::class => $this,
            self::class => $this,
            Resolver::class => $this->resolver,
        ];

        $this->defineBatchSafe($sources);
    }

    /**
     * 初始化定义库
     *
     * @param array $sources
     */
    private function defineBatchSafe(array $sources = []): void
    {
        foreach ($sources as $id => $source) {
            try {
                $definition = DefinitionCollector::makeSafely($source);
                if ($definition !== false) {
                    $this->definitions[$id] = $definition;
                    unset($this->entries[$id]);
                }
            } catch (TypeError) {
            }
        }
    }

    /**
     * 获取标识符的定义
     *
     * @param string $id
     * @return ClassDefinition|FunctionDefinition|MethodDefinition|false
     */
    private function getDefinition(string $id): ClassDefinition|FunctionDefinition|MethodDefinition|false
    {
        return $this->definitions[$id] ??= DefinitionCollector::makeSafely($id);
    }
}
