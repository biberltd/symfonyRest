<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 11:26
 */

namespace AppBundle\Controller;


use AppBundle\Service\TokenManager;
use AppBundle\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
/**
 */
class LoginController extends BaseController
{



    /**
     * @Rest\Post("/login",  defaults={"_format":"json"})
     */
    public function postAction(Request $request)
    {
        $data = json_decode($request->getContent());
        /**
         * @var UserManager $userManager
         */
        $userManager = $this->get('auth.user_manager');
        $account = $userManager->login($data);


        if(!$account instanceof UserInterface)
        {
            return $this->response(null,401,'Login incorrect');
        }



        /**
         * @var TokenManager $tokenManager
         */
        $tokenManager = $this->container->get('auth.token_manager');

        $token = $tokenManager->createToken($account);
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();

        /**
         * @var UserInterface $user;
         */
        $user = $token->getUser();
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();




        return $this->response($token);

    }
}