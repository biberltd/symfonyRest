<?php
/**
 * Created by PhpStorm.
 * User: erman.titiz
 * Date: 23.05.2017
 * Time: 15:54
 */

namespace AppBundle\Exception;

class BaseException extends \Exception
{

    /**
     * @var string
     */
    private $reason;

    public $errorCode;

    private $replacements = [];


    public function __construct($errorCode, array $replacements = null, $currentMsg = null, \Exception $previous = null)
    {
        $this->errorCode = $errorCode;
        $this->replacements = $replacements ?? $this->replacements;
        $code = $this->getErrorCode();
        $message = $currentMsg ?? $this->getErrorMessage();
        $this->reason = $message;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    public function getErrorCode()
    {
        $sliceCode = $this->sliceCode($this->errorCode);

        return $sliceCode[0];
    }

    public function getErrorMessage()
    {
        $translator = new ExceptionTranslater();
        $message = $translator->getTranslation($this->errorCode, $this->replacements);
        if($this->reason!=null)
        {
            $message = $translator->getTranslation($message, $this->replacements) ?? $message;
        }
        return $message;
    }

    /**
     * @param $errorCode
     * @return array
     */
    private function sliceCode($errorCode)
    {
        return explode(".", $errorCode);
    }
}