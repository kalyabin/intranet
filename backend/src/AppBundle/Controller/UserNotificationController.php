<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Repository\UserNotificationRepository;
use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Utils\UserNotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\UserEntity;

/**
 * Уведомления для пользователя
 *
 * @Security("is_fully_authenticated()")
 * @Route(service="notification_controller")
 *
 * @package AppBundle\Controller
 */
class UserNotificationController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserNotificationManager
     */
    protected $notificationManager;

    /**
     * @var UserNotificationRepository
     */
    protected $notificationRepository;

    /**
     * UserNotificationController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserNotificationManager $notificationManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserNotificationManager $notificationManager)
    {
        $this->entityManager = $entityManager;
        $this->notificationManager = $notificationManager;
        $this->notificationRepository = $entityManager->getRepository(UserNotificationEntity::class);
    }

    /**
     * Получить список непрочитанных уведомлений
     *
     * @Method({"GET"})
     * @Route("/notifications", name="notifications", options={"expose": true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unreadListAction(Request $request): JsonResponse
    {
        $limit = $request->get('limit', null);
        $limit = is_scalar($limit) ? (int) $limit : null;

        /** @var UserEntity $user */
        $user = $this->getUser();

        $list = $this->notificationRepository->findAllUnreadUserNotification($user, $limit);

        return new JsonResponse([
            'list' => $list
        ]);
    }

    /**
     * Пометить все сообщения как прочитанные
     *
     * @Method({"POST"})
     * @Route("/notifications/read-all", name="notifications.read", options={"expose": true})
     *
     * @return JsonResponse
     */
    public function setAllRead(): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        $result = $this->notificationManager->setAllNotificationIsRead($user);

        return new JsonResponse([
            'updateMessages' => $result
        ]);
    }
}
