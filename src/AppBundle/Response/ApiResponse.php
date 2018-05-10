<?php

namespace AppBundle\Response;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use JMS\Serializer\SerializationContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
class ApiResponse
{

    private $container;
    private $viewHandler;
    private $exception = null;
    public $totalRecords;

    /**
     * ApiResponse constructor.
     * @param ContainerInterface $container
     * @param ViewHandler $viewHandler
     */
    public function __construct(ContainerInterface $container, ViewHandler $viewHandler)
    {
        $this->container = $container;
        $this->viewHandler = $viewHandler;
    }

    /**
     * @param null $data
     * @param null $pagination
     * @param int $statusCode
     * @param null $message
     * @param array $headers
     * @return mixed
     */
    public function responseView($data = null, $pagination = null, $statusCode = 200, $message = null, array $headers = array()) {
        if($data === null) {
            $data = array();
        }
        $data = array(
            'code' => $statusCode,
            'result' => array(
                'set'=> $data
            ),
            'pagination' => $pagination,
            'message' => $message ?? 'msg.success.default'
        );

        if(!is_null($this->exception)) $data['exception'] = $this->exception;
        /**
         * @todo : seralizer kullanıcı yetkilerine göre çalışacak. Kullanıcı yetkilerini grup isimlerine çeviren bir kod yazacağız
         */
        /*$context = new Context();
        $context->addGroup('stock');
        $context->addGroup('name');*/
        $view = new View($data, $statusCode, $headers);
        //$view->setContext($context);
        return $this->viewHandler->handle($view);
    }

    /**
     * @param $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setTotalRecords($count){
        $this->totalRecords = $count;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalRecords(){
        return $this->totalRecords;
    }
}