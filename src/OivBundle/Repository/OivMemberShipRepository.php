<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 15/11/18
 * Time: 01:36
 */

namespace OivBundle\Repository;

class OivMemberShipRepository extends BaseRepository
{

    /**
     * SELECT OIV_MEMBERSHIP FROM oivdataw.oiv_member_ship where ISO3='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.education_data where COUNTRY_CODE='FRA')
     *
     * @param array $aCriteria
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isMemberShip($aCriteria = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.oivMembership')
            ->from($this->_entityName, 'o');
        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->where('o.iso3 = :countryCode')
                //->where('o.lastDate  = (SELECT MAX(v.lastDate) FROM '.$this->_entityName.' v WHERE v.iso3 = :countryCode)')
                ->andWhere('o.year  = :year')
                ->setParameter('countryCode', $aCriteria['countryCode'])
                ->setParameter('year', $aCriteria['year']);
        }
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['oivMembership'])) {
            return $result['oivMembership'];
        }
        return false;
    }
}