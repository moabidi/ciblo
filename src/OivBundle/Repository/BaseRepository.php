<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 15/11/18
 * Time: 23:46
 */

namespace OivBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class BaseRepository extends EntityRepository
{
    /**@var QueryBuilder $_queryBuilder */
    protected $_queryBuilder;
    protected $_defaultCountryLan = 'countryNameFr';

    public function getMaxVersion()
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('MAX(o.versioning) as version')
            ->from($this->_entityName, 'o')
            ->andWhere('o.usableData = :usableData')
            ->setParameter('usableData','1');
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
        return $result['version'];
    }

    /**
     * 
     * @param string $field
     * @param array $aCriteria
     * @return mixed
     */
    public function getDistinctValueField($field, $aCriteria = [])
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.'.$field)
            ->from($this->_entityName, 'o')
            ->distinct('o.'.$field)
            ->orderBy('o.'.$field)
            ->Where('o.usableData = :usableData')
            ->setParameter('usableData','1');
            if (isset($aCriteria['countryCode'])) {
                $this->_queryBuilder
                ->andWhere('o.countryCode = :countryCode')
                ->setParameter('countryCode', $aCriteria['countryCode']);
            }
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        return $this->reformatArray($result);
    }

    /**
     * SELECT COUNTRY_CODE AS pays,oivdataw.variety_data.*
    FROM oivdataw.variety_data where COUNTRY_CODE='FRA' AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getGlobalResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        if (!isset($aCriteria['countryName'])) {
            $aCriteria['countryName'] = 'countryNameFr';
        }
        $this->_defaultCountryLan = $aCriteria['countryName'];
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_sort;
        $this->_order = $order ? $order:$this->_order;
        $this->getQueryResult($aCriteria);
        $this->_queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if ($sort) {
            if ($this->_sort == 'o.countryCode') {
                $this->_sort = 'c.'.$this->_defaultCountryLan;
            }
            $this->_queryBuilder->orderBy($this->_sort, $this->_order);
        } else {
            $this->addDefaultOrder();
        }
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();//var_dump($result[0],$this->reformatArray($result)[0]);
        return $this->reformatArray($result);
    }


    /**
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @param null $sort
     * @param null $order
     * @param null $groupBy
     * @return array
     */
    public function getExportResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null, $groupBy=null)
    {
        return $this->getGlobalResult($aCriteria,$offset,$limit,$sort,$order);
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalResult($aCriteria = [])
    {
        $this->getQueryResult($aCriteria, true);
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
        if (isset($result['total'])) {
            return (int)$result['total'];
        }
        return null;
    }

    /**
     * @param array $aCriteria
     * @return null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCountDB($aCriteria = [])
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('COUNT(o) as total')
            ->from($this->_entityName, 'o')
            ->andWhere('o.usableData = \'1\'');
        $this->addCountryCriteria($aCriteria);
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
        if (isset($result['total'])) {
            return $result['total'];
        }
    }

    /**
     * @param array $aCriteria
     * @param bool $count
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryResult($aCriteria = [], $count = false)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        if (!isset($aCriteria['countryName'])) {
            $this->_defaultCountryLan = 'countryNameFr';
        }
        if ($count) {
            $this->_queryBuilder->select('count(o) as total');
        } else {
            $this->_queryBuilder->select('c.tradeBloc, c.'.$this->_defaultCountryLan.' as countryNameFr, o');
        }
        $this->_queryBuilder->from($this->_entityName, 'o')->andWhere('o.usableData = \'1\'');
        $this->addAllCriteria($aCriteria);

    }

    /**
     * @param $result
     * @return mixed
     */
    protected function reformatArray($result)
    {
        if (count($result)) {
            array_walk($result, function(&$v,$k){
                if( isset($v[0]) && is_array($v[0])) {
                    $s = $v[0];
                    $s = $this->formatArray($s);
                    unset($v[0]);
                    $v = array_merge($v, $s);
                }elseif(is_array($v)) {
                    $v = $this->formatArray($v);
                }
            });
        }
        return $result;
    }

    /**
     * @param $v
     * @return mixed
     */
    protected function formatArray($v)
    {
        array_walk($v, function(&$f, $k) use($v){
            if ($f instanceof \DateTime) {
                $f = $f->format('Y-m-d');
            } else if ( $k == 'value' && $f) {
                if (isset($v['measureType']) && in_array($v['measureType'], ['KG_CAPITA','L_PER_CAPITA_15'])) {
                    $f = number_format(floatval($f), 1, '.','');
                    $f = floatval($f);
                }else{
                    $f = round($f);
                }
            }
        });
        return $v;
    }

    /**
     * @param array $aCriteria
     */
    protected function addAllCriteria($aCriteria = [])
    {
        $this->addCountryCriteria($aCriteria);
        //$this->addYearCriteria($aCriteria);
        unset($aCriteria['countryCode']);
        unset($aCriteria['countryName']);
        unset($aCriteria['year']);
        unset($aCriteria['yearMin']);
        unset($aCriteria['yearMax']);
        $queryBuilder = $this->_queryBuilder;
        $queryBuilder
            ->Where('o.usableData = :usableData')
            ->setParameter('usableData','1');
        array_walk($aCriteria, function($val, $field) use($queryBuilder){
            if (property_exists($this->_entityName, $field) && $val) {
                $this->_queryBuilder
                    ->andWhere(sprintf('o.%s like :%s',$field,$field))
                    ->setParameter(sprintf('%s',$field),'%'.$val.'%');
            }
        });
        $this->_queryBuilder = $queryBuilder;
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
                $this->_queryBuilder->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode');
            }elseif ( count(array_intersect($aCountryCode, ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANIE']))) {
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
                }
                $this->_queryBuilder->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode');
            }
        } else {
            $this->_queryBuilder->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode');
        }
    }


    /**
     * @param array $aCriteria
     */
    protected function addYearCriteria($aCriteria = [])
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
     * @param array $aCriteria
     */
    protected function addValueCriteria($aCriteria = [])
    {
        if(!empty($aCriteria['valueMin']) || !empty($aCriteria['valueMax'])){

            if (!empty($aCriteria['valueMin'])) {
                $this->_queryBuilder
                    ->Andwhere('o.value >= :valueMin')
                    ->setParameter('valueMin', $aCriteria['valueMin']);
            }
            if (!empty($aCriteria['valueMax'])) {
                $this->_queryBuilder
                    ->Andwhere('o.value <= :valueMax')
                    ->setParameter('valueMax', $aCriteria['valueMax']);
            }
        }elseif (!empty($aCriteria['value'])) {
            $this->_queryBuilder
                ->Andwhere('o.value = :value')
                ->setParameter('value',$aCriteria['value']);
        }
    }

    /**
     * @param array $aCriteria
     */
    protected function addMeasureTypeCriteria($aCriteria = [])
    {
        if (!empty($aCriteria['measureType'])) {
            $this->_queryBuilder
                ->Andwhere('o.measureType = :measureType')
                ->setParameter('measureType',$aCriteria['measureType']);
        }
    }



}