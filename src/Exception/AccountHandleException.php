<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 07.11.2017
 * Time: 11:30
 */

namespace App\Exception;

use RuntimeException;
use Symfony\Component\Debug\ErrorHandler;

class AccountHandleException extends RuntimeException
{
    /**
     * @var ErrorHandler
     */
    private $multipleErrors;

    /**
     * AccountHandleException constructor.
     * @param $errorMessage
     * @param $multipleErrors
     */
    public function __construct($errorMessage, ErrorHandler $multipleErrors)
    {
        $this->multipleErrors = $multipleErrors;
        parent::__construct($errorMessage);
    }


    /**
     * @return ErrorHandler
     */
    public function getMultipleErrors():ErrorHandler
    {
        return $this->multipleErrors;
    }

    /**
     * @param ErrorHandler $multipleErrors
     */
    public function setMultipleErrors(ErrorHandler $multipleErrors):void
    {
        $this->multipleErrors = $multipleErrors;
    }

}