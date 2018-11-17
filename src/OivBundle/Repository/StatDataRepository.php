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

    /**
     * SELECT VALUE FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016' and  stat_type='C_PROD_GRP'
     * @param string $staType
     * @param array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getValueStatType($staType, $aCriteria = [])
    {
        if (!$staType) {
            return null;
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.year, o.value, o.measureType')
            ->from($this->_entityName, 'o')
            ->where('o.statType = :statType')
            ->setParameter('statType',$staType);

        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }

        if( (isset($aCriteria['minDate']) && $aCriteria['minDate']) || (isset($aCriteria['maxDate']) && $aCriteria['minDate'])){
            if (isset($aCriteria['minDate'])) {
                $queryBuilder
                    ->Andwhere('o.year >= :minDate')
                    ->setParameter('minDate', $aCriteria['minDate']);
            }
            if (isset($aCriteria['maxDate'])) {
                $queryBuilder
                    ->Andwhere('o.year <= :maxDate')
                    ->setParameter('maxDate', $aCriteria['maxDate']);
            }
            $queryBuilder->groupBy('o.year, o.value, o.measureType');
            $result = $queryBuilder->getQuery()->getArrayResult();
            //var_dump($result);die;
            return $result;
        }elseif (isset($aCriteria['year'])) {
            $queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
        $result =  $queryBuilder->getQuery()->getOneOrNullResult();
        if (isset($result['value'])) {
            return floatval($result['value']) ? floatval($result['value']):'0';
        }
        return null;
    }

    /**
     * SELECT * FROM oivdataw.stat_data where COUNTRY_CODE='FRA'  and  YEAR='2016'
     * @param array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getGlobalResult($aCriteria = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o')
            ->from($this->_entityName, 'o')
            ->where('1=1');

        if (isset($aCriteria['statType'])) {
            $queryBuilder
                ->where('o.statType = :statType')
                ->setParameter('statType', $aCriteria['statType']);
        }
        if (isset($aCriteria['countryCode'])) {
            $queryBuilder
                ->Andwhere('o.countryCode = :countryCode')
                ->setParameter('countryCode',$aCriteria['countryCode']);
        }
        if (isset($aCriteria['year'])) {
            $queryBuilder
                ->Andwhere('o.year = :year')
                ->setParameter('year',$aCriteria['year']);
        }
        $result = $queryBuilder->getQuery()->getArrayResult();
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
            'countryCode' => ['filter','tab1','tab2'],
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