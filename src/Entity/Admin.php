<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 10:50
 */

namespace app\Entity;



use AppBundle\Entity\Interfaces\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="admin")
 */
class Admin implements UserInterface
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $relatedAccount;


    /**
     * @ORM\OneToOne(targetEntity="Customer", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $loadedCustomer;


    /**
     * @ORM\OneToMany(targetEntity="Account", mappedBy="relatedAdmin")
     */
    private $relatedAccounts;

    public function __construct()
    {
        $this->relatedAccounts = new ArrayCollection();
    }


    /**
     * @return Account
     */
    public function getRelatedAccount():Account
    {
        return $this->relatedAccount;
    }

    /**
     * @param Account $relatedAccount
     */
    public function setRelatedAccount($relatedAccount):void
    {
        $this->relatedAccount = $relatedAccount;
    }

    /**
     * @return Customer
     */
    public function getLoadedCustomer():Customer
    {
        return $this->loadedCustomer;
    }

    /**
     * @param Customer $loadedCustomer
     */
    public function setLoadedCustomer(Customer $loadedCustomer):void
    {
        $this->loadedCustomer = $loadedCustomer;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id):void
    {
        $this->id = $id;
    }
    

    public function __toString()
    {
        return $this->getRelatedAccount()->getFirstName() . ' ' . $this->getRelatedAccount()->getLastName();
    }

    /**
     * @return ArrayCollection|Account[]
     */
    public function getRelatedAccounts()
    {
        return $this->relatedAccounts;
    }

    /**
     * @param Account $account
     */
    public function addRelatedAccount(Account $account):void
    {
        if(!$this->relatedAccounts->contains($account)){
            $this->relatedAccounts[] = $account;
            $account->setRelatedAdmin($this);
        }
    }

    /**
     * @param Account $account
     */
    public function removeRelatedAccount(Account $account):void
    {
        if(!$this->relatedAccounts->contains($account)){
            return ;
        }
        $this->relatedAccounts->removeElement($account);
        $account->setRelatedAdmin(null);
    }


}