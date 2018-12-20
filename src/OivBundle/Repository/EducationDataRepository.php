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

    /**
     * @return array
     */
    public static function getConfigFields() {
        return [
            'countryNameFr' => ['tab1','tab2'],
            'versioning' => [],
//            'countryCode' => ['tab1','tab2'],
            'formationTitle' => ['filter','tab1','tab2'],
            'university' => ['filter','tab1','tab2'],
            'tutelle' => ['tab2'],
            'level' => ['filter','tab1','tab2'],
            'diploma' => ['tab1','tab2'],
            'cooperation' => ['tab2'],
            'month'=> ['tab2'],
            'hourCourses'=>['tab2'],
            'credits'=>['tab2'],
            'prior'=>['tab2'],
            'deadline'=>['tab2'],
            'contact'=>['tab2'],
            'adress'=>['tab2'],
            'lastDate'=>['tab2'],
            'internetAdress'=>['tab2']
        ];
    }
}