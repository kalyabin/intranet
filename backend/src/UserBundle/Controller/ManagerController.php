<?php

namespace UserBundle\Controller;


use HttpHelperBundle\Response\FormValidationJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Form\Type\UserType;
use UserBundle\Utils\UserManager;

/**
 * Управление пользователями:
 *
 * - регистрация новых;
 * - блокирование старых;
 * - редактирование;
 * - удаление;
 *
 * @Route(service="user.manager_controller")
 *
 * @Security("has_role('USER_MANAGEMENT')")
 *
 * @package UserBundle\Controller
 */
class ManagerController extends Controller
{
    /**
     * @var UserManager
     */
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Генерирует 403 ошибку, если текущий пользователь совпадает с переданным
     *
     * @param UserEntity $user
     */
    protected function checkIsUserIsNotCurrent(UserEntity $user)
    {
        /** @var UserEntity $currentUser */
        $currentUser = $this->getUser();

        if ($user->getId() == $currentUser->getId()) {
            throw $this->createAccessDeniedException('Для изменения текущего аккаунта воспользуйтесь профилем.');
        }
    }

    /**
     * Получить пользователя по идентификатору или сгенерировать 404
     *
     * @param integer $id
     *
     * @return UserEntity
     */
    protected function getUserById($id): UserEntity
    {
        /** @var UserRepository $repository */
        $repository = $this->userManager->getEntityManager()->getRepository(UserEntity::class);
        $user = $repository->findOneById($id);

        if (!$user) {
            throw $this->createNotFoundException('Пользователь не найден');
        }

        return $user;
    }

    /**
     * Создание пользователя
     *
     * @Method({"POST"})
     * @Route("/user/manager/create", options={"expose": true}, name="user.manager.create")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request): JsonResponse
    {
        $user = new UserEntity();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        $result = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->userManager->createUserByAdmin($user);
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'user' => $result,
            'success' => $user->getId() > 0
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Редактирование пользователя.
     *
     * Текущий пользователь не может редактировать свой аккаунт через эту форму.
     *
     * @Method({"POST"})
     * @Route("/user/manager/update/{id}", options={"expose": true}, name="user.manager.update", requirements={"id": "\d+"})
     *
     * @param integer $id Идентификатор редактируемого пользователя
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction($id, Request $request): JsonResponse
    {
        $user = $this->getUserById($id);

        // текущий пользователь не может менять свой аккаунт через админскую панель
        $this->checkIsUserIsNotCurrent($user);

        $oldPassword = $user->getPassword();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->userManager->updateUserByAdmin($user, $oldPassword != $user->getPassword());
            $success = $result instanceof UserEntity;
        }

        $response = new FormValidationJsonResponse();
        $response->jsonData = [
            'user' => $user,
            'success' => $success
        ];
        $response->handleForm($form);
        return $response;
    }

    /**
     * Получить список пользователей с постраничной навигацией.
     *
     * В $_GET нужно передать:
     * - pageSize - размер одной страницы (не более 500 штук);
     * - pageNum - номер текущей страницы начиная с 1;
     *
     * Все параметры опциональные.
     *
     * @Method({"GET"})
     * @Route("/user/manager/list", options={"expose": true}, name="user.manager.list")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $pageSize = (int) $request->get('pageSize', 500);
        $pageSize = min(100, $pageSize);

        $pageNum = (int) $request->get('pageNum', 1);
        $offset = $pageNum - 1;
        $offset = max(0, $offset);

        /** @var UserRepository $repository */
        $repository = $this->userManager->getEntityManager()->getRepository(UserEntity::class);

        $totalCount = $repository->getTotalCount();
        /** @var UserEntity[] $list */
        $list = $repository->findBy([], [], $pageSize, $offset * $pageNum);

        return new JsonResponse([
            'list' => $list,
            'pageSize' => $pageSize,
            'pageNum' => $pageNum,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Получить карточку пользователя.
     *
     * @Method({"GET"})
     * @Route("/user/manager/details/{id}", options={"expose": true}, name="user.manager.details", requirements={"id": "\d+"})
     *
     * @param integer $id Идентификатор просматриваемого пользователя
     *
     * @return JsonResponse
     */
    public function detailsAction($id): JsonResponse
    {
        $user = $this->getUserById($id);

        return new JsonResponse([
            'user' => $user,
            'roles' => $user->getRoles(),
            'status' => $user->getStatus(),
        ]);
    }
}
