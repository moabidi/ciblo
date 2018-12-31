<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="STAT_DATA_PARAMETER")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\StatDataParameterRepository")
 */
class StatDataParameter
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
     * @var string
     *
     * @ORM\Column(name="PRIORITY", type="integer")
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="PRODUCT_PRIORITY", type="integer")
     */
    private $productPriority;

    /**
     * @var string
     *
     * @ORM\Column(name="STAT_TYPE", type="string", length=255, nullable=false)
     */
    private $statType;

    /**
     * @var string
     *
     * @ORM\Column(name="PRODUCT", type="string", length=255, nullable=false)
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="INDICATOR", type="string", length=255, nullable=false)
     */
    private $indicator;

    /**
     * @var string
     *
     * @ORM\Column(name="PRINTABLE_COUNTRY_VIEW", type="string", length=1, nullable=false)
     */
    private $printableCountryView;

    /**
     * @var string
     *
     * @ORM\Column(name="PRINTABLE_DATA_PUBLIC", type="string", length=1, nullable=false)
     */
    private $printableDataPublic;

    /**
     * @var string
     *
     * @ORM\Column(name="PRINTABLE_DATA_BACKOFFICE", type="string", length=1, nullable=false)
     */
    private $printableDataBackoffice;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getProductPriority()
    {
        return $this->productPriority;
    }

    /**
     * @param string $productPriority
     */
    public function setProductPriority($productPriority)
    {
        $this->productPriority = $productPriority;
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * @param string $indicator
     */
    public function setIndicator($indicator)
    {
        $this->indicator = $indicator;
    }

    /**
     * @return string
     */
    public function getPrintableCountryView()
    {
        return $this->printableCountryView;
    }

    /**
     * @param string $printableCountryView
     */
    public function setPrintableCountryView($printableCountryView)
    {
        $this->printableCountryView = $printableCountryView;
    }

    /**
     * @return string
     */
    public function getPrintableDataPublic()
    {
        return $this->printableDataPublic;
    }

    /**
     * @param string $printableDataPublic
     */
    public function setPrintableDataPublic($printableDataPublic)
    {
        $this->printableDataPublic = $printableDataPublic;
    }

    /**
     * @return string
     */
    public function getPrintableDataBackoffice()
    {
        return $this->printableDataBackoffice;
    }

    /**
     * @param string $printableDataBackoffice
     */
    public function setPrintableDataBackoffice($printableDataBackoffice)
    {
        $this->printableDataBackoffice = $printableDataBackoffice;
    }

}

