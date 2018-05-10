<?php
/**
 * Created by PhpStorm.
 * User: erman.titiz
 * Date: 15.05.2017
 * Time: 15:43
 */

namespace AppBundle\EventListener;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use AppBundle\Response\ApiResponse;
use AppBundle\Exception\BaseException;
class ExceptionListener
{

    protected $kernel;

    /**
     * @var ApiResponse
     */
    protected  $apiResponse;

    public function __construct( $kernel, ApiResponse $apiResponse)
    {
        $this->kernel = $kernel;
        $this->apiResponse = $apiResponse;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        $exception = $event->getException();
        $data = array(
            'file'=> $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' =>$exception->getTraceAsString()
        );
        if($exception instanceof BaseException) {
            $code = $exception->getErrorCode();
            $message = $exception->getMessage();
        }else{
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $exception = new BaseException($exception->getCode() ?? "400", array(),$exception->getMessage());
        }


        $code = $code === 0 ? 400 : $code;

        $this->apiResponse->setException($data);
        $response = $this->apiResponse->responseView(null,array(),$code,$message);
        $event->setResponse($response);
    }

}