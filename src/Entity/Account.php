<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="account")
 * @UniqueEntity("email")
 *
 * @ORM\HasLifecycleCallbacks
 */
class Account extends BaseUser implements EquatableInterface
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $companyName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstName;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Regex(
     *     pattern="/^[^\W][a-zA-Z0-9._]+(\.[a-zA-Z0-9._]+)*\@[a-zA-Z0-9_]+\-?[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/",
     *     message="falsche Eingabe im E-Mail.Gültiges Format abc@def.asd oder abc.def@saq.de oder abc_def@yahoo.com"
     * )
     *
     */
    private $altMail;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\Regex(
     *     pattern="/^\+?\d*+$/",
     *     message="Telefonummer ist nicht gültig. Gültiges Format zB. +43911678123 order 12345678"
     * )
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Regex(
     *     pattern="/^\+?\d*+$/",
     *     message="Telefonummer ist nicht gültig. Gültiges Format zB. +43911678123 order 12345678"
     * )
     */
    private $fax;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('Customer','Admin','Producer','Carrier')")
     */
    private $type;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('Male','Female','Company')")
     */
    private $gender;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isDeleted = 0;


    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", nullable=true, columnDefinition="ENUM( 'Dr.', 'Prof.', 'Prof. Dr.', 'Herr', 'Frau' )" )
     */
    private $title;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private $failedLogins = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastLoginAttempt;

    /**
     * @ORM\ManyToOne(targetEntity="Admin", inversedBy="relatedAccounts", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $relatedAdmin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Address", mappedBy="relatedAccount", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $relatedAddresss;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Carrier", mappedBy="relatedAccount", orphanRemoval=true, cascade={"persist"})
     * @Assert\Type(type="App\Entity\Carrier")
     * @Assert\Valid()
     */
    private $relatedCarrierEntry;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Producer", mappedBy="relatedAccount", orphanRemoval=true, cascade={"persist"})
     * @Assert\Type(type="App\Entity\Producer")
     * @Assert\Valid()
     */
    private $relatedProducerEntry;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Customer", mappedBy="relatedAccount", orphanRemoval=true, cascade={"persist"})
     * @Assert\Type(type="App\Entity\Customer")
     * @Assert\Valid()
     */
    private $relatedCustomerEntry;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="es darf nicht leer sein")
     * @Assert\Type(type="string")
     * @Assert\Regex(
     *     pattern="/^[^\W][a-zA-Z0-9._]+(\.[a-zA-Z0-9._]+)*\@[a-zA-Z0-9_]+\-?[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/",
     *     message="falsche Eingabe im E-Mail.Gültiges Format abc@def.asd oder abc.def@saq.de oder abc_def@yahoo.com"
     * )
     */
    protected $email;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     * @Assert\Regex(
     *     pattern="/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/",
     *     message="Must Include: One Capital letter, One Small Letter, One Number, One special Character, and eight or more characters"
     * )
     */
    protected $plainPassword;



    public function __construct()
    {
        $this->isActive = true;
        $this->relatedAddresss = new ArrayCollection();
        parent::__construct();
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getAltMail(): ?string
    {
        return $this->altMail;
    }

    /**
     * @param string|null $altMail
     */
    public function setAltMail(string $altMail = null): void
    {
        $this->altMail = $altMail;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(string $phone = null): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }

    /**
     * @param string|null $fax
     */
    public function setFax(string $fax = null): void
    {
        $this->fax = $fax;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(string $type= null): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function getIsDeleted(): bool
    {
        return (bool)$this->isDeleted;
    }

    /**
     * @param bool|null $isDeleted
     */
    public function setIsDeleted(bool $isDeleted = null): void
    {
        $this->isDeleted = $isDeleted;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(string $title = null): void
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getFailedLogins(): ?int
    {
        return $this->failedLogins;
    }

    /**
     * @param int|null $failedLogins
     */
    public function setFailedLogins(int $failedLogins = null): void
    {
        $this->failedLogins = $failedLogins;
    }

    /**
     * @return null|DateTime
     */
    public function getLastLoginAttempt(): ?DateTime
    {
        return $this->lastLoginAttempt;
    }

    /**
     * @param null|DateTime $lastLoginAttempt
     */
    public function setLastLoginAttempt(DateTime $lastLoginAttempt = null): void
    {
        $this->lastLoginAttempt = $lastLoginAttempt;
    }

    /**
     * @return Admin|null
     */
    public function getRelatedAdmin(): ?Admin
    {
        return $this->relatedAdmin;
    }

    /**
     * @param Admin|null $relatedAdmin
     */
    public function setRelatedAdmin(Admin $relatedAdmin = null): void
    {
        $this->relatedAdmin = $relatedAdmin;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(string $firstName = null): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(string $lastName =null): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->firstName . ' ' . $this->lastName . '(' . $this->username . ')';
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     */
    public function setGender(string $gender = null): void
    {
        $this->gender = $gender;
    }

    /**
     * @return ArrayCollection|Address[]
     */
    public function getRelatedAddresss()
    {
        return $this->relatedAddresss;
    }

    /**
     * @param Address $address
     * @return bool
     */
    public function addRelatedAddress(Address $address): bool
    {
        if(!$this->relatedAddresss->contains($address)){
            $this->relatedAddresss[] = $address;
            $address->setRelatedAccount($this);
            return true;
        }
        return false;
    }

    /**
     * @param Address $address
     * @return bool
     */
    public function removeRelatedAddress(Address $address): bool
    {
        if(!$this->relatedAddresss->contains($address)){
            return false;
        }
        $this->relatedAddresss->removeElement($address);
        $address->setRelatedAccount(null);
        return true;
    }
    /**
     * @return Carrier|null
     */
    public function getRelatedCarrierEntry(): ?Carrier
    {
        return $this->relatedCarrierEntry;
    }

    /**
     * @param Carrier|null $relatedCarrierEntry
     */
    public function setRelatedCarrierEntry(Carrier $relatedCarrierEntry = null): void
    {
        $this->relatedCarrierEntry = $relatedCarrierEntry;
    }

    /**
     * @return Producer|null
     */
    public function getRelatedProducerEntry(): ?Producer
    {
        return $this->relatedProducerEntry;
    }

    /**
     * @param Producer|null $relatedProducerEntry
     */
    public function setRelatedProducerEntry(Producer $relatedProducerEntry = null): void
    {
        $this->relatedProducerEntry = $relatedProducerEntry;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName.'';
    }

    /**
     * @param string|null $companyName
     */
    public function setCompanyName(string $companyName = null): void
    {
        $this->companyName = $companyName.'';
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return '' !== $this->companyName ? $this->companyName.'' : $this->firstName . ' ' . $this->lastName;
    }


    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }


    // Some methods for FOS

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(): void
    {
        $this->isDeleted = false;
        $this->failedLogins = 0;
        $this->lastLoginAttempt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function PreUpdate(): void
    {
        $this->lastLoginAttempt = new \DateTime();
    }

    /**
     * @return Customer|null
     */
    public function getRelatedCustomerEntry(): ?Customer
    {
        return $this->relatedCustomerEntry;
    }

    /**
     * @param Customer|null $relatedCustomerEntry
     */
    public function setRelatedCustomerEntry(Customer $relatedCustomerEntry = null): void
    {
        $this->relatedCustomerEntry = $relatedCustomerEntry;
        if($this->relatedCustomerEntry !== null){
            $this->relatedCustomerEntry->setRelatedAccount($this);
        }
    }

    // Validation Callback
    // TODO: Refactor & Outsource

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if($this->companyName === '' && $this->firstName === '' && $this->lastName === '' )
        {

            $context->buildViolation('Either company name nor first name and last name are set!')
                ->atPath('firstName')
                ->addViolation();
            $context->buildViolation('Either company name nor first name and last name are set!')
                ->atPath('lastName')
                ->addViolation();
            $context->buildViolation('Either company name nor first name and last name are set!')
                ->atPath('companyName')
                ->addViolation();
        }
        if($this->type === 'Admin' && ($this->firstName === null || $this->lastName === null)){
            $context->buildViolation('Each admin has to been an first and last name!')
                ->atPath('firstName')
                ->addViolation();
            $context->buildViolation('Each admin has to been an first and last name!')
                ->atPath('lastName')
                ->addViolation();
        }

    }

    /**
     * @return bool|int
     */
    public function isAccountNonExpired()
    {
        //reverse the boolean value account becomes expired
        $isDeleted = $this->isDeleted;
        $isDeleted = !$isDeleted;
        return $isDeleted;
    }

    /**
     * @return bool|null
     */
    public function isAccountNonLocked(): ?bool
    {
        return $this->isActive;
    }

    /**
     * Function to help FOSuserbundle login with user change
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user):bool
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;

    }
}