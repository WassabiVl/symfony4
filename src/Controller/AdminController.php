<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 02.11.2017
 * Time: 11:45
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Address;
use App\Entity\Admin;
use App\Entity\Batch;
use App\Entity\Carrier;
use App\Entity\Customer;
use App\Entity\Producer;
use App\Exception\AccountHandleException;
use App\Utils\ErrorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;


class AdminController extends BaseAdminController
{
    /**
     * @var ErrorHandler
     */
    private $errorHandler;


    public function __construct()
    {
        $this->errorHandler = new ErrorHandler();
    }

    /**
     * @param Batch $entity
     * @param $entityProperties
     * @return Form
     * @throws Exception
     */
    protected function createBatchNewForm($entity, $entityProperties): Form
    {

        $id = $this->request->query->get('parent');
        $action = $this->request->query->get('action');
        if ($action === 'new' && $id === null) { // since creating a new batch requires empty form
            return parent::createNewForm($entity, $entityProperties);
        }

        if ($id === null) {
            $translated = $this->get('translator')->trans('Producer id is missing!');
            throw new Exception($translated);

        }
        $producer = $this->em->getRepository(Producer::class)->find($id);
        if($producer === null){
            $translated = $this->get('translator')->trans('Producer not found!');
            throw new Exception($translated);
        }
        $entity->setRelatedProducer($producer);
        return parent::createNewForm($entity, $entityProperties);
    }

    /**
     * @param Address $entity
     * @param $entityProperties
     * @return Form
     * @throws Exception
     */
    protected function createAddressNewForm($entity, $entityProperties): Form
    {

        $id = $this->request->query->get('parent');
        $action = $this->request->query->get('action');
        if ($action === 'new' && $id === null) { // since creating a new batch requires empty form
            return parent::createNewForm($entity, $entityProperties);
        }

        if ($id === null) {
            $translated = $this->get('translator')->trans('Account id is missing!');
            throw new Exception($translated);

        }
        $account = $this->em->getRepository(Account::class)->find($id);
        if($account === null){
            $translated = $this->get('translator')->trans('Account not found!');
            throw new Exception($translated);
        }
        $entity->setRelatedAccount($account);
        return parent::createNewForm($entity, $entityProperties);
    }

