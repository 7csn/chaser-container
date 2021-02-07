<?php

declare(strict_types=1);

namespace chaser\container;

use chaser\container\definition\{ClassDefinition, FunctionDefinition, MethodDefinition};
use chaser\container\exception\DefinedException;
use Closure;

/**
 * 定义收集器
 *
 * @package chaser\container
 */
class DefinitionCollector
{
    /**
     * 【类名】定义库
     *
     * @var ClassDefinition[] [$className => ClassDefinition]
     */
    private static array $classDefinitions = [];

    /**
     * 【类名::方法名】定义库
     *
     * @var MethodDefinition[][] [$className => [$methodName => MethodDefinition]]
     */
    private static array $methodDefinitions = [];

    /**
     * 【函数名】定义库
     *
     * @var FunctionDefinition[] [$functionName => FunctionDefinition]
     */
    private static array $functionDefinitions = [];

    /**
     * 创建定义
     *
     * @param callable|string $source
     * @return ClassDefinition|FunctionDefinition|MethodDefinition
     * @throws DefinedException
     */
    public static function make(callable|string $source): ClassDefinition|FunctionDefinition|MethodDefinition
    {
        if (is_string($source)) {
            return self::get($source);
        }

        if ($source instanceof Closure) {
            return self::getFunction($source);
        }

        [$class, $method] = $source;

        if (is_object($class)) {
            $class = $class::class;
        }

        return self::getMethod($class, $method);
    }

    /**
     * 安全地创建定义
     *
     * @param mixed $source
     * @return ClassDefinition|FunctionDefinition|MethodDefinition|false
     */
    public static function makeSafely(callable|string $source): ClassDefinition|FunctionDefinition|MethodDefinition|false
    {
        try {
            return self::make($source);
        } catch (DefinedException) {
            return false;
        }
    }

    /**
     * 获取标识符定义
     *
     * @param string $source
     * @return ClassDefinition|FunctionDefinition|MethodDefinition
     * @throws DefinedException
     */
    public static function get(string $source): ClassDefinition|FunctionDefinition|MethodDefinition
    {
        if (str_contains($source, '::')) {
            return self::getMethod(...explode('::', $source, 2));
        }
        return function_exists($source) ? self::getFunction($source) : self::getClass($source);
    }

    /**
     * 获取指定【类名】的定义
     *
     * @param string $class
     * @return ClassDefinition
     * @throws DefinedException
     */
    public static function getClass(string $class): ClassDefinition
    {
        return self::$classDefinitions[$class] ??= new ClassDefinition($class);
    }

    /**
     * 获取指定【类名::方法名】的定义
     *
     * @param string $class
     * @param string $method
     * @return MethodDefinition
     * @throws DefinedException
     */
    public static function getMethod(string $class, string $method): MethodDefinition
    {
        return self::$methodDefinitions[$class][$method] ??= new MethodDefinition($class, $method);
    }

    /**
     * 获取指定【闭包/函数名】的定义
     *
     * @param Closure|string $function
     * @return FunctionDefinition
     * @throws DefinedException
     */
    public static function getFunction(Closure|string $function): FunctionDefinition
    {
        return is_string($function)
            ? self::$functionDefinitions[$function] ??= new FunctionDefinition($function)
            : new FunctionDefinition($function);
    }
}
