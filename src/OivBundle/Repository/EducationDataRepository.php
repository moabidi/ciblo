<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:20
 */

namespace OivBundle\Repository;

class EducationDataRepository extends BaseRepository
{
    protected $_sort = 'formationTitle';
    protected $_order = 'ASC';

    /**
     * SELECT count(*) FROM oivdataw.variety_data where COUNTRY_CODE='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @var array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountEducation($aCriteria = [])
    {
        return parent::getCountDB($aCriteria);
    }

    /**
     * Add default order
     */
    protected function addDefaultOrder()
    {
        $this->_queryBuilder->orderBy('c.countryNameFr','ASC');
        $this->_queryBuilder->addOrderBy('o.level','ASC');
        $this->_queryBuilder->addOrderBy('o.university','ASC');
    }
}