<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VarietyData
 *
 * @ORM\Table(name="VARIETY_DATA")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\VarietyDataRepository")
 */
class VarietyData
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
     */
    private $versioning = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_CODE", type="string", length=255, nullable=true)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="IS_MAIN_VARIETY", type="string", length=1, nullable=false)
     */
    private $isMainVariety;

    /**
     * @var string
     *
     * @ORM\Column(name="AREA_CULTIVATED", type="string", length=255, nullable=true)
     */
    private $areaCultivated;

    /**
     * @var string
     *
     * @ORM\Column(name="AREA_YEAR", type="string", length=255, nullable=true)
     */
    private $areaYear;

    /**
     * @var string
     *
     * @ORM\Column(name="GRAPE_VARIETY_NAME", type="string", length=255, nullable=true)
     */
    private $grapeVarietyName;

    /**
     * @var string
     *
     * @ORM\Column(name="CODE_VIVC", type="string", length=255, nullable=true)
     */
    private $codeVivc;

    /**
     * @var string
     *
     * @ORM\Column(name="VARIETY_NATIONAL_NAME_VIVC", type="string", length=255, nullable=true)
     */
    private $varietyNationalNameVivc;

    /**
     * @var string
     *
     * @ORM\Column(name="SYNONYM", type="string", length=255, nullable=true)
     */
    private $synonym;

    /**
     * @var string
     *
     * @ORM\Column(name="NATIONAL_VARIETY_ID", type="string", length=10, nullable=true)
     */
    private $nationalVarietyId;

    /**
     * @var string
     *
     * @ORM\Column(name="GRAPE_COLOR", type="string", length=25, nullable=true)
     */
    private $grapeColor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LAST_DATE", type="datetime", nullable=true)
     */
    private $lastDate;

    /**
     * @var string
     *
     * @ORM\Column(name="INTERNET_ADRESS", type="string", length=4000, nullable=true)
     */
    private $internetAdress;

    /**
     * @var bool
     *
     * @ORM\Column(name="USABLE_DATA", type="string", length=1, nullable=false)
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
    public function getIsMainVariety()
    {
        return $this->isMainVariety;
    }

    /**
     * @param string $isMainVariety
     */
    public function setIsMainVariety($isMainVariety)
    {
        $this->isMainVariety = $isMainVariety;
    }

    /**
     * @return string
     */
    public function getAreaCultivated()
    {
        return $this->areaCultivated;
    }

    /**
     * @param string $areaCultivated
     */
    public function setAreaCultivated($areaCultivated)
    {
        $this->areaCultivated = $areaCultivated;
    }

    /**
     * @return string
     */
    public function getAreaYear()
    {
        return $this->areaYear;
    }

    /**
     * @param string $areaYear
     */
    public function setAreaYear($areaYear)
    {
        $this->areaYear = $areaYear;
    }

    /**
     * @return string
     */
    public function getGrapeVarietyName()
    {
        return $this->grapeVarietyName;
    }

    /**
     * @param string $grapeVarietyName
     */
    public function setGrapeVarietyName($grapeVarietyName)
    {
        $this->grapeVarietyName = $grapeVarietyName;
    }

    /**
     * @return string
     */
    public function getCodeVivc()
    {
        return $this->codeVivc;
    }

    /**
     * @param string $codeVivc
     */
    public function setCodeVivc($codeVivc)
    {
        $this->codeVivc = $codeVivc;
    }

    /**
     * @return string
     */
    public function getVarietyNationalNameVivc()
    {
        return $this->varietyNationalNameVivc;
    }

    /**
     * @param string $varietyNationalNameVivc
     */
    public function setVarietyNationalNameVivc($varietyNationalNameVivc)
    {
        $this->varietyNationalNameVivc = $varietyNationalNameVivc;
    }

    /**
     * @return string
     */
    public function getSynonym()
    {
        return $this->synonym;
    }

    /**
     * @param string $synonym
     */
    public function setSynonym($synonym)
    {
        $this->synonym = $synonym;
    }

    /**
     * @return string
     */
    public function getNationalVarietyId()
    {
        return $this->nationalVarietyId;
    }

    /**
     * @param string $nationalVarietyId
     */
    public function setNationalVarietyId($nationalVarietyId)
    {
        $this->nationalVarietyId = $nationalVarietyId;
    }

    /**
     * @return string
     */
    public function getGrapeColor()
    {
        return $this->grapeColor;
    }

    /**
     * @param string $grapeColor
     */
    public function setGrapeColor($grapeColor)
    {
        $this->grapeColor = $grapeColor;
    }

    /**
     * @return \DateTime
     */
    public function getLastDate()
    {
        return $this->lastDate;
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
    public function getInternetAdress()
    {
        return $this->internetAdress;
    }

    /**
     * @param string $internetAdress
     */
    public function setInternetAdress($internetAdress)
    {
        $this->internetAdress = $internetAdress;
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
            'isMainVariety' => 'Variéte Principale',
            'areaCultivated' => 'Surface',
            'areaYear' => 'Année surface cultivée',
            'grapeVarietyName' => 'Nom variété',
            'codeVivc' => 'VIVC',
            'infoCodeVivc' => 'Vitis International Variety Catalogue',
            'varietyNationalNameVivc' => 'Code OIV',
            'synonym' => 'Synonyme',
            'nationalVarietyId' => 'Id variété nationale',
            'grapeColor' => 'Couleur',
            'internetAdress' => 'adresse internet',
        ];
    }
}

