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
    protected $_sort = 'countryCode';
    protected $_order = 'ASC';

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
        $result =  $this->_queryBuilder->getQuery()->getOneOrNullResult();//var_dump($this->_queryBuilder->getQuery()->getSQL());
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
            ->where('1 = 1');

        $this->addStatTypeCriteria($aCriteria);
        $this->addCountryCriteria($aCriteria);
        $this->addYearCriteria($aCriteria);
        $this->_queryBuilder->groupBy('o.year, o.measureType');
    }

    /**
     * SELECT * FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016'
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getGlobalResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        $this->_sort = $sort ? $sort:$this->_sort;
        $this->_order = $order ? $order:$this->_order;
        $this->_queryBuilder = $this->getQueryResult($aCriteria);
        $this->_queryBuilder
                            ->setFirstResult($offset)
                            ->setMaxResults($limit)
                            ->orderBy('o.'.$this->_sort,$this->_order);
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
        return $this->reformatArray($result);
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalResult($aCriteria = [])
    {
        $queryBuilder = $this->getQueryResult($aCriteria, true);
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['total'])) {
            return (int)$result['total'];
        }
        return null;
    }

    public function getQueryResult($aCriteria = [], $count = false)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();

        if ($count) {
            $this->_queryBuilder->select(' count(o) as total');
        } else {
            $this->_queryBuilder->select('o');
        }

        $this->_queryBuilder
                ->from($this->_entityName, 'o')
                ->where('1=1');

        $this->addStatTypeCriteria($aCriteria);
        $this->addCountryCriteria($aCriteria);
        $this->addYearCriteria($aCriteria);
        return $this->_queryBuilder;
    }

    private function addStatTypeCriteria($aCriteria)
    {
        if (!empty($aCriteria['statType'])) {
            $aStatType = explode(',',$aCriteria['statType']);
            if (count($aStatType) == 1) {
                $this->_queryBuilder
                    ->where('o.statType = :statType')
                    ->setParameter('statType', $aStatType[0]);
            } else {
                $this->_queryBuilder
                    ->where('o.statType IN (\''. implode('\',\'',$aStatType) .'\')');
            }
        }
    }

    /**
     * @param $aCriteria
     */
    protected function addCountryCriteria($aCriteria = [])
    {
        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
            $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
            $aCountryCode = explode(',',$aCriteria['countryCode']);
            if ( count(array_intersect($aCountryCode, ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE']))) {
                if (count($aCountryCode) ==1) {
                    $this->_queryBuilder
                        ->innerJoin('OivBundle:Country', 'c', 'WITH', 'c.iso3 = o.countryCode AND c.tradeBloc  = :tradeBloc')
                        ->setParameter('tradeBloc', $aCountryCode[0]);
                } else {
                    $this->_queryBuilder
                        ->innerJoin('OivBundle:Country', 'c', 'WITH', 'c.iso3 = o.countryCode AND c.tradeBloc  IN (\''. implode('\',\'',$aCountryCode) .'\')');
                }
            } else {
                if (count($aCountryCode) ==1) {
                    $this->_queryBuilder
                        ->Andwhere('o.countryCode = :countryCode')
                        ->setParameter('countryCode', $aCountryCode[0]);
                } else {
                    $this->_queryBuilder
                        ->Andwhere('o.countryCode IN (\''. implode('\',\'',$aCountryCode) .'\')');
                        //->setParameter('countryCode', implode('\',\'',$aCountryCode));
                }
            }
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