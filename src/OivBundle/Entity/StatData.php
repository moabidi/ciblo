<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $versioning = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_CODE", type="string", length=255, nullable=false)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="STAT_TYPE", type="string", length=255, nullable=false)
     */
    private $statType;

    /**
     * @var string
     *
     * @ORM\Column(name="MEASURE_TYPE", type="string", length=255, nullable=false)
     */
    private $measureType;

    /**
     * @var string
     *
     * @ORM\Column(name="METRIC_COMP_TYPE", type="string", length=255, nullable=false)
     */
    private $metricCompType = 'UNIQUE';

    /**
     * @var integer
     *
     * @ORM\Column(name="YEAR", type="integer", nullable=false)
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
            'measureType' => 'Unité',
            'metricCompType' => 'Produit',
            'year' => 'Année',
            'value' => 'Valeur',
            'infoSource' => 'Source info',
            'grapesDestination' => 'Vocation du raisin',
        ];
    }
}

