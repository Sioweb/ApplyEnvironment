<?php


namespace Sioweb\ApplyEnvironment\Security\Authentication;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Contao\System;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\IpUtils;

class Authenticator extends AbstractGuardAuthenticator
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    private $ScopeMatcher;
    private $container;

    /**
     * Default message for authentication failure.
     *
     * @var string
     */
    private $failMessage = 'Invalid credentials';

    /**
     * Creates a new instance of FormAuthenticator
     */
    public function __construct($ScopeMatcher, RouterInterface $router, ContaoFrameworkInterface $framework)
    {
        $this->router = $router;
        
        $this->framework = $framework;
        $this->framework->initialize();
        $this->container = System::getContainer();
        $this->ScopeMatcher = $ScopeMatcher;
        $this->ScopeMatcher->matchAttribute('_scope', 'apply_environment');
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if (
            $this->ScopeMatcher->matches($request) && (
            $request->server->has('HTTP_CLIENT_IP')
            || $request->server->has('HTTP_X_FORWARDED_FOR')
            || !(IpUtils::checkIp($request->getClientIp(), $this->container->getParameter('whitelisted_ip_addresses')) || PHP_SAPI === 'cli-server')
        )) {
            if (file_exists($request->server->get('DOCUMENT_ROOT').'/../.env')) {
                (new Dotenv())->load($request->server->get('DOCUMENT_ROOT').'/../.env');
            }

            ##########################################################################
            #                                                                        #
            #  Access to debug front controllers is only allowed on localhost or     #
            #  with authentication. Use the "contao:install-web-dir -p" command to   #
            #  set a password for the dev entry point.                               #
            #                                                                        #
            ##########################################################################
            $accessKey = @getenv('APP_DEV_ACCESSKEY', true);

            if (false === $accessKey) {
                header('HTTP/1.0 403 Forbidden');
                die(sprintf('You are not allowed to access this file. Check %s for more information.', basename(__FILE__)));
            }

            if (
                null === $request->getUser()
                || !password_verify($request->getUser().':'.$request->getPassword(), $accessKey)
            ) {
                header('WWW-Authenticate: Basic realm="Contao debug"');
                header('HTTP/1.0 401 Unauthorized');
                die(sprintf('You are not allowed to access this file. Check %s for more information.', basename(__FILE__)));
            }

            unset($accessKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // $url = $this->router->generate('homepage');
        // return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        // $url = $this->router->generate('login');
        // return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('contao_backend_login');
        return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
