<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 11:34
 */

namespace AppBundle\Service;
use AppBundle\Security\WebServiceUser;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Security\UserManager as BaseUserManager;
use AppBundle\Util\PasswordUpdater;
use AppBundle\Entity\User;
class UserManager extends BaseUserManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var string
     */
    private $class;

    private $container;

    /**
     * UserManager constructor.
     * @param PasswordUpdater $passwordUpdater
     * @param ObjectManager $om
     * @param $class
     */
    public function __construct(PasswordUpdater $passwordUpdater, ObjectManager $om, $class)
    {
        parent::__construct($passwordUpdater);
        $this->objectManager = $om;
        $this->class = $class;
    }
    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->objectManager->getRepository($this->getClass());
    }
    /**
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user)
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }
    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        if (false !== strpos($this->class, ':')) {
            $metadata = $this->objectManager->getClassMetadata($this->class);
            $this->class = $metadata->getName();
        }
        return $this->class;
    }
    /**
     * {@inheritdoc}
     */
    public function findUserBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }
    /**
     * {@inheritdoc}
     */
    public function findUsers()
    {
        return $this->getRepository()->findAll();
    }
    /**
     * {@inheritdoc}
     */
    public function reloadUser(UserInterface $user)
    {
        $this->objectManager->refresh($user);
    }
    /**
     * {@inheritdoc}
     */
    public function updatePassword(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        parent::updatePassword($user);
        $this->updateUser($user,$andFlush);
    }
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
    public function login($data)
    {

        /**
         * @var UserManager $userManager
         */
        $account = $this->checkUser($data);

        if(!$account)
        {
            return false;
        }

        $verifyAccount = $this->verifyPassword($account,$data);

        if ($verifyAccount) {
            return $account;
        }

        return false;
    }

    /**
     * @param $data
     * @return WebServiceUser|bool
     */
    public function loginWithAppKey($data)
    {

        /**
         * @var UserManager $userManager
         */
        $account = $this->checkUser($data);

        if(!$account)
        {
            return false;
        }

        $verifyAccount = $this->verifyPassword($account,$data);

        if ($verifyAccount) {
            $webUSer = new WebServiceUser();
            $webUSer->setUser($account);
            return $webUSer;

        }

        return false;
    }

    public function checkUser($data)
    {
        return $this->findUserByUsernameOrEmail($data->{$this->container->getParameter('user_identity_field')});
    }
    public function verifyPassword(UserInterface $user,$data)
    {
        $pService = $this->container->get("auth.password_service");
        if(property_exists($data,'confirmationToken'))
        {
            $verifyAccount = $user->getConfirmationToken() == $data->confirmationToken ? true : false;
            return $verifyAccount;
        }
        $verifyAccount = $pService->verifyHash($data->{$this->container->getParameter('password_identity_field')}, $user->getPassword());

        return $verifyAccount;
    }
    public function changePassword(UserInterface $user,$data)
    {
        $verifyAccount = $this->verifyPassword($user,$data);
        if ($verifyAccount) {
            $user->setPlainPassword($data->newpassword);
            $this->updatePassword($user,true);
            return true;
        }
        return false;
    }

    public function reminderPassword(UserInterface $user)
    {
        $tokenManager = $this->container->get('auth.token_manager');
        $token = $tokenManager->generateToken(10);
        $user->setConfirmationToken($token);
        $user->setPasswordRequestedAt((new \DateTime('now'))->add(new \DateInterval('P7D')));
        $this->objectManager->persist($user);
        $this->objectManager->flush();
        return $user;
    }
    public function create($username, $password, $email, $active, $role)
    {
        $user = $this->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setIsActive((bool) $active);
        $roles = explode(',',$role ?? []);
        $user->setRoles($roles);
        $this->updatePassword($user);

        return $user;
    }

}