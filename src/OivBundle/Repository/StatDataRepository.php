<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:19
 */

namespace OivBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class StatDataRepository extends BaseRepository
{
    /**@var QueryBuilder $_queryBuilder */
    private $_queryBuilder;

    /**
     * SELECT VALUE FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016' and  stat_type='C_PROD_GRP'
     * @param string $statType
     * @param array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSingleValueStatType($statType, $aCriteria = [])
    {
        if (!$statType) {
            return null;
        }

        $this->makeQuery(array_merge($aCriteria,['statType'=>$statType]));
//        var_dump($this->_queryBuilder->getQuery()->getDQL());
//        var_dump($this->_queryBuilder->getQuery()->getResult());die;
        $result =  $this->_queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['value'])) {
            $val = intval($result['value']) ? intval($result['value']):'0';
            $measure = $result['measureType'];
            return ['val' => $val, 'measure' => $measure];
        }
        return null;
    }

    /**
     * @param $statType
     * @param array $aCriteria
     * @return null
     */
    public function getMultiValueStatType($statType, $aCriteria = [])
    {
        if (!$statType) {
            return null;
        }
        $this->makeQuery(array_merge($aCriteria,['statType'=>$statType]));
        return $this->_queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param array $aCriteria
     */
    private function makeQuery($aCriteria = [])
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.year, SUM(o.value) as value, o.measureType')
            ->from($this->_entityName, 'o')
            ->where('o.statType = :statType')
            ->setParameter('statType',$aCriteria['statType']);

        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
            $this->_queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }

        $this->addYearCriteria($aCriteria);
        $this->_queryBuilder->groupBy('o.year, o.measureType');
    }

    /**
     * SELECT * FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016'
     * @param array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getGlobalResult($aCriteria = [])
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o')
            ->from($this->_entityName, 'o')
            ->where('1=1')
            ->setMaxResults(10);

        if (!empty($aCriteria['statType'])) {
            $this->_queryBuilder
                ->where('o.statType = :statType')
                ->setParameter('statType', $aCriteria['statType']);
        }
        if (!empty($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }

        $this->addYearCriteria($aCriteria);
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
        return $this->reformatArray($result);
    }

    private function addYearCriteria($aCriteria)
    {
        if(!empty($aCriteria['yearMin']) || !empty($aCriteria['yearMax'])){

            if (!empty($aCriteria['yearMin'])) {
                $this->_queryBuilder
                    ->Andwhere('o.year >= :yearMin')
                    ->setParameter('yearMin', $aCriteria['yearMin']);
            }
            if (!empty($aCriteria['yearMax'])) {
                $this->_queryBuilder
                    ->Andwhere('o.year <= :yearMax')
                    ->setParameter('yearMax', $aCriteria['yearMax']);
            }
        }elseif (!empty($aCriteria['year'])) {
            $this->_queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
    }

    /**
     * @return array
     */
    public static function getConfigFields()
    {
        return [
            'versioning' => [],
            'countryCode' => ['tab1','tab2'],
            'statType' => ['filter','tab1','tab2'],
            'measureType' => ['tab1','tab2'],
            'metricCompType' => ['filter','tab1','tab2'],
            'year' => ['tab1','tab2'],
            'value' => ['filter','tab1','tab2'],
            'infoSource'=> [],
            'lastDate'=>['filter','tab1','tab2'],
            'grapesDestination'=>['filter','tab1','tab2']
        ];
    }

}