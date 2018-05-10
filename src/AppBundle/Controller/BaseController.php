<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 11:26
 */

namespace AppBundle\Controller;

use AppBundle\Entity\EmployeeWorkshop;
use AppBundle\Response\ApiPagination;
use AppBundle\Response\ApiResponse;
use AppBundle\Response\RepositoryResponse;
use AppBundle\Traits\Information;
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

    use Information;
    /**
     * @return mixed
     * @FOS\Get("init")
     */
    public function initAction()
    {
        $data = [];
        array_walk($this->data,function($item, $key) use (&$data){
            $data[$key]= array_values($item);
            //return array_values($row);
        });

        $data['workshopPrice'] = $this->getFinancialexpense();

        return $this->response($data);
    }

    public function getFinancialexpense($arrayValue=true)
    {
        $financialExpenseRepo = $this->getRepo('FinancialExpense');
        $employeWorkshopRepo = $this->getRepo('EmployeeWorkshop');

        $employeeRepo = $this->getRepo('Employee');
        $financialExpense = $financialExpenseRepo->getLastFinancialExpense();

        $data['expense'] = $financialExpense;

        $totalSallary = $employeeRepo->getTotalGrossSalary();



        $totalWorkmanshipExpense = 0;
        foreach($financialExpense->getExpense()['workshopExpense'] as $workmanshipExpense)
        {
            $totalWorkmanshipExpense += $workmanshipExpense['price'];
        }

        $totalgeneralExpense = 0;
        foreach($financialExpense->getExpense()['generalExpense'] as $generalExpense)
        {
            $totalgeneralExpense += $generalExpense['price'];
        }

        $totalsuppliesExpense = [];
        foreach($financialExpense->getExpense()['suppliesExpense'] as $suppliesExpense)
        {
            $price = isset($totalsuppliesExpense[$suppliesExpense['workshop']['id']]) ?? 0;
            $totalsuppliesExpense[$suppliesExpense['workshop']['id']] = $price + ($suppliesExpense['price'] / $suppliesExpense['time']);
        }

        $workshopTotals=[];
        /**
         * @var EmployeeWorkshop $employeeWorkshop
         */
        foreach($employeWorkshopRepo->findAll() as $employeeWorkshop)
        {
            $workHour = $employeeWorkshop->getWorkingRate() * $employeeWorkshop->getEmployee()->getWorkingHour();

            $workSallary = $employeeWorkshop->getWorkingRate() * $employeeWorkshop->getEmployee()->getGrossSalary();

            $workshop = $workshopTotals[$employeeWorkshop->getWorkshop()->getId()] ?? ['workshop'=>null,'hour'=>0,'sallary'=>0,'key'=>0,'workshopPrice'=>0,'generalPrice'=>0,'suppliesPrice'=>0,'totalPrice'=>0];

            $workshop['hour'] +=$workHour;
            $workshop['sallary'] +=$workSallary;
            $workshop['key'] =$workshop['sallary'] / $totalSallary;

            $workshop['workshop'] = [
                'id'=>$employeeWorkshop->getWorkshop()->getId(),
                'name'=>$employeeWorkshop->getWorkshop()->getName()
            ];
            $workshop['workshopPrice'] = ($totalWorkmanshipExpense * $workshop['key']) / $workshop['hour'];
            $workshop['generalPrice'] = ($totalgeneralExpense * $workshop['key']) / $workshop['hour'];
            $workshop['suppliesPrice'] = $totalsuppliesExpense[$employeeWorkshop->getWorkshop()->getId()] ?? 0;
            $workshop['totalPrice'] = $workshop['workshopPrice'] + $workshop['generalPrice'] + $workshop['suppliesPrice'];
            $workshopTotals[$employeeWorkshop->getWorkshop()->getId()]  = $workshop;
        }
        return $arrayValue ? array_values($workshopTotals) : $workshopTotals;
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


    public function jsonToObject(&$json)
    {
        foreach ($json as $index => &$value)
        {
            if(is_array($value)) $this->jsonToObject($value);
            if(is_object($value) && property_exists($value,'id')) $value = $value->id;
            if(is_object($value) && property_exists($value,'key') && property_exists($value,'label') && count((array)$value)==2) $value = $value->key;
            if(is_object($value) && !property_exists($value,'id')) $this->jsonToObject($value);
        }
    }

    public function obj2array ( $Instance=null )
    {
        $rtn = [];
        if (is_null($Instance)) return null;
        if (is_array($Instance)) {
            foreach ($Instance as $ins) $rtn[] = $this->obj2array($ins);
            return $rtn;
        }

        if(is_object($Instance))
        {
            if (method_exists($Instance, 'getId')) $rtn['id'] = $Instance->getId();
            if (method_exists($Instance, 'getTime')) $rtn['time'] = $Instance->getTime();
            if (method_exists($Instance, 'getName')) $rtn['name'] = $Instance->getName();
            if (method_exists($Instance, 'getCode')) $rtn['code'] = $Instance->getCode();
            if (method_exists($Instance, 'getUnit')) $rtn['unit'] = $Instance->getUnit();

            if (method_exists($Instance, 'getWorkmanship')) $rtn['workmanship'] = $this->obj2array($Instance->getWorkmanship());
            if (method_exists($Instance, 'getCurrency')) $rtn['currency'] = $this->obj2array($Instance->getCurrency());
            if (method_exists($Instance, 'getCompany')) $rtn['company'] = $this->obj2array($Instance->getCompany());
            if (method_exists($Instance, 'getMachine')) $rtn['machine'] = $this->obj2array($Instance->getMachine());
            if (method_exists($Instance, 'getDepreciationTime')) $rtn['depreciationTime'] = $Instance->getDepreciationTime();
            if (method_exists($Instance, 'getPrice')) $rtn['price'] = $Instance->getPrice();
            if (method_exists($Instance, 'getEnergy')) $rtn['energy'] = $Instance->getEnergy();
            if (method_exists($Instance, 'getWorkshop')) {
                if (!is_null($Instance->getWorkshop())) {
                    $rtn['workshop'] = [];
                    $rtn['workshop']['id'] = $Instance->getWorkshop()->getId();
                    $rtn['workshop']['name'] = $Instance->getWorkshop()->getName();
                    $rtn['workshop']['company'] = [];
                    $rtn['workshop']['company']['id']= $Instance->getWorkshop()->getDepartment()->getCompany()->getId();
                    $rtn['workshop']['company']['name']= $Instance->getWorkshop()->getDepartment()->getCompany()->getName();
                } else {
                    $rtn['workshop'] = null;
                }
            }
            if(method_exists($Instance,'getMachine')) $rtn['machine'] = $this->obj2array($Instance->getMachine());
        }
        return $rtn;
    }
}