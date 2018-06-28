<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 05/01/2018
 * Time: 14:38
 */

namespace App\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Security\LoginManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\EventListener\AuthenticationListener as FOSAuthenticationListener;

class LoginSuccessHandler extends FOSAuthenticationListener implements AuthenticationSuccessHandlerInterface
{
    private
        $router,
        $authorizationChecker,
        $session,
        $token;


    public function __construct(RouterInterface $router, AuthorizationCheckerInterface $authorizationChecker, LoginManagerInterface $login_manager, SessionInterface $session,
                                TokenStorageInterface $tokenStorage)
    {

        parent::__construct($login_manager, 'main');
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
        $this->session =$session;
        $this->token=$tokenStorage;
    }

    public static function getSubscribedEvents() :array
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onRegistrationCompleted',
        );
    }
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        // this trick prevent login now
//        $this->session->getFlashBag()->add('success', 'Congrats, your account is now activated. Please wait for the Admin to verify all your data. We will get in touch with you by email when successful');
//        $this->token->setToken(null);
//        return new RedirectResponse($this->router->generate('homepage'));
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * TODO: add more routes once page are available
     * @param Request $request
     * @param TokenInterface $token
     * @return RedirectResponse|null never null
     * @throws RouteNotFoundException
     * @throws MissingMandatoryParametersException
     * @throws InvalidParameterException
     * @throws AuthenticationCredentialsNotFoundException
     * @throws \InvalidArgumentException
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?RedirectResponse
    {
        $response = null;
        if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')) {
            $response = new RedirectResponse($this->router->generate('customer_dashboard'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_ADMIN') || $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            $response = new RedirectResponse($this->router->generate('admin'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_CARRIER')){
            $response = new RedirectResponse($this->router->generate('carrier_dashboard'));
        } elseif ($this->authorizationChecker->isGranted('ROLE_PRODUCER')){
            $response = new RedirectResponse($this->router->generate('producer_dashboard'));}
        else{
            $this->session->getFlashBag()->add('error', 'Invalid User');
            $this->authorizationChecker->isGranted(null);
            $this->token->setToken(null);
//            $this->session->invalidate();
            return new RedirectResponse($this->router->generate('homepage'));
        }
        $local =$request->getLocale();
        $request->setLocale($local);
        return $response;
    }


}