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
        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
            $queryBuilder
                ->where('o.countryCode = :countryCode')
                ->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM '.$this->_entityName.' v WHERE v.countryCode = :countryCode)')
                ->setParameter('countryCode', $aCriteria['countryCode']);
        }
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['total'])) {
            return $result['total'];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getConfigFields()
    {
        return [
            'versioning' => [],
            'countryCode' => ['tab1', 'tab2'],
            'appellationCode' => ['filter', 'tab1', 'tab2'],
            'appellationName' => ['filter', 'tab1', 'tab2'],
            'parentCode' => ['tab1', 'tab2'],
            'parentName' => ['tab1', 'tab2'],
            'typeNationalCode' => ['filter', 'tab1', 'tab2'],
            'typeInternationalCode' => ['filter', 'tab1', 'tab2'],
            'productCategoryName' => ['filter', 'tab1', 'tab2'],
            'productType' => ['filter', 'tab1', 'tab2'],
            'referenceName' => ['tab1', 'tab2'],
            'lastDate' => ['tab1', 'tab2'],
            'url' => ['tab1', 'tab2']
        ];
    }
}