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
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo'],
            'versioning' => ['form'],
            'countryCode' => ['form'],
            'city' => ['form','tab2','tab3','export','exportBo'],
            'formationTitle' => ['form','filter','tab1','tab2','tab3','export','exportBo'],
            'university' => ['form','filter','tab1','tab2','tab3','export','exportBo'],
            'tutelle' => ['form','tab2','tab3','export','exportBo'],
            'level' => ['form','filter','tab1','tab2','tab3','export','exportBo'],
            'diploma' => ['form','tab1','tab2','tab3','export','exportBo'],
            'cooperation' => ['form','tab2','tab3','export','exportBo'],
            'month'=> ['form','tab2','tab3','export','exportBo'],
            'hourCourses'=>['form','tab2','tab3','export','exportBo'],
            'credits'=>['form','tab2','tab3','export','exportBo'],
            'prior'=>['form','tab2','tab3','export','exportBo'],
            'deadline'=>['form','tab2','tab3','export','exportBo'],
            'contact'=>['form','tab2','tab3','export','exportBo'],
            'adress'=>['form','tab2','tab3','export','exportBo'],
            'internetAdress'=>['form','tab2','tab3','export','exportBo'],
            'lastDate'=>['tab2','tab3','export','exportBo'],
            'usableData' => ['form'],
            'lastData' => [],
        ];
    }
}