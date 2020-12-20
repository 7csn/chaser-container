<?php

declare(strict_types=1);

namespace chaser\container\exception;

/**
 * 反射异常类
 *
 * @package chaser\container\exception
 */
class ReflectedException extends Exception
{
    /**
     * 错误码：类不存在
     */
    public const CODE_CLASS_NOT_EXIST = 1;

    /**
     * 错误码：类函数不存在
     */
    public const CODE_METHOD_NOT_EXIST = 2;

    /**
     * 错误码：类属性不存在
     */
    public const CODE_PROPERTY_NOT_EXIST = 3;

    /**
     * 错误码：方法不存在
     */
    public const CODE_FUNCTION_NOT_EXIST = 4;
}
