<?php
/**
 * Created by JetBrains PhpStorm.
 * User: moabidi
 * Date: 17/01/16
 * Time: 14:45
 * To change this template use File | Settings | File Templates.
 */

namespace CibloBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class OrderRepository extends EntityRepository{

    public function getCountOrders(){
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('COUNT(o) as totalOrders')
            ->from($this->_entityName, 'o');
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getOrdersDistribution($limit){
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('o.date, SUBSTRING(o.date, 1, 10) as name ,COUNT(o) as y')
            ->from($this->_entityName, 'o')
            ->groupBy('name')
            ->orderBy('y','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getCADistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('SUBSTRING(o.date, 1, 10) as name, SUM(o.totalPaid) as y')
            ->from($this->_entityName, 'o')
            ->groupBy('name')
            ->orderBy('y','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getCATaux(){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('SUBSTRING(o.date, 1, 10) as day, SUM(o.totalPaid) as total, COUNT(o) as nbOrders')
            ->from($this->_entityName, 'o')
            ->groupBy('day')
            ->orderBy('total','DESC');
        return $queryBuilder->getQuery()->getArrayResult();
    }

}