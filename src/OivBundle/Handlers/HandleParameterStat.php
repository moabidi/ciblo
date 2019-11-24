<?php
namespace OivBundle\Handlers;

use OivBundle\Entity\Parameters;
use Monolog\Logger;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 *
 * @author Abidi Mohamed
 *        
 */
final class HandleParameterStat
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
     * 
     * @param array $parameters
     * @return boolean
     */
    public function updateParameters($parameters)
    {
        if  ( $this->checkValidityParameters($parameters)) {
            foreach($parameters as $parameter => $val){
                $oParameters = $this->_oDoctrine->getRepository('OivBundle:Parameters')->findOneBy(['name' => $parameter]);
                $oParameters->setValue($val);
                $this->_oDoctrine->getManager()->persist($oParameters);
            }
            $this->_oDoctrine->getManager()->flush();
            return true;
        }
        return false;
    }
    
    private function getAvailableLastStatYears()
    {
        return array_combine(range(date('Y')-5,date('Y')),range(date('Y')-5,date('Y')));
    }
    
    /**
     * 
     * @param string $parameter
     * @return boolean
     */
    private function checkValidityParameters($parameters) {
        $aAvailablesParameters = $this->_oDoctrine->getRepository('OivBundle:Parameters')->getAvailablesParameters();

        foreach ($parameters as $parameter => $val) {
            if (array_key_exists($parameter, $aAvailablesParameters) === false) {
                return false;
            }
            if  ( $parameter == Parameters::LAST_STAT_YEAR && !in_array($val,$this->getAvailableLastStatYears())) {
                return false;
            }elseif($parameter != Parameters::LAST_STAT_YEAR && ($val != (string)(float)$val) && ($val !=(string)(int)$val)){
                return false;
            }
        }
        return true;
    }
}

