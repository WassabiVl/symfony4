<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 11:43
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="address")
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    // TODO: Make sure that either companyName or names are filled
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $companyName;

    /**
     * @ORM\Column(type="string", nullable=true, columnDefinition="ENUM( 'Dr.', 'Prof.', 'Prof. Dr.', 'Herr', 'Frau' )" )
     */
    private $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $street;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $buildingNumber;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $city;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $state;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=8, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=8, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="relatedAddresss")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false))
     */
    private $relatedAccount;


    public function __toString()
    {
        $result = (\strlen($this->companyName) > 0) ? $this->companyName . PHP_EOL : '';
        $firstNameLength = \strlen($this->firstName);
        $lastNameLength = \strlen($this->lastName);
        if($firstNameLength || $lastNameLength){
            $result .= ($firstNameLength && $lastNameLength) ? $this->firstName . ' ' . $this->lastName : $this->firstName;
            $result .= ($lastNameLength && !$firstNameLength) ? $this->lastName : '';
            $result .= PHP_EOL;
        }
        $result .= $this->street . ' ';
        $result .= $this->buildingNumber. PHP_EOL;
        $result .= $this->zip. ' ';
        $result .= $this->city. PHP_EOL;
        $result .= $this->state. ', ';
        $result .= $this->country;
        return $result;
    }

    /**
     * @return Account|null
     */
    public function getRelatedAccount():?Account
    {
        return $this->relatedAccount;
    }

    /**
     * @param Account $relatedAccount
     */
    public function setRelatedAccount(Account $relatedAccount = null):void
    {
        $this->relatedAccount = $relatedAccount;
    }
    
    /**
     * @return string
     */
    public function getStreet(): String
    {
        return $this->street.'';
    }

    /**
     * @param String $street
     */
    public function setStreet(String $street =null):void
    {
        $this->street = $street.'';
    }

    /**
     * @return int
     */
    public function getZip(): int
    {
        return (int)$this->zip;
    }

    /**
     * @param int|null $zip
     */
    public function setZip(int $zip = null):void
    {
        $this->zip = (int)$zip;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city):void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country):void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $companyName
     */
    public function setCompanyName($companyName):void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName):void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName):void
    {
        $this->lastName = $lastName;
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


    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title):void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBuildingNumber():?string
    {
        return $this->buildingNumber;
    }

    /**
     * @param string $buildingNumber
     */
    public function setBuildingNumber($buildingNumber):void
    {
        $this->buildingNumber = $buildingNumber;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state):void
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude = null): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude = null): void
    {
        $this->longitude = $longitude;
    }


}