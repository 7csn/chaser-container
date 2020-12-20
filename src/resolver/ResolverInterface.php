<?php

declare(strict_types=1);

namespace chaser\container\resolver;

use chaser\container\exception\ResolvedException;

/**
 * 反射解析器接口
 *
 * @package chaser\container\resolver
 */
interface ResolverInterface
{
    /**
     * 解析器是否有效
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * 根据提供参数解析反射实体并返回
     *
     * @param array $parameters
     * @return mixed
     * @throws ResolvedException
     */
    public function action(array $parameters = []);
}
