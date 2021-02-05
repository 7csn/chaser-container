<?php

declare(strict_types=1);

namespace chaser\container;

use chaser\container\exception\{NotFoundException, ResolvedException};
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * 解析器
 *
 * @package chaser\container
 */
class Resolver
{
    /**
     * 类方法参数库
     *
     * @var Parameter[][] [$classname => [$methodName => Parameter]]
     */
    private static array $methodParameters = [];

    /**
     * 函数参数库
     *
     * @var Parameter[] [$functionName => Parameter]
     */
    private static array $functionParameters = [];

    /**
     * 容器
     *
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * 初始化解析调度器
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 类型解析
     *
     * @param ReflectionClass $reflection
     * @param array $arguments
     * @return object
     * @throws NotFoundException
     * @throws ResolvedException
     */
    public function classAction(ReflectionClass $reflection, array $arguments = []): object
    {
        if (null === $constructor = $reflection->getConstructor()) {
            try {
                return $reflection->newInstanceWithoutConstructor();
            } catch (ReflectionException) {
                $message = sprintf('Class[%s] is an internal class that cannot be instantiated without invoking the constructor.', $reflection->name);
                throw $this->exception($message);
            }
        }

        $parameters = self::getMethodParameters($constructor);
        $dependencies = $this->dependencies($parameters, $arguments);

        try {
            return $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException) {
            $message = sprintf('Class[%s] constructor is not public.', $reflection->name);
            throw $this->exception($message);
        }
    }

    /**
     * 类方法解析
     *
     * @param ReflectionMethod $reflection
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     * @throws ResolvedException
     */
    public function methodAction(ReflectionMethod $reflection, array $arguments = []): mixed
    {
        $object = null;

        if ($reflection->isStatic() === false) {
            $objectParameters = $arguments['$object'] ?? [];
            $object = $this->container->make($reflection->class, $objectParameters);
        }

        $parameters = self::getMethodParameters($reflection);
        $dependencies = $this->dependencies($parameters, $arguments);

        try {
            return $reflection->invokeArgs($object, $dependencies);
        } catch (ReflectionException) {
            $message = sprintf('Method[%s::%s] invocation failed.', $reflection->class, $reflection->name);
            throw $this->exception($message);
        }
    }

    /**
     * 函数解析
     *
     * @param ReflectionFunction $reflection
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     * @throws ResolvedException
     */
    public function functionAction(ReflectionFunction $reflection, array $arguments = []): mixed
    {
        $parameters = self::getFunctionParameters($reflection);
        $dependencies = $this->dependencies($parameters, $arguments);
        return $reflection->invokeArgs($dependencies);
    }

    /**
     * 获取类方法反射参数列表
     *
     * @param ReflectionMethod $method
     * @return Parameter[]
     */
    private static function getMethodParameters(ReflectionMethod $method): array
    {
        return self::$methodParameters[$method->class][$method->name]
            ??= self::getParameters($method, $method->class . '::' . $method->name);
    }

    /**
     * 获取函数反射参数列表
     *
     * @param ReflectionFunction $function
     * @return Parameter[]
     */
    private static function getFunctionParameters(ReflectionFunction $function): array
    {
        return $function->name === '{closure}'
            ? self::getParameters($function, $function->name)
            : (self::$functionParameters[$function->name] ??= self::getParameters($function, $function->name));
    }

    /**
     * 获取调用反射参数列表
     *
     * @param ReflectionMethod|ReflectionFunction $caller
     * @param string $callName
     * @return array
     */
    private static function getParameters(ReflectionMethod|ReflectionFunction $caller, string $callName): array
    {
        return array_map(fn($parameter) => new Parameter($parameter, $callName), $caller->getParameters());
    }

    /**
     * 参数列表依赖
     *
     * @param array $parameters
     * @param array $arguments
     * @return array
     * @throws NotFoundException
     * @throws ResolvedException
     */
    private function dependencies(array $parameters, array $arguments): array
    {
        return array_map(fn(Parameter $parameter) => $this->dependency($parameter, $arguments), $parameters);
    }

    /**
     * 参数依赖
     *
     * @param Parameter $parameter
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     * @throws ResolvedException
     */
    private function dependency(Parameter $parameter, array $arguments): mixed
    {
        $position = $parameter->position();
        if (key_exists($position, $arguments)) {
            return $arguments[$position];
        }

        $name = $parameter->name();
        if (key_exists($name, $arguments)) {
            return $arguments[$name];
        }

        $classnames = $parameter->classes();

        if (empty($classnames)) {
            return $this->makeValue($parameter);
        }

        $args = $arguments['$' . $name] ?? [];

        if (empty($args) === false) {
            foreach ($classnames as $classname) {
                if (isset($args[$classname])) {
                    return $this->container->make($classname, $args[$classname]);
                }
            }
        }

        return $this->container->make(current($classnames), $args);
    }

    /**
     * 参数取值
     *
     * @param Parameter $parameter
     * @return mixed
     * @throws ResolvedException
     */
    private function makeValue(Parameter $parameter): mixed
    {
        if ($parameter->allowNull()) {
            return null;
        }

        if (null !== $default = $parameter->defaultValue()) {
            return $default;
        }

        $message = sprintf('Parameter[%s] of %s has no value provided.', $parameter->name(), $parameter->callName());
        throw $this->exception($message);
    }

    /**
     * 创建解析异常
     *
     * @param string $message
     * @return ResolvedException
     */
    private function exception(string $message): ResolvedException
    {
        return new ResolvedException(sprintf('Resolving exception: %s%s%s', $this->resolving(), PHP_EOL, $message));
    }

    /**
     * 解析进程串
     *
     * @return string
     */
    private function resolving(): string
    {
        return join(',', $this->container->getResolveStack());
    }
}
