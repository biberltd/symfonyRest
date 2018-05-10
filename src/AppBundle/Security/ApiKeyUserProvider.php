<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 08/09/2017
 * Time: 16:37
 */

namespace AppBundle\Security;
use AppBundle\Entity\Token;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use AppBundle\Entity\User;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    public function getUserForApiKey($apiKey)
    {

        $qb = $this->container->get('doctrine')->getRepository(User::class)->createQueryBuilder('u');
        $qb ->leftJoin(Token::class, 't',
            Join::WITH, $qb->expr()->eq('t.user', 'u.id'))
            ->where($qb->expr()->eq('t.token', $qb->expr()->literal($apiKey)));
        if(intval($this->container->getParameter('timeout')) > 0) {
            $qb->andWhere($qb->expr()->gte('t.expireAt', ':now'))->setParameter('now', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME);
        }
        $qb->setMaxResults(1);
        try {
            $user = $qb->getQuery()->getSingleResult();
            /**
             * auth-api ye refresh token isteği gönderilecek
             */
            return $user;
        } catch (NoResultException $e) {
            return false;
        }

    }
    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUser($username);
        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        return $user;
    }
    /**
     * {@inheritdoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userManager->getClass(), get_class($user)));
        }
        if (null === $reloadedUser = $this->findUserBy(array('id' => $user->getId()))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
        }
        return $reloadedUser;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();
        return $userClass === $class || is_subclass_of($class, $userClass);
    }
    /**
     * Finds a user by username.
     *
     * This method is meant to be an extension point for child classes.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    protected function findUser($username)
    {
        return $this->userManager->findUserByUsername($username);
    }
}