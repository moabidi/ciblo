<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 15/11/18
 * Time: 23:46
 */

namespace OivBundle\Repository;


use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{

    public function getDistinctValueField($field)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.'.$field)
            ->from($this->_entityName, 'o')
            ->distinct('o.'.$field)
            ->orderBy('o.'.$field);
//        return $queryBuilder->getQuery()->getArrayResult();
//        var_dump($result);die;
        $result = $queryBuilder->getQuery()->getArrayResult();
        return $this->reformatArray($result);
    }

    /**
     * SELECT COUNTRY_CODE AS pays,oivdataw.variety_data.*
    FROM oivdataw.variety_data where COUNTRY_CODE='FRA' AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getGlobalResult($aCriteria = [], $offset = 0, $limit = 100)
    {
        $queryBuilder = $this->getQueryResult($aCriteria);
        $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $result = $queryBuilder->getQuery()->getArrayResult();
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

    /**
     * @param array $aCriteria
     * @param bool $count
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryResult($aCriteria = [], $count = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        if ($count) {
            $queryBuilder = $queryBuilder->select('count(o) as total');
        } else {
            $queryBuilder = $queryBuilder->select('c.tradeBloc, c.countryNameFr, o');
        }
        $queryBuilder->from($this->_entityName, 'o');

        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
            $aCriteria['countryCode'] = trim($aCriteria['countryCode']);
            if ( in_array($aCriteria['countryCode'], ['AFRIQUE','AMERIQUE','ASIE','EUROPE','OCEANTE'])) {
                $queryBuilder
                    ->innerJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode AND c.tradeBloc  = :tradeBloc')
                    ->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = o.countryCode)')
                    ->setParameter('tradeBloc', $aCriteria['countryCode']);
            } else {
                $queryBuilder
                    ->andWhere('o.countryCode = :countryCode')
                    ->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = :countryCode)')
                    ->setParameter('countryCode', $aCriteria['countryCode']);
            }
        } else {
            $queryBuilder->leftJoin('OivBundle:Country','c','WITH','c.iso3 = o.countryCode');
        }
        unset($aCriteria['countryCode']);
        unset($aCriteria['year']);
        //var_dump($aCriteria);die;
        array_walk($aCriteria, function($val, $field) use($queryBuilder){
            if($val) {
                //var_dump($field,$val);
                //$cond = 'o.'.$field.' = :'+$field;
                $queryBuilder
                    ->andWhere(sprintf('o.%s = :%s',$field,$field))
                    ->setParameter(sprintf('%s',$field),$val);
            }
        });
        return $queryBuilder;
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

}