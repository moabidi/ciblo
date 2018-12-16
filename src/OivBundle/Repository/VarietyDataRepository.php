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
     * @return array
     */
    public static function getConfigFields() {
        return [
            'versioning' => [],
            'countryCode' => ['tab1','tab2'],
            'isMainVariety' => ['filter','tab1','tab2'],
            'areaCultivated' => ['filter','tab1','tab2'],
            'areaYear' => ['filter','tab1','tab2'],
            'grapeVarietyName' => ['filter','tab1','tab2'],
            'codeVivc' => ['filter','tab1','tab2'],
            'varietyNationalNameVivc'=> [],
            'synonym'=>['filter','tab1','tab2'],
            'nationalVarietyId'=>['filter','tab1','tab2'],
            'grapeColor'=>[],
            'lastDate'=>['filter','tab1','tab2'],
            'internetAdress'=>['tab1']
        ];
    }
}