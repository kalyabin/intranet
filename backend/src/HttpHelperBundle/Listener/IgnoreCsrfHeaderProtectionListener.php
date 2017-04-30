<?php

namespace HttpHelperBundle\Listener;


/**
 * Отключить проверку на CSRF заголовки (например, для unit-тестов)
 *
 * @package HttpHelperBundle\Listener
 */
class IgnoreCsrfHeaderProtectionListener extends AbstractCsrfProtectionListener
{
    /**
     * @inheritdoc
     */
    public function isDisabledClass(\ReflectionClass $class)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isDisabledMethod(\ReflectionMethod $method)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid()
    {
        return true;
    }
}
