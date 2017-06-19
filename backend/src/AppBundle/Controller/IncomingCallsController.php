<?php

namespace AppBundle\Controller;

use AppBundle\Event\IncomingCallNotificationEvent;
use AppBundle\Form\Type\IncomingCallResendType;
use Doctrine\ORM\EntityManagerInterface;
use HttpHelperBundle\Annotation\DisableCsrfProtection;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;
use UserBundle\Utils\UserManager;

/**
 * Управление входящими звонками: получение входящих звонков секретарями и переотправка их арендаторам
 *
 * @Route(service="incoming_calls_controller")
 * @package AppBundle\Controller
 */
class IncomingCallsController extends Controller
{
    /**
     * @var RolesManager
     */
    protected $rolesManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(RolesManager $rolesManager, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->rolesManager = $rolesManager;
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Получение входящих звонков с АТС
     *
     * @DisableCsrfProtection()
     * @Method({"POST"})
     * @Route("/incoming-call", name="incoming-calls.receive", options={"expose": true})
     *
     * @Security("has_role('ROLE_ATS')")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function receiveIncomingCallAction(Request $request): JsonResponse
    {
        $callerId = $request->get('callerId');
        if (!is_scalar($callerId) || !is_string($callerId) || empty($callerId)) {
            throw new BadRequestHttpException('Caller id must be a string');
        }

        $receivers = [];

        // получить всех пользователей-менеджеров с правом получения входящих звонков
        $roles = $this->rolesManager->getParentRoles('ROLE_INCOMING_CALLS_MANAGEMENT');
        $res = $this->userRepository->findByRole($roles);
        foreach ($res as $batch) {
            foreach ($batch as $user) {
                /** @var UserEntity $user */
                $event = new IncomingCallNotificationEvent(null, [
                    'receiver' => $user,
                    'callerId' => $callerId
                ]);
                $this->eventDispatcher->dispatch('user_notification', $event);
                $receivers[] = $user->getEmail();
            }
        }

        return new JsonResponse([
            'success' => count($receivers) > 0,
            'receivers' => $receivers
        ]);
    }

    /**
     * Переотправка входящих звонков от менеджера контрагенту
     *
     * @Security("has_role('ROLE_INCOMING_CALLS_MANAGEMENT')")
     * @Method({"POST"})
     * @Route("/resend-incoming-call", name="incoming-calls.resend", options={"expose": true})
     *
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function resendIncomingCallAction(Request $request): FormValidationJsonResponse
    {
        $formData = new IncomingCallResendType();

        $form = $this->createForm(IncomingCallResendType::class, $formData);
        $form->handleRequest($request);

        $receivers = [];

        if ($form->isSubmitted() && $form->isValid()) {
            // отправка уведомления всем пользователям контрагента
            $roles = $this->rolesManager->getParentRoles('ROLE_INCOMING_CALLS_CUSTOMER');
            $res = $this->userRepository->findByRoleAndCustomer($roles, $formData->customer);
            foreach ($res as $batch) {
                foreach ($batch as $user) {
                    /** @var UserEntity $user */
                    $event = new IncomingCallNotificationEvent(null, [
                        'receiver' => $user,
                        'callerId' => $formData->callerId,
                        'comment' => $formData->comment
                    ]);
                    $this->eventDispatcher->dispatch('user_notification', $event);
                    $receivers[] = $user->getEmail();
                }
            }
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'success' => count($receivers) > 0,
        ];
        $response->handleForm($form);
        return $response;
    }
}
