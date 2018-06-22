<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 12:37
 *
 * This needs a lifecycle...doooiinnggggg
 * http://symfony.com/doc/2.2/cookbook/doctrine/file_uploads.html
 * https://symfony.com/doc/3.4/controller/upload_file.html
 */
namespace app\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="document")
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks()
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="Document")
     * @ORM\JoinColumn(name="previous_version", referencedColumnName="id", nullable=true)
     * @var Document
     */
    private $previousVersion;
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @var datetime
     */
    private $dateUploaded;
    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('UG','Bill','OrderConfirmation', 'ADR')")
     */
    private $docType;

    /**
     * @Vich\UploadableField(mapping="document_file", fileNameProperty="name")
     * @var File
     */
    private $documentFile;

    public function __toString() {
        return $this->name.'';
    }

    /**
     * @return string
     */
    public function getDocType(): string
    {
        return $this->docType.'';
    }

    /**
     * @param string $docType
     */
    public function setDocType(string $docType):void
    {
        $this->docType = $docType;
    }

    /**
     * @return DateTime
     */
    public function getDateUploaded(): DateTime
    {
        return $this->dateUploaded;
    }

    /**
     * @param DateTime $dateUploaded
     */
    public function setDateUploaded(DateTime $dateUploaded):void
    {
        $this->dateUploaded = $dateUploaded;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name.'';
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name = null): void
    {
        $this-> name = $name.'';
    }


    /**
     * @return Document|null
     */
    public function getPreviousVersion(): ?Document
    {
        return $this->previousVersion;
    }

    /**
     * @param Document|null $previousVersion
     */
    public function setPreviousVersion(Document $previousVersion = null):void
    {
        $this->previousVersion = $previousVersion;
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
     * @return File|null
     */
    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    /**
     * * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     *
     * @param File|UploadedFile $documentFile
     * @return void
     * @throws \Exception
     */
    public function setDocumentFile(?File $documentFile = null): void
    {
        $this->documentFile = $documentFile;
        if (null !== $documentFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->dateUploaded = new \DateTime();
        }
    }

}