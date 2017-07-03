<?php

namespace RentBundle;

use RentBundle\DependencyInjection\RentExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Модуль аренды (переговорки, другие помещения)
 */
class RentBundle extends Bundle
{
    public function getContainerExtension(): RentExtension
    {
        return new RentExtension();
    }
}
