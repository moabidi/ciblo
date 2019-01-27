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
     * @param array $aCriteria
     * @param bool $count
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryResult($aCriteria = [], $count = false)
    {
        $this->_queryBuilder = $this->getEntityManager()->createQueryBuilder();
        if ($count) {
            $this->_queryBuilder->select('count(o) as total');
            $this->_queryBuilder->select('count(distinct(o.grapeVarietyName)) as total');
        } else {
            $this->_queryBuilder->select('c.tradeBloc, c.countryNameFr, o');
            $this->_queryBuilder->addGroupBy('o.grapeVarietyName');
        }
        $this->_queryBuilder->from($this->_entityName, 'o');
        $this->addAllCriteria($aCriteria);

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
            'id'=>['tab3'],
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo'],
            'versioning' => [],
            'countryCode' => ['form'],
            'isMainVariety' => ['form'],
            'areaCultivated' => ['form'],
            'areaYear' => ['form'],
            'grapeVarietyName' => ['form','filter','tab1','tab2','tab3','export','exportBo'],
            'synonym'=>['form','filter','tab2','tab3','export','exportBo'],
            'codeVivc' => ['form','tab1','tab2','tab3','export','exportBo'],
            'varietyNationalNameVivc'=> ['form'],
            'nationalVarietyId'=>['form'],
            'grapeColor'=>['form'],
            'lastDate'=>['tab2','tab3','export','exportBo'],
            'internetAdress'=>['form'],
            'usableData' => ['form'],
            'lastData' => [],
        ];
    }
}