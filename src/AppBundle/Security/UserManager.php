<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 10:14
 */

namespace AppBundle\Security;

use AppBundle\Util\PasswordUpdater;
use AppBundle\Util\CanonicalFieldsUpdater;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Service\UserManagerInterface;

abstract class UserManager implements UserManagerInterface
{
    private $passwordUpdater;
    private $canonicalFieldsUpdater;
    public function __construct(PasswordUpdater $passwordUpdater)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->canonicalFieldsUpdater = new CanonicalFieldsUpdater();
    }
    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class();
        return $user;
    }
    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('email' => $this->canonicalizeEmail($email)));
    }
    /**
     * {@inheritdoc}
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(array('username' => $this->canonicalizeUsername($username)));
    }
    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            return $this->findUserByEmail($usernameOrEmail);
        }
        return $this->findUserByUsername($usernameOrEmail);
    }
    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }
    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }
    /**
     * {@inheritdoc}
     */
    public function updateCanonicalFields(UserInterface $user)
    {
        //$this->canonicalFieldsUpdater->updateCanonicalFields($user);
    }
    /**
     * {@inheritdoc}
     */
    public function updatePassword(UserInterface $user)
    {
        $this->passwordUpdater->hashPassword($user);
    }
    /**
     * @return PasswordUpdaterInterface
     */
    protected function getPasswordUpdater()
    {
        return $this->passwordUpdater;
    }

    public function canonicalizeEmail($email)
    {
        return $this->canonicalize($email);
    }
    /**
     * Canonicalizes a username.
     *
     * @param string|null $username
     *
     * @return string|null
     */
    public function canonicalizeUsername($username)
    {
        return $this->canonicalize($username);
    }
    public function canonicalize($string)
    {
        if (null === $string) {
            return null;
        }
        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);
        return $result;
    }
}