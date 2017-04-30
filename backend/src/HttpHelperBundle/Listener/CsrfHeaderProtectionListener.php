<?php

namespace HttpHelperBundle\Listener;

use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Проверяет в запросе наличие заголовка с подписью X-CSRF-Token и проверяет в этом заголовке наличие токена с нужным идентификатором.
 *
 * @inheritdoc
 *
 * @package HttpHelperBundle\Listener
 */
class CsrfHeaderProtectionListener extends AbstractCsrfProtectionListener
{
    /**
     * Возвращает true, если внутри метода контроллера отключена проверка токенов
     *
     * @param \ReflectionMethod $method
     *
     * @return bool
     */
    public function isDisabledMethod(\ReflectionMethod $method)
    {
        $annotation = $this->annotationReader->getMethodAnnotation($method, $this->getDisabledAnnotationClass());
        return $annotation !== null;
    }

    /**
     * Возвращает true, если внутри класса контроллера отключена проверка токенов
     *
     * @param \ReflectionClass $class
     *
     * @return bool
     */
    public function isDisabledClass(\ReflectionClass $class)
    {
        $annotation = $this->annotationReader->getClassAnnotation($class, $this->getDisabledAnnotationClass());
        return $annotation !== null;
    }

    /**
     * Возвращает true, если токен был передан и он валиден.
     *
     * @return boolean
     */
    public function isTokenValid()
    {
        $token = $this->requestStack->getMasterRequest()->headers->get($this->tokenHeader);
        return $this->tokenProvider->isTokenValid(
            new CsrfToken($this->tokenIntention, $token)
        );
    }
}
