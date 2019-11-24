<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VarietyData
 *
 * @ORM\Table(name="VARIETY_DATA")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\VarietyDataRepository")
 * @ORM\HasLifecycleCallbacks()
 *
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
     * @Assert\NotBlank()
     */
    private $versioning = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_CODE", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="IS_MAIN_VARIETY", type="string", length=1, nullable=false)
     * @Assert\Length(max=1)
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
     * @Assert\Length(max=10)
     */
    private $nationalVarietyId;

    /**
     * @var string
     *
     * @ORM\Column(name="GRAPE_COLOR", type="string", length=25, nullable=true)
     * @Assert\Length(max=25)
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
     * @Assert\Url()
     */
    private $internetAdress;

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
    public function getIsMainVariety()
    {
        return $this->isMainVariety;
    }

    /**
     * @param string $isMainVariety
     */
    public function setIsMainVariety($isMainVariety)
    {
        if ($isMainVariety) {
            $this->isMainVariety = '1';
        }else{
            $this->isMainVariety = '0';
        }
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
     * @ORM\PrePersist
     */
    public function setCustumValues()
    {
        $this->setIsMainVariety($this->isMainVariety);
    }

    /**
     * @return array
     */
    public static function getImportFieldsIdentifier()
    {
        return [
            'countryCode' => 'Pays',
            'isMainVariety' => 'Variéte Principale',
            'areaCultivated' => 'Surface',
            'areaYear' => 'Année surface cultivée',
            'grapeVarietyName' => 'Nom variété',
            'synonym' => 'Synonyme',
            'codeVivc' => 'Code VIVC',
            'varietyNationalNameVivc' => 'Nom national de variété',
            'nationalVarietyId' => 'Code national de variété',
            'grapeColor' => 'Couleur de raisin',
            'internetAdress' => 'Site internet',
//            'versioning' => 'Version',
//            'lastDate' => 'dernière date de mise à jour',
//            'usableData' => 'Utilisé',
        ];
    }
    
    /**
     * @return array
     */
    public static function getConfigFields() {
        return [
            'id'=>['tab3'],
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo','importBo'],
            'countryCode' => ['form','required'],
            'isMainVariety' => ['form','exportBo','importBo'],
            'areaCultivated' => ['form','editable','exportBo','importBo'],
            'areaYear' => ['form','editable','exportBo','importBo'],
            'grapeVarietyName' => ['form','filter','tab1','tab2','tab3','export','exportBo','editable','importBo'],
            'varietyNationalNameVivc'=> ['form','tab1','tab2','tab3','editable','exportBo','importBo'],
            'synonym'=>['form','filter','tab2','tab3','export','exportBo','editable','importBo'],
            'codeVivc' => ['form','tab1','tab2','tab3','export','exportBo','editable','importBo'],
            'nationalVarietyId'=>['form','editable','exportBo','importBo'],
            'grapeColor'=>['form','editable','exportBo','importBo'],
            'internetAdress'=>['form','editable','exportBo','importBo'],
            'versioning' => ['form','required'],
            'usableData' => ['form','editable'],
            'lastDate'=>['form','tab2','tab3'],
            'lastData' => [],
        ];
    }
}

