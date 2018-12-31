<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NamingData
 *
 * @ORM\Table(name="NAMING_DATA")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\NamingDataRepository")
 */
class NamingData
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
     * @ORM\Column(name="APPELLATION_CODE", type="string", length=255, nullable=false)
     */
    private $appellationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="APPELLATION_NAME", type="string", length=255, nullable=false)
     */
    private $appellationName;

    /**
     * @var string
     *
     * @ORM\Column(name="PARENT_CODE", type="string", length=255, nullable=true)
     */
    private $parentCode;

    /**
     * @var string
     *
     * @ORM\Column(name="PARENT_NAME", type="string", length=255, nullable=true)
     */
    private $parentName;

    /**
     * @var string
     *
     * @ORM\Column(name="TYPE_NATIONAL_CODE", type="string", length=255, nullable=false)
     */
    private $typeNationalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="TYPE_INTERNATIONAL_CODE", type="string", length=255, nullable=false)
     */
    private $typeInternationalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="PRODUCT_CATEGORY_NAME", type="string", length=255, nullable=true)
     */
    private $productCategoryName;

    /**
     * @var string
     *
     * @ORM\Column(name="PRODUCT_TYPE", type="string", length=25, nullable=false)
     */
    private $productType;

    /**
     * @var string
     *
     * @ORM\Column(name="REFERENCE_NAME", type="string", length=4000, nullable=true)
     */
    private $referenceName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LAST_DATE", type="datetime", nullable=true)
     */
    private $lastDate;

    /**
     * @var string
     *
     * @ORM\Column(name="URL", type="string", length=4000, nullable=true)
     */
    private $url;

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
    public function getAppellationCode()
    {
        return $this->appellationCode;
    }

    /**
     * @param string $appellationCode
     */
    public function setAppellationCode($appellationCode)
    {
        $this->appellationCode = $appellationCode;
    }

    /**
     * @return string
     */
    public function getAppellationName()
    {
        return $this->appellationName;
    }

    /**
     * @param string $appellationName
     */
    public function setAppellationName($appellationName)
    {
        $this->appellationName = $appellationName;
    }

    /**
     * @return string
     */
    public function getParentCode()
    {
        return $this->parentCode;
    }

    /**
     * @param string $parentCode
     */
    public function setParentCode($parentCode)
    {
        $this->parentCode = $parentCode;
    }

    /**
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * @param string $parentName
     */
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;
    }

    /**
     * @return string
     */
    public function getTypeNationalCode()
    {
        return $this->typeNationalCode;
    }

    /**
     * @param string $typeNationalCode
     */
    public function setTypeNationalCode($typeNationalCode)
    {
        $this->typeNationalCode = $typeNationalCode;
    }

    /**
     * @return string
     */
    public function getTypeInternationalCode()
    {
        return $this->typeInternationalCode;
    }

    /**
     * @param string $typeInternationalCode
     */
    public function setTypeInternationalCode($typeInternationalCode)
    {
        $this->typeInternationalCode = $typeInternationalCode;
    }

    /**
     * @return string
     */
    public function getProductCategoryName()
    {
        return $this->productCategoryName;
    }

    /**
     * @param string $productCategoryName
     */
    public function setProductCategoryName($productCategoryName)
    {
        $this->productCategoryName = $productCategoryName;
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param string $productType
     */
    public function setProductType($productType)
    {
        $this->productType = $productType;
    }

    /**
     * @return string
     */
    public function getReferenceName()
    {
        return $this->referenceName;
    }

    /**
     * @param string $referenceName
     */
    public function setReferenceName($referenceName)
    {
        $this->referenceName = $referenceName;
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
            'appellationCode' => 'Code',
            'appellationName' => 'IG/AO',
            'parentCode' => 'Code IG/AO supérieure',
            'parentName' => 'Unité géographique supérieure',
            'typeNationalCode' => 'Type d\'indication national',
            'typeInternationalCode' => 'Type d\'indication international',
            'productCategoryName' => 'Catégories de produits',
            'productType' => 'Produits',
            'referenceName' => 'Base reglementaire',
            'url' => 'Lien sur base légale',
            'lastDate' => 'dernière date de mise à jour',
        ];
    }
}

