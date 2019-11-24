<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 15/11/18
 * Time: 23:28
 */

namespace OivBundle\Repository;


class StatDataParameterRepository extends BaseRepository
{

    /**
     * @param string $view
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getListProduct($view = 'public')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o')
            ->from($this->_entityName, 'o')
            ->orderBy('o.productPriority')
            ->addOrderBy('o.priority');
        if ($view == 'public') {
            $queryBuilder->where('o.printableDataPublic = \'Y\'');
        }else{
            $queryBuilder->where('o.printableDataBackoffice = \'Y\'');
        }
        $result = $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        $aListProduct = [];
        foreach ($result as $row) {
            $product = $row['product'];
            if (!isset($aListProduct[$product])) {
                $aListProduct[$product] = [];
            }
            $aListProduct[$product][] = $row;
        }
        return $aListProduct;
    }

    /**
     * @param string $view
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getListStatType($view = 'public')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.indicator')
            ->from($this->_entityName, 'o')
            ->distinct('o.indicator')
            ->orderBy('o.productPriority')
            ->addOrderBy('o.priority');
        if ($view == 'public') {
            $queryBuilder->where('o.printableDataPublic = \'Y\'');
        }else{
            $queryBuilder->where('o.printableDataBackoffice = \'Y\'');
        }
        return $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
    }

    /**
     * @return array
     */
    public function getNotCalculatedStatType()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o.indicator')
            ->from($this->_entityName, 'o')
            ->distinct('o.indicator')
            ->orderBy('o.productPriority')
            ->addOrderBy('o.priority')
            ->where('o.indicator NOT LIKE \'%COMPUTED%\' ');
        $result = $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        array_walk($result, function(&$v){
            $v = $v['indicator'];
        });
        return $result;
    }
}