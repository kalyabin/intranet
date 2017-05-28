<?php

namespace TicketBundle\Controller;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\CustomerRepository;
use Doctrine\Common\Persistence\ObjectManager;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use HttpHelperBundle\Response\ListJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TicketBundle\Entity\Repository\TicketCategoryRepository;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Form\Type\TicketMessageType;
use TicketBundle\Form\Type\TicketType;
use TicketBundle\Utils\TicketManager;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Контроллер для работы с тикетами.
 * Как со стороны менеджера, так и со стороны арендатора.
 *
 * @Security("is_authenticated()")
 * @Route(service="ticket.ticket_controller")
 *
 * @package TicketBundle\Controller
 */
class TicketController extends Controller
{
    /**
     * @var TicketManager
     */
    protected $ticketManager;

    /**
     * @var TicketRepository
     */
    protected $ticketRepository;

    /**
     * @var TicketCategoryRepository
     */
    protected $ticketCategoryRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var RolesManager
     */
    protected $rolesManager;

    /**
     * TicketController constructor.
     *
     * @param TicketManager $ticketManager
     * @param RolesManager $rolesManager
     * @param ObjectManager $entityManager
     */
    public function __construct(TicketManager $ticketManager, RolesManager $rolesManager, ObjectManager $entityManager)
    {
        $this->ticketManager = $ticketManager;
        $this->rolesManager = $rolesManager;
        $this->ticketRepository = $entityManager->getRepository(TicketEntity::class);
        $this->ticketCategoryRepository = $entityManager->getRepository(TicketCategoryEntity::class);
        $this->customerRepository = $entityManager->getRepository(CustomerEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
    }

    /**
     * Поиск категории.
     *
     * Если у пользователя нет доступа к этой категории - генерирует 403-ю ошибку.
     * Если категория не найдена - генерирует 404-ю ошибку.
     *
     * @param string $id
     * @param string $access Право доступа: просмотр, редактирование создание
     *
     * @return TicketCategoryEntity
     */
    protected function findCategory(string $id, string $access = 'view'): TicketCategoryEntity
    {
        $category = $this->ticketCategoryRepository->findOneById($id);
        if (!$category) {
            throw $this->createNotFoundException('Категория не найдена');
        }
        $this->denyAccessUnlessGranted($access, $category, 'У вас нет права для просмотра данной очереди');

        return $category;
    }

    /**
     * Получение тикета.
     *
     * Если у пользователя нет доступа к данному тикету - генерирует 403-ю ошибку.
     * Если тикет на найден - генерирует 404-ю ошибку.
     *
     * @param string $category Идентификатор категории
     * @param int $id Идентификатор тикета
     * @param string $access Право доступа к тикету (по умолчанию - просмотр)
     *
     * @return TicketEntity
     */
    protected function findTicket(string $category, int $id, string $access = 'view'): TicketEntity
    {
        $ticket = $this->ticketRepository->findOneByIdAndCategory($id, $category);
        if (!$ticket) {
            throw $this->createNotFoundException('Заявка не найдена');
        }
        $this->denyAccessUnlessGranted($access == 'create' ? 'create' : $access, $ticket->getCategory(), 'У вас нет права для просмотра данной очереди');
        $this->denyAccessUnlessGranted($access, $ticket, 'У вас нет прав производить данное действие');

        return $ticket;
    }

    /**
     * Получить доступные категории для пользователя
     *
     * @Method({"GET"})
     * @Route("/ticket", name="ticket.categories", options={"expose": true})
     *
     * @return JsonResponse
     */
    public function categoriesAction(): JsonResponse
    {
        /** @var TicketCategoryEntity[] $result */
        $result = [];

        foreach ($this->ticketCategoryRepository->findAll() as $category) {
            /** @var TicketCategoryEntity $category */
            if ($this->isGranted('view', $category)) {
                $result[] = $category;
            }
        }

        return new JsonResponse([
            'list' => $result
        ]);
    }

    /**
     * Получить доступный список тикетов для пользователя.
     *
     * В запросе необходимо передавать:
     * - customer - идентификатор котрагента;
     * - opened - флаг для получения только открытых заявок.
     * - page - номер текущей страницы для постраничной навигации
     * - pageSize - количество элементов на странице.
     *
     * @Method({"GET"})
     * @Route("/ticket/{category}", name="ticket.list", options={"expose": true}, requirements={"category": "\w+"})
     *
     * @param string $category
     * @param Request $request
     *
     * @return ListJsonResponse
     */
    public function listAction(string $category, Request $request): ListJsonResponse
    {
        $category = $this->findCategory($category);

        // фильтр по контрагенту
        $customer = (int) $request->get('customer', null);
        if ($customer) {
            // получить контрагента и проверить право доступа к нему
            $customer = $this->customerRepository->findOneById($customer);

            if (!$customer) {
                throw $this->createNotFoundException('Арендатор не найден');
            }

            $this->denyAccessUnlessGranted('view', $customer, 'У вас нет доступа для просмотра данного арендатора');
        }

        $opened = $request->get('opened', true);
        $opened = $opened == 1 || $opened === true;

        $pageSize = (int) $request->get('pageSize', 500);
        $pageSize = min(100, $pageSize);

        $pageNum = (int) $request->get('pageNum', 1);
        $offset = $pageNum - 1;
        $offset = max(0, $offset);

        $totalCount = (int) $this->ticketRepository
            ->findAllByFilter($category->getId(), $customer, $opened)
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $list = $this->ticketRepository
            ->findAllByFilter($category->getId(), $customer, $opened)
            ->setFirstResult($offset)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();


        return new ListJsonResponse($list, $pageSize, $pageNum, $totalCount);
    }

    /**
     * Создать новый тикет
     *
     * @Method({"POST"})
     * @Route("/ticket/{category}", name="ticket.create", options={"expose": true}, requirements={"category": "\w+"})
     *
     * @param string $category Категория в которой создать тикет
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createTicketAction(string $category, Request $request): FormValidationJsonResponse
    {
        $category = $this->findCategory($category, 'create');

        $formType = new TicketType();
        $form = $this->createForm(TicketType::class, $formType);
        $form->handleRequest($request);

        $ticket = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket = $this->ticketManager->createTicket($formType, $category, $this->getUser());
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'ticket' => $ticket,
            'success' => $ticket instanceof TicketEntity && $ticket->getId() > 0
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Просмотр тикета
     *
     * @Method({"GET"})
     * @Route(
     *     "/ticket/{category}/{ticket}",
     *     name="ticket.details",
     *     options={"expose": true}, requirements={
     *          "category": "\w+",
     *          "ticket": "\d+"
     *     }
     * )
     *
     * @param string $category Идентификатор категории
     * @param int $ticket Идентификатор тикета внутри категории
     *
     * @return JsonResponse
     */
    public function detailsAction(string $category, int $ticket): JsonResponse
    {
        $ticket = $this->findTicket($category, $ticket, 'view');

        return new JsonResponse([
            'ticket' => $ticket,
            'messages' => $ticket->getMessage()->getValues(),
            'history' => $ticket->getHistory()->getValues(),
        ]);
    }

    /**
     * Создать сообщение внутри тикета
     *
     * @Method({"PUT"})
     * @Route(
     *     "/ticket/{category}/{id}",
     *     name="ticket.message",
     *     options={"expose": true}, requirements={
     *          "category": "\w+",
     *          "id": "\d+"
     *     }
     * )
     *
     * @param string $category Идентификатор категории тикета
     * @param int $ticket Идентификатор тикета
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function messageAction(string $category, int $ticket, Request $request): FormValidationJsonResponse
    {
        $ticket = $this->findTicket($category, $ticket, 'message');

        $messageType = new TicketMessageType();
        $form = $this->createForm(TicketMessageType::class, $messageType);
        $form->handleRequest($request);

        $message = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserEntity $user */
            $user = $this->getUser();

            // тип сообщения в зависимости от типа пользователя
            $type = $this->ticketManager->getMessageTypeByUser($user);
            $message = $this->ticketManager->createTicketMessage($ticket, $messageType, $type, $user);
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'ticket' => $ticket,
            'message' => $message,
            'success' => $message instanceof TicketCategoryEntity && $message->getId() > 0
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Закрытие тикета
     *
     * @Method({"POST"})
     * @Route(
     *     "/ticket/{category}/{id}/close",
     *     name="ticket.close",
     *     options={"expose": true}, requirements={
     *          "category": "\w+",
     *          "id": "\d+"
     *     }
     * )
     *
     * @param string $category Категория тикета
     * @param int $ticket Идентификатор тикета внутри категории
     *
     * @return JsonResponse
     */
    public function closeAction(string $category, int $ticket): JsonResponse
    {
        $ticket = $this->findTicket($category, $ticket, 'update');

        /** @var UserEntity $user */
        $user = $this->getUser();
        $success = $this->ticketManager->closeTicket($ticket, $user);

        return new JsonResponse([
            'ticket' => $ticket,
            'success' => $success,
        ]);
    }

    /**
     * Получить список менеджеров, занимающихся управлением определенной категорией тикетов.
     *
     * Доступ только для администратора тикетной системы.
     *
     * @Security("has_role('ROLE_TICKET_ADMIN_MANAGEMENT')")
     * @Method({"GET"})
     * @Route("/ticket/{category}/managers", options={"expose": true}, requirements={"category": "\w+"})
     *
     * @param string $category
     *
     * @return JsonResponse
     */
    public function managersAction(string $category): JsonResponse
    {
        $category = $this->findCategory($category);
        $roles = $this->rolesManager->getParentRoles($category->getManagerRole());
        $res = $this->userRepository->findByRole($roles, UserEntity::STATUS_ACTIVE);

        /** @var UserEntity[] $list */
        $list = [];

        foreach ($res as $users) {
            foreach ($users as $user) {
                $list[] = $user;
            }
        }

        return new JsonResponse([
            'list' => $list
        ]);
    }

    /**
     * Назначить ответственного менеджера для тикета.
     *
     * По умолчанию назначаемый менеджер - текущий пользователь.
     * Если текущий пользователь является администратором тикетной системы, то он может назначать других менеджеров.
     *
     * @Method({"POST"})
     * @Route(
     *     "/ticket/{category}/{id}/assign",
     *     name="ticket.assign",
     *     options={"expose": true},
     *     requirements={
     *          "category": "\w+",
     *          "id": "\d+"
     *     }
     * )
     *
     * @param string $category Идентификатор тикетной категории
     * @param int $ticket Идентификатор тикета
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function assignAction(string $category, int $ticket, Request $request): JsonResponse
    {
        $ticket = $this->findTicket($category, $ticket, 'assign');

        // назначаемый менеджер (по умолчанию - текущий пользователь)
        $user = $this->getUser();
        $setNewUser = (int) $request->get('managerId', null);
        if ($setNewUser) {
            // назначение другого пользователя для тикета
            // может делать только администратор тикетной системы
            $this->denyAccessUnlessGranted('ROLE_TICKET_ADMIN_MANAGEMENT');
            // проверить есть ли такой пользователь с таким правом доступа или нет
            $user = null;
            $roles = $this->rolesManager->getParentRoles($ticket->getCategory()->getId());
            $res = $this->userRepository->findByRole($roles, UserEntity::STATUS_ACTIVE);
            foreach ($res as $users) {
                foreach ($users as $item) {
                    /** @var UserEntity $item */
                    if ($item->getId() == $setNewUser) {
                        $user = $item;
                        break;
                    }
                }
            }

            if (!$user) {
                throw $this->createNotFoundException('Менеджер заявок не найден');
            }
        }

        $success = $this->ticketManager->appointTicketToManager($ticket, $user);

        return new JsonResponse([
            'ticket' => $ticket,
            'success' => $success,
            'user' => $user,
        ]);
    }
}
