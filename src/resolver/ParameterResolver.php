<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\ContainerInterface;
use chaser\container\exception\ContainerException;
use chaser\container\exception\NotFoundException;
use chaser\container\exception\ResolvedException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionException;

/**
 * 参数反射解析类
 *
 * @package chaser\container\resolver
 *
 * @property ReflectionParameter $reflector
 */
class ParameterResolver extends Resolver
{
    /**
     * 参数名
     *
     * @var string
     */
    protected string $name;

    /**
     * 参数位置
     *
     * @var int
     */
    protected int $position;

    /**
     * 参数类型名（若有）
     *
     * @var string|null
     */
    protected ?string $classname;

    /**
     * 是否可为空
     *
     * @var bool
     */
    protected bool $allowsNull;

    /**
     * 类型反射
     *
     * @var ReflectionNamedType|null
     */
    protected ?ReflectionNamedType $type;

    /**
     * @inheritDoc
     */
    public function __construct(ContainerInterface $container, ReflectionParameter $reflector)
    {
        parent::__construct($container, $reflector);

        $this->name = $reflector->name;
        $this->position = $reflector->getPosition();
        $this->classname = $reflector->getClass()->name ?? null;
        $this->allowsNull = $reflector->allowsNull();
        if ($reflector->hasType()) {
            $this->type = $reflector->getType();
        }
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
    public function action(array $parameters = [])
    {
        if (key_exists($this->name, $parameters)) {
            $value = &$parameters[$this->name];
        } elseif (key_exists($this->position, $parameters)) {
            $value = &$parameters[$this->position];
        } else {
            return $this->classname ? $this->resolveClassWithParameters() : $this->resolvePrimitiveDefault();
        }
        return $this->classname ? $this->resolveClass($value) : $this->resolvePrimitive($value);
    }

    /**
     * 解析常规默认实体
     *
     * @return mixed|null
     * @throws ResolvedException
     */
    protected function resolvePrimitiveDefault()
    {
        if ($this->allowsNull) {
            return null;
        }
        try {
            return $this->reflector->getDefaultValue();
        } catch (ReflectionException $e) {
            throw new ResolvedException($this->exceptionMessage());
        }
    }

    /**
     * 根据给定值解析常规实体
     *
     * @param mixed $value
     * @return mixed
     * @throws ResolvedException
     */
    protected function resolvePrimitive(&$value)
    {
        if ($this->type === null || ($value === null && $this->allowsNull)) {
            return $value;
        }

        if (
            strpos($this->type->getName(), gettype($value)) === 0
            || strpos(gettype($value), $this->type->getName()) === 0
            || ($this->reflector->isCallable() && is_callable($value))
        ) {
            return $value;
        }

        throw new ResolvedException($this->exceptionMessage());
    }

    /**
     * 根据给定数组解析类实体
     *
     * @param array $parameters
     * @return mixed
     * @throws ResolvedException
     */
    protected function resolveClassWithParameters(array &$parameters = [])
    {
        if (empty($parameters) && $this->reflector->allowsNull()) {
            return null;
        }

        try {
            return $this->container->make($this->classname, $parameters);
        } catch (ContainerException|NotFoundException $e) {
            throw new ResolvedException($this->exceptionMessage());
        }
    }

    /**
     * 根据给定值解析类实体
     *
     * @param mixed $value
     * @return ParameterResolver|mixed
     * @throws ResolvedException
     */
    protected function resolveClass(&$value)
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        if (is_array($value)) {
            return $this - $this->resolveClassWithParameters($value);
        }

        throw new ResolvedException($this->exceptionMessage());
    }

    /**
     * 异常信息前缀
     *
     * @return string
     */
    protected function exceptionMessage(): string
    {
        $reflector = $this->reflector->getDeclaringFunction();

        $callableName = $reflector instanceof ReflectionMethod
            ? $reflector->class . '::' . $this->name
            : 'closure';

        return sprintf('Parameter[%s] of %s：the type of entry must be %s', $this->name, $callableName, $this->type->getName());
    }
}
