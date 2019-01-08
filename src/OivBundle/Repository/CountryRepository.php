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
            ->where('o.iso3 = :countryCode')
            ->setParameter('countryCode', $countryName);
        if ($result = $queryBuilder->getQuery()->getOneOrNullResult()) {
            return $result;
        } else {
            $queryBuilder = $this->getEntityManager()->createQueryBuilder();
            $queryBuilder
                ->select('o')
                ->from($this->_entityName, 'o')
                ->where('o.countryNameFr like :countryName')
                ->orWhere('o.countryNameEn like :countryName')
                ->orWhere('o.countryNameIt like :countryName')
                ->orWhere('o.countryNameEs like :countryName')
                ->orWhere('o.countryNameDe like :countryName')
                ->setParameter('countryName', '%' . $countryName . '%');
            $result = $queryBuilder->getQuery()->getResult();
            if (count($result)) {
                return $result[0];
            }else {
                return null;
            }
        }

    }

    public function checkFiltredCountry($countryCode,$aSelectedCountry)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o')
            ->from($this->_entityName, 'o')
            ->where('o.iso3 = :countryCode')
            ->andWhere('o.tradeBloc IN (\''. implode('\',\'',$aSelectedCountry) .'\')')
            ->setParameter('countryCode', $countryCode);
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}