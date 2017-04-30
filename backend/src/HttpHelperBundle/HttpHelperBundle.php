<?php

namespace HttpHelperBundle;

use HttpHelperBundle\DependencyInjection\HttpHelperExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Модуль для дополнительных хелперов работы с HTTP-запросами
 *
 * @package HttpHelperBundle
 */
class HttpHelperBundle extends Bundle
{
    public function getContainerExtension(): HttpHelperExtension
    {
        return new HttpHelperExtension();
    }
}
