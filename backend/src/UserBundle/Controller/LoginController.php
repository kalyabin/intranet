<?php

namespace UserBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use UserBundle\Controller\Response\AuthenticationJsonResponse;

/**
 * Авторизация пользователя
 *
 * @Route(service="user.login_controller")
 *
 * @package UserBundle\Controller
 */
class LoginController extends Controller
{
    /**
     * Проверка авторизации
     *
     * @Method({"POST"})
     * @Route("/login/check", options={"expose" : true}, name="login.simple_check")
     *
     * @return AuthenticationJsonResponse
     */
    public function simpleLoginCheckAction(): AuthenticationJsonResponse
    {
        $response = new AuthenticationJsonResponse();

        /** @var AuthenticationUtils $authenticationUtils */
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error instanceof BadCredentialsException || $error instanceof AuthenticationException) {
            $response->handleFailure($error);
        } else {
            $response->handleFailRequest();
        }

        return $response;
    }

    /**
     * Показать форму авторизации по логин-паролю
     *
     * @Method({"GET"})
     *
     * @Route("/login", options={"expose" : true}, name="login.simple_form")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function simpleLoginAction(): Response
    {
        return $this->redirect('/#/sign-in');
    }
}
