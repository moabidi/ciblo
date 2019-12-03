<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:19
 */

namespace OivBundle\Repository;

use Doctrine\ORM\Query\Expr;

class NamingDataRepository extends BaseRepository
{
    protected $_sort = 'appellationName';
    protected $_order = 'ASC';

    /**
     * SELECT count(*) FROM oivdataw.naming_data  where COUNTRY_CODE='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.naming_data where COUNTRY_CODE='FRA')
     * @var array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountNaming($aCriteria = [])
    {
        //return parent::getCountDB($aCriteria);
        return $this->getTotalResult($aCriteria);
    }

    /**
     * Add default order
     */
    protected function addDefaultOrder()
    {
        $this->_queryBuilder->orderBy('c.countryNameFr','ASC');
        $this->_queryBuilder->addOrderBy('o.typeInternationalCode','ASC');
        $this->_queryBuilder->addOrderBy('o.appellationName','ASC');
    }

    /**
     * @param array $aCriteria
     * @return int
     */
    public function getTotalResult($aCriteria = [])
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('count(o) as total')
            ->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);
        if(!isset($aCriteria['box'])) {
            $this->_queryBuilder->addGroupBy('o.appellationCode');
            $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
            if (count($result)) {
                return count($result);
            }
        } else{
            $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
            return $result['total'];
        }
        return null;
    }

    /**
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getGlobalResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_sort;
        $this->_order = $order ? $order:$this->_order;
        if (!isset($aCriteria['countryName'])) {
            $aCriteria['countryName'] = 'countryNameFr';
        }
        $this->_defaultCountryLan = $aCriteria['countryName'];
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder->select('c.'.$this->_defaultCountryLan.' as countryNameFr, o.id, o.countryCode, o.versioning,  o.appellationCode,o.appellationName, o.parentCode, o.parentName, o.typeNationalCode, o.typeInternationalCode,
         count(distinct o.productType) as productCategoryName, count( distinct o.productType) as productType, count(distinct o.referenceName) as referenceName,
            o.lastDate, o.url');

        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);
        if(!isset($aCriteria['box'])) {
            $this->_queryBuilder->addGroupBy('o.appellationCode');
        } else {
            $this->_queryBuilder->select('c.'.$this->_defaultCountryLan.' as countryNameFr, o.id, o.countryCode, o.versioning,  o.appellationCode,o.appellationName, o.parentCode, o.parentName, o.typeNationalCode, o.typeInternationalCode,
         o.productType as productCategoryName, o.productCategoryName as productType, o.referenceName as referenceName,
            o.lastDate, o.url');
        }
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
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
        return $this->reformatArray($result);
    }

    /**
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @param null $sort
     * @param null $order
     * @param null $groupBy
     * @return mixed
     */
    public function getExportResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null, $groupBy=null)
    {
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_sort;
        $this->_order = $order ? $order:$this->_order;
        if (!isset($aCriteria['countryName'])) {
            $aCriteria['countryName'] = 'countryNameFr';
        }
        $this->_defaultCountryLan = $aCriteria['countryName'];
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('c.'.$this->_defaultCountryLan.' as countryNameFr, o')
            ->from($this->_entityName, 'o')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->addAllCriteria($aCriteria);
        if ($groupBy) {
            $this->_queryBuilder->groupBy('o.'.$groupBy);
        }
        if ($sort) {
            if ($this->_sort == 'o.countryCode') {
                $this->_sort = 'c.countryNameFr';
            }
            $this->_queryBuilder->orderBy($this->_sort, $this->_order);
        } else {
            $this->addDefaultOrder();
        }
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
        return $this->reformatArray($result);
    }
    /**
     * @param $appellationName
     * @param bool $isCtg
     * @return array
     */
    public function getInfoNaming($appellationName, $appellationCode, $isCtg=true,$groupByCtg=true)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        if ($isCtg) {
            $this->_queryBuilder
                ->select('o.productCategoryName, o.productType');
                //->andWhere('o.productCategoryName is not null');
            if ($groupByCtg) {
                $this->_queryBuilder
                    ->orderBy('o.productCategoryName','ASC')
                    ->addOrderBy('o.productType','ASC');
                    //->addGroupBy('o.productCategoryName');
            }else{
                $this->_queryBuilder
                    ->orderBy('o.productType','ASC')
                    ->addOrderBy('o.productCategoryName','ASC')
                    ->addGroupBy('o.productCategoryName,o.productType');
            }
        } else {
            $this->_queryBuilder
                ->select('o.referenceName, o.url')
                ->addOrderBy('o.referenceName', 'ASC');
            if ($groupByCtg) {
                $this->_queryBuilder->addGroupBy('o.referenceName');
            }else{
                $this->_queryBuilder->addGroupBy('o.referenceName,o.url');
            }
        }
        $this->_queryBuilder
            ->from($this->_entityName, 'o')
            ->andWhere('o.usableData = :usableData')
            ->setParameter('usableData','1')
            ->andWhere('o.appellationName = :appellationName')
            ->andWhere('o.appellationCode = :appellationCode')
            ->setParameter('appellationName', $appellationName)
            ->setParameter('appellationCode', $appellationCode);
        return $this->_queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param $appellationName
     * @param $appellationCode
     * @return array
     */
    public function getIdListByAppelationCode($appellationName,$appellationCode,$version)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.id')
            ->from($this->_entityName, 'o')
            ->where('o.appellationName = :appellationName')
            ->andWhere('o.appellationCode = :appellationCode')
            ->andWhere('o.versioning = :version')
            ->setParameter('appellationName', $appellationName)
            ->setParameter('appellationCode', $appellationCode)
            ->setParameter('version', $version);
        $result = $this->_queryBuilder->getQuery()->getArrayResult();
        array_walk($result, function(&$val){
           $val = $val['id'];
        });
        return $result;
    }

    /**
     * @param array $aCriteria
     * @return array
     */
	public function getAllAppelationCode($aCriteria = [])
	{
		$this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
			->select('distinct(o.appellationCode) as appellationCode, o.appellationName')
			->from($this->_entityName, 'o')
            ->andWhere('o.usableData = :usableData')
            ->setParameter('usableData','1')
            ->orderBy('o.appellationCode','ASC');
        if (isset($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->andWhere('o.countryCode = :countryCode')
                ->setParameter('countryCode', $aCriteria['countryCode']);
        }
		$result = $this->_queryBuilder->getQuery()->getArrayResult();
		$aAllcode = [];
		array_walk($result, function($val) use(&$aAllcode) {
			$aAllcode[$val['appellationCode']] = $val['appellationName'];
        });
        return $aAllcode;
	}

    /**
     * @param array $aCriteria
     * @return array
     */
	public function getParentCode($aCriteria = [])
	{
		$this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
			->select('distinct(o.parentCode) as parentCode, o.parentName')
			->from($this->_entityName, 'o')
            ->andWhere('o.usableData = :usableData')
            ->setParameter('usableData','1')
            ->orderBy('o.parentCode','ASC');
        if (isset($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->andWhere('o.countryCode = :countryCode')
                ->setParameter('countryCode', $aCriteria['countryCode']);
        }
		$result = $this->_queryBuilder->getQuery()->getArrayResult();
		$aAllcode = [];
		array_walk($result, function($val) use(&$aAllcode) {
			$aAllcode[$val['parentCode']] = $val['parentName'];
        });
        return $aAllcode;
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
            ->andWhere('o.usableData = :usableData')
            ->setParameter('usableData','1');
        if (isset($aCriteria['appellationCode'])) {
            if (is_array($aCriteria['appellationCode'])) {
                $this->_queryBuilder
                    ->andWhere('o.appellationCode IN (:appellationCode)')
                    ->setParameter('appellationCode', $aCriteria['appellationCode'] );
                ;
            } else {
                $this->_queryBuilder
                    ->andWhere('o.appellationCode =:appellationCode')
                    ->setParameter('appellationCode',$aCriteria['appellationCode']);
            }
            unset($aCriteria['appellationCode']);
        }
        if (isset($aCriteria['appellationName'])) {
            if (is_array($aCriteria['appellationName'])) {;
                $this->_queryBuilder
                    ->andWhere('o.appellationName IN (:appellationName)')
                    ->setParameter('appellationName',$aCriteria['appellationName']);
            } else {
                $this->_queryBuilder
                    ->andWhere('o.appellationName =:appellationName')
                    ->setParameter('appellationName',$aCriteria['appellationName']);
            }
            unset($aCriteria['appellationName']);
        }
        if (isset($aCriteria['typeInternationalCode'])) {
            $this->_queryBuilder
                ->andWhere('o.typeInternationalCode =:typeInternationalCode')
                ->setParameter('typeInternationalCode',$aCriteria['typeInternationalCode']);
            unset($aCriteria['typeInternationalCode']);
        }
        if (isset($aCriteria['typeNationalCode'])) {
            $this->_queryBuilder
                ->andWhere('o.typeNationalCode =:typeNationalCode')
                ->setParameter('typeNationalCode',$aCriteria['typeNationalCode']);
            unset($aCriteria['typeNationalCode']);
        }
        array_walk($aCriteria, function($val, $field) use($queryBuilder){
            if (property_exists($this->_entityName, $field) && $val) {
                $this->_queryBuilder
                    ->andWhere(sprintf('o.%s like :%s',$field,$field))
                    ->setParameter(sprintf('%s',$field),'%'.$val.'%');
            }
        });
        $this->_queryBuilder = $queryBuilder;
    }
}