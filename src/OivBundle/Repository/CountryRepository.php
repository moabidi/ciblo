<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 15/11/18
 * Time: 23:28
 */

namespace OivBundle\Repository;


class CountryRepository extends BaseRepository
{

    public function getCountryCode($countryName)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o')
            ->from($this->_entityName, 'o')
            ->where('o.countryNameFr = :countryName')
            ->orWhere('o.countryNameFr = :countryName')
            ->orWhere('o.countryNameFr = :countryName')
            ->orWhere('o.countryNameFr = :countryName')
            ->setParameter('countryName', $countryName);
        return $queryBuilder->getQuery()->getSingleResult();

    }
}