    protected function newAccountAction()
    {
        try{
            return parent::newAction();
        }
        catch(AccountHandleException $e){
            $flashBag = $this->request->getSession()->getFlashBag();
            foreach ($e->getMultipleErrors()->get()->getValues() as $x){
                $flashBag->add('error '.$x['cssClass'], $x['errorMessage']);
            }
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'new',
                'entity' => 'Account'
            ));

        }
    }
    
    protected function createNewAccountEntity()
    {
        $account  = new Account();
        $request = $this->request;

        // Symfony redirects, first save the post
        if($request->getMethod() === 'POST' && $request->getSession() !== null) {
        $request->getSession()->set('account_', $this->request->get('account'));
        }

        // After redirect it's get so load saved data

        if($request->getMethod() === 'GET' && !empty($request->getSession()->get('account_'))){
            $data = $request->getSession()->get('account_');

            // Token is not necessary to refill form
            unset($data['_token']);

            // Convert Carrier to Object
            $costPerKm = (int)$data['relatedCarrierEntry']['costPerKm'];
            $data['relatedCarrierEntry'] = new Carrier();
            $data['relatedCarrierEntry']->setCostPerKm($costPerKm);

            // Convert Producer to Object
            $number = $data['relatedProducerEntry']['number'];
            $producer = new Producer();
            $producer->setNumber($number);
            $data['relatedProducerEntry'] = $producer;

            // Convert related Addresses to Object
            if(isset($data['relatedAddresss'])){
                foreach($data['relatedAddresss'] as $address){
                    $newAddress = new Address();
                    foreach($address as $k => $v){
                        $setCall = 'set'.ucfirst($k);
                        $newAddress->$setCall($v);
                    }
                    $account->addRelatedAddress($newAddress);
                }
                unset($data['relatedAddresss']);
            }
            // Convert array to object
            foreach($data as $k => $v){
                $setCall = 'set'.ucfirst($k);
                if ($v === '' || \is_array($v)){
                    $account->$setCall(null);
                } else{
                $account->$setCall($v);
                }
            }
            $request->getSession()->set('account_','');
        }
        return $account;
    }


    /**
     * @param ArrayCollection|Account $entity
     * @throws \Exception
     */
    protected function preUpdateAccountEntity($entity): void
    {

        $em = $this->getDoctrine()->getManager();
        $uid = $entity->getId();
        $newType = $entity->getType();
        
        // Check if the type has changed
        // If the type has changed, delete the old one
        // Throw error if relations exist

        if($newType !== 'Admin'){
            $admin = $em->getRepository(Admin::class)->findOneBy(array('relatedAccount' => $uid));
            if($admin){
                $em->remove($admin);
            }
        }
        if($newType !== 'Producer'){
            /* Todo: Check for related orders
             If producer has orders delete is impossible, account has to be soft-deleted */
            $producer = $em->getRepository(Producer::class)->findOneBy(array('relatedAccount' => $uid));
            if($producer && $producer->getRelatedBatches()->count()>0){
                $entity->setType('Producer');
                $translated = $this->get('translator')->trans('This producer has related batches, first remove the relations!');
                $this->errorHandler->add($translated,'');
            }
            if($producer){
                $em->remove($producer);
            }
            $entity->setRelatedProducerEntry(null);
        }
        if($newType !== 'Carrier'){
            /* Todo: Check for related orders
             If carrier has orders delete is impossible, account has to be soft-deleted */
            $carrier = $em->getRepository(Carrier::class)->findOneBy(array('relatedAccount' => $uid));
            if($carrier && $carrier->getRelatedProducers()->count()>0){
                $entity->setType('Carrier');
                $translated = $this->get('translator')->trans('This carrier has related producer, first remove the relations!');
                $this->errorHandler($translated,'');
            }
            if($carrier) {
                $em->remove($carrier);
            }
            $entity->setRelatedCarrierEntry(null);
        }

        if($newType !== 'Customer'){
            /* Todo: Check for related orders
             If customer has orders delete is impossible, account has to be soft-deleted */
            $entity->setRelatedCustomerEntry(null);
        }

        // Intercept errors
        if( $this->errorHandler->hasErrors()){
            throw new AccountHandleException('Form Errors',  $this->errorHandler);
        }

        switch($entity->getType()){
            case 'Admin':
                $admin = $em->getRepository(Admin::class)->findOneBy(array('relatedAccount' => $uid));
                if(!$admin){
                    $admin = new Admin();
                    $admin->setRelatedAccount($entity);
                    $em->persist($admin);
                }
                break;

            case 'Producer':
                $producer = $em->getRepository(Producer::class)->findOneBy(array('relatedAccount' => $uid));
                if(!$producer){
                    $producer = new Producer();
                    $producer->setRelatedAccount($entity);
                    $producer->setRelatedCarrier($entity->getRelatedProducerEntry()->getRelatedCarrier());
                    $producer->setNumber($entity->getRelatedProducerEntry()->getNumber());
                    $producer->setPickUpAddress($entity->getRelatedProducerEntry()->getPickUpAddress());
                    $em->persist($producer);
                    $entity->setRelatedProducerEntry($producer);
                };
                break;

            case 'Carrier' :
                $carrier = $em->getRepository(Carrier::class)->findOneBy(array('relatedAccount' => $uid));
                if(!$carrier) {
                    $carrier = new Carrier();
                    $carrier->setRelatedAccount($entity);
                    $carrier->setCostPerKm($entity->getRelatedCarrierEntry()->getCostPerKm());
                    $em->persist($carrier);
                    $entity->setRelatedCarrierEntry($carrier);
                }
                break;

            case 'Customer' :
                /** @var Customer $customer */
                $customer = $em->getRepository(Customer::class)->findOneBy(array('relatedAccount' => $uid));
                $previousDocument = $entity->getRelatedCustomerEntry()->getRelatedUg();
                $uploaded = $this->request->files->get('account')['relatedCustomerEntry']['relatedUg']['documentFile']['file'];
                $discountGroup = $this->request->request->get('account')['relatedCustomerEntry']['discountGroup'];
                if(!$customer) {
                    $customer = new Customer();
                    $customer->setRelatedAccount($entity);
                    $customer->setBillAddress($entity->getRelatedCustomerEntry()->getBillAddress());
                    $customer->setShippingAddress($entity->getRelatedCustomerEntry()->getShippingAddress());
                    $customer->setIsUgValid($entity->getRelatedCustomerEntry()->getIsUgValid());
                    $customer->setCustomerNumber($entity->getRelatedCustomerEntry()->getCustomerNumber());
                    $customer->setDebitNumber($entity->getRelatedCustomerEntry()->getDebitNumber());
                    $customer->setDiscountGroup($entity->getRelatedCustomerEntry()->getDiscountGroup());
                    $customer->setRelatedUg($this->get(DocumentController::class)->persistFile($uploaded,null,'UG'));
                    $em->persist($customer);
                    $entity->setRelatedCustomerEntry($customer);
                }
                 elseif ($customer) {
                     if ($uploaded !== null){
                         $customer->setRelatedUg($this->get(DocumentController::class)->persistFile($uploaded,$previousDocument, 'UG'));
                     }
                     if ($discountGroup === null || $discountGroup === ''){
                         $customer->setDiscountGroup(null);
                     }
                }
                break;
        }

        $role = 'ROLE_USER';
        switch ($entity->getType()){
            case 'Producer':
                $role = 'ROLE_PRODUCER';
                break;
            case 'Carrier':
                $role = 'ROLE_CARRIER';
                break;
            case 'Customer':
                $role = 'ROLE_CUSTOMER';
                break;
            case 'Admin':
                $role = 'ROLE_ADMIN';
                break;
        }
        $entity->setRoles(array($role));
        $this->get('fos_user.user_manager')->updateUser($entity, false);
        $em->persist($entity);
    }

    /**
     * @param Account $account
     * @throws \Exception
     */
    protected function prePersistAccountEntity($account): void
    {
        // Generate random password
        $password_pattern = 'ABCDEFGHJKMNPQRSTUVWXabcdefghjkmnpqrstuvewx23456789?!#$§';
        $password = '';
        for($i = 0, $rand = random_int(8,12); $i < $rand; $i++){
            $password .= $password_pattern[random_int(0, \strlen($password_pattern)-1)];
        }
        
        $account->setPlainPassword($password);
        $account->setUsername($account->getEmail());
        $account->setIsDeleted(false);
        $account->setIsActive(true);
        $account->setEnabled(true);
        $account->setFailedLogins(0);
        $account->setLastLoginAttempt(new \DateTime);

        $em = $this->getDoctrine()->getManager();

        $layout  = 'easy_admin/_Mail/registeredByAdmin.html.twig';
        switch($account->getType()){
            case 'Admin':
                $account->setRelatedCustomerEntry(null);
                $account->setRelatedCarrierEntry(null);
                $account->setRelatedProducerEntry(null);

                $admin = new Admin();
                $admin->setRelatedAccount($account);
                $em->persist($admin);
                break;

            case 'Producer':
                $account->setRelatedCustomerEntry(null);
                $account->setRelatedCarrierEntry(null);

                $layout  = 'easy_admin/_Mail/registeredByAdminProducer.html.twig';
                $producer = $account->getRelatedProducerEntry();
                $producer->setRelatedAccount($account);
                $producer->setRelatedCarrier($account->getRelatedProducerEntry()->getRelatedCarrier());
                $producer->setNumber($account->getRelatedProducerEntry()->getNumber());
                $account->setRelatedProducerEntry($producer);
                break;

            case 'Carrier' :
                $account->setRelatedCustomerEntry(null);
                $account->setRelatedProducerEntry(null);

                $layout  = 'easy_admin/_Mail/registeredByAdminCarrier.html.twig';
                $carrier = $account->getRelatedCarrierEntry();
                $carrier->setRelatedAccount($account);
                $carrier->setCostPerKm($account->getRelatedCarrierEntry()->getCostPerKm());
                $account->setRelatedCarrierEntry($carrier);
                break;
            case 'Customer':
                $account->setRelatedCarrierEntry(null);
                $account->setRelatedProducerEntry(null);

                /**
                 * @var $customer Customer
                 */
                $customer = $account->getRelatedCustomerEntry();
                $customer->setRelatedAccount($account);
                $previousVersion = $customer->getRelatedUg();
                $newVersion = $this->request->files->get('account')['relatedCustomerEntry']['relatedUg']['documentFile']['file'];
                if ($newVersion !== null && $previousVersion->getId() !== null){
                    $document = $this->get(DocumentController::class)->persistFile($newVersion,$previousVersion,'UG');
                    $customer->setRelatedUg($document);
                }
                $customer->setIsUgValid(false);
                $account->setRelatedCustomerEntry($customer);
                break;
        }
        $session = $this->request->getSession();
        if ($session !== null) {
        $session->set('_account_related_template', $layout);
        $session->set('_account_new_plain_password', $password);
        }

        $role = 'ROLE_USER';
        switch ($account->getType()){
            case 'Producer':
                $role = 'ROLE_PRODUCER';
                break;
            case 'Carrier':
                $role = 'ROLE_CARRIER';
                break;
            case 'Customer':
                $role = 'ROLE_CUSTOMER';
                break;
            case 'Admin':
                $role = 'ROLE_ADMIN';
                break;
        }
        $account->setRoles(array($role));

        $this->get('fos_user.user_manager')->updateUser($account, false);
    }

    protected function editAccountAction()
    {
        try{
            $response = parent::editAction();
            if ($response instanceof RedirectResponse) {
                return $this->redirect($this->generateUrl('easyadmin', ['entity' => 'Account', 'action' => 'show', 'id' => $this->request->attributes->get('easyadmin')['item']->getId()]));
            }
            return $response;
        } catch(AccountHandleException $e){
            $flashBag =  $this->request->getSession()->getFlashBag();
            foreach ($e->getMultipleErrors()->get()->getValues() as $x){
               $flashBag->add('error '.$x['cssClass'], $x['errorMessage']);
            }
            return $this->redirect($this->generateUrl('easyadmin', ['entity' => 'Account', 'action' => 'edit', 'id' => $this->request->attributes->get('easyadmin')['item']->getId()]));
        }
    }

    /**
     * @return OptimisticLockException|ORMException|\Exception|RedirectResponse
     * @throws ORMException
     */
    protected function deleteUserAction(){
        $flashBag =  $this->request->getSession()->getFlashBag();
        $id = $this->request->query->get('id');

        $repository = $this->em->getRepository(Account::class);
        /** @var Account $entity */
        $entity = $repository->find($id);
        if($entity->getType() === 'Admin') {
            if ($this->getUser() === $entity) {
                $flashBag->add('error', 'you are trying to delete YourSelf');
                return $this->redirectToRoute('easyadmin', array(
                    'action' => 'list',
                    'entity' => 'Account'
                ));
            }
            /** @var Admin $admin */
            $admin = $this->em->getRepository(Admin::class)->findOneBy(array('relatedAccount'=>$entity));
            if ($admin) {
                $users = $admin->getRelatedAccounts();
                foreach ($users as $user) {
                    $user->setRelatedAdmin(null);
                    try {
                        $this->em->persist($user);
                    } catch (ORMException $e) {
                        $flashBag->add('error ' . $e->getTraceAsString(), $e->getMessage());
                        continue;
                    }
                }
                $this->em->remove($admin);
            }
            try {

                $this->em->remove($entity);
                $this->em->flush();

            } catch (ORMException $e) {
                $flashBag->add('error ' . $e->getTraceAsString(), $e->getMessage());
            }
            $flashBag->add('success', 'Admin ' .$entity->getFirstName() . ' is deleted');
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'entity' => 'Account'
            ));
        }

        $entity->setIsDeleted(true);
        $email = $entity->getEmail();

        $id = '';
        while($repository->findOneBy(array('email' => str_replace('@','#'.$id, $email)))){
            $id = ($id === '') ? 0 : (int)$id +1;
        }
        $entity->setEmail(str_replace('@', '#' . $id, $email));
        $entity->setIsDeleted(true);
        $entity->setIsActive(false);
        $entity->setEnabled(false);

        try {
            $this->em->flush();
        } catch (OptimisticLockException $e) {
                $flashBag->add('error '.$e->getTraceAsString(), $e->getMessage());
            return $e;
        } catch (ORMException $e) {
            $flashBag->add('error '.$e->getTraceAsString(), $e->getMessage());
            return $e;
        }
        $flashBag->add('success', 'User ' .$entity->getFirstName() . ' is deleted');
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => 'Account'
        ));
    }

    private function getEntityDqlFilter($type): string
    {
        switch ($type){
            case 'admin' :
                $dql_filter = "entity.type = 'Admin' and entity.isDeleted = 0";
                break;
            case 'carrier' :
                $dql_filter = "entity.type = 'Carrier' and entity.isDeleted = 0";
                break;
            case 'producer' :
                $dql_filter = "entity.type = 'Producer' and entity.isDeleted = 0";
                break;
            case 'customer' :
                $dql_filter = "entity.type = 'Customer' and entity.isDeleted = 0";
                break;
            case 'deleted' :
                $dql_filter = 'entity.isDeleted = 1';
                break;
            default:
                $dql_filter = 'entity.isDeleted = 0';
                break;
        }

        return $dql_filter;
    }

    protected function listAccountAction()
    {
        $this->entity['list']['dql_filter'] = $this->getEntityDqlFilter($this->request->query->get('filter'));
        if ($this->getEntityDqlFilter($this->request->query->get('filter')) === 'deleted'){
            $this->entity['list']['dql_filter'] = $this->getEntityDqlFilter($this->request->query->get('filter'));
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'entity' => 'Deleted',
            ));
        }
        return parent::listAction();
    }

    protected function createSearchAccountQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null)
    {
        $dqlFilter = $this->getEntityDqlFilter($this->request->query->get('filter'));
        return parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter); // TODO: Change the autogenerated stub
    }

}