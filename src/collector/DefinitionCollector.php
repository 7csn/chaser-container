<?php

declare(strict_types=1);

namespace chaser\container\collector;

use chaser\container\definition\{ClassDefinition,
    ClosureDefinition,
    DefinitionInterface,
    FunctionDefinition,
    MethodDefinition
};
use chaser\container\exception\DefinedException;
use Closure;

/**
 * 定义收集器
 *
 * @package chaser\container\collector
 */
class DefinitionCollector
{
    /**
     * 类名定义库：以【类名】为源
     *
     * @var array [$classname => ClassDefinition]
     */
    protected static array $classDefinitions = [];

    /**
     * 类函数名定义库：以【类名::函数名】为源
     *
     * @var array [$classname::$methodName => MethodDefinitions]
     */
    protected static array $methodDefinitions = [];

    /**
     * 方法名定义库：以【方法名】为源
     *
     * @var array [$functionName => FunctionDefinition]
     */
    protected static array $functionDefinitions = [];

    /**
     * 创建定义
     *
     * @param mixed $source
     * @return DefinitionInterface
     * @throws DefinedException
     */
    public static function make($source): DefinitionInterface
    {
        $definition = self::makeSafely($source);

        if ($definition) {
            return $definition;
        }

        throw new DefinedException;
    }

    /**
     * 安全地创建定义
     *
     * @param mixed $source
     * @return DefinitionInterface|null
     */
    public static function makeSafely($source): ?DefinitionInterface
    {
        if ($source instanceof Closure) {
            $definition = self::closure($source);
        } elseif (is_string($source)) {
            $definition = self::get($source);
        }

        if (isset($definition) && $definition->isResolvable()) {
            return $definition;
        }

        return null;
    }

    /**
     * 获取标识符定义
     *
     * @param string $id
     * @return DefinitionInterface
     */
    public static function get(string $id): DefinitionInterface
    {
        return is_callable($id)
            ? strpos($id, '::')
                ? self::method(...explode('::', $id))
                : self::function($id)
            : self::class($id);
    }

    /**
     * 获取指定类名的定义对象
     *
     * @param string $classname
     * @return ClassDefinition
     */
    public static function class(string $classname): ClassDefinition
    {
        return self::$classDefinitions[$classname] ??= new ClassDefinition($classname);
    }

    /**
     * 获取指定闭包的定义对象
     *
     * @param Closure $closure
     * @return ClosureDefinition
     */
    public static function closure(Closure $closure): ClosureDefinition
    {
        return new ClosureDefinition($closure);
    }

    /**
     * 获取指定方法名名的定义对象
     *
     * @param string $functionName
     * @return FunctionDefinition
     */
    public static function function(string $functionName): FunctionDefinition
    {
        return self::$functionDefinitions[$functionName] ??= new FunctionDefinition($functionName);
    }

    /**
     * 获取指定类函数名的定义对象
     *
     * @param string $classname
     * @param string $methodName
     * @return MethodDefinition
     */
    public static function method(string $classname, string $methodName): MethodDefinition
    {
        return self::$methodDefinitions[$classname . '::' . $methodName] ??= new MethodDefinition($classname, $methodName);
    }
}
