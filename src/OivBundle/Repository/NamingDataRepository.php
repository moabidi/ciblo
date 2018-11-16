<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:19
 */

namespace OivBundle\Repository;

class NamingDataRepository extends BaseRepository
{

    /**
     * SELECT count(*) FROM oivdataw.naming_data  where COUNTRY_CODE='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.naming_data where COUNTRY_CODE='FRA')
     * @var array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountNaming($aCriteria = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(o) as total')
            ->from($this->_entityName, 'o');
        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->where('o.countryCode = :countryCode')
                ->where('o.lastDate  = (SELECT MAX(v.lastDate) FROM '.$this->_entityName.' v WHERE v.countryCode = :countryCode)')
                ->setParameter('countryCode', $aCriteria['countryCode']);
        }
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['total'])) {
            return $result['total'];
        }
        return null;
    }
}