<?php

namespace OivBundle\Handlers;
use Monolog\Logger;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Mapping\Entity;
use OivBundle\Entity\Country;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use OivBundle\Entity\EducationData;
use OivBundle\Entity\NamingData;
use OivBundle\Entity\StatData;
use OivBundle\Entity\Users;
use OivBundle\Entity\VarietyData;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 05/07/19
 * Time: 22:36
 */
class HandleImportFile
{

    /**
     * @var Logger $_oLogger
     */
    private $_oLogger;
    
    /**
     * @var Registry $_oDoctrine
     */
    private $_oDoctrine;

    /**
     * @var DataCollectorTranslator $_oTranslator
     */
    private $_oTranslator;
    
    public function __construct(Logger $oLogger, Registry $oDoctrine, DataCollectorTranslator $oTranslator)
    {
        $this->_oLogger = $oLogger;
        $this->_oDoctrine = $oDoctrine;
        $this->_oTranslator = $oTranslator;
    }

    /**
     * how Add massive content into db :
     * 1- get max version from current data table into db
     * 2- Set all current  data table to USABLE_DATA = 0
     * 3- Add new data into table db with VERSIONING = max+1
     *
     * @param Container $oContainer
     * @param array $result
     * @param string $table
     * @return bool
     */
    public function saveContentFile($result, $table)
    {
        $success = false;
        if (count($result['labelfields']) && count($result['data'])>0) {
            $aFields = array_keys($result['labelfields']);
            $class = 'OivBundle\\Entity\\'.$table;
            $aAllLangCountries = $this->_oDoctrine->getRepository('OivBundle:Country')->getAllLangCountries();
            $maxVersion = $this->_oDoctrine->getRepository('OivBundle:'.$table)->getMaxVersion();
            $maxVersion = $maxVersion+1;

            $indexCountry = array_search('countryCode',$aFields);
            if ($table == 'StatData') {
                $success = $this->_oDoctrine->getRepository('OivBundle:'.$table)->createQueryBuilder('o')->update()->set('o.usableData','0')->getQuery()->execute();
                $indexStatType = array_search('statType',$aFields);
                $alistStatType = $this->_oDoctrine->getRepository('OivBundle:StatDataParameter')->getListStatType('private');
                $aIdentifierStatType  = [];
                foreach ($alistStatType as $statType) {
                    $aIdentifierStatType[$statType['indicator']] = $this->_oTranslator->trans($statType['indicator']);
                }
            }
            
            if ($table == 'NamingData') {
                $indexProductType = array_search('productType',$aFields);
                $alistCtgAppelation = $this->_oDoctrine->getRepository('OivBundle:NamingData')->getDistinctValueField('productType');
                $aIdentifierCtgAppelation = [];
                foreach ($alistCtgAppelation as $ctg) {
                    $aIdentifierCtgAppelation[$ctg['productType']] = $this->_oTranslator->trans($ctg['productType']);
                }
            }
            if (in_array($table,['NamingData','EducationData','VarietyData'])) {
                $this->setUsableDataByCountry($table,$aAllLangCountries,$result['data'][0][$indexCountry]);
            }
            foreach ($result['data'] as $index => $row) {
                //$countryName = strtolower(iconv('UTF-8','ASCII//TRANSLIT',$row[$indexCountry]));
                $countryName = strtolower($row[$indexCountry]);
                if (!isset($aAllLangCountries[$countryName])) {
                    throw new \Exception('Aucun pays trouvé avec le nom : '.$countryName);
                }
                $row[$indexCountry] = $aAllLangCountries[$countryName];
                if ($table == 'StatData') {
                    $row[$indexStatType] = array_search($row[$indexStatType],$aIdentifierStatType);
                }elseif ($table == 'NamingData'){
                    $row[$indexProductType] = array_search($row[$indexProductType],$aIdentifierCtgAppelation);
                }

                $oData = new $class();
                foreach($row as $key => $val) {
                    $idField = $aFields[$key];
                    if ($idField == 'lastDate') {
                        $val = new \DateTime($val);
                    }elseif($idField == 'value') {
                        if ($val == '0' || $val !='') {
                            $val = str_replace(',', '.', $val);
                            $val = number_format($val, 2, '.', '');
                        }else{
                            $val = null;
                        }
                    }
                    $oData->{'set'.ucfirst($idField)}($val);
                }
                if (!$oData->getLastDate()) {
                    $oData->setLastDate(new \DateTime());
                }
                $oData->setUsableData(1);
                $oData->setLastData(1);
                $oData->setVersioning($maxVersion);
                $this->_oDoctrine->getManager()->persist($oData);
                if (($index+1)%1000 == 0) {
                    $this->_oDoctrine->getManager()->flush();
                    $this->_oDoctrine->getManager()->clear();
                }
            }
            $this->_oDoctrine->getManager()->flush();
            $this->_oDoctrine->getManager()->clear();
            $success = $maxVersion;
        }
        return $success;
    }

