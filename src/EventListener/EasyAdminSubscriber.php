<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 08.11.2017
 * Time: 16:41
 */

namespace App\EventListener;


use App\Entity\Account;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;


class EasyAdminSubscriber implements EventSubscriberInterface
{

    protected $twig;
    protected $swift;
    protected $container;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $swift, ContainerInterface $container)
    {
        $this->twig = $twig;
        $this->swift = $swift;
        $this->container = $container;
    }

    public static function getSubscribedEvents():array
    {

        return array(
            EasyAdminEvents::POST_PERSIST => array('postPersist'),
            EasyAdminEvents::POST_UPDATE => array('postUpdate'),
        );
    }

    /**
     * @param GenericEvent $event
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function postPersist(GenericEvent $event): void
    {
        // Because of the debug toolbar symfony runs this event twice
        // So if the account_ is empty don't trigger this.
        /**
         * @var $session Session
         */
        $session = $event->getArguments()['request']->getSession();

        if($session->get('account_') !== ''){


            $account = $event->getSubject();
            if ($account instanceof Account) {
            $password = $session->get('_account_new_plain_password');
            $template = $this->twig->render($session->get('_account_related_template'), ['name' => $account->getContactName(),
                    'login' => $account->getUsername(),
                    'password' => $password]);

            $message = (new \Swift_Message())
                ->setSubject($this->container->get('translator')->trans('register.subject'))
                ->setFrom($this->container->getParameter('mailer_name'))
                ->setTo($account->getEmail())
                ->setBody(
                    $template,
                    'text/html',
                    'utf-8'
                );

            $this->swift->send($message);

            $session->remove('account_');
            $session->remove('_account_related_template');
            $session->remove('_account_new_plain_password');
            }
        }

    }

    public function postUpdate(GenericEvent $event)
    {

    }


}