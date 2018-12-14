<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:20
 */

namespace OivBundle\Repository;

class EducationDataRepository extends BaseRepository
{

    /**
     * SELECT count(*) FROM oivdataw.variety_data where COUNTRY_CODE='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @var array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountEducation($aCriteria = [])
    {
//        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
//        $queryBuilder
//            ->select('COUNT(o) as total')
//            ->from($this->_entityName, 'o');
//        if (!empty($aCriteria['countryCode']) && $aCriteria['countryCode'] != 'oiv') {
//            $queryBuilder
//                ->where('o.countryCode = :countryCode')
//                ->andWhere('o.lastDate  = (SELECT MAX(v.lastDate) FROM ' . $this->_entityName . ' v WHERE v.countryCode = :countryCode)')
//                ->setParameter('countryCode', $aCriteria['countryCode']);
//        }
//        $result = $queryBuilder->getQuery()->getOneOrNullResult();
//        if (isset($result['total'])) {
//            return $result['total'];
//        }
        $tableName = $this->getEntityManager()->getClassMetadata($this->_entityName)->getTableName();
        $tableCountryName = $this->getEntityManager()->getClassMetadata('OivBundle:Country')->getTableName();
        $cnx = $this->getEntityManager()->getConnection();
        $stm = $cnx->prepare('select COUNT(*) as total from  '.$tableName.' o' .
            ' inner join '.$tableCountryName.' c on c.ISO3 = o.COUNTRY_CODE AND c.TRADE_BLOC  = :tradeBloc'.
            ' inner join ( SELECT COUNTRY_CODE, MAX(LAST_DATE) as LAST_DATE FROM '. $tableName .' group by COUNTRY_CODE) b ON b.COUNTRY_CODE = o.COUNTRY_CODE AND b.LAST_DATE = o.LAST_DATE'
        );
        $stm->bindValue('tradeBloc', $aCriteria['countryCode']);
        $stm->execute();
        $result = $stm->fetch();
        if (isset($result['total'])) {
            return $result['total'];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getConfigFields() {
        return [
            'versioning' => [],
            'countryCode' => ['tab1','tab2'],
            'formationTitle' => ['filter'],
            'university' => ['filter'],
            'tutelle' => ['tab1','tab2'],
            'level' => ['filter','tab1','tab2'],
            'diploma' => ['tab1','tab2'],
            'cooperation' => ['tab1','tab2'],
            'month'=> ['tab1','tab2'],
            'hourCourses'=>['tab1','tab2'],
            'credits'=>['tab1','tab2'],
            'prior'=>['tab1','tab2'],
            'deadline'=>['tab1','tab2'],
            'contact'=>['tab1','tab2'],
            'adress'=>['tab1','tab2'],
            'lastDate'=>['tab1','tab2'],
            'internetAdress'=>['tab1','tab2']
        ];
    }
}