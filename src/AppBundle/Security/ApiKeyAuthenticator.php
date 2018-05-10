<?php

/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 08/09/2017
 * Time: 16:35
 */
namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Exception\BaseException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var integer
     */
    protected $timeout;
    /**
     * @var string
     */
    protected $fieldName;
    public function __construct($timeout, $fieldName = 'apiKey') {
        $this->timeout = $timeout;
        $this->fieldName = $fieldName;
    }
    public function createToken(Request $request, $providerKey)
    {
        // lookup the api key in the header
        $apiKey = $request->headers->get($this->fieldName);
        if (!$apiKey) {
            //use the query parameter as a fallback
            $apiKey = $request->query->get($this->fieldName);
            if(!$apiKey) {
                throw new BaseException("401",[],'No API key found!');
            }
        }
        return new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );
    }
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }
        $apiKey = $token->getCredentials();
        $user = $userProvider->getUserForApiKey($apiKey);
        if (!$user) {
            throw new BaseException("401",[],sprintf('API Key "%s" does not exist.', $apiKey));
        }
        //check if api key is expired
        if($this->timeout > 0) {
            $expiredDate = new \DateTime();
            $expiredDate->modify('-'. $this->timeout . ' second');
            if($user->getLastLogin() < $expiredDate) {
                //throw new BaseException("401",[],"API Key is expired.");
            }
        }
        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles()
        );
    }
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new BaseException("401",[],$exception->getMessage());
    }
}