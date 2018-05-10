<?php

namespace AppBundle\Response;

class RepositoryResponse
{
    private $resultSet;
    private $totalRecords;

    /**
     * RepositoryResponse constructor.
     * @param $resultSet
     * @param int $totalRecords
     */
    public function __construct($resultSet, int $totalRecords = 1)
    {
        $this->resultSet = $resultSet;
        $this->totalRecords = $totalRecords;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setTotalRecords(int $count){
        $this->totalRecords = $count;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalRecords(){
        return $this->totalRecords;
    }

    /**
     * @return mixed
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * @param mixed $resultSet
     */
    public function setResultSet($resultSet): void
    {
        $this->resultSet = $resultSet;
    }
}