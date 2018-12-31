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
            ->where('o.iso3 = :countryName')
            ->orWhere('o.countryNameFr = :countryName')
            ->orWhere('o.countryNameEn = :countryName')
            ->orWhere('o.countryNameIt = :countryName')
            ->orWhere('o.countryNameEs = :countryName')
            ->orWhere('o.countryNameDe = :countryName')
            ->setParameter('countryName', $countryName);
        return $queryBuilder->getQuery()->getSingleResult();

    }
}