<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 14/11/18
 * Time: 22:21
 */

namespace OivBundle\Repository;
use Doctrine\ORM\Query\Expr;

class VarietyDataRepository extends BaseRepository
{

    protected $_sort = 'grapeVarietyName';
    protected $_order = 'ASC';
    /**
     * SELECT count(*) FROM oivdataw.variety_data where COUNTRY_CODE='FRA'
     * AND  LAST_DATE  = (SELECT MAX(LAST_DATE) FROM oivdataw.variety_data where COUNTRY_CODE='FRA')
     * @var array $aCriteria
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountVariety($aCriteria = [])
    {
        return parent::getCountDB($aCriteria);
    }

    /**
     * Add default order
     */
    protected function addDefaultOrder()
    {
        $this->_queryBuilder->orderBy('c.countryNameFr','ASC');
        $this->_queryBuilder->addOrderBy('o.codeVivc','ASC');
    }

    /**
     * @return array
     */
    public static function getConfigFields() {
        return [
            'countryNameFr' => ['tab1','tab2'],
            'versioning' => [],
//            'countryCode' => ['tab1','tab2'],
            'isMainVariety' => [],
            'areaCultivated' => [],
            'areaYear' => [],
            'grapeVarietyName' => ['filter','tab1','tab2'],
            'synonym'=>['filter','tab2'],
            'codeVivc' => ['tab1','tab2'],
            'varietyNationalNameVivc'=> [],
            'nationalVarietyId'=>[],
            'grapeColor'=>[],
            'lastDate'=>['tab2'],
            'internetAdress'=>[]
        ];
    }
}