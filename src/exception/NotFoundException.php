<?php

declare(strict_types=1);

namespace chaser\container\exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * 标识符实体未找到异常
 *
 * @package chaser\container\exception
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{

}
