<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 13/12/2017
 * Time: 17:57
 */

namespace App\EventListener;

use App\Entity\Account;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailureListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    private $em;
    private $httpKernel;

    public function __construct(HttpKernelInterface $httpKernel, ObjectManager $entityManager)
    {
        $this->em = $entityManager;
        $this->httpKernel = $httpKernel;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     */
    public function setLogger(LoggerInterface $logger):void
    {
        // TODO: Implement setLogger() method.
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onFailure(AuthenticationFailureEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $username = $token->getUser();
        $failed = $this->em->getRepository(Account::class)->findOneBy(array('email' => $username));
        //check if user exists before trying to get the user#s previous failed login
        if ($failed !== null ) {
        $error = $failed->getFailedLogins()+1;
        $failed->setFailedLogins($error);
        $this->em->persist($failed);
        $this->em->flush();
        }
    }
}