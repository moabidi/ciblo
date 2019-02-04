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
        if(!isset($aCriteria['bo'])) {
            $this->_queryBuilder->addGroupBy('o.appellationCode');
            $result = $this->_queryBuilder->getQuery()->getArrayResult();
            if (count($result)) {
                return count($result);
            }
        } else{
            $result = $this->_queryBuilder->getQuery()->getOneOrNullResult();
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
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder->select('c.countryNameFr, o.id, o.countryCode, o.versioning,  o.appellationCode,o.appellationName, o.parentCode, o.parentName, o.typeNationalCode, o.typeInternationalCode,
         count(distinct o.productType) as productCategoryName, count( distinct o.productCategoryName) as productType, count(distinct o.referenceName) as referenceName,
            o.lastDate, o.url');

        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);
        if(!isset($aCriteria['bo'])) {
            $this->_queryBuilder->addGroupBy('o.appellationCode');
        } else {
            $this->_queryBuilder->select('c.countryNameFr, o.id, o.countryCode, o.versioning,  o.appellationCode,o.appellationName, o.parentCode, o.parentName, o.typeNationalCode, o.typeInternationalCode,
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

    public function getExportResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        $this->_sort = $sort ? 'o.'.$sort:'o.'.$this->_sort;
        $this->_order = $order ? $order:$this->_order;
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder->select('c.countryNameFr, o');

        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);
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
     * @param $appellationName
     * @param bool $isCtg
     * @return array
     */
    public function getInfoNaming($appellationName, $isCtg=true)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        if ($isCtg) {
            $this->_queryBuilder
                ->select('o.productCategoryName, o.productType')
                ->addGroupBy('o.productCategoryName')
                ->andWhere('o.productCategoryName is not null')
                ->orderBy('o.productType','ASC')
                ->addOrderBy('o.productCategoryName','ASC');
        } else {
            $this->_queryBuilder
                ->select('o.referenceName, o.url')
                ->addGroupBy('o.referenceName')
                ->andWhere('o.productCategoryName is not null')
                ->addOrderBy('o.referenceName', 'ASC');
        }
        $this->_queryBuilder
            ->from($this->_entityName, 'o')
            ->where('o.appellationName = :appellationName')
            ->setParameter('appellationName', $appellationName);
        return $this->_queryBuilder->getQuery()->getArrayResult();
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
            'countryCode' => ['form',],
            'appellationCode' => ['form',],
            'appellationName' => ['form','filter','tab1','tab2','tab3','export','exportBo' ],
            'parentCode' => ['form',],
            'parentName' => ['form','tab2','tab3','export','exportBo'],
            'typeNationalCode' => ['form','filter', 'tab1', 'tab2','tab3','export','exportBo'],
            'typeInternationalCode' => ['form','filter', 'tab1', 'tab2','tab3','export','exportBo'],
            'productType' => ['form','filter','tab2','tab3','export','exportBo'],
            'productCategoryName' => ['form','filter','tab3','export','exportBo'],
            'referenceName' => ['form','tab2','tab3','export','exportBo'],
            'url' => ['form','tab3','export','exportBo'],
            'lastDate' => ['tab2','tab3'],
            'usableData' => ['form'],
            'lastData' => [],
        ];
    }
}