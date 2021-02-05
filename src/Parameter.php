<?php

declare(strict_types=1);

namespace chaser\container;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * 参数分析
 *
 * Class ParameterType
 * @package chaser\container
 */
class Parameter
{
    /**
     * 参数名
     *
     * @var string
     */
    private string $name;

    /**
     * 参数位置
     *
     * @var int
     */
    private int $position;

    /**
     * 参数值是否可以为 null
     *
     * @var bool
     */
    private bool $allowsNull;

    /**
     * 全限定类型名列表
     *
     * @var string[]
     */
    private array $classes;

    /**
     * 默认值
     *
     * @var mixed
     */
    private mixed $defaultValue = null;

    /**
     * 调用名称
     *
     * @var string
     */
    private string $callName;

    /**
     * 初始化信息
     *
     * @param ReflectionParameter $parameter
     * @param string|null $callName
     */
    public function __construct(ReflectionParameter $parameter, string $callName = null)
    {
        $this->name = $parameter->name;
        $this->position = $parameter->getPosition();
        $this->allowsNull = $parameter->allowsNull();
        $this->classes = self::getAllClassname($parameter);

        $this->callName = $callName ?? self::getCallName($parameter);

        try {
            $this->defaultValue = $parameter->getDefaultValue();
        } catch (ReflectionException) {
        }
    }

    /**
     * 获取全部全限定类型名
     *
     * @param ReflectionParameter $parameter
     * @return string[]
     */
    private static function getAllClassname(ReflectionParameter $parameter): array
    {
        if (null === $type = $parameter->getType()) {
            return [];
        }

        $class = $parameter->getDeclaringClass();

        if ($type instanceof ReflectionNamedType) {
            return (array)self::getClassnameOrNull($type, $class);
        }

        return $type instanceof ReflectionUnionType ? array_reduce($type->getTypes(), function ($classnames, $type) use ($class) {
            if (null !== $classname = self::getClassnameOrNull($type, $class)) {
                $classnames[] = $classname;
            }
            return $classnames;
        }, []) : [];
    }

    /**
     * 从类型名反射中获取类名，无则返回 null
     *
     * @param ReflectionNamedType $type
     * @param ReflectionClass|null $class
     * @return string|null
     */
    private static function getClassnameOrNull(ReflectionNamedType $type, ?ReflectionClass $class): ?string
    {
        if ($type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if ($class !== null) {
            if ($name === 'self') {
                $name = $class->getName();
            } elseif ($name === 'parent' && null !== $class = $class->getParentClass()) {
                $name = $class->getName();
            }
        }

        return $name;
    }

    /**
     * 异常信息前缀
     *
     * @param ReflectionParameter $parameter
     * @return string
     */
    private static function getCallName(ReflectionParameter $parameter): string
    {
        $caller = $parameter->getDeclaringFunction();
        return property_exists($caller, 'class') ? "{$caller->class}::{$caller->name}" : $caller->name;
    }

    /**
     * 返回参数名称
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * 返回参数位置
     *
     * @return int
     */
    public function position(): int
    {
        return $this->position;
    }

    /**
     * 返回参数是否可以为空
     *
     * @return bool
     */
    public function allowNull(): bool
    {
        return $this->allowsNull;
    }

    /**
     * 获取参数全限定类型名列表
     *
     * @return string[]
     */
    public function classes(): array
    {
        return $this->classes;
    }

    /**
     * 返回参数默认值
     *
     * @return mixed
     */
    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * 返回调用名称
     *
     * @return string
     */
    public function callName(): string
    {
        return $this->callName;
    }
}
