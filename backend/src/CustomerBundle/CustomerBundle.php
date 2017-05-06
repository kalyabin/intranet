<?php

namespace CustomerBundle;

use CustomerBundle\DependencyInjection\CustomerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Модуль работы с контрагентами
 *
 * @package CustomerBundle
 */
class CustomerBundle extends Bundle
{
    public function getContainerExtension(): CustomerExtension
    {
        return new CustomerExtension();
    }
}
