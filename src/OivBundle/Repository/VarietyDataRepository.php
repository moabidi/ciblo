<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:21
 */

namespace OivBundle\Repository;
use Doctrine\ORM\Query\Expr;

class VarietyDataRepository extends BaseRepository
{

    protected $_sort = 'grapeVarietyName';
    protected $_order = 'ASC';
    /**
     * SELECT count(*) FROM oivdataw.variety_data where COUNTRY_CODE='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @var array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountVariety($aCriteria = [])
    {
        $this->getQueryResult($aCriteria,true);
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
        if (isset($result['total'])) {
            return $result['total'];
        }
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalResult($aCriteria = [])
    {
        return $this->getCountVariety($aCriteria);
    }

    /**
     * @param array $aCriteria
     * @param bool $count
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryResult($aCriteria = [], $count = false)
    {
        if (!isset($aCriteria['countryName'])) {
            $aCriteria['countryName'] = 'countryNameFr';
        }
        $this->_defaultCountryLan = $aCriteria['countryName'];

        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        if ($count) {
            $this->_queryBuilder->select('count(o) as total');
        } else {
            $this->_queryBuilder->select('c.tradeBloc, c.'.$this->_defaultCountryLan.' as countryNameFr, o');
            if(!isset($aCriteria['bo'])) {
                $this->_queryBuilder->addGroupBy('o.grapeVarietyName');
                $this->_queryBuilder->addGroupBy('o.countryCode');
                $this->_queryBuilder->addGroupBy('o.versioning');
            }
        }
        $this->_queryBuilder
            ->from($this->_entityName, 'o')
            ->andWhere('o.usableData = :usableData')
            ->setParameter('usableData','1');
        $this->addAllCriteria($aCriteria);
        if (isset($aCriteria['isMainVariety'])) {
            $this->_queryBuilder->andWhere('o.isMainVariety = \'1\'');
        }

    }

    /**
     * Add default order
     */
    protected function addDefaultOrder()
    {
        $this->_queryBuilder->orderBy('c.'.$this->_defaultCountryLan,'ASC');
        $this->_queryBuilder->addOrderBy('o.codeVivc','ASC');
    }
}