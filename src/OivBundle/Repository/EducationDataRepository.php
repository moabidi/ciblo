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
     * @return array
     */
    public static function getConfigFields() {
        return [
            'versioning' => [],
            'countryCode' => ['tab1','tab2'],
            'formationTitle' => ['filter'],
            'university' => ['filter'],
            'tutelle' => ['tab1','tab2'],
            'level' => ['filter','tab1','tab2'],
            'diploma' => ['tab1','tab2'],
            'cooperation' => ['tab1','tab2'],
            'month'=> ['tab1','tab2'],
            'hourCourses'=>['tab1','tab2'],
            'credits'=>['tab1','tab2'],
            'prior'=>['tab1','tab2'],
            'deadline'=>['tab1','tab2'],
            'contact'=>['tab1','tab2'],
            'adress'=>['tab1','tab2'],
            'lastDate'=>['tab1','tab2'],
            'internetAdress'=>['tab1','tab2']
        ];
    }
}