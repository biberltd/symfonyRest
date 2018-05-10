<?php
/**
 * Created by PhpStorm.
 * User: ertiz
 * Date: 5.03.2018
 * Time: 13:40
 */

namespace AppBundle\Repository;


use AppBundle\Response\RepositoryResponse;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class CoreRepository extends EntityRepository
{

    private $orderBy=[];
    /**
     * @param QueryBuilder $queryBuilder
     * @param array|null $limit
     * @return QueryBuilder
     */
    public function addLimitToQueryBuilder(QueryBuilder $queryBuilder, array $limit = null) {
        $limit = $limit ?? ['offset' => 0, 'limit' => 20];
        if ($limit != null) {
            if (isset($limit['offset']) && isset($limit['limit'])) {
                $queryBuilder->setFirstResult($limit['offset']);
                $queryBuilder->setMaxResults($limit['limit']);
            }
        }
        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array|null $orderBy
     * @param string|null $alias
     * @return QueryBuilder
     */
    public function addOrderByToQueryBuilder(QueryBuilder $queryBuilder, array $orderBy = null, string $alias = null) {
        $orderBy = $orderBy ?? [];
        if(count($orderBy) < 1){
            return $queryBuilder;
        }
        foreach($orderBy as $index => $orderStr){
            if(intval($index)!=$index && in_array($index,['lt','-','<']))
            {
                $direction='DESC';
                $orderColumn=$orderStr;
            }else{
                $directionIndicator = substr($orderStr, 0, 1);
                $orderColumn = str_replace(['+', '-'], '', $orderStr);
                $direction = $directionIndicator == '-' ? 'DESC' : 'ASC';
            }
            $indexArr = explode('.',$orderColumn);
            if(is_array($indexArr) && count($indexArr)>1)
            {
                $tableColumn = $indexArr[0];
                unset($indexArr[0]);
                $jsonColumn =implode('.',$indexArr);
                $queryBuilder->addOrderBy("JSON_EXTRACT(".$alias.".".$tableColumn.", '$.$jsonColumn') ",$direction);
            }else{
                $queryBuilder->addOrderBy($alias.".".$orderColumn, $direction);
            }
        }
        return $queryBuilder;
    }

    public function filterToBuilderParser(QueryBuilder &$qb,$alias,$filters)
    {

        foreach($filters as $index => $filter)
        {
            if($index=='limit' && !is_null($filter))
            {
                $this->addLimitToQueryBuilder($qb,$filters) ;
                continue;
            }
            if($index=='sort' && !is_null($filter))
            {
                $this->addOrderByToQueryBuilder($qb,$filters['sort'],$alias);
                continue;
            }
            if($index=='offset' && !is_null($filter))
            {
                continue;
            }
            if($index=='like' && !is_null($filter))
            {
                $this->addLikeByToQueryBuilder($qb,$filters['like'],$alias);
                continue;
            }
            if(is_array($filter))
            {

                $whereIn=[];
                foreach($filter as $i => $v)
                {
                    switch ((string)$i)
                    {
                        case 'gt':
                        case 'lt':
                        case 'gte':
                        case 'lte':
                        case 'eq':
                            $function = $i;
                            break;
                        default:
                            $function = 'in';
                            $whereIn[] = $v;

                    }
                    if($function!='in')
                    {
                        if(strripos($index,'date') !== false)
                        {
                            $time = ($function == 'gte') ? ' 00:00:00' : ' 23:59:59';
                            $time = ($function == 'eq' || $function=='gt' || $function=='lt') ? '' : $time;

                            $qb->andWhere($qb->expr()->{$function}($alias.'.'.$index, ':'.$index.$i))
                                ->setParameter($index.$i, date('Y-m-d'.$time,strtotime($v)));
                        }else{
                            $qb->andWhere($qb->expr()->{$function}($alias.'.'.$index, ':'.$index.$i))
                                ->setParameter($index.$i, $v);
                        }
                    }
                }
                if(count($whereIn)>0)
                    $qb->andWhere($qb->expr()->in($alias.'.'.$index, ':'.$index))
                        ->setParameter($index, $whereIn);
            }else{
                if($filter!="")
                {

                    if(strpos($filter,"~")>-1)
                    {
                        $filter = "%".str_replace('~','',$filter)."%";
                        $qb->andWhere($qb->expr()->like($alias.'.'.$index, ':'.$index))->setParameter($index, $filter);
                    }else{
                        $qb->andWhere($qb->expr()->eq($alias.'.'.$index, ':'.$index))->setParameter($index, $filter);
                    }

                }

            }
        }
    }

    private function addLikeByToQueryBuilder(QueryBuilder &$qb, $filter, $alias)
    {

        $arg=[];
        foreach ($filter as $index => $value)
        {
            if(strpos($index,'.'))
            {
                $indexArr = explode('.',$index);
                $tableColumn = $indexArr[0];
                unset($indexArr[0]);
                $jsonColumn =implode('.',$indexArr);
                $qb->andWhere($qb->expr()->isNotNull("JSON_SEARCH(".$alias.".".$tableColumn.", '$.$jsonColumn','$value') "));
                continue;
            }
            if(strpos($index,'|'))
            {
                $expr = $qb->expr()->orX();
                $filterIndex = explode('|',$index);
                foreach($filterIndex as $findex)
                    $arg[] = $qb->expr()->like($alias.'.'.$findex,':'.str_replace('|','',$index));

                $qb->andWhere($expr->addMultiple($arg))->setParameter(str_replace('|','',$index), '%'.$value.'%');
                continue;
            }
            if(strpos($index,'&'))
            {
                $expr = $qb->expr()->andX();
                $filterIndex = explode('&',$index);
                foreach($filterIndex as $findex)
                    $arg[] = $qb->expr()->like($alias.'.'.$findex,':'.str_replace('&','',$index));

                $qb->andWhere($expr->addMultiple($arg))->setParameter(str_replace('&','',$index), '%'.$value.'%');
                continue;
            }

            $qb->andWhere($qb->expr()->like($alias.'.'.$index,':'.$index))->setParameter($index, '%'.$value.'%');

        }
        return $qb;
    }

    public function addOrderBy($column)
    {
        if(is_array($column))
        {
            $this->orderBy=$column;
        }else{
            $this->orderBy[]=$column;
        }

        return $this;
    }

    public function findWithFilter(array $criteria)
    {
        $limit = isset($criteria['limit']) ? $criteria['limit'] : null;
        $offset = isset($criteria['offset']) ? $criteria['offset'] : null;
        $sort = isset($criteria['sort']) ? $criteria['sort'] : null;
        $like = isset($criteria['like']) ? $criteria['like'] : null;

        unset($criteria['limit']);
        unset($criteria['offset']);
        unset($criteria['sort']);
        unset($criteria['like']);

        $count = parent::count($criteria);
        $criteria['limit'] = $limit;
        $criteria['offset'] = $offset;
        $criteria['sort'] = $sort;
        $criteria['like'] = $like;

        $qb = $this->createQueryBuilder('s');
        $this->filterToBuilderParser($qb,'s',$criteria);

        if(count($this->orderBy)>0)
        {
            foreach ($this->orderBy as $orderBy)
            {
                list($alias,$column) = strpos($orderBy,'.')>-1 ? explode('.',str_replace(['+','-'],['',''],$orderBy)) : ['s',str_replace(['+','-'],['',''],$orderBy)];
                $qb->addOrderBy($alias.'.'.$column,strpos($orderBy,'-')>-1 ? 'DESC' : 'ASC');
            }

        }
        $data = $qb->getQuery()->getResult();

        //$data = parent::findBy($criteria, $orderBy, $limit, $offset);

        return new RepositoryResponse($data,$count);
    }
}