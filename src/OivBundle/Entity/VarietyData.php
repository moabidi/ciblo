<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VarietyData
 *
 * @ORM\Table(name="variety_data")
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


}

