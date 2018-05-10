<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 10:21
 */

namespace AppBundle\Util;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class updating the canonical fields of the user.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class CanonicalFieldsUpdater
{
    private $usernameCanonicalizer;
    private $emailCanonicalizer;

    public function updateCanonicalFields(UserInterface $user)
    {
        $user->setUsernameCanonical($this->canonicalizeUsername($user->getUsername()));
        $user->setEmailCanonical($this->canonicalizeEmail($user->getEmail()));
    }
    /**
     * Canonicalizes an email.
     *
     * @param string|null $email
     *
     * @return string|null
     */
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