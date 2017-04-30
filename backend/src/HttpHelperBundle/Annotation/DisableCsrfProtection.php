<?php

namespace HttpHelperBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;


/**
 * Аннотация для отключения проверки токенов CSRF.
 *
 * Может быть подключена к методу или классу-контроллеру.
 *
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 *
 * @package HttpHelperBundle\Annotation
 */
class DisableCsrfProtection
{

}
