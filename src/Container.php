<?php

declare(strict_types=1);

namespace chaser\container;

use chaser\container\collector\DefinitionCollector;
use chaser\container\definition\DefinitionInterface;
use chaser\container\dispatcher\ResolverDispatcher;
use chaser\container\exception\{
    ContainerException,
    NotFoundException,
    ResolvedException,
};
use Psr\Container\ContainerInterface as PsrContainerInterface;

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
     * @var ResolverDispatcher
     */
    protected ResolverDispatcher $dispatcher;

    /**
     * 共享实体库
     *
     * @var array
     */
    protected array $entries = [];

    /**
     * 定义库
     *
     * @var array
     */
    protected array $definitions = [];

    /**
     * 创建实体标识符堆栈
     *
     * @var array
     */
    protected array $makeStack = [];

    /**
     * @inheritDoc
     */
    public function define(string $id, $source)
    {
        $this->definitions[$id] = DefinitionCollector::make($source);
        unset($this->entries[$id]);
    }

    /**
     * @inheritDoc
     */
    public function make(string $id, array $parameters = [])
    {
        $definition = $this->getDefinition($id);

        $this->makeStack[] = $id;

        try {
            $entry = $this->dispatcher->resolve($definition, $parameters);
        } catch (ResolvedException $e) {
            throw $definition->isResolvable()
                ? NotFoundException::create($e, $this->makeStack)
                : ContainerException::create($e, $this->makeStack);
        }

        array_pop($this->makeStack);

        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function set(string $id, $entry)
    {
        $this->entries[$id] = $entry;
    }

    /**
     * 共享容器自身，初始化定义
     *
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->dispatcher = new ResolverDispatcher($this);

        $this->entries = [
            PsrContainerInterface::class => $this,
            ContainerInterface::class => $this,
            self::class => $this,
            ResolverDispatcher::class => $this->dispatcher,
        ];

        $this->initDefinitions($sources);
    }

    /**
     * 初始化定义库
     *
     * @param array $sources
     */
    public function initDefinitions(array $sources = [])
    {
        $this->definitions = [];

        foreach ($sources as $id => $source) {
            $definition = DefinitionCollector::makeSafely($source);

            if ($definition) {
                $this->definitions[$id] = $definition;
                unset($this->entries[$id]);
            }
        }
    }

    /**
     * 获取标识符的定义
     *
     * @param string $id
     * @return DefinitionInterface
     */
    protected function getDefinition(string $id)
    {
        return $this->definitions[$id] ??= DefinitionCollector::get($id);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        return isset($this->entries[$id]) || key_exists($id, $this->entries)
            ? $this->entries[$id]
            : $this->entries[$id] = $this->make($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->entries[$id])
            || key_exists($id, $this->entries)
            || isset($this->definitions[$id])
            || $this->getDefinition($id)->isResolvable();
    }
}
