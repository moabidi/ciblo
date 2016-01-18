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

class VisitRepository extends EntityRepository{

    public function getResolutionDistribution($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        /*$queryBuilder->add('select', new Expr\Select(array('v, count(v.resolution) as nb')))
                    ->add('from', new Expr\From($this->_entityName, 'v'))
                    ->add('orderBy', new Expr\GroupBy('v.resolution'));
                    ->add('orderBy', new Expr\OrderBy('nb', 'DESC'));*/

      $queryBuilder->select('v.resolution as name, COUNT(v.resolution) as y')
      ->from($this->_entityName, 'v')
      ->groupBy('v.resolution')
      ->orderBy('y','DESC')
      ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getDeviceDistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.prepherique, COUNT(v.prepherique) as nb')
            ->from($this->_entityName, 'v')
            ->groupBy('v.prepherique')
            ->orderBy('nb','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getOsDistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.os, COUNT(v.os) as nb')
            ->from($this->_entityName, 'v')
            ->groupBy('v.os')
            ->orderBy('nb','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getNavigatorDistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.navigateur, COUNT(v.navigateur) as nb')
            ->from($this->_entityName, 'v')
            ->groupBy('v.navigateur')
            ->orderBy('nb','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getTauxEvolution(){
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.date, SUM(v.nbVist) as nbVisit, (SELECT count(o.date) FROM CibloBundle:Order o where SUBSTRING(o.date, 1, 10) = v.date) as nbOrders')
            ->from($this->_entityName, 'v')
            ->groupBy('v.date')
            ->orderBy('v.date','ASC');
        return $queryBuilder->getQuery()->getArrayResult();
    }
}