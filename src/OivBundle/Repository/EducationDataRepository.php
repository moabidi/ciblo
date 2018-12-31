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
            'id'=>['tab3'],
            'countryNameFr' => ['tab1','tab2','tab3'],
            'versioning' => ['form'],
            'countryCode' => ['form'],
            'formationTitle' => ['form','filter','tab1','tab2','tab3'],
            'university' => ['form','filter','tab1','tab2','tab3'],
            'tutelle' => ['form','tab2','tab3'],
            'level' => ['form','filter','tab1','tab2','tab3'],
            'diploma' => ['form','tab1','tab2','tab3'],
            'cooperation' => ['form','tab2','tab3'],
            'month'=> ['form','tab2','tab3'],
            'hourCourses'=>['form','tab2','tab3'],
            'credits'=>['form','tab2','tab3'],
            'prior'=>['form','tab2','tab3'],
            'deadline'=>['form','tab2','tab3'],
            'contact'=>['form','tab2','tab3'],
            'adress'=>['form','tab2','tab3'],
            'lastDate'=>['tab2','tab3'],
            'internetAdress'=>['form','tab2','tab3'],
            'usableData' => ['form'],
            'lastData' => [],
        ];
    }
}