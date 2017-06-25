<?php

namespace CustomerBundle\Controller;

use CustomerBundle\Entity\Repository\ServiceRepository;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use CustomerBundle\Form\Type\ServiceType;
use Doctrine\ORM\EntityManagerInterface;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use HttpHelperBundle\Response\ListJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Управление услугами: создание, редактирование и удаление
 *
 * @Route(service="customer.service_manager_controller")
 * @Security("has_role('ROLE_SERVICE_MANAGEMENT')")
 *
 * @package CustomerBundle\Controller
 */
class ServiceManagerController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ServiceRepository
     */
    protected $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ServiceEntity::class);
    }

    /**
     * Получить услугу по идентификатору.
     * Если услуга не найдена, то генерирует 404-ю ошибку.
     *
     * @param string $id
     *
     * @return ServiceEntity
     */
    protected function getService(string $id): ServiceEntity
    {
        $service = $this->repository->findOneById($id);
        if (!$service) {
            throw $this->createNotFoundException('Услуга не найдена');
        }
        return $service;
    }

    /**
     * Получить список всех услуг (в том числе деактивированных)
     *
     * @Method({"GET"})
     * @Route("/manager/service", name="service.manager.list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function listAction(): ListJsonResponse
    {
        $list = $this->repository->findAll();

        $response = new ListJsonResponse($list, count($list), 0, count($list));
        return $response;
    }

    /**
     * Создание услуги
     *
     * @Method({"POST"})
     * @Route("/manager/service/create", name="service.manager.create", options={"expose": true})
     *
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createAction(Request $request): FormValidationJsonResponse
    {
        $entity = new ServiceEntity();

        $form = $this->createForm(ServiceType::class, $entity);
        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($entity->getTariff() as $tariff) {
                /** @var ServiceTariffEntity $tariff */
                $tariff->setService($entity);
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $success = true;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'service' => $entity,
            'success' => $success,
        ];
        $response->handleForm($form);

        return $response;
    }

    /**
     * Получить детальную информацию об услуге по её идентификатору
     *
     * @Method({"GET"})
     * @Route("/manager/service/{id}", name="service.manager.details", requirements={"id": "[a-zA-Z0-9_-]+"}, options={"expose": true})
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function detailsAction(string $id): JsonResponse
    {
        $service = $this->getService($id);

        return new JsonResponse([
            'service' => $service
        ]);
    }

    /**
     * Обновление услуги по её идентификатору
     *
     * @Method({"POST"})
     * @Route("/manager/service/{id}", name="service.manager.update", requirements={"id": "[\w_-]+"}, options={"expose": true})
     *
     * @param string $id
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function updateAction(string $id, Request $request): FormValidationJsonResponse
    {
        $entity = $this->getService($id);

        $form = $this->createForm(ServiceType::class, $entity);
        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($entity->getTariff() as $tariff) {
                /** @var ServiceTariffEntity $tariff */
                $tariff->setService($entity);
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $success = true;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'service' => $entity,
            'success' => $success,
        ];
        $response->handleForm($form);

        return $response;
    }

    /**
     * Удаление услуги
     *
     * @Method({"DELETE"})
     * @Route("/manager/service/{id}", name="service.manager.delete", requirements={"id": "[\w_-]+"}, options={"expose": true})
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function deleteAction(string $id): JsonResponse
    {
        $entity = $this->getService($id);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $id,
            'success' => true,
        ]);
    }
}
