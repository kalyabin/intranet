<?php

namespace RentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use HttpHelperBundle\Response\ListJsonResponse;
use RentBundle\Entity\Repository\RoomRepository;
use RentBundle\Entity\Repository\RoomRequestRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Form\Type\RoomRequestCreateManagerType;
use RentBundle\Form\Type\RoomRequestManagerType;
use RentBundle\Form\Type\RoomType;
use RentBundle\Utils\RoomRequestManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\UserEntity;

/**
 * Контроллер для управления помещениями: заведение новых помещений, удаление и календарь
 *
 * @Security("has_role('ROLE_RENT_MANAGEMENT')")
 * @Route(service="rent.room_manager.controller")
 *
 * @package RentBundle\Controller
 */
class RoomManagerController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RoomRepository
     */
    protected $roomRepository;

    /**
     * @var RoomRequestRepository
     */
    protected $requestRepository;

    /**
     * @var RoomRequestManager
     */
    protected $requestManager;

    public function __construct(EntityManagerInterface $entityManager, RoomRequestManager $requestManager)
    {
        $this->requestManager = $requestManager;
        $this->entityManager = $entityManager;
        $this->roomRepository = $entityManager->getRepository(RoomEntity::class);
        $this->requestRepository = $entityManager->getRepository(RoomRequestEntity::class);
    }

    /**
     * Поиск помещения по идентификатору.
     *
     * Если не найдено - генерирует 404-ю ошибку.
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
     * Список помещений
     *
     * @Method({"GET"})
     * @Route("/manager/room", name="room.manager.list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function listAction(): ListJsonResponse
    {
        $list = $this->roomRepository->findAll();

        return new ListJsonResponse($list, count($list), 0, count($list));
    }

    /**
     * Создание помещения
     *
     * @Method({"POST"})
     * @Route("/manager/room", name="room.manager.create", options={"expose": true})
     *
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createAction(Request $request): FormValidationJsonResponse
    {
        $entity = new RoomEntity();

        $form = $this->createForm(RoomType::class, $entity);
        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $success = true;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'room' => $entity,
            'success' => $success,
        ];
        $response->handleForm($form);

        return $response;
    }

    /**
     * Получить помещение по идентификатору
     *
     * @Method({"GET"})
     * @Route("/manager/room/{id}", name="room.manager.details", options={"expose": true}, requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function detailsAction(int $id): JsonResponse
    {
        $entity = $this->getRoomById($id);

        return new JsonResponse([
            'room' => $entity,
            'requests' => $this->requestRepository->findActualByRoom($entity),
        ]);
    }

    /**
     * Обновление помещения
     *
     * @Method({"POST"})
     * @Route("/manager/room/{id}", name="room.manager.update", options={"expose": true}, requirements={"id": "\d+"})
     *
     * @param int $id
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function updateAction(int $id, Request $request): FormValidationJsonResponse
    {
        $entity = $this->getRoomById($id);

        $form = $this->createForm(RoomType::class, $entity);
        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $success = true;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'room' => $entity,
            'success' => $success,
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Удаление помещения
     *
     * @Method({"DELETE"})
     * @Route("/manager/room/{id}", name="room.manager.remove", options={"expose": true}, requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function removeAction(int $id): JsonResponse
    {
        $entity = $this->getRoomById($id);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $id,
            'success' => true,
        ]);
    }

    /**
     * Просмотреть список всех актуальных заявок
     *
     * @Method({"GET"})
     * @Route("/manager/room/request", name="room.manager.request_list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function actualRequestListAction(): ListJsonResponse
    {
        $list = $this->requestRepository->findAllActual();

        return new ListJsonResponse($list, count($list), 0, count($list));
    }

    /**
     * Создание заявки на бронирование от лица арендатора
     *
     * @Method({"POST"})
     * @Route("/manager/room/request", name="room.manager.request_create", options={"expose": true})
     *
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createRequestAction(Request $request): FormValidationJsonResponse
    {
        $entity = new RoomRequestEntity();

        $form = $this->createForm(RoomRequestCreateManagerType::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserEntity $user */
            $user = $this->getUser();
            // все заявки, созданные от лица менеджера являются подтвержденными
            $entity->setStatus(RoomRequestEntity::STATUS_APPROVED);
            $this->requestManager->createRequest($entity, $user);
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'success' => $entity->getId() > 0,
            'request' => $entity
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Форма редактирования заявки (отказ или подтверждение)
     *
     * @Method({"POST"})
     * @Route("/manager/room/request/{id}", name="room.manager.request_update", options={"expose": true})
     *
     * @param int $id
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function updateRequestAction(int $id, Request $request): FormValidationJsonResponse
    {
        $entity = $this->requestRepository->findOneById($id);
        if (!$entity instanceof RoomRequestEntity) {
            throw $this->createNotFoundException('Заявка не найдена');
        }

        $oldStatus = $entity->getStatus();

        $form = $this->createForm(RoomRequestManagerType::class, $entity);
        $form->handleRequest($request);

        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserEntity $user */
            $user = $this->getUser();
            $this->requestManager->updateRequestByManager($entity, $user, $oldStatus);
            $success = true;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'success' => $success,
            'request' => $entity,
        ];
        $response->handleForm($form);
        return $response;
    }
}
