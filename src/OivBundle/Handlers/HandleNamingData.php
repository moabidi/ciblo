<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 05/07/19
 * Time: 23:25
 */

namespace OivBundle\Handlers;


use OivBundle\Repository\StatDataRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Monolog\Logger;
use Doctrine\Bundle\DoctrineBundle\Registry;
use OivBundle\Entity\NamingData;

class HandleNamingData
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
     * Combine products and Reference Base
     * @param Request $request
     * @return array
     */
    public static function getCombineDataNaming($aRequestedData)
    {
        $aProductsType = isset($aRequestedData['products']) ? $aRequestedData['products']:[];
        $aCategories = isset($aRequestedData['categories']) ? $aRequestedData['categories']:[];
        $aReferences = isset($aRequestedData['references']) ? $aRequestedData['references']:[];
        $aUrls = isset($aRequestedData['urls']) ? $aRequestedData['urls']:[];
        $maxLengthProduct = count($aCategories);
        $maxReference = count($aReferences);
        if ($maxLengthProduct == 0 || $maxReference ==0){
            throw new \Exception('Au moins une categorie et une base légale par appellation.');
        }
        $aCombinedData = [];
        for($i=0;$i<$maxLengthProduct;$i++)  {
            for($j=0;$j<$maxReference;$j++) {
                if ( trim($aCategories[$i]) != '' && trim($aReferences[$j]) !='' ) {
                    $aCombinedData[] = [
                        'productCategoryName' => isset($aProductsType[$i]) ? $aProductsType[$i] : '',
                        'productType' => isset($aCategories[$i]) ? $aCategories[$i] : '',
                        'referenceName' => isset($aReferences[$j]) ? $aReferences[$j] : '',
                        'url' => isset($aUrls[$j]) ? $aUrls[$j] : '',
                    ];
                } else {
                    throw new \Exception('Les categories de produits et les bases légales ne doivent pas être vide.');
                }
            }
        }
        return $aCombinedData;
    }

    /**
     * Combine products and Reference Base for many Appelation Code, used when import file
     * Header products file : IG/AO;	Code IG/AO;	Catégories de produits;	Type de produit
     * Header refernces file : IG/AO;	Code IG/AO;	Base règlementaire;	Lien sur base légale
     * @param array $result
     * @param array $aProducts
     * @param array $aReferences
     * @return array
     */
    public static function combineAndMergeNamingData($result,$aProducts,$aReferences)
    {
        $aProducts = self::removeDuplicatedData($aProducts['data']);
        $aReferences = self::removeDuplicatedData($aReferences['data']);
        $aCombRefProduct = [];
        $aAddedCode = [];
        foreach($aProducts as $product) {
            $found = false;
            foreach($aReferences as $i=>$ref) {
                if ($product[1] == $ref[1] && ($ref[2]||$ref[3]) ) {
                    $aCombRefProduct[] = [$ref[0], $ref[1], $product[2], $product[3],$ref[2],$ref[3]];
                    $aAddedCode[] = $product[1];
                    $found = true;
                }
            }
            if(!$found) {
                $aCombRefProduct[] = [$product[0], $product[1], $product[2], $product[3],null,null];
            }
        }
        if(count($aReferences)) {
            foreach ($aReferences as $i => $ref) {
                if(in_array($ref[1],$aAddedCode))
                    unset($aReferences[$i]);
            }
        }
        if(count($aReferences)) {
            foreach ($aReferences as $ref) {
                $aCombRefProduct[] = [$ref[0], $ref[1], null, null, $ref[2], $ref[3]];
            }
        }
        $aCombinedData = [];
        $aFoundedCode = [];
        foreach($aCombRefProduct as $product) {
            $namingCode = $product[1];
            $indexCode = null;
            foreach ($result['data'] as $index => $row) {
                if ($row[2] == $namingCode) {
                    $indexCode = $index;
                    $aRowCode = $row;
                    $aRowCode[7] = $product[2];//productCategoryName
                    $aRowCode[8] = $product[3];//productType
                    $aRowCode[9] = $product[4];//referenceName
                    $aRowCode[10] = $product[5];//url
                    if (!in_array($namingCode,$aFoundedCode)) {
                        $result['data'][$indexCode] = $aRowCode;
                        $aFoundedCode[] =$namingCode;
                    }else{
                        $aCombinedData[] = $aRowCode;
                    }
                    break;
                }
            }
            if ($indexCode === null) {
                throw new \Exception('Le code IG/AO suivant n\'existe pas : '.$namingCode);
            }
        }
        if( count($aCombinedData)) {
            $data = $result['data'];
            $data = array_merge($data, $aCombinedData);
            $data = StatDataRepository::sortResult($data,1);
            $data = array_values($data);
            $result['data'] = $data;
        }
        return $result;
    }

    /**
     * @param $data
     * @param $tag
     * @return mixed
     */
	public function checkContentAppelationCode($data,$tag)
	{
        $aIndex = NamingData::getIndexCodeByTag($tag);
        $aAllAppellationCode = $this->_oDoctrine->getRepository('OivBundle:NamingData')->getAllAppelationCode(['countryCode'=>$data[0][$aIndex['indexCountryCode']]]);
        $this->checkNotDuplicatedCode($data,$aIndex['indexAppelationCode'],$aIndex['indexParentCode']);
        /** Add new code and name to the existent list that is given from db */
        array_walk($data, function($val) use (&$aAllAppellationCode,$aIndex) {
            if ($val[$aIndex['indexAppelationCode']] && $val[$aIndex['indexAppelationName']] && !isset($aAllAppellationCode[$aIndex['indexAppelationCode']])) {
                $aAllAppellationCode[$val[$aIndex['indexAppelationCode']]] = $val[$aIndex['indexAppelationName']];
            }
        });
        /** Set All AppellationCode to Upper */
        $aUpperAllAppellationCode = [];
        foreach($aAllAppellationCode as $code => $name) {
            $aUpperAllAppellationCode[strtoupper($code)] = $name;
        }
        $aAllAppellationCode = $aUpperAllAppellationCode;
        /** check that all code/name exist */
        $aCurrentAppellationCode = [];
        $aCurrentAppelationName = [];
        array_walk($data, function(&$val,$key) use ($aAllAppellationCode,$aIndex,&$aCurrentAppellationCode,&$aCurrentAppelationName) {
			$val[$aIndex['indexAppelationCode']] = $this->searchCode($aAllAppellationCode,$val[$aIndex['indexAppelationCode']],$val[$aIndex['indexAppelationName']],$key);
			$val[$aIndex['indexAppelationName']] = $this->searchName($aAllAppellationCode,$val[$aIndex['indexAppelationCode']],$val[$aIndex['indexAppelationName']],$key);
			$val[$aIndex['indexParentCode']] = $this->searchCode($aAllAppellationCode,$val[$aIndex['indexParentCode']],$val[$aIndex['indexParentName']],$key,true);
			$val[$aIndex['indexParentName']] = $this->searchName($aAllAppellationCode,$val[$aIndex['indexParentCode']],$val[$aIndex['indexParentName']],$key,true);
            
            if ($val[$aIndex['indexAppelationCode']] == $val[$aIndex['indexParentCode']]){
                throw new \Exception('Le code IG/AO et le code parent sont identiques : '.$val[$aIndex['indexAppelationCode']]);
            }
            if ($val[$aIndex['indexAppelationName']] == $val[$aIndex['indexParentName']]){
                throw new \Exception('Le nom IG/AO et le nom parent sont identiques : '.$val[$aIndex['indexParentName']]);
            }

            if (!$val[$aIndex['indexParentCode']] && $val[$aIndex['indexParentName']]){
                throw new \Exception('Le code parent est vide pour le nom: '.$val[$aIndex['indexParentName']]);
            }
            if ($val[$aIndex['indexParentCode']] && !$val[$aIndex['indexParentName']]){
                throw new \Exception('Le nom parent est vide pour le code: '.$val[$aIndex['indexParentCode']]);
            }
            /** Check no doublon code/name appellation */
            if (in_array($val[$aIndex['indexAppelationCode']], $aCurrentAppellationCode)) {
                throw new \Exception('Le code IG/AO est en doublon : '.$val[$aIndex['indexAppelationCode']]);
            }
            if (in_array($val[$aIndex['indexAppelationName']], $aCurrentAppelationName)) {
                throw new \Exception('Le nom IG/AO est en doublon : '.$val[$aIndex['indexAppelationName']]);
            }
            $aCurrentAppellationCode[] = $val[$aIndex['indexAppelationCode']];
            $aCurrentAppelationName[] = $val[$aIndex['indexAppelationName']];
		});
		return $data;
	}

    /**
     * Threow eror if data has duplicated code
     * @param $data
     * @param $indexAppelationCode
     * @param $indexParentCode
     * @throws \Exception
     */
    public static function checkNotDuplicatedCode(&$data,$indexAppelationCode,$indexParentCode)
    {
        $aUniqueCode = [];
        foreach ($data as $index => &$row){
            $uper = strtoupper(trim($row[$indexAppelationCode]));
            $lower = strtolower(trim($row[$indexAppelationCode]));
            if (isset($aUniqueCode[$uper]) || isset($aUniqueCode[$lower])) {
                throw new \Exception('Code dupliqué plusieur fois : '.$row[$indexAppelationCode]);
            }
            $aUniqueCode[$uper] = true;
            $aUniqueCode[$lower] = true;
            $row[$indexAppelationCode] = $uper;
            $row[$indexParentCode] = strtoupper(trim($row[$indexParentCode]));
        }
    }

    /**
     * @param array $aAllAppellationCode
     * @param string $code
     * @param string $name
	 * @param int $line
     * @return string
     */	
	public static function searchCode($aAllAppellationCode, $code, $name, $line,$isParent=false)
	{
        $line = $line+2;
	    $code = strtoupper($code);
		if( trim($code) == '' && trim($name) == '' && !$isParent) {
			throw new \Exception('Code et Nom appellation sont vide dans la ligne '.$line);
		} elseif (trim($code) == '') {
            $name = trim($name);
			if (!$isParent && ($val = array_search($name, $aAllAppellationCode)) === false ) {
			    throw new \Exception('Aucun Code appellation trouvé pour le nom : \''.$name.'\' (ligne '.$line.')');
			}elseif (!$isParent) {
                return $val;
            }
		}
        if ($isParent && $code && !isset($aAllAppellationCode[$code])) {
            throw new \Exception('Aucun  Code appellation superieur trouvé ('.$code.') pour le nom \''.$name.'\' (ligne '.$line.')');
        }
		return $code;
	}

    /**
     * @param array $aAllAppellationCode
     * @param string $code
     * @param string $name
	 * @param int $line
     * @return string
     */	
	public static function searchName($aAllAppellationCode, $code, $name, $line,$isParent=false)
	{
		if( trim($code) == '' && trim($name) == '' && !$isParent) {
			throw new \Exception('Code et Nom appellation sont vide (ligne '.$line.')');
		} elseif (trim($name) == '') {
            $code = trim($code);
			if (!$isParent && !isset($aAllAppellationCode[$code]) && !isset($aAllAppellationCode[ucfirst($code)])) {
				throw new \Exception('Aucun nom d\'appellation trouvé pour le code : \''.$code.'\' (ligne '.$line.')');
			}elseif(isset($aAllAppellationCode[$code])) {
                return $aAllAppellationCode[$code];
            }elseif(isset($aAllAppellationCode[ucfirst($code)])) {
                return $aAllAppellationCode[ucfirst($code)];
            }
		}
        if ($isParent && isset($aAllAppellationCode[$code])) {
            return $aAllAppellationCode[$code];
        }
		return $name;
	}
	
	/**
	* @param $container
	* @param array $data
	*/
	public static function generateCombinedDataFile($container, $data=[])
	{
		$pathFile = $container->getParameter('import_bdd_file').'/NamingData_'.date('Y-m-d-H-i-s').'.csv';
        $oFile = new Filesystem();
        $oFile->dumpFile($pathFile,'');
		$fp = fopen($pathFile,'w');
		array_walk($data, function($row) use($fp) {
			fputcsv($fp,$row);
		});
		fclose($fp);
	}

    /**
     * @param array $data [0=>['a'=>'a1','b'=>'b1'],1=>['c'=>'c1','d'=>'d1']]
     * @return mixed
     */
    public static function removeDuplicatedData($data)
    {
        $data = array_map('json_encode', $data);
        $data = array_unique($data);
        $data = array_map('json_decode', $data);

        return $data;
    }
}