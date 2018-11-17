<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:19
 */

namespace OivBundle\Repository;

class StatDataRepository extends BaseRepository
{

    /**
     * SELECT VALUE FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016' and  stat_type='C_PROD_GRP'
     * @param string $staType
     * @param array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getValueStatType($staType, $aCriteria = [])
    {
        if (!$staType) {
            return null;
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.value')
            ->from($this->_entityName, 'o')
            ->where('o.statType = :statType')
            ->setParameter('statType',$staType);

        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }
        if (isset($aCriteria['year'])) {
            $queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
        $result =  $queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['value'])) {
            return floatval($result['value']) ? floatval($result['value']):'0';
        }
        return null;
    }

    /**
     * SELECT * FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016'
     * @param array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getGlobalResult($aCriteria = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o')
            ->from($this->_entityName, 'o')
            ->where('1=1');

        if (isset($aCriteria['statType'])) {
            $queryBuilder
                ->where('o.statType = :statType')
                ->setParameter('statType', $aCriteria['statType']);
        }
        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }
        if (isset($aCriteria['year'])) {
            $queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
        return  $queryBuilder->getQuery()->getOneOrNullResult();
    }

}