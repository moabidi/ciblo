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

    public function getDistinctValueField($field)
    {
//        if ( $this->getEntityName() == 'OivBundle\Entity\StatData' && $field == 'statType') {
//            die('++');
//
//        }
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.'.$field)
            ->from($this->_entityName, 'o')
            ->distinct('o.'.$field)
            ->orderBy('o.'.$field);
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
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
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_sort;
        $this->_order = $order ? $order:$this->_order;
        $this->getQueryResult($aCriteria);
        $this->_queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if ($sort) {
            if ($this->_sort == 'o.countryCode') {
                $this->_sort = 'c.countryNameFr';
            }
            $this->_queryBuilder->orderBy($this->_sort, $this->_order);
        } else {
            $this->addDefaultOrder();
        }
        $result = $this->_queryBuilder->getQuery()->getArrayResult();//var_dump($result[0],$this->reformatArray($result)[0]);
        return $this->reformatArray($result);
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalResult($aCriteria = [])
    {
        $this->getQueryResult($aCriteria, true);
//        var_dump($this->_queryBuilder->getDQL());die;
        $result = $this->_queryBuilder->getQuery()->getOneOrNullResult();
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
            ->from($this->_entityName, 'o');
        $this->addCountryCriteria($aCriteria);
        $result = $this->_queryBuilder->getQuery()->getOneOrNullResult();
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
        if ($count) {
            $this->_queryBuilder->select('count(o) as total');
        } else {
            $this->_queryBuilder->select('c.tradeBloc, c.countryNameFr, o');
        }
        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);

    }

    public static function getTaggedFields($tag)
    {
        $aFields =[];
        foreach (static::getConfigFields() as $name => $aTags) {
            if (in_array($tag, $aTags)) {
                $aFields[$name] = $name;
            }
        }
        return $aFields;
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
                    foreach ($s as &$f) {
                        if ($f instanceof \DateTime) {
                            $f = $f->format('Y-m-d');
                        }
                    }
                    unset($v[0]);
                    $v = array_merge($v, $s);
                }elseif(is_array($v)) {
                    foreach ($v as &$f) {
                        if ($f instanceof \DateTime) {
                            $f = $f->format('Y-m-d');
                        }
                    }
                }
            });
        }
        return $result;
    }

    /**
     * @param array $aCriteria
     */
    protected function addAllCriteria($aCriteria = [])
    {
        $this->addCountryCriteria($aCriteria);
        //$this->addYearCriteria($aCriteria);
        unset($aCriteria['countryCode']);
        unset($aCriteria['year']);
        unset($aCriteria['yearMin']);
        unset($aCriteria['yearMax']);
        //var_dump($aCriteria);die;
        $queryBuilder = $this->_queryBuilder;
        array_walk($aCriteria, function($val, $field) use($queryBuilder){
            if($val) {
                //var_dump($field,$val);
                //$cond = 'o.'.$field.' = :'+$field;
                $this->_queryBuilder
                    ->andWhere(sprintf('o.%s like :%s',$field,$field))
                    ->setParameter(sprintf('%s',$field),'%'.$val.'%');
            }
        });
        $this->_queryBuilder = $queryBuilder;
    }

    /**
     * @param array $aCriteria
     */
    protected function addCountryCriteria($aCriteria = [])
    {
        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
            $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
            if ( in_array($aCriteria['countryCode'], ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE'])) {
                $this->_queryBuilder
                    ->innerJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode AND c.tradeBloc  = :tradeBloc')
                    //->innerJoin('( SELECT v.countryCode, MAX(v.lastDate) as lastDate FROM ' . $this->_entityName . ' v group by v.countryCode)','b','ON', 'b.countryCode = o.countryCode AND b.lastDate = o.lastDate')
                    //->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = o.countryCode)')
                    ->setParameter('tradeBloc', $aCriteria['countryCode']);
            } else {
                $aCountryCode = explode(',',$aCriteria['countryCode']);
                if (count($aCountryCode) ==1) {
                    $this->_queryBuilder
                        ->andWhere('o.countryCode = :countryCode')
                        ->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode')
                        //->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = o.countryCode)')
                        ->setParameter('countryCode', $aCountryCode[0]);
                } else {
                    $this->_queryBuilder
                        ->andWhere('o.countryCode IN (\''. implode('\',\'',$aCountryCode) .'\')')
                        ->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode');
                        //->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = o.countryCode)');
                }
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

}