    /**
     * check country Name and set usable_data country's to 0
     * @param $table
     * @param $aAllLangCountries
     * @param $countryName
     * @throws \Exception
     */
    private function setUsableDataByCountry($table,$aAllLangCountries,$countryName)
    {
        $countryName = strtolower($countryName);
        if (!isset($aAllLangCountries[$countryName])) {
            throw new \Exception('Aucun pays trouvé avec le nom : '.$countryName);
        }
        $countryCode = $aAllLangCountries[$countryName];
        $success = $this->_oDoctrine->getRepository('OivBundle:'.$table)
            ->createQueryBuilder('o')
            ->update()->set('o.usableData','0')
            ->where('o.countryCode = :countryCode')
            ->setParameter('countryCode', $countryCode)
            ->getQuery()->execute();
    }


    /**
     * @param File $file
     * @param $table
     * @param null $custumHeader
     * @return array
     * @throws \Exception
     */
    public function getContentFile(File $file, $table,$custumHeader=null)
    {
        //$x =$this->_detectFileEncoding($file->getPath());var_dump($x);die;
        $result = [];
        if ($file && in_array($file->getMimeType(),['text/plain','text/csv','application/octet-stream'])) {
            if (($handle = fopen($file->getPathname(), "r")) !== false) {
                $class = 'OivBundle\\Entity\\'.$table;
                $isHeader = true;
                $utf8Encode = true;
                $aHeaderIdentifier = [];
                $aIndexIdentifier = [];
                $aRequiredFields = [];
                $aData = [];
                $country = '';
                $countryName = '';
                $indexCountry = 0;
                $aAllLangCountries = $this->_oDoctrine->getRepository('OivBundle:Country')->getAllLangCountries();
                while (($contentLine = fgetcsv($handle, 0, ";")) !== false) {
                    if ($isHeader) {
                        $utf8Encode = $this->checkUtf8Encoding($contentLine);
                        $contentLine = $utf8Encode ? $contentLine:array_map('utf8_encode',$contentLine);
                        $aHeaderIdentifier = $this->getHeaderIdentfier($contentLine,$class,$custumHeader);
                        $aIndexIdentifier = array_keys($aHeaderIdentifier);
                        $indexCountry = array_search('countryCode',$aIndexIdentifier);
                        $aRequiredFields = $this->getRequiredFields($table,$aIndexIdentifier);
                        $isHeader = false;
                    } else {
						if ($table == 'EducationData' && $country != '' && ($contentLine[$indexCountry] != $country) ) {
							throw new \Exception('Pour les données formation, il faut faire l\'import pour un seul pays, le fichier à importer contient plus qu\'un pays.');
						}
                        $contentLine = $utf8Encode ? $contentLine:array_map('utf8_encode',$contentLine);
                        $aData[] = $contentLine;
						if (in_array($custumHeader,['namingProduct','namingReference'])) {
                            continue;
                        }
                        $countryName = strtolower($contentLine[$indexCountry]);
                        if (!isset($aAllLangCountries[$countryName])) {
						    throw new \Exception('Aucun pays trouvé avec le nom : '.$countryName);
						}
                        array_walk($aRequiredFields, function($v,$k) use($contentLine,$aHeaderIdentifier){
                            if (trim($contentLine[$v]) =='' ) {
                                throw new \Exception('Le champ "'.$aHeaderIdentifier[$k].'" est obligatoire');
                            }
                        });
                    }
                }
                fclose($handle);
                if (count($aData) ==0) {
                    throw new \Exception('Le fichier à importer est vide');
                }
                $result = ['labelfields'=>$aHeaderIdentifier,'data'=>$aData];
                if ($table == 'EducationData') {
                    $result['nbReplacement'] = $this->_oDoctrine->getRepository('OivBundle:EducationData')->getTotalResult(['countryCode'=>$aAllLangCountries[$countryName]]);
                    $result['nbNew'] = count($aData);
                }
            }
        }
        return $result;
    }

