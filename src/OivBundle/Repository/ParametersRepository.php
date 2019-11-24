<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 09/07/19
 * Time: 23:26
 */

namespace OivBundle\Repository;


use Doctrine\ORM\EntityRepository;
use OivBundle\Entity\Parameters;

class ParametersRepository extends EntityRepository
{

    public function getAvailablesParameters()
    {
        $aResult = $this->getEntityManager()->createQueryBuilder()
            ->select('distinct(o.name) as parameter, o.value' )
            ->from($this->_entityName,'o')
            ->orderBy('o.name')
            ->getQuery()
            ->getArrayResult();
        $aParameters = [];
        array_walk($aResult,function($val) use (&$aParameters) {
            $aParameters[$val['parameter']] = $val['parameter'] == Parameters::LAST_STAT_YEAR ? (int)$val['value']:(float)$val['value'];
        });
        return $aParameters;
    }
}