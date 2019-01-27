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
//        $result =  $this->_queryBuilder->getQuery()->getOneOrNullResult();
        $result =  $this->_queryBuilder->getQuery()->getArrayResult();
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
    public function getGlobalZoneResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_defaultSort;
        $this->_order = $order ? $order:$this->_defaultorder;
        $this->_queryBuilder = $this->getQueryResult($aCriteria);
        $this->_queryBuilder
                            ->setFirstResult($offset)
                            ->setMaxResults($limit);
        if ($this->_sort == 'o.countryCode') {
            $this->_sort = 'c.countryNameFr';
        }
        $this->_queryBuilder->orderBy($this->_sort,$this->_order);
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
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
        $aBaseCriteria = $aCriteria;
        if ( $listZone = $this->getZoneCriteria($aCriteria)) {
            foreach($listZone as $zone) {
                $aBaseCriteria['countryCode'] = $zone;
                $resultZone =  $this->getGlobalZoneResult($aBaseCriteria, 0, null, $sort, $order);
                $result = array_merge($result,$resultZone);
            }
        }
        if ( $listCountries = $this->getCounriesCriteria($aCriteria)) {
            $aBaseCriteria['countryCode'] = implode(',',$listCountries);
            $resultZone = $this->getGlobalZoneResult($aBaseCriteria, 0, null, $sort, $order);
            $result = array_merge($result,$resultZone);
        }
        $order = $this->_order == 'DESC'? SORT_DESC:SORT_ASC;
        $result = $this->array_sort($result, substr($this->_sort,2), $order);
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
        $result = $zone ? $queryBuilder->getQuery()->getResult():$queryBuilder->getQuery()->getOneOrNullResult();
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

    public function getZoneCriteria($aCriteria = [])
    {
        $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
        $aCountryCode = explode(',',$aCriteria['countryCode']);
        $result =  array_intersect($aCountryCode, ['oiv','AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE']);
        return $result;
    }

    public function getCounriesCriteria($aCriteria = [])
    {
        $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
        $aCountryCode = explode(',',$aCriteria['countryCode']);
        $result = array_diff($aCountryCode,['oiv','AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE']);
        return $result;
    }

    public function getQueryResult($aCriteria = [], $count = false)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $zone = $this->getZone($aCriteria);
        if ($count) {
            if ($zone) {
                $this->_queryBuilder->select('c.countryNameFr, SUM(o.value) as value');
            } else {
                $this->_queryBuilder->select(' count(o) as total');
            }
        } else {
            if ($zone) {
                $this->_queryBuilder->select('\''.$zone.'\' as countryNameFr, SUM(o.value) as value, o.id, o.countryCode, o.statType, o.measureType, o.metricCompType, o.year, o.infoSource, o.lastDate, o.grapesDestination');
            } else {
                $this->_queryBuilder->select('c.countryNameFr, o');
            }
        }

        $this->_queryBuilder
                ->from($this->_entityName, 'o')
                ->where('1=1');

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
                    ->where('o.statType = :statType')
                    ->setParameter('statType', $aStatType[0]);
            } else {
                $this->_queryBuilder
                    ->where('o.statType IN (\''. implode('\',\'',$aStatType) .'\')');
            }
        }
    }

    private function getZone($aCriteria)
    {
        $zone = $aCriteria['countryCode'] == 'oiv' ? 'World':'';
        if(!$zone) {
            $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
            $aCountryCode = explode(',',$aCriteria['countryCode']);
            $zone = count(array_intersect($aCountryCode, ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE']))>0 ? implode(' - ',$aCountryCode):'';
        }
        return $zone;
    }

    private function array_sort($array, $on, $order=SORT_ASC)
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

    /**
     * @return array
     */
    public static function getConfigFields()
    {
        return [
            'id'=>['tab3'],
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo'],
            'versioning' => ['form'],
            'countryCode' => ['form'],
            'statType' => ['form','filter','tab1','tab2','tab3','export','exportBo'],
            'metricCompType' => ['form','tab1','tab3','exportBo'],
            'year' => ['form','tab1','tab2','tab3','export','exportBo'],
            'measureType' => ['form','tab1','tab2','tab3','export','exportBo'],
            'value' => ['form','filter','tab1','tab2','tab3','export','exportBo'],
            'grapesDestination'=>['form'],
            'infoSource'=> ['form','exportBo'],
            'lastDate'=>['exportBo'],
            'usableData' => ['form'],
            'lastData' => [],
        ];
    }

}