<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 11:26
 */

namespace AppBundle\Controller;

use AppBundle\Response\ApiPagination;
use AppBundle\Response\ApiResponse;
use AppBundle\Response\RepositoryResponse;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends FOSRestController
{

    private $em = null;
    public $perPage = 10;
    public $recordCount = 0;
    public $offset = 0;
    public $single = false;
    /**
     * @return mixed
     * @FOS\Get("init")
     */
    public function initAction()
    {
        $data = [];

        return $this->response($data);
    }

    public function getEntityManager()
    {
        return $this->em!=null ? $this->em : $this->getDoctrine()->getManager();
    }
    public function getRepo($entity)
    {
        return $this->getEntityManager()->getRepository(is_object($entity) ?  $entity : "AppBundle:".$entity);
    }
    public function singularResult()
    {
        $this->single=true;
        return $this;
    }
    public function response($data,$httpStatusCode=200,$message=null,$headers=array())
    {
        /**
         * @var ApiResponse $apiResponse
         */
        $apiResponse = $this->get('app.api_response');

        $resultSet = $data instanceof RepositoryResponse ? $data->getResultSet() : $data;
        $resultSet =  $this->single && is_array($resultSet) ? $resultSet[0] : $resultSet;
        $this->recordCount = $data instanceof RepositoryResponse ? $data->getTotalRecords() : count($resultSet);

        $pagination = new ApiPagination($this->perPage,$this->recordCount,$this->offset,false);

        return $apiResponse->responseView($resultSet,$pagination,$httpStatusCode,$message,$headers);
    }

    public function queryToFilter(Request $request)
    {


        $filter['limit']=$this->perPage;

        foreach ($request->query->all() as $key => $value)
        {
            $filter[$key] = $value;
        }

        $filter['offset']= isset($filter['offset']) ? $filter['offset'] : 0;

        $this->offset = $filter['offset'];
        $this->perPage = $filter['limit'];
        return $filter;
    }
}