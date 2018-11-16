<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 15/11/18
 * Time: 23:46
 */

namespace OivBundle\Repository;


use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{

    public function getDistinctValueField($field)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.'.$field)
            ->from($this->_entityName, 'o')
            ->distinct('o.'.$field)
            ->orderBy('o.'.$field);
        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * SELECT COUNTRY_CODE AS pays,oivdataw.variety_data.*
    FROM oivdataw.variety_data where COUNTRY_CODE='FRA' AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @param array $aCriteria
     * @return array
     */
    public function getGlobalResult($aCriteria = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('c.tradeBloc, c.countryNameFr, o')
            ->from($this->_entityName, 'o')
            ->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode');
        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->where('o.countryCode = :countryCode')
                ->where('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = :countryCode)')
                ->setParameter('countryCode', $aCriteria['countryCode']);
        }
        return $queryBuilder->getQuery()->getArrayResult();
    }
}