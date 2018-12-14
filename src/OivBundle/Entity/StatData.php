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


}

