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
     * @param array $aCriteria
     * @param int $offset
     * @param int $limit
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getGlobalResult($aCriteria = [], $offset = 0, $limit = 100, $sort= null, $order = null)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder->select('o.id, o.countryCode, o.versioning,  o.appellationCode,o.appellationName, o.parentCode, o.parentName, o.typeNationalCode, o.typeInternationalCode,
         count(distinct o.productCategoryName) as productCategoryName, count( distinct o.productType) as productType, count(distinct o.referenceName) as referenceName,
            o.lastDate, o.url');

        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);
        $this->_queryBuilder->addGroupBy('o.appellationName');
        $this->_queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);
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
                ->select('o.referenceName')
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
            'id' => ['tab1', 'tab2'],
            'versioning' => [],
            'countryCode' => ['tab1', 'tab2'],
            'appellationCode' => ['filter', 'tab1', 'tab2'],
            'appellationName' => ['filter', 'tab1', 'tab2'  ],
            'parentCode' => ['tab1', 'tab2'],
            'parentName' => ['tab1', 'tab2'],
            'typeNationalCode' => ['filter', 'tab1', 'tab2'],
            'typeInternationalCode' => ['filter', 'tab1', 'tab2'],
            'productCategoryName' => ['filter', 'tab1', 'tab2'],
            'productType' => ['filter', 'tab1', 'tab2'],
            'referenceName' => ['tab1', 'tab2'],
            'lastDate' => ['tab1', 'tab2'],
            'url' => ['tab1', 'tab2']
        ];
    }
}