<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 08/09/2017
 * Time: 17:29
 */

namespace AppBundle\Service;

use AppBundle\Entity\Token;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TokenManager
{
    protected $container;
    protected $fieldname;
    protected $class;
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, $fieldname, $class) {
        $this->container = $container;
        $this->fieldname = $fieldname;
        $this->class = $class;
    }
    /**
     * @param UserInterface $user
     * @return Token
     */
    public function createToken(UserInterface $user) {
        $this->cleanupTokens($user);
        $user->setLastLogin(new \DateTime());
        $token = new Token($this->generateToken(), $user);
        if(intval($this->container->getParameter('timeout')) > 0){
            $expiresAt = clone $user->getLastLogin();
            $expiresAt->modify('+' . (intval($this->container->getParameter('timeout'))) . ' seconds');
            $token->setExpireAt($expiresAt);
        }
        //$user->addToken($token);
        $this->container->get('doctrine')->getManager()->persist($token);
        $this->container->get('doctrine')->getManager()->flush();
        return $token;
    }
    /**
     * @param UserInterface $user
     * @return Token
     */
    public function refreshToken($apiKey) {
        try {
            $token = $this->container->get('doctrine')->getRepository($this->class)->findOneBy([
                'token' => $apiKey
            ]);
            if ($token) {
                if(intval($this->container->getParameter('timeout')) > 0){
                    $expiresAt = new \DateTime();
                    $expiresAt->modify('+' . (intval($this->container->getParameter('timeout')) / 1000) . ' seconds');
                    $token->setExpireAt($expiresAt);
                    $em = $this->container->get('doctrine')->getManager();
                    $em->persist($token);
                    $em->flush();
                }
            }
        } catch (NoResultException $e) {
            // Don't do anything here.
        }
    }
    /**
     * @param Request $request
     */
    public function onLogout(Request $request, UserInterface $user = null) {
        $apiKey = $request->headers->get($this->fieldname);
        if ($apiKey) {
            $token = $this->container->get('doctrine')->getRepository($this->class)->findOneBy([
                'token' => $apiKey
            ]);
            $em = $this->container->get('doctrine')->getManager();
            $em->remove($token);
            $em->flush();
        } else if ($user) {
            $this->cleanupTokens($user);
        }
    }
    /**
     * @param UserInterface $user
     */
    public function cleanupTokens(UserInterface $user) {
        if(intval($this->container->getParameter('timeout')) == 0){
            return;
        }
        $qb = $this->container
            ->get('doctrine')
            ->getRepository($this->class)
            ->createQueryBuilder('t')
        ;
        $qb
            ->where($qb->expr()->eq('t.user', $user->getId()))
            ->andWhere($qb->expr()->lt('t.expireAt', ':now'))
            ->setParameter('now', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
        ;
        $tokens = $qb->getQuery()->getResult();
        if (count($tokens)) {
            $em = $this->container->get('doctrine')->getManager();
            foreach ($tokens as $token) {
                $em->remove($token);
            }
            $em->flush();
        }
    }
    /**
     * @return string
     */
    public function generateToken($randomByte=32) {

        return rtrim(strtr(base64_encode(random_bytes($randomByte)), '+/', '-_'), '=');
    }

}