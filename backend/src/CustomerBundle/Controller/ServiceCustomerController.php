<?php

namespace CustomerBundle\Controller;


use CustomerBundle\Entity\Repository\ServiceRepository;
use CustomerBundle\Entity\ServiceActivatedEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use CustomerBundle\Utils\ServiceManager;
use Doctrine\ORM\EntityManagerInterface;
use HttpHelperBundle\Response\FormValidationJsonResponse;
use HttpHelperBundle\Response\ListJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\UserEntity;

/**
 * Управление собственными услугами арендатора: просмотр доступных, активация, деактивация
 *
 * @Route(service="customer.service_customer_controller")
 * @Security("has_role('ROLE_SERVICE_CUSTOMER')")
 *
 * @package CustomerBundle\Controller
 */
class ServiceCustomerController extends Controller
{
    /**
     * @var ServiceRepository
     */
    protected $serviceRepository;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function __construct(EntityManagerInterface $entityManager, ServiceManager $serviceManager)
    {
        $this->serviceRepository = $entityManager->getRepository(ServiceEntity::class);
        $this->serviceManager = $serviceManager;
    }

    /**
     * Получить услугу
     *
     * Если услуга не найдена - генерирует 404-ю ошибку.
     *
     * @param string $id
     *
     * @return ServiceEntity
     */
    protected function getService(string $id): ServiceEntity
    {
        $service = $this->serviceRepository->findOneById($id);
        if (!$service) {
            throw $this->createNotFoundException('Услуга не найдена');
        }
        return $service;
    }

    /**
     * Получить список всех доступных услуг (включая уже подключенных).
     *
     * Просматривать услуги могут все, в том числе и менеджеры.
     *
     * @Security("is_fully_authenticated()")
     * @Method({"GET"})
     * @Route("/customer/service", name="service.customer.list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function listAction(): ListJsonResponse
    {
        $list = $this->serviceRepository->findAllActive();

        return new ListJsonResponse($list, count($list), 0, count($list));
    }

    /**
     * Получить список всех активированных услуг
     *
     * @Method({"GET"})
     * @Route("/customer/service/activated", name="service.customer.activated_list", options={"expose": true})
     *
     * @return ListJsonResponse
     */
    public function activatedListAction(): ListJsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();
        $customer = $user->getCustomer();

        $list = $customer->getService()->getValues();

        return new ListJsonResponse($list, count($list), 0, count($list));
    }

    /**
     * Активация услуги.
     *
     * На вход также необходимо передать идентификатор тарифа для активации (если услуга содержит тарифы).
     *
     * @Method({"POST"})
     * @Route(
     *     "/customer/service/{id}/activate",
     *     name="service.customer.activate",
     *     options={"expose": true},
     *     requirements={"id": "[a-zA-Z0-9_-]+"}
     * )
     *
     * @param string $id Идентификатор услуги
     * @param Request $request
     *
     * @return FormValidationJsonResponse
     */
    public function activateAction(string $id, Request $request): FormValidationJsonResponse
    {
        $service = $this->getService($id);

        /** @var UserEntity $user */
        $user = $this->getUser();
        $customer = $user->getCustomer();

        $error = null;
        $success = false;

        // если указан тариф - получить его
        $tariffId = $request->get('tariff', null);
        $tariffId = is_scalar($tariffId) ? (int) $tariffId : null;

        // если у услуги есть тарифы, но они не были указаны - генерировать ошибку
        $tariff = null;
        foreach ($service->getTariff() as $item) {
            /** @var ServiceTariffEntity $item */
            if ($item->getId() == $tariffId) {
                $tariff = $item;
                break;
            }
        }
        if ($service->getTariff()->count() > 0 && !$tariff) {
            $error = 'Необходимо указать тариф';
        } elseif ($this->serviceManager->serviceIsAssigned($customer, $service)) {
            // услуга уже активирована
            $error = 'Услуга уже была активирована ранее';
        } else {
            // активировать услугу
            $success = $this->serviceManager->activateService($customer, $service, $tariff);
        }

        $activated = null;
        foreach ($customer->getService() as $activatedService) {
            /** @var ServiceActivatedEntity $activatedService */
            if ($activatedService->getService()->getId() == $service->getId()) {
                $activated = $activatedService;
            }
        }

        $response = new FormValidationJsonResponse();
        $response->setData([
            'activated' => $activated,
            'success' => $success,
            'submitted' => true,
            'valid' => is_null($error),
            'validationErrors' => !is_null($error) ? [$error] : [],
            'firstError' => (string) $error
        ]);
        if (!empty($error) || !$success) {
            $response->setStatusCode(FormValidationJsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * Деактивация услуги
     *
     * @Method({"POST"})
     * @Route(
     *     "/customer/service/{id}/deactivate",
     *     name="service.customer.deactivate",
     *     options={"expose": true},
     *     requirements={"id": "[a-zA-Z0-9_-]+"}
     * )
     *
     * @param string $id Идентификатор деактивируемой услуги
     *
     * @return FormValidationJsonResponse
     */
    public function deactivateAction(string $id): FormValidationJsonResponse
    {
        $service = $this->getService($id);

        /** @var UserEntity $user */
        $user = $this->getUser();
        $customer = $user->getCustomer();

        $error = null;
        $success = false;

        if (!$this->serviceManager->serviceIsAssigned($customer, $service)) {
            $error = 'Услуга не была активирована ранее';
        } else {
            $success = $this->serviceManager->deactivateService($customer, $service);
        }

        $response = new FormValidationJsonResponse();
        $response->setData([
            'service' => $service,
            'success' => $success,
            'submitted' => true,
            'valid' => is_null($error) && $success,
            'validationErrors' => !is_null($error) ? [$error] : [],
            'firstError' => (string) $error
        ]);
        if (!empty($error) || !$success) {
            $response->setStatusCode(FormValidationJsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