    /**
     * @param $contentLine
     * @return bool
     */
    private function checkUtf8Encoding($contentLine)
    {
        $utf8Encode = true;
        array_walk($contentLine, function($v) use(&$utf8Encode) {
            if (!mb_detect_encoding($v,'UTF-8',true)){
                $utf8Encode = false;
            }
        });
        return $utf8Encode;
    }

    /**
     * @param $aHeader
     * @param Entity $class
     * @param string $custum
     * @param string $encoding
     * @return array|null
     */
    private function getHeaderIdentfier($aHeader, $class, $custum=null,$encoding=null)
    {
        $aHeaderIdentfier = [];
        if ($custum == 'namingProduct') {
            $aFieldsName = $class::getImportCustumFieldsIdentifier(true);
            $msg = ' pour les produits';
        } elseif ($custum == 'namingReference') {
            $aFieldsName = $class::getImportCustumFieldsIdentifier(false);
            $msg = ' pour les bases légales';
        } else {
            $aFieldsName = $class::getImportFieldsIdentifier();
            $msg = '';
        }

        foreach ($aHeader as $key => &$val) {
            $val = $key==0 ? preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $val):$val;
            if (($identifier = array_search($val, $aFieldsName)) === false) {//var_dump($val,$aFieldsName,$aHeader);die;
                if (property_exists($class, $val)) {
                    $identifier = $val;
                } else {
                    $msg = 'Les noms des colonnes de fichier importé '.$msg.' ne sont pas valide. Il faut respecter l\'ordre et l\'orthoraphe de ces colonnes : '.PHP_EOL;
                    $msg .= implode(' | ',$aFieldsName).PHP_EOL;
                    $msg .= 'Voilà les colonnes du fichier envoyé : '.PHP_EOL;
                    $msg .= implode(' | ',$aHeader);
                    throw new \Exception($msg);
                }
            }
            $aHeaderIdentfier[$identifier] = $val;
        }
        return $aHeaderIdentfier;
    }

    function _detectFileEncoding($filepath) {
        // VALIDATE $filepath !!!
        $output = array();
        exec('file -i ' . $filepath, $output);
        if (isset($output[0])){
            $ex = explode('charset=', $output[0]);
            return isset($ex[1]) ? $ex[1] : null;
        }
        return null;
    }

    /**
     * return array of required fields [identifier => index_On_aIndexIdentifier]
     * @param string $table
     * @param string[] $aIndexIdentifier
     * @return string[]
     */
    protected function getRequiredFields($table,$aIndexIdentifier)
    {
        $class = 'OivBundle\\Entity\\'.$table;
        $aFields =[];
        foreach ($class::getConfigFields() as $name => $aTags) {
            if (in_array('required', $aTags) && $name != 'versioning') {
                $aFields[$name] = array_search($name,$aIndexIdentifier);
            }
        }
        return $aFields;
    }
}