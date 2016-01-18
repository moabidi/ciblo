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

    public function getCountVisit(){
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('SUM(v.nbVist) as totalVisit')
            ->from($this->_entityName, 'v');
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getResolutionDistribution($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        /*$queryBuilder->add('select', new Expr\Select(array('v, count(v.resolution) as nb')))
                    ->add('from', new Expr\From($this->_entityName, 'v'))
                    ->add('orderBy', new Expr\GroupBy('v.resolution'));
                    ->add('orderBy', new Expr\OrderBy('nb', 'DESC'));*/

      $queryBuilder->select('v.resolution as name, SUM(v.nbVist) as y')
      ->from($this->_entityName, 'v')
      ->groupBy('v.resolution')
      ->orderBy('y','DESC')
      ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getDeviceDistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.prepherique as name, SUM(v.nbVist) as y')
            ->from($this->_entityName, 'v')
            ->groupBy('v.prepherique')
            ->orderBy('y','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getOsDistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.os as name, SUM(v.nbVist) as y')
            ->from($this->_entityName, 'v')
            ->groupBy('v.os')
            ->orderBy('y','DESC')
            ->setMaxResults( $limit );;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getNavigatorDistribuation($limit){

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('v.navigateur as name, SUM(v.nbVist) as y')
            ->from($this->_entityName, 'v')
            ->groupBy('v.navigateur')
            ->orderBy('y','DESC')
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