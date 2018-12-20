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
        return parent::getCountDB($aCriteria);
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
         count(distinct o.productCategoryName) as productCategoryName, count( distinct o.productType) as productType, count(distinct o.referenceName) as referenceName,
            o.lastDate, o.url');

        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);
        $this->_queryBuilder->addGroupBy('o.appellationName');
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
                ->orderBy('o.productCategoryName','ASC')
                ->addOrderBy('o.productType','ASC');
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
            'countryNameFr' => ['tab1','tab2'],
            'id' => [],
            'versioning' => [],
//            'countryCode' => ['tab1', 'tab2'],
            'appellationCode' => [],
            'appellationName' => ['filter', 'tab1', 'tab2'  ],
            'parentCode' => [],
            'parentName' => ['tab2'],
            'typeNationalCode' => ['filter', 'tab1', 'tab2'],
            'typeInternationalCode' => ['filter', 'tab1', 'tab2'],
            'productCategoryName' => ['filter', 'tab2'],
            'productType' => ['tab2'],
            'referenceName' => ['tab2'],
            'lastDate' => ['tab2'],
            'url' => []
        ];
    }
}