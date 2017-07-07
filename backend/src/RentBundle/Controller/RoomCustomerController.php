<?php

namespace RentBundle\Controller;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\EntityManagerInterface;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use HttpHelperBundle\Response\ListJsonResponse;
use RentBundle\Entity\Repository\RoomRepository;
use RentBundle\Entity\Repository\RoomRequestRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Form\Type\RoomRequestType;
use RentBundle\Utils\RoomRequestManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\UserEntity;

/**
 * Просмотр комнат арендатором
 *
 * @Route(service="rent.room_customer.controller")
 * @Security("has_role('ROLE_RENT_CUSTOMER')")
 *
 * @package RentBundle\Controller
 */
class RoomCustomerController extends Controller
{
    /**
     * @var RoomRepository
     */
    protected $roomRepository;

    /**
     * @var RoomRequestRepository
     */
    protected $roomRequestRepository;

    /**
     * @var RoomRequestManager
     */
    protected $requestManager;

    public function __construct(RoomRequestManager $requestManager)
    {
        $this->requestManager = $requestManager;
        $this->roomRepository = $requestManager->getEntityManager()->getRepository(RoomEntity::class);
        $this->roomRequestRepository = $requestManager->getEntityManager()->getRepository(RoomRequestEntity::class);
    }

    /**
     * Получение помещения по идентификатору.
     *
     * Если не найдено - генерирует 404.
     *
     * @param int $id
     *
     * @return RoomEntity
     */
    protected function getRoomById(int $id): RoomEntity
    {
        $entity = $this->roomRepository->findOneById($id);

        if (!$entity) {
            throw $this->createNotFoundException('Помещение не найдено');
        }

        return $entity;
    }

    /**
     * Список комнат
     *
     * @Method({"GET"})
     * @Route("/customer/room", name="room.customer.list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function listAction(): ListJsonResponse
    {
        $list = $this->roomRepository->findAll();

        return new ListJsonResponse($list, count($list), 0, count($list));
    }

    /**
     * Получить детальную информацию о комнате, включая календарь.
     *
     * @Method({"GET"})
     * @Route("/customer/room/{id}", name="room.customer.details", requirements={"id": "\d+"}, options={"expose": true})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function detailsAction(int $id): JsonResponse
    {
        $room = $this->getRoomById($id);

        /** @var UserEntity $user */
        $user = $this->getUser();
        /** @var CustomerEntity $customer */
        $customer = $user->getCustomer();

        // получить заявки но вернуть их в скрытом виде
        // при этом заявки арендатора по комнате будут возвращены в специальном массиве
        // для того, чтобы он всегда видел в календаре свои заявки
        $customerRequest = [];
        $reservedRequests = [];
        foreach ($this->roomRequestRepository->findActualByRoom($room) as $request) {
            if (!$request->isOpened()) {
                continue;
            }
            if ($request->getCustomer()->getId() == $customer->getId()) {
                $customerRequest[] = $request;
            } else {
                $data = $request->jsonSerialize();
                $reservedRequests[] = [
                    'from' => $data['from'],
                    'to' => $data['to'],
                ];
            }
        }

        return new JsonResponse([
            'room' => $room,
            'myRequests' => $customerRequest,
            'reserved' => $reservedRequests,
        ]);
    }

    /**
     * Получить список актуальных заявок текущего арендатора.
     *
     * @Method({"GET"})
     * @Route("/customer/room/request", name="room.customer.request_list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function actualRequestListAction(): ListJsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();
        /** @var CustomerEntity $customer */
        $customer = $user->getCustomer();

        $list = $this->roomRequestRepository->findActualByCustomer($customer);

        return new ListJsonResponse($list, count($list), 0, count($list));
    }

    /**
     * Отправить заявку на бронирование переговорки
     *
     * @Method({"POST"})
     * @Route("/customer/room/request", name="room.customer.request_create", options={"expose": true})
     *
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createRequestAction(Request $request): FormValidationJsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();
        /** @var CustomerEntity $customer */
        $customer = $user->getCustomer();

        $entity = new RoomRequestEntity();
        $entity->setCustomer($customer);

        $form = $this->createForm(RoomRequestType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->requestManager->createRequest($entity, $user);
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'success' => $entity->getId() > 0,
            'request' => $entity,
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Отмена заявки на бронирование комнаты
     *
     * @Method({"DELETE"})
     * @Route("/customer/room/request/{id}", name="room.customer.request_cancel", options={"expose": true})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function cancelRequestAction(int $id): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();
        /** @var CustomerEntity $customer */
        $customer = $user->getCustomer();

        $entity = $this->roomRequestRepository->findOneByIdAndCustomer($id, $customer);
        if (!$entity) {
            throw $this->createNotFoundException('Заявка не найдена');
        }

        $this->requestManager->cancelRequest($entity, $user);

        return new JsonResponse([
            'request' => $entity,
            'success' => true,
        ]);
    }
}
