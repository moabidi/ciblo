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
        $result = $queryBuilder->getQuery()->getArrayResult();
        $aListProduct = [];
        foreach ($result as $row) {
            $product = $row['product'];
            if (!isset($aListProduct[$product])) {
                $aListProduct[$product] = [];
            }
            $aListProduct[$product][] = $row;
        }
        //var_dump($aListProduct);die;
        return $aListProduct;
    }
}