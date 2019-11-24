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
    protected $_defaultSort = 'countryCode';
    protected $_sort = 'countryCode';
    protected $_defaultorder = 'ASC';
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
        $result =  $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        if(count($result) == 1 && isset($result[0])){
            $result = $result[0];
        } elseif(count($result) > 1) {
            foreach($result as $item) {
                if (isset($item['measureType']) && $item['measureType'] == 'tonnes') {
                    $result = $item; break;
                }
            }
        }
        if (isset($result['value'])) {
            if (!in_array($result['measureType'], ['KG_CAPITA','L_PER_CAPITA_15'])) {
                $result['value'] = round($result['value']);
            }
            return ['val' => $result['value'], 'measure' => $result['measureType']];
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
        $result= $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        array_walk($result, function(&$v){
            if (!in_array($v['measureType'], ['KG_CAPITA','L_PER_CAPITA_15'])) {
                $v['value'] = round($v['value']);
            }
        });
        return $result;
    }

    /**
     * @param array $aCriteria
     */
    private function makeQuery($aCriteria = [])
    {
        $unauthorizedStatType = ['COMSUMPTION_CAPITA_TABLE_GRP_COMPUTED','CONSUMPTION_DRIED_GRP_PER_CAPITA_COMPUTED','CONSUMPTION_WINE_CAPITA_COMPUTED'];
        if ( $listZone = $this->getZoneCriteria($aCriteria)) {
            if (is_array($aCriteria['statType'])) {
                $aCriteria['statType'] = array_diff($aCriteria['statType'],$unauthorizedStatType);
            }elseif(in_array($aCriteria['statType'],$unauthorizedStatType)){
                $aCriteria['statType'] = 'UNAVAILABLE';
            }
        }
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.year, SUM(o.value) as value, o.measureType')
            ->from($this->_entityName, 'o')
            ->where('o.usableData = 1');

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
    public function getGlobalZoneResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        if (!isset($aCriteria['countryName'])) {
            $aCriteria['countryName'] = 'countryNameFr';
        }
        $this->_defaultCountryLan = $aCriteria['countryName'];
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_defaultSort;
        $this->_order = $order ? $order:$this->_defaultorder;
        $this->_queryBuilder = $this->getQueryResult($aCriteria);
        $this->_queryBuilder
                            ->setFirstResult($offset)
                            ->setMaxResults($limit);
        if ($this->_sort == 'o.countryCode') {
            $this->_sort = 'c.'.$this->_defaultCountryLan;
        }
        $this->_queryBuilder->orderBy($this->_sort,$this->_order);
        $this->_queryBuilder->leftJoin('OivBundle:StatDataParameter','p','WITH','p.indicator = o.statType')
            ->addOrderBy('p.priority', 'ASC')
            ->addOrderBy('o.year', 'ASC');
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        return $this->reformatArray($result);
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
        $result = [];
        if (!isset($aCriteria['countryName'])) {
            $aCriteria['countryName'] = 'countryNameFr';
        }
        $this->_defaultCountryLan = $aCriteria['countryName'];
        $aBaseCriteria = $aCriteria;
        if ( $listZone = $this->getZoneCriteria($aCriteria)) {
            $aBaseCriteriaZone = $aBaseCriteria;
            $unauthorizedStatType = ['COMSUMPTION_CAPITA_TABLE_GRP_COMPUTED','CONSUMPTION_DRIED_GRP_PER_CAPITA_COMPUTED','CONSUMPTION_WINE_CAPITA_COMPUTED'];
            foreach($listZone as $zone) {
                $aBaseCriteriaZone['countryCode'] = $zone;
                if(!is_array($aBaseCriteriaZone['statType']) && in_array($aBaseCriteriaZone['statType'],$unauthorizedStatType)){
                    $aBaseCriteriaZone['statType'] = 'UNAVAILABLE';
                }elseif(!is_array($aBaseCriteriaZone['statType'])){
                    $aBaseCriteriaZone['statType'] = explode(',',$aBaseCriteriaZone['statType']);
                    $aBaseCriteriaZone['statType'] = array_diff($aBaseCriteriaZone['statType'],$unauthorizedStatType);
                    $aBaseCriteriaZone['statType'] = implode(',',$aBaseCriteriaZone['statType']);
                }
                $resultZone =  $this->getGlobalZoneResult($aBaseCriteriaZone, 0, null, $sort, $order);
                $result = array_merge($result,$resultZone);
            }
        }
        if ( $listCountries = $this->getCounriesCriteria($aCriteria)) {
            $aBaseCriteria['countryCode'] = implode(',',$listCountries);
            $resultZone = $this->getGlobalZoneResult($aBaseCriteria, 0, null, $sort, $order);
            $result = array_merge($result,$resultZone);
        }

        $order = $this->_order == 'DESC'? SORT_DESC:SORT_ASC;
        $indexSort = substr($this->_sort,2);
        if($this->_sort == 'c.'.$aCriteria['countryName']) {
            $indexSort = 'defaultSort';
        }
        $result = self::sortResult($result, $indexSort, $order);
        $result = array_values($result);
        /** Sort by the second parameter whitch is Year */
        if ($indexSort != 'defaultSort') {//var_dump($result);
            usort($result, function($v1, $v2) use ($indexSort, $order){
                if ($v1[$indexSort] == $v2[$indexSort]) {
                    if ($v1['countryNameFr'] == $v2['countryNameFr']) {
                        return strcmp($v1['year'],$v2['year']);
                    } else {
                        return strcmp($v1['countryNameFr'],$v2['countryNameFr']);
                    }
                }
                return $order == SORT_ASC ? strcmp($v1[$indexSort],$v2[$indexSort]):strcmp($v2[$indexSort],$v1[$indexSort]);
            });
        }
        return array_slice($result,$offset,$limit);
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalZoneResult($aCriteria = [])
    {
        $queryBuilder = $this->getQueryResult($aCriteria, true);
        $zone = $this->getZone($aCriteria);
        $result = $zone ? $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getResult():$queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
        if (isset($result['total'])) {
            return (int)$result['total'];
        }elseif(is_array($result)){
            return count($result);
        }
        return null;
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalResult($aCriteria = [])
    {
        $total = 0;
        $aBaseCriteria = $aCriteria;
        if ( $listZone = $this->getZoneCriteria($aCriteria)) {
            foreach($listZone as $zone) {
                $aBaseCriteria['countryCode'] = $zone;
                $total += $this->getTotalZoneResult($aBaseCriteria);
            }
        }
        if ( $listCountries = $this->getCounriesCriteria($aCriteria)) {
            $aBaseCriteria['countryCode'] = implode(',',$listCountries);
            $total += $this->getTotalZoneResult($aBaseCriteria);
        }
        return $total;
    }

    /**
     * @param array $aCriteria
     * @return array
     */
    public function getZoneCriteria($aCriteria = [])
    {
        $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
        $aCountryCode = explode(',',$aCriteria['countryCode']);
        $result =  array_intersect($aCountryCode, ['oiv','AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANIE']);
        return $result;
    }

    public function getCounriesCriteria($aCriteria = [])
    {
        $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
        $aCountryCode = explode(',',$aCriteria['countryCode']);
        $result = array_diff($aCountryCode,['oiv','AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANIE']);
        return $result;
    }

    public function getQueryResult($aCriteria = [], $count = false)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $zone = $this->getZone($aCriteria);
        if ($count) {
            if ($zone) {
                $this->_queryBuilder->select('c.'.$this->_defaultCountryLan.', SUM(o.value) as value');
            } else {
                $this->_queryBuilder->select(' count(o) as total');
            }
        } else {
            if ($zone) {
                $this->_queryBuilder->select('concat( \''.$zone.'\',p.priority,o.year) as defaultSort, \'\' as tradeBloc , \''.$zone.'\' as countryNameFr, SUM(o.value) as value, o.id, o.countryCode, o.statType, o.measureType, o.metricCompType, o.year, \'\' as infoSource, o.lastDate, o.grapesDestination');
            } else {
                $this->_queryBuilder->select('concat(c.'.$this->_defaultCountryLan.',p.priority,o.year) as defaultSort, c.tradeBloc, c.'.$this->_defaultCountryLan.' as countryNameFr, o');
            }
        }

        $this->_queryBuilder
                ->from($this->_entityName, 'o')
                ->where('o.usableData = :usableData')
                ->setParameter('usableData','1');

        $this->addStatTypeCriteria($aCriteria);
        $this->addCountryCriteria($aCriteria);
        $this->addYearCriteria($aCriteria);
        $this->addValueCriteria($aCriteria);

        if ($zone) {
            $this->_queryBuilder
                ->groupBy('o.year')
                ->addGroupBy('o.statType');
        }
        return $this->_queryBuilder;
    }

    private function addStatTypeCriteria($aCriteria)
    {
        if (!empty($aCriteria['statType'])) {
            $aStatType = explode(',',$aCriteria['statType']);
            if (count($aStatType) == 1) {
                $this->_queryBuilder
                    ->andWhere('o.statType = :statType')
                    ->setParameter('statType', $aStatType[0]);
            } else {
                $this->_queryBuilder
                    ->andWhere('o.statType IN (\''. implode('\',\'',$aStatType) .'\')');
            }
        }
    }

    /**
     * @param $aCriteria
     * @return string
     */
    private function getZone($aCriteria)
    {
        $zone = $aCriteria['countryCode'] == 'oiv' ? 'World':'';
        if(!$zone) {
            $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
            $aCountryCode = explode(',',$aCriteria['countryCode']);
            $zone = count(array_intersect($aCountryCode, ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANIE']))>0 ? implode(' - ',$aCountryCode):'';
        }
        return $zone;
    }

    public static function sortResult($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

}