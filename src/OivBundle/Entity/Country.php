<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="COUNTRY")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\CountryRepository")
 */
class Country
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
     * @ORM\Column(name="COUNTRY_NAME_FR", type="string", length=255, nullable=false)
     */
    private $countryNameFr;

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_NAME_EN", type="string", length=255, nullable=false)
     */
    private $countryNameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_NAME_IT", type="string", length=255, nullable=false)
     */
    private $countryNameIt;

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_NAME_ES", type="string", length=255, nullable=false)
     */
    private $countryNameEs;

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_NAME_DE", type="string", length=255, nullable=false)
     */
    private $countryNameDe;

    /**
     * @var string
     *
     * @ORM\Column(name="ISO2", type="string", length=3, nullable=false)
     */
    private $iso2;

    /**
     * @var string
     *
     * @ORM\Column(name="ISO3", type="string", length=3, nullable=false)
     */
    private $iso3;

    /**
     * @var string
     *
     * @ORM\Column(name="ALPHA", type="string", length=3, nullable=false)
     */
    private $alpha;

    /**
     * @var string
     *
     * @ORM\Column(name="TRADE_BLOC", type="string", length=60, nullable=false)
     */
    private $tradeBloc;

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
    public function getCountryNameFr()
    {
        return $this->countryNameFr;
    }

    /**
     * @param string $countryNameFr
     */
    public function setCountryNameFr($countryNameFr)
    {
        $this->countryNameFr = $countryNameFr;
    }

    /**
     * @return string
     */
    public function getCountryNameEn()
    {
        return $this->countryNameEn;
    }

    /**
     * @param string $countryNameEn
     */
    public function setCountryNameEn($countryNameEn)
    {
        $this->countryNameEn = $countryNameEn;
    }

    /**
     * @return string
     */
    public function getCountryNameIt()
    {
        return $this->countryNameIt;
    }

    /**
     * @param string $countryNameIt
     */
    public function setCountryNameIt($countryNameIt)
    {
        $this->countryNameIt = $countryNameIt;
    }

    /**
     * @return string
     */
    public function getCountryNameEs()
    {
        return $this->countryNameEs;
    }

    /**
     * @param string $countryNameEs
     */
    public function setCountryNameEs($countryNameEs)
    {
        $this->countryNameEs = $countryNameEs;
    }

    /**
     * @return string
     */
    public function getCountryNameDe()
    {
        return $this->countryNameDe;
    }

    /**
     * @param string $countryNameDe
     */
    public function setCountryNameDe($countryNameDe)
    {
        $this->countryNameDe = $countryNameDe;
    }

    /**
     * @return string
     */
    public function getIso2()
    {
        return $this->iso2;
    }

    /**
     * @param string $iso2
     */
    public function setIso2($iso2)
    {
        $this->iso2 = $iso2;
    }

    /**
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * @param string $iso3
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;
    }

    /**
     * @return string
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * @param string $alpha
     */
    public function setAlpha($alpha)
    {
        $this->alpha = $alpha;
    }

    /**
     * @return string
     */
    public function getTradeBloc()
    {
        return $this->tradeBloc;
    }

    /**
     * @param string $tradeBloc
     */
    public function setTradeBloc($tradeBloc)
    {
        $this->tradeBloc = $tradeBloc;
    }


}

