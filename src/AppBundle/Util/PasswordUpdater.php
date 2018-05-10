<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 10:20
 */

namespace AppBundle\Util;

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
/**
 * Class updating the hashed password in the user when there is a new password.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class PasswordUpdater
{


    private $passwordService;
    /**
     * PasswordUpdater constructor.
     */
    public function __construct($passwordService)
    {
        $this->passwordService = $passwordService;
    }

    public function hashPassword(UserInterface $user)
    {
        $plainPassword = $user->getPlainPassword();
        if (0 === strlen($plainPassword)) {
            return;
        }
        $encoder = $this->passwordService;

        $hashedPassword = $encoder->generateHash($plainPassword);
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}