<?php

namespace Controller;

use Model\User;
use Model\Language;
use Model\Fdr;
use Model\UserOptions;
use Entity\FdrToUser;

use Repository\UserRepository;

use Component\EntityManagerComponent as EM;
use Component\RuntimeManager;
use Component\FlightComponent;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class UsersController extends CController
{
    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();
    }

    public function login ($args)
    {
        if (empty($args)
            || !isset($args['login'])
            || !isset($args['pass'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $U = new User();
        $data = [
            'user' => $args['login'],
            'pwd' => $args['pass']
        ];

        $success = false;
        $lang = 'en';

        $isAuthorized = $U->tryAuth($data, $_SESSION, $_COOKIE);

        if (!$isAuthorized
            || !isset($U->username)
            || empty($U->username)
        ) {
            return json_encode([
                'status' => 'fail',
                'message' => 'userUnexist',
                'messageText' => 'User unexist'
            ]);
        }

        $usrInfo = $U->GetUsersInfo($U->username);
        $lang = strtolower($usrInfo['lang']);

        return json_encode([
            'status' => 'ok',
            'login' => $args['login'],
            'lang' => $lang
        ]);
    }

    public function userLogout()
    {
        if (!isset($this->_user->username)
            || ($this->_user->username === '')
        ) {
            throw new ForbiddenException('user is not authorized');
        }

        $this->_user->logout($this->_user->username);

        return json_encode('ok');
    }

    public function getUserSettings()
    {
        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $O = new UserOptions();
        $userId = intval($this->_user->userInfo['id']);
        $settings = $O->GetOptions($userId);
        unset($O);

        return json_encode($settings);
    }

    public function setUserSettings($settings)
    {
        if (!isset($settings)
            || empty($settings)
            || !is_array($settings)
        ) {
            throw new BadRequestException(json_encode($settings));
        }

        $O = new UserOptions();
        $userId = intval($this->_user->userInfo['id']);
        $O->UpdateOptions($settings, $userId);
        unset($O);

        return json_encode('ok');
    }

    public function userChangeLanguage($data)
    {
        if (!isset($data)
            || !isset($data['lang'])
            || empty($data['lang'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $lang = $data['lang'];

        $L = new Language;
        $L->SetLanguageName($lang);
        unset($L);

        $this->_user->SetUserLanguage($this->_user->username, $lang);

        return json_encode('ok');
    }

    public function getUsers()
    {
        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $userId = intval($this->_user->userInfo['id']);

        $em = EM::get();
        $users = $em->getRepository('Entity\User')->getUsers($userId);

        return json_encode($users);
    }

    public function getUser($args)
    {
        if (!isset($args['id'])) {
            throw new BadRequestException(json_encode($args));
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $requestedUserId = $args['id'];
        $userId = intval($this->_user->userInfo['id']);
        $role = strval($this->_user->userInfo['role']);

        if (UserRepository::isUser($role) && $requestedUserId !== $userId) {
            throw new ForbiddenException('forbidden info for current user');
        }

        $em = EM::get();

        $user = $em->find('Entity\User', $requestedUserId);
        $creatorId = $user->getCreatorId();

        if (UserRepository::isModerator($role) && $creatorId !== $userId) {
            throw new ForbiddenException('forbidden info for current user');
        }

        return json_encode($user->get());
    }

    public function getLogo($args)
    {
        if (!isset($args)
            || !isset($args['id'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $creatorId = intval($this->_user->userInfo['id']);
        $userId = intval($args['id']);

        $em = EM::get();

        $user = $em->getRepository('Entity\User')->findOneBy(['id' => $userId]);
        if (!$user) {
            throw new NotFoundException("requested user not found. Id: ". $userId);
        }

        if (!$em->getRepository('Entity\User')->isAvaliable($userId, $creatorId)) {
            throw new ForbiddenException('user is not authorized');
        }

        header('Content-type:image/png');

        return stream_get_contents($user->getLogo());
    }

    public function create($args)
    {
        if (!isset($args['login'])
            || empty($args['login'])
            || !isset($args['pass'])
            || empty($args['pass'])
            || !isset($args['company'])
            || empty($args['company'])
            || !isset($args['avaliableFdrs'])
            || empty($args['avaliableFdrs'])
        ) {
            throw new BadRequestException([json_encode($args), 'notAllNecessarySent']);
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $authorId = intval($this->_user->userInfo['id']);

        $filePath = strval($_FILES['userLogo']['tmp_name']);
        $fileForInserting = RuntimeManager::storeFile($filePath, 'user-logo');
        $login = $args['login'];
        $avaliableFdrs = $args['avaliableFdrs'];

        if ($this->_user->CheckUserPersonalExist($login)) {
            throw new ForbiddenException(['user already exist', 'alreadyExist']);
        }

        $createdUserId = intval($this->_user->CreateUserPersonal([
            'login' => $login,
            'pass' => $args['pass'],
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'],
            'role' => $args['role'],
            'company' => $args['company'],
            'creatorId' => $authorId,
            'logo' => $fileForInserting,
        ]));

        foreach($avaliableFdrs as $id) {
            $this->_user->SetFDRavailable($createdUserId, intval($id));
        }

        RuntimeManager::unlinkRuntimeFile($fileForInserting);

        $em = EM::get();
        $user = $em->find('Entity\User', $createdUserId);

        return json_encode([
            'id' => $createdUserId,
            'login' => $login,
            'pass' => $args['pass'],
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'],
            'role' => $args['role'],
            'company' => $args['company'],
            'creatorId' => $authorId,
            'logo' => $em->getRepository('Entity\User')::getLogoUrl($createdUserId)
        ]);
    }

    public function update($args)
    {
        if (!isset($args['id'])
            || empty($args['id'])
            || !isset($args['pass'])
            || empty($args['pass'])
            || !isset($args['avaliableFdrs'])
            || empty($args['avaliableFdrs'])
        ) {
            throw new BadRequestException([json_encode($args), 'notAllNecessarySent']);
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $userId = intval($this->_user->userInfo['id']);
        $userIdToUpdate = intval($args['id']);

        $em = EM::get();

        if (!$em->getRepository('Entity\User')->isAvaliable($userIdToUpdate, $userId)) {
            throw new ForbiddenException('current user not able to update this');
        }

        $filePath = strval($_FILES['userLogo']['tmp_name']);
        $fileForUpdating = RuntimeManager::storeFile($filePath, 'user-logo');
        $login = $args['login'];
        $avaliableFdrs = $args['avaliableFdrs'];

        $user = $em->find('Entity\User', $userIdToUpdate);

        if (!$user) {
            throw new NotFoundException('user with id '.$userIdToUpdate.' not found');
        }

        $this->_user->UpdateUserPersonal(
            $userIdToUpdate,
            [
                'login' => $login,
                'pass' => $args['pass'],
                'name' => $args['name'],
                'email' => $args['email'],
                'phone' => $args['phone'],
                'role' => $args['role'],
                'company' => $args['company'],
                'id_creator' => $userId,
                'logo' => $fileForUpdating
            ]
        );

        RuntimeManager::unlinkRuntimeFile($fileForUpdating);

        $fdrsToUser = $em->getRepository('Entity\FdrToUser')->findBy(['userId' => $userIdToUpdate]);
        if (isset($fdrToUser)) {
            foreach ($fdrsToUser as $fdrToUser) {
                $em->remove($fdrToUser);
            }
        }

        foreach($avaliableFdrs as $id) {
            $this->_user->SetFDRavailable($userIdToUpdate, intval($id));
        }

        $em->flush();

        return json_encode([
            'id' => $userIdToUpdate,
            'login' => $login,
            'pass' => $args['pass'],
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'],
            'role' => $args['role'],
            'company' => $args['company'],
            'creatorId' => $userId,
            'logo' => $em->getRepository('Entity\User')::getLogoUrl($userIdToUpdate)
        ]);
    }


    public function delete($args)
    {
        if (!isset($args['userId'])
            || empty($args['userId'])
            || !is_int(intval($args['userId']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $userId = intval($this->_user->userInfo['id']);
        $userIdToDelete = intval($args['userId']);

        $em = EM::get();

        if (!$em->getRepository('Entity\User')->isAvaliable($userIdToDelete, $userId)) {
            throw new ForbiddenException('current user not able to delete this');
        }

        $user = $em->find('Entity\User', $userIdToDelete);
        if (isset($user)) {
            $em->remove($user);
        }

        $fdrsToUser = $em->getRepository('Entity\FdrToUser')->findBy(['userId' => $userIdToDelete]);
        if (isset($fdrToUser)) {
            foreach ($fdrsToUser as $fdrToUser) {
                $em->remove($fdrToUser);
            }
        }

        $flightsToFolders = $em->getRepository('Entity\FlightToFolder')->findBy(['userId' => $userIdToDelete]);
        if (isset($flightToFolder)) {
            foreach ($flightsToFolders as $flightToFolder) {
                $em->remove($flightToFolder);
            }
        }

        $flights = $em->getRepository('Entity\Flight')->findBy(['id_user' => $userIdToDelete]);
        $FC = new FlightComponent;
        foreach ($flights as $flightId) {
            $FC->DeleteFlight(intval($flightId), $userIdToDelete);
        }

        $em->flush();

        return json_encode('ok');
    }

    public function getUserActivity($args)
    {
        if (!isset($args['userId'])
            || empty($args['userId'])
            || !is_int(intval($args['userId']))
            || !isset($args['page'])
            || empty($args['page'])
            || !is_int(intval($args['page']))
            || !isset($args['pageSize'])
            || empty($args['pageSize'])
            || !is_int(intval($args['pageSize']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $userId = $args['userId'];
        $page = $args['page'];
        $pageSize = $args['pageSize'];

        $em = EM::get();

        $userActivityResult = $em->getRepository('Entity\UserActivity')
            ->findBy(
                ['userId' => $userId],
                ['date' => 'DESC'],
                $pageSize,
                ($page - 1) * $pageSize
            );

        $activity = [];
        foreach($userActivityResult as $item) {
            $activity[] = $item->get();
        }

        $total = $em->getRepository('Entity\UserActivity')
            ->createQueryBuilder('userActivity')
            ->select('count(userActivity.id)')
            ->where('userActivity.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        echo json_encode([
            'rows' => $activity,
            'pages' => round($total / $pageSize)
        ]);
    }
}
