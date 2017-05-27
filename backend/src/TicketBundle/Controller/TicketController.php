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
use TicketBundle\Form\Type\TicketType;
use TicketBundle\Utils\TicketManager;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Контроллер для работы с тикетами.
 * Как со стороны менеджера, так и со стороны арендатора.
 *
 * @Security("is_authenticated()")
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

        return new JsonResponse($result);
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
        $categoryEntity = $this->ticketCategoryRepository->findOneById($category);

        if (!$categoryEntity) {
            throw $this->createNotFoundException('Категория не найдена');
        }

        $this->denyAccessUnlessGranted('view', $categoryEntity, 'У вас нет доступа к данной очереди');

        // фильтр по контрагенту
        $customerId = (int) $request->get('customer', null);
        if ($customerId) {
            // получить контрагента и проверить право доступа к нему
            $customer = $this->customerRepository->findOneById($customerId);

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
            ->findAllByFilter($categoryEntity->getId(), $customerId, $opened)
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $list = $this->ticketRepository
            ->findAllByFilter($categoryEntity->getId(), $customerId, $opened)
            ->setFirstResult($offset)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();


        return new ListJsonResponse($list, $pageSize, $pageNum, $totalCount);
    }

    /**
     * Создать новый тикет
     *
     * @Method({"GET"})
     * @Route("/ticket/{category}", name="ticket.create", options={"expose": true}, requirements={"category": "\w+"})
     *
     * @param string $category Категория в которой создать тикет
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createTicketAction(string $category, Request $request): FormValidationJsonResponse
    {
        $categoryEntity = $this->ticketCategoryRepository->findOneById($category);
        if (!$categoryEntity) {
            throw $this->createNotFoundException('Категория не найдена');
        }
        $this->denyAccessUnlessGranted('create', $categoryEntity);

        $formType = new TicketType();
        $form = $this->createForm(TicketType::class, $formType);
        $form->handleRequest($request);

        $ticket = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket = $this->ticketManager->createTicket($formType, $categoryEntity, $this->getUser());
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'ticket' => $ticket,
            'success' => $ticket instanceof TicketEntity && $ticket->getId() > 0
        ];
        $response->handleForm($form);
        return $response;
    }
}
