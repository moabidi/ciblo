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
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.oivMembership')
            ->from($this->_entityName, 'o');
        if (isset($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->where('o.iso3 = :countryCode')
                //->where('o.lastDate  = (SELECT MAX(v.lastDate) FROM '.$this->_entityName.' v WHERE v.iso3 = :countryCode)')
                ->andWhere('o.year  = :year')
                ->setParameter('countryCode', $aCriteria['countryCode'])
                ->setParameter('year', $aCriteria['year']);
        }
        $result = $this->_queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['oivMembership'])) {
            return $result['oivMembership'];
        }
        return false;
    }

    public function getMemberCountries($aCriteria)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.iso3')
            ->from($this->_entityName, 'o')
            ->where('o.oivMembership = \'1\'')
            ->andWhere('o.year  = '.date('Y'));
        $this->addCountryCriteria($aCriteria);
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
        return$result;
    }

    /**
     * @param $aCriteria
     */
    protected function addCountryCriteria($aCriteria = [])
    {
        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
            $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
            $aCountryCode = explode(',',$aCriteria['countryCode']);
            if (in_array('oiv',$aCountryCode)) {
                $this->_queryBuilder->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.iso3');
            }elseif ( count(array_intersect($aCountryCode, ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE']))) {
                if (count($aCountryCode) ==1) {
                    $this->_queryBuilder
                        ->innerJoin('OivBundle:Country', 'c', 'WITH', 'c.iso3 = o.iso3 AND c.tradeBloc  = :tradeBloc')
                        ->setParameter('tradeBloc', $aCountryCode[0]);
                } else {
                    $this->_queryBuilder
                        ->innerJoin('OivBundle:Country', 'c', 'WITH', 'c.iso3 = o.iso3 AND c.tradeBloc  IN (\''. implode('\',\'',$aCountryCode) .'\')');
                }
            } else {
                if (count($aCountryCode) ==1) {
                    $this->_queryBuilder
                        ->Andwhere('o.iso3 = :countryCode')
                        ->setParameter('countryCode', $aCountryCode[0]);
                } else {
                    $this->_queryBuilder
                        ->Andwhere('o.iso3 IN (\''. implode('\',\'',$aCountryCode) .'\')');
                }
            }
        }
    }
}