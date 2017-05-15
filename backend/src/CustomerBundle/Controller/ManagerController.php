<?php

namespace CustomerBundle\Controller;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\CustomerRepository;
use CustomerBundle\Form\Type\CustomerType;
use Doctrine\Common\Persistence\ObjectManager;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use HttpHelperBundle\Response\ListJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер для управления контрагентами
 *
 * @Route(service="customer.manager_controller")
 *
 * @Security("has_role('USER_MANAGEMENT')")
 *
 * @package CustomerBundle\Controller
 */
class ManagerController extends Controller
{
    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var CustomerRepository
     */
    protected $repository;

    public function __construct(ObjectManager $objectManager)
    {
        $this->entityManager = $objectManager;
        $this->repository = $objectManager->getRepository(CustomerEntity::class);
    }

    /**
     * Получить контрагента по идентификатору или сгенерировать 404
     *
     * @param int $id
     *
     * @return CustomerEntity
     */
    protected function getById(int $id): CustomerEntity
    {
        $customer = $this->repository->findOneById($id);

        if (!$customer) {
            throw $this->createNotFoundException('Арендатор не найден');
        }

        return $customer;
    }

    /**
     * Список всех контрагентов
     *
     * @Method({"GET"})
     * @Route("/manager/customer", name="customer.manager.list", options={"expose": true})
     *
     * @param Request $request
     *
     * @return ListJsonResponse
     */
    public function listAction(Request $request): ListJsonResponse
    {
        $pageSize = (int) $request->get('pageSize', 500);
        $pageSize = min(100, $pageSize);

        $pageNum = (int) $request->get('pageNum', 1);
        $offset = $pageNum - 1;
        $offset = max(0, $offset);

        $totalCount = $this->repository->getTotalCount();
        /** @var CustomerEntity[] $list */
        $list = $this->repository->findBy([], [], $pageSize, $offset * $pageNum);

        return new ListJsonResponse($list, $pageSize, $pageNum, $totalCount);
    }

    /**
     * Создание нового контрагента
     *
     * @Method({"POST"})
     * @Route("/manager/customer", name="customer.manager.create", options={"expose": true})
     *
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function createAction(Request $request): FormValidationJsonResponse
    {
        $customer = new CustomerEntity();

        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'customer' => $customer,
            'success' => $customer->getId() > 0
        ];
        $response->handleForm($form);

        return $response;
    }

    /**
     * Редактирование контрагента
     *
     * @Method({"POST"})
     * @Route("/manager/customer/{id}", name="customer.manager.update", requirements={"id": "\d+"}, options={"expose": true})
     *
     * @param int $id Идентификатор редактируемого контрагента
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function updateAction(int $id, Request $request): FormValidationJsonResponse
    {
        $customer = $this->getById($id);

        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            $success = true;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'customer' => $customer,
            'success' => $success
        ];
        $response->handleForm($form);

        return $response;
    }

    /**
     * Удалить контрагента
     *
     * @Method({"DELETE"})
     * @Route("/manager/customer/{id}", requirements={"id": "\d+"}, options={"expose": true}, name="customer.manager.delete")
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteAction(int $id): JsonResponse
    {
        $customer = $this->getById($id);

        $this->entityManager->remove($customer);

        $this->entityManager->flush();

        return new JsonResponse([
            'customer' => $customer,
            'success' => true,
        ]);
    }

    /**
     * Получить информацию о контрагенте
     *
     * @Method({"GET"})
     * @Route("/manager/customer/{id}", requirements={"id": "\d+"}, options={"expose": true}, name="customer.manager.details")
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function detailsAction(int $id): JsonResponse
    {
        $customer = $this->getById($id);

        return new JsonResponse([
            'customer' => $customer
        ]);
    }
}
