<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 26/07/19
 * Time: 16:56
 */

namespace OivBundle\Handlers;

use Monolog\Logger;
use Doctrine\Bundle\DoctrineBundle\Registry;
use OivBundle\Entity\StatData;

class HandleCalculatedStat
{
    
    /**
     * @var Logger $_oLogger
     */
    private $_oLogger;
    
    /**
     * @var Registry $_oDoctrine
     */
    private $_oDoctrine;
    
    public function __construct(Logger $oLogger, Registry $oDoctrine)
    {
        $this->_oLogger = $oLogger;
        $this->_oDoctrine = $oDoctrine;
    }

    /**
     * 1 - select all rows having valid value (not null or empty) and usable_data = 1 (active version)
     * 2 - calculate value for each row
     * 3 - insert/update the calculated value
     *
     * @param $oContainer
     * @param $statType
     * @param $year
     */
    public function calculateStat()
    {
        $this->_oLogger->addInfo('>>> Start query CalcStatDATA ');
        $timeStart = microtime(true);
        $em = $this->_oDoctrine->getManager();
        $maxVersion = $this->_oDoctrine->getRepository('OivBundle:StatData')->getMaxVersion();
        $aParameters = $this->_oDoctrine->getRepository('OivBundle:Parameters')->getAvailablesParameters();
        $aConfSql = StatData::getCalculatedStat($aParameters);
        $aResult = [];
        $total = 0;
        foreach ($aConfSql as $statType => $aConf) {
            $timeStartStat = microtime(true);
            $aRows = $em->createQuery($aConf['select'])->getResult();
            foreach($aRows as $index => $row) {
                $oQuery = $em->createQuery($aConf['value'])
                    ->setParameter('p',$row['countryCode'])
                    ->setParameter('y',$row['year']);
                if ($statType == 'COMSUMPTION_WINE_COMPUTED')
                    $oQuery->setParameter('y2',($row['year']-1));
                $value = $oQuery->setMaxResults(1)->getOneOrNullResult();
                $value = $value && trim($value['v']) !='' ? $value['v']:null;
                $oData = $this->_oDoctrine->getRepository('OivBundle:StatData')->findOneBy([
                    'countryCode' => $row['countryCode'],
                    'statType' => $statType,
                    'year' => $row['year'],
                ]);
                if (!$oData) {
                    $oData = new StatData();
                    $oData->setCountryCode($row['countryCode']);
                    $oData->setStatType($statType);
                    $oData->setYear($row['year']);
                }
                $oData->setValue($value);
                $oData->setMeasureType($aConf['measure']);
                $oData->setUsableData(1);
                $oData->setLastData(1);
                $oData->setVersioning($maxVersion);
                $oData->setLastDate(new \DateTime());
                $this->_oDoctrine->getManager()->persist($oData);
                if (($index+1)%1000 == 0) {
                    $this->_oDoctrine->getManager()->flush();
                    $this->_oDoctrine->getManager()->clear();
                }
            }
            $this->_oDoctrine->getManager()->flush();
            $this->_oDoctrine->getManager()->clear();
            $total += count($aRows);
            $aResult[$statType] = ['count'=>count($aRows),'execTime'=>round((microtime(true) -$timeStartStat)/60)];
            $this->_oLogger->addInfo('>>> CalcStatDATA => '.$statType.' || '.count($aRows).' row(s) added or updated');
        }
        $timeEnd = microtime(true);
        $this->_oLogger->addInfo('>>> End CalcStatDATA => Total rows is '.$total.' || Execution time '.round(($timeEnd-$timeStart)/60).' minutes');
        return $aResult;
    }
}