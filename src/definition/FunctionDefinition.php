<?php

declare(strict_types=1);

namespace chaser\container\definition;

use chaser\container\collector\ReflectionCollector;
use chaser\container\ContainerInterface;
use chaser\container\exception\ReflectedException;
use chaser\container\exception\ResolvedException;
use chaser\container\resolver\FunctionResolver;
use ReflectionFunction;
use TypeError;

/**
 * 方法名定义
 *
 * @package chaser\container\definition
 *
 * @property ?ReflectionFunction $reflector
 */
class FunctionDefinition extends Definition
{
    /**
     * 定义基础分析
     *
     * @param string $functionName
     */
    public function __construct(string $functionName)
    {
        $this->name = $functionName;
        try {
            $this->reflector = ReflectionCollector::function($functionName);
            $this->isResolvable = true;
        } catch (ReflectedException $e) {
        }
    }

    /**
     * @inheritDoc
     */
    public function resolver(ContainerInterface $container): FunctionResolver
    {
        try {
            return new FunctionResolver($container, $this->reflector);
        } catch (TypeError $e) {
            throw new ResolvedException("Function[{$this->name}] does not exists");
        }
    }
}
