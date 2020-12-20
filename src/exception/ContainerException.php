<?php

declare(strict_types=1);

namespace chaser\container\exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * 容器操作异常类
 *
 * @package chaser\container\exception
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * 容易异常：补充解析标识符前缀消息
     *
     * @param Exception $exception
     * @param array $makes
     * @return static
     */
    public static function create(Exception $exception, array $makes = [])
    {
        return new static('Making exception: ' . implode(',', $makes) . PHP_EOL . $exception->getMessage());
    }
}
