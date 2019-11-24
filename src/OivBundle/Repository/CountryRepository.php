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

    /**
     *
     * @param string $field
     * @param array $aCriteria
     * @return mixed
     */
    public function getDistinctValueField($field, $aCriteria = [])
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $this->_queryBuilder
            ->select('o.'.$field)
            ->from($this->_entityName, 'o')
            ->distinct('o.'.$field)
            ->orderBy('o.'.$field);
        if (isset($aCriteria['countryCode'])) {
            $this->_queryBuilder
                ->andWhere('o.countryCode = :countryCode')
                ->setParameter('countryCode', $aCriteria['countryCode']);
        }
        $result = $this->_queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
        return $this->reformatArray($result);
    }

    /**
     * @param $countryName
     * @return array|mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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
            $result = $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getResult();
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
        return $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getOneOrNullResult();
    }

    public function getCountries($countryLang)
    {
        $aCountries = $this->getAllCountries($countryLang);
        $aCountriesBis = [];
        foreach($aCountries as $aCountry) {
            $aCountriesBis[$aCountry['iso3']] = $aCountry;
        }
        return $aCountriesBis;
    }

	public function getAllLangCountries($countryLang = 'countryNameFr')
	{
		$aCountries = $this->getAllCountries($countryLang);
		$aCountriesBis = [];
        foreach($aCountries as $aCountry) {
            $aCountriesBis[strtolower($aCountry['countryNameEn'])] = $aCountry['iso3'];
			$aCountriesBis[strtolower($aCountry['countryNameEs'])] = $aCountry['iso3'];
			$aCountriesBis[strtolower($aCountry['countryNameDe'])] = $aCountry['iso3'];
			$aCountriesBis[strtolower($aCountry['countryNameFr'])] = $aCountry['iso3'];
			$aCountriesBis[strtolower($aCountry['countryNameIt'])] = $aCountry['iso3'];
        }
        return $aCountriesBis;
	}

	private function getAllCountries($countryLang)
	{
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('m.oivMembership, o.countryNameFr, o.countryNameEn, o.countryNameIt, o.countryNameEs, o.countryNameDe, o.tradeBloc, o.iso2, o.iso3')
            ->from($this->_entityName, 'o')
            ->innerJoin('OivBundle:OivMemberShip', 'm', 'WITH', 'm.iso3 = o.iso3 AND m.year = '.date('Y'))
            ->orderBy('o.'.$countryLang, 'ASC');
        return $queryBuilder->getQuery()->useResultCache(true)->setResultCacheLifetime(3600)->getArrayResult();
	}
}