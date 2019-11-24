<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * StatData
 *
 * @ORM\Table(name="STAT_DATA")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\StatDataRepository")
 */
class StatData
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="VERSIONING", type="bigint", nullable=true)
     * @Assert\NotBlank()
     */
    private $versioning = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_CODE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="STAT_TYPE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $statType;

    /**
     * @var string
     *
     * @ORM\Column(name="MEASURE_TYPE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $measureType;

    /**
     * @var string
     *
     * @ORM\Column(name="METRIC_COMP_TYPE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $metricCompType = 'UNIQUE';

    /**
     * @var integer
     *
     * @ORM\Column(name="YEAR", type="integer", nullable=false)
     * @Assert\NotBlank()
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="VALUE", type="decimal", precision=19, scale=2, nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="INFO_SOURCE", type="string", length=16383, nullable=true)
     */
    private $infoSource;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LAST_DATE", type="datetime", nullable=false)
     */
    private $lastDate;

    /**
     * @var string
     *
     * @ORM\Column(name="GRAPES_DESTINATION", type="string", length=255, nullable=true)
     */
    private $grapesDestination;

    /**
     * @var bool
     *
     * @ORM\Column(name="USABLE_DATA", type="string", length=1, nullable=false)
     * @Assert\Length(max=1)
     */
    private $usableData;

    /**
     * @var bool
     *
     * @ORM\Column(name="LAST_DATA", type="string", length=1, nullable=false)
     */
    private $lastData;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * @param int $versioning
     */
    public function setVersioning($versioning)
    {
        $this->versioning = $versioning;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getStatType()
    {
        return $this->statType;
    }

    /**
     * @param string $statType
     */
    public function setStatType($statType)
    {
        $this->statType = $statType;
    }

    /**
     * @return string
     */
    public function getMeasureType()
    {
        return $this->measureType;
    }

    /**
     * @param string $measureType
     */
    public function setMeasureType($measureType)
    {
        $this->measureType = $measureType;
    }

    /**
     * @return string
     */
    public function getMetricCompType()
    {
        return $this->metricCompType;
    }

    /**
     * @param string $metricCompType
     */
    public function setMetricCompType($metricCompType)
    {
        $this->metricCompType = $metricCompType;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        if ($value !== null) {
            $value = str_replace(' ','',$value);
            $value = str_replace(',','.',$value);
        }
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getInfoSource()
    {
        return $this->infoSource;
    }

    /**
     * @param string $infoSource
     */
    public function setInfoSource($infoSource)
    {
        $this->infoSource = $infoSource;
    }

    /**
     * @return \DateTime
     */
    public function getLastDate()
    {
        if ($this->lastDate) {
            return $this->lastDate->format('Y-m-d H:i:s');
        }
        return $this->lastData;
    }

    /**
     * @param \DateTime $lastDate
     */
    public function setLastDate($lastDate)
    {
        $this->lastDate = $lastDate;
    }

    /**
     * @return string
     */
    public function getGrapesDestination()
    {
        return $this->grapesDestination;
    }

    /**
     * @param string $grapesDestination
     */
    public function setGrapesDestination($grapesDestination)
    {
        $this->grapesDestination = $grapesDestination;
    }

    /**
     * @return boolean
     */
    public function isUsableData()
    {
        return $this->usableData;
    }

    /**
     * @param boolean $usableData
     */
    public function setUsableData($usableData)
    {
        $this->usableData = $usableData;
    }

    /**
     * @return boolean
     */
    public function isLastData()
    {
        return $this->lastData;
    }

    /**
     * @param boolean $lastData
     */
    public function setLastData($lastData)
    {
        $this->lastData = $lastData;
    }

    /**
     * @param $aHeader
     * @return array|null
     */
    public static function getImportFieldsIdentifier()
    {
        return [
            'countryCode' => 'Pays',
            'statType' => 'Série',
            'year' => 'Année',
            'measureType' => 'Unité',
            'value' => 'Valeur',
            'infoSource' => 'Source',
            'lastDate' => 'Dernière mise à jour'
        ];
    }
    
    /**
     * @return array
     */
    public static function getConfigFields()
    {
        return [
            'id'=>['tab3'],
            'tradeBloc' => ['tab2','tab3','export','exportBo'],
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo','importBo'],
            'countryCode' => ['form','required'],
            'statType' => ['form','filter','tab1','tab2','tab3','export','exportBo','importBo','required'],
            'metricCompType' => ['tab1'],
            'year' => ['form','tab1','tab2','tab3','export','exportBo','importBo','required'],
            'measureType' => ['form','tab1','tab2','tab3','export','exportBo','importBo','required'],
            'value' => ['form','filter','tab1','tab2','tab3','export','exportBo','importBo','editable'],
            'grapesDestination'=>[],
            'infoSource'=> ['tab3','form','exportBo','importBo','editable'],
            'versioning' => ['form','required'],
            'usableData' => ['form','editable'],
            'lastDate'=>['form','exportBo','importBo'],
            'lastData' => [],
        ];
    }

    /**
     * @param array $aParams
     * @return array
     */
    public static function getCalculatedStat($aParams=["S_TAUX_EXTRACTION_JUSETMOUS"=>1.28, "U_COEF_CONSO_RT"=>0.9, "V_COEF_NOUVELLE_VIGNE"=>0.075, "X_EXTRACTION_RATE_WINE"=>1.35,"Y_EXTRACTION_RATE_RS"=>4,"Z_PERTE_RF_COEF"=>0.05])
    {
        return [
            "CONSUMPTION_WINE_CAPITA_COMPUTED"=>[
                "value" => "SELECT (SA.value/SA2.value)*100 as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='POPULATION_PLUS_15' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='S_CONSUMPTION_WINE' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='POPULATION_PLUS_15' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='S_CONSUMPTION_WINE' AND SA.usableData =1 AND SA.value !=''",
               "measure"=> "L_PER_CAPITA_15"],
            "COMSUMPTION_DRIED_GRP_PER_CAPITA_COMPUTED"=>[
                "value" => "SELECT (SA.value/SA2.value) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='TOTAL_POPULATION' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='N_NOMSUMPTION_DRIED_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='TOTAL_POPULATION' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='N_NOMSUMPTION_DRIED_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=>  "KG_CAPITA"],
            "COMSUMPTION_CAPITA_TABLE_GRP_COMPUTED"=>[
                "value" => "SELECT (SA.value/SA2.value) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='TOTAL_POPULATION' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='L_COMSUMPTION_TABLE_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='TOTAL_POPULATION' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='L_COMSUMPTION_TABLE_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "KG_CAPITA"],
            "COMSUMPTION_DRIED_GRP_COMPUTED"=>[
                "value" => "SELECT (SA.value+SA2.value-SA3.value) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='J_IMPORT_DRIED_GRP' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='K_EXPORT_DRIED_GRP' AND SA3.usableData =1 AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		where SA.statType='G_PROD_DRIED_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='J_IMPORT_DRIED_GRP' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='K_EXPORT_DRIED_GRP' AND SA3.usableData =1 AND SA3.value !='' AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		where SA.statType='G_PROD_DRIED_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "COMSUMPTION_TABLE_GRP_COMPUTED_M1"=>[
                "value" => sprintf("SELECT (SA.value*(1-%02.2f) + SA2.value - SA3.value - (SA4.value*%02.2f)- (SA5.value*%02.2f)) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='H_IMPORT_GRP' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='I_EXPORT_GRP' AND SA3.usableData =1 AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["Z_PERTE_RF_COEF"],$aParams["X_EXTRACTION_RATE_WINE"],$aParams["Y_EXTRACTION_RATE_RS"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='H_IMPORT_GRP' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='I_EXPORT_GRP' AND SA3.usableData =1 AND SA3.value !='' AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.value !='' AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.value !='' AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.value !='' ",
                "measure"=>  "TONNES"],
            "COMSUMPTION_TABLE_GRP_COMPUTED_M2"=>[
                "value" =>  sprintf("SELECT (SA.value + (SA2.value*(1-%02.2f))) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='J_IMPORT_DRIED_GRP' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='F_PROD_TABLE_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["U_COEF_CONSO_RT"]),
                "select" =>  "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='J_IMPORT_DRIED_GRP' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='F_PROD_TABLE_GRP' AND SA.usableData =1 AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "COMSUMPTION_WINE_COMPUTED"=>[
                "value" =>  "SELECT (SA.value+SA2.value-SA3.value+SA4.value+SA5.value-SA6.value) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='Q_IMPORT_WINE' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='R_EXPORT_WINE' AND SA3.usableData =1 AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.countryCode=SA.countryCode AND SA4.year=:y2
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='Q_IMPORT_WINE' AND SA5.usableData =1 AND SA5.countryCode=SA.countryCode AND SA5.year=:y2
		INNER JOIN OivBundle:StatData SA6 with SA6.statType='R_EXPORT_WINE' AND SA6.usableData =1 AND SA6.countryCode=SA.countryCode AND SA6.year=:y2
		where SA.statType='P_PRODUCTION_WINE' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",
                "select" =>  "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='Q_IMPORT_WINE' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='R_EXPORT_WINE' AND SA3.usableData =1 AND SA3.value !='' AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		where SA.statType='P_PRODUCTION_WINE' AND SA.usableData =1 AND SA.value !=''",
                "measure"=>  "MILLE_HL"],
            "INPUT_PRODUCTION_DRIED_GRP_COMPUTED"=>[
                "value" =>  sprintf("SELECT SA.value*%02.2f as v FROM OivBundle:StatData SA where SA.statType='G_PROD_DRIED_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["Y_EXTRACTION_RATE_RS"]),
                "select" =>  "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA where SA.statType='G_PROD_DRIED_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "INPUT_PRODUCTION_JUICE_MUST_COMPUTED"=>[
                "value" => sprintf("SELECT SA.value*%02.2f as v FROM OivBundle:StatData SA where SA.statType='PRODUCTION_JUICE_MUST' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["S_TAUX_EXTRACTION_JUSETMOUS"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA where SA.statType='PRODUCTION_JUICE_MUST' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "INPUT_PRODUCTION_WINE_COMPUTED"=>[
                "value" => sprintf("SELECT SA.value*%02.2f as v FROM OivBundle:StatData SA where SA.statType='P_PRODUCTION_WINE' AND SA.countryCode=:p AND SA.usableData =1 AND SA.year=:y",$aParams["X_EXTRACTION_RATE_WINE"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA where SA.statType='P_PRODUCTION_WINE'  AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "PRODUCTION_TABLE_GRP_COMPUTED_M1"=>[
                "value" => "SELECT (SA.value+SA2.value-SA3.value) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='I_EXPORT_GRP' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='H_IMPORT_GRP' AND SA3.usableData =1 AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		where SA.statType='L_COMSUMPTION_TABLE_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='I_EXPORT_GRP' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='H_IMPORT_GRP' AND SA3.usableData =1 AND SA3.value !='' AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		where SA.statType='L_COMSUMPTION_TABLE_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "PRODUCTION_TABLE_GRP_COMPUTED_M2"=>[
                "value" => sprintf("SELECT (SA.value*(1-%02.2f) + SA2.value - SA3.value - (SA4.value*%02.2f)- (SA5.value*%02.2f)) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='H_IMPORT_GRP' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='I_EXPORT_GRP' AND SA3.usableData =1 AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["Z_PERTE_RF_COEF"],$aParams["X_EXTRACTION_RATE_WINE"],$aParams["Y_EXTRACTION_RATE_RS"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='H_IMPORT_GRP' AND SA2.usableData =1 AND SA2.value !=''AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='I_EXPORT_GRP' AND SA3.usableData =1 AND SA3.value !=''AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.value !=''AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.value !='' AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "PRODUCTION_TABLE_GRP_COMPUTED_M3"=>[
                "value" => sprintf("SELECT (SA.value*(1-%02.2f) + SA3.value - (SA4.value*%02.2f)- (SA5.value*%02.2f)) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='H_IMPORT_GRP' AND SA3.usableData =1 AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["Z_PERTE_RF_COEF"],$aParams["X_EXTRACTION_RATE_WINE"],$aParams["Y_EXTRACTION_RATE_RS"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='H_IMPORT_GRP' AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		INNER JOIN OivBundle:StatData SA3 with SA3.statType='P_PRODUCTION_WINE' AND SA3.usableData =1 AND SA3.value !='' AND SA3.countryCode=SA.countryCode AND SA3.year=SA.year
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='G_PROD_DRIED_GRP' AND SA4.usableData =1 AND SA4.value !='' AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=> "TONNES"],
            "PRODUCTION_TABLE_GRP_COMPUTED_M4"=>[
                "value" => sprintf("SELECT (SA.value*(1-%02.2f) - (SA4.value*%02.2f)- (SA5.value*%02.2f)) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["Z_PERTE_RF_COEF"],$aParams["X_EXTRACTION_RATE_WINE"],$aParams["Y_EXTRACTION_RATE_RS"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA4 with SA4.statType='P_PRODUCTION_WINE' AND SA4.usableData =1 AND SA4.value !='' AND SA4.countryCode=SA.countryCode AND SA4.year=SA.year
		INNER JOIN OivBundle:StatData SA5 with SA5.statType='G_PROD_DRIED_GRP' AND SA5.usableData =1 AND SA5.value !='' AND SA5.countryCode=SA.countryCode AND SA5.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=>  "TONNES"],
            "YIELD_COMPUTED"=>[
                "value" => sprintf("SELECT (SA.value/(SA2.value*(1-(%02.2f)))) as v FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='A_SURFACE' AND SA2.usableData =1 AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.countryCode=:p AND SA.year=:y",$aParams["V_COEF_NOUVELLE_VIGNE"]),
                "select" => "SELECT SA.countryCode, SA.year FROM OivBundle:StatData SA
		INNER JOIN OivBundle:StatData SA2 with SA2.statType='A_SURFACE'  AND SA2.usableData =1 AND SA2.value !='' AND SA2.countryCode=SA.countryCode AND SA2.year=SA.year
		where SA.statType='C_PROD_GRP' AND SA.usableData =1 AND SA.value !=''",
                "measure"=>  "TONNES_PER_HECTARE"],
        ];
    }

// select VALUE from STAT_DATA WHERE STAT_TYPE = 'C_PROD_GRP' AND COUNTRY_CODE = 'ZAF' AND YEAR = '2016';
// select VALUE from STAT_DATA WHERE STAT_TYPE = 'H_IMPORT_GRP' AND COUNTRY_CODE = 'ZAF' AND YEAR = '2016';
// select VALUE from STAT_DATA WHERE STAT_TYPE = 'I_EXPORT_GRP' AND COUNTRY_CODE = 'ZAF' AND YEAR = '2016';
// select VALUE from STAT_DATA WHERE STAT_TYPE = 'P_PRODUCTION_WINE' AND COUNTRY_CODE = 'ZAF' AND YEAR = '2016';
// select VALUE from STAT_DATA WHERE STAT_TYPE = 'G_PROD_DRIED_GRP' AND COUNTRY_CODE = 'ZAF' AND YEAR = '2016';
//(SA.value*(1-%02.2f) + SA2.value - SA3.value - (SA4.value*%02.2f)- (SA5.value*%02.2f)
//(0,95×1966291.00) +  6000 - 304928.79 - (10531.00×1,35) - 54600.00×4 = 1336430,81
}

