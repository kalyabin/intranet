<?php

namespace HttpHelperBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Если пришел запрос с Content-type = application/json, то заменяет POST-параметры данными из тела запроса
 *
 * @package HttpHelperBundle\Listener
 */
class JsonRequestListener
{
    /**
     * Replace data if method is POST and request content-type is JSON
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $requestContent = is_string($request->getContent()) ?
            trim($request->getContent()) : '';

        if (strtolower($request->getMethod()) == 'post' && $request->getContentType() == 'json' && !empty($requestContent)) {
            $decoder = new JsonDecode(true);
            try {
                $data = $decoder->decode($request->getContent(), JsonEncoder::FORMAT);
                $request->request->replace($data);
            } catch (\Exception $ex) {
                // возможно неправильный JSON
                // генерируем 400-ю ошибку
                throw new HttpException(400, null, $ex);
            }
        }
    }

}
