<?php
/**
 * Created by PhpStorm.
 * User: ertiz
 * Date: 25/12/2017
 * Time: 13:15
 */

namespace AppBundle\Security;


use AppBundle\Entity\User;

class WebServiceUser
{

    /**
     * @var User
     */
    private $user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}