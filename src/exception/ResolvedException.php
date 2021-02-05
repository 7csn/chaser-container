<?php

declare(strict_types=1);

namespace chaser\container\exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * 解析异常
 *
 * @package chaser\container\exception
 */
class ResolvedException extends Exception implements ContainerExceptionInterface
{
}
