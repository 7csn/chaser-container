<?php

declare(strict_types=1);

namespace chaser\container\collector;

use chaser\container\exception\ReflectedException;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

/**
 * 反射收集器
 *
 * @package chaser\container\collector
 */
class ReflectionCollector
{
    /**
     * 类型反射库
     *
     * @var array [$className => ReflectionClass]
     */
    protected static array $classReflections = [];

    /**
     * 类函数反射库
     *
     * @var array [$className => [$methodName => ReflectionMethod]]
     */
    protected static array $methodReflections = [];

    /**
     * 类属性反射库
     *
     * @var array [$className => [$propertyName => ReflectionProperty]]
     */
    protected static array $propertyReflections = [];

    /**
     * 类属性名列表
     *
     * @var array [$className => [...$propertyName]]
     */
    protected static array $propertyNames = [];

    /**
     * 方法反射库
     *
     * @var array [$functionName => ReflectionFunction]
     */
    protected static array $functionReflections = [];

    /**
     * 类反射
     *
     * @param string $className
     * @return ReflectionClass
     * @throws ReflectedException
     */
    public static function class(string $className): ReflectionClass
    {
        try {
            return self::$classReflections[$className] ??= new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ReflectedException($className, ReflectedException::CODE_CLASS_NOT_EXIST);
        }
    }

    /**
     * 类函数反射
     *
     * @param string $className
     * @param string $methodName
     * @return ReflectionMethod
     * @throws ReflectedException
     */
    public static function method(string $className, string $methodName): ReflectionMethod
    {
        try {
            return self::$methodReflections[$className][$methodName] ??= self::class($className)->getMethod($methodName);
        } catch (ReflectionException $e) {
            throw new ReflectedException($className . '::' . $methodName, ReflectedException::CODE_METHOD_NOT_EXIST);
        }
    }

    /**
     * 类属性反射
     *
     * @param string $className
     * @param string $propertyName
     * @return ReflectionProperty
     * @throws ReflectedException
     */
    public static function property(string $className, string $propertyName): ReflectionProperty
    {
        try {
            return self::$propertyReflections[$className][$propertyName] ??= self::class($className)->getProperty($propertyName);
        } catch (ReflectionException $e) {
            throw new ReflectedException($className . '::' . $propertyName, ReflectedException::CODE_PROPERTY_NOT_EXIST);
        }
    }

    /**
     * 类属性名列表
     *
     * @param string $className
     * @return string[]
     * @throws ReflectedException
     */
    public static function properties(string $className): array
    {
        return self::$propertyNames[$className] ??= array_map(function (ReflectionProperty $property) {
            return $property->name;
        }, self::class($className)->getProperties());
    }

    /**
     * 方法反射
     *
     * @param string $functionName
     * @return ReflectionFunction
     * @throws ReflectedException
     */
    public static function function(string $functionName): ReflectionFunction
    {
        try {
            return self::$functionReflections[$functionName] ??= new ReflectionFunction($functionName);
        } catch (ReflectionException $e) {
            throw new ReflectedException($functionName, ReflectedException::CODE_FUNCTION_NOT_EXIST);
        }
    }

    /**
     * 闭包反射
     *
     * @param Closure $closure
     * @return ReflectionFunction
     * @throws ReflectedException
     */
    public static function closure(Closure $closure): ReflectionFunction
    {
        try {
            return new ReflectionFunction($closure);
        } catch (ReflectionException $e) {
            throw new ReflectedException;
        }
    }
}
