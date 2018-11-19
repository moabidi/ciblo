<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:19
 */

namespace OivBundle\Repository;

class StatDataRepository extends BaseRepository
{

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
            ->select('o.year, o.value, o.measureType')
            ->from($this->_entityName, 'o')
            ->where('o.statType = :statType')
            ->setParameter('statType',$aCriteria['statType']);

        if (isset($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }

        if( (isset($aCriteria['minDate']) && $aCriteria['minDate']) || (isset($aCriteria['maxDate']) && $aCriteria['minDate'])){
            if (isset($aCriteria['minDate'])) {
                $this->_queryBuilder
                    ->Andwhere('o.year >= :minDate')
                    ->setParameter('minDate', $aCriteria['minDate']);
            }
            if (isset($aCriteria['maxDate'])) {
                $this->_queryBuilder
                    ->Andwhere('o.year <= :maxDate')
                    ->setParameter('maxDate', $aCriteria['maxDate']);
            }
        }elseif (isset($aCriteria['year'])) {
            $this->_queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
        $this->_queryBuilder->groupBy('o.year, o.value, o.measureType');
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
            ->where('1=1');

        if (isset($aCriteria['statType'])) {
            $this->_queryBuilder
                ->where('o.statType = :statType')
                ->setParameter('statType', $aCriteria['statType']);
        }
        if (isset($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }
        if (isset($aCriteria['year'])) {
            $this->_queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
//        if (count($result)) {
//            array_walk($result, function(&$v,$k){
//                $s = $v[0];//var_dump($v,$k);die;
//                unset($v[0]);
//                $v = array_merge($v,$s);
//            });
//        }
//        return $result;
        return $this->reformatArray($result);
    }

    /**
     * @return array
     */
    public static function getConfigFields() {
        return [
            'versioning' => [],
            'countryCode' => ['tab1','tab2'],
            'statType' => ['filter','tab1','tab2'],
            'measureType' => ['tab1','tab2'],
            'metricCompType' => ['filter','tab1','tab2'],
            'year' => ['filter','tab1','tab2'],
            'value' => ['filter','tab1','tab2'],
            'infoSource'=> [],
            'lastDate'=>['filter','tab1','tab2'],
            'grapesDestination'=>['filter','tab1','tab2']
        ];
    }

}