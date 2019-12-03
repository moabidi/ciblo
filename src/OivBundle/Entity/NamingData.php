<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(name="APPELLATION_CODE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $appellationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="APPELLATION_NAME", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
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
     * @ORM\Column(name="PRODUCT_TYPE", type="string", length=255, nullable=true)
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
     * @Assert\Length(max=1)
     */
    private $usableData;

    /**
     * @var bool
     *
     * @ORM\Column(name="LAST_DATA", type="string", length=1, nullable=false)
     */
    private $lastData;

    public function __clone()
    {
        // TODO: Implement __clone() method.
        $this->id = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
        $this->appellationCode = trim(strtoupper($appellationCode));
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
        $this->parentCode = strtoupper($this->setNullIfEmpty($parentCode));
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
        $this->parentName = $this->setNullIfEmpty($parentName);
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
        $this->typeNationalCode = $this->setNullIfEmpty($typeNationalCode);
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
        $this->productCategoryName = $this->setNullIfEmpty($productCategoryName);
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
        $this->productType = $this->setNullIfEmpty($productType);
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
        $this->referenceName = $this->setNullIfEmpty($referenceName);
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $this->setNullIfEmpty($url);
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
     * @param $val
     * @return null|string
     */
    private function setNullIfEmpty($val) {
     return trim($val) != '' ? trim($val):NULL;
    }

    /**
     * @param bool $all
     * @return array
     */
    public static function getImportFieldsIdentifier($all = false)
    {
        $aFields = [
            'countryCode' => 'Pays',
            'appellationName' => 'IG/AO',
            'appellationCode' => 'Code IG/AO',
            'parentName' => 'Unité géographique supérieure',
            'parentCode' => 'Code unité supérieure',
            'typeNationalCode' => 'Type d\'indication national',
            'typeInternationalCode' => 'Type d\'indication international'
        ];
        if ($all) {
            $aFields = array_merge($aFields,[
                'productType' => 'Catégories de produits',
                'productCategoryName' => 'Type de produits',
                'referenceName' => 'Base reglementaire',
                'url' => 'Lien sur base légale'
            ]);
        }
        return $aFields;
    }

    /**
     * @param $aHeader
     * @return array|null
     */
    public static function getImportCustumFieldsIdentifier($isCtg = true)
    {
        if ($isCtg) {
            return [
                'appellationCode' => 'Code IG/AO',
                'appellationName' => 'IG/AO',
                'productType' => 'Catégories de produits',
                'productCategoryName' => 'Type de produit'
            ];
        } else {
            return [
                'appellationCode' => 'Code IG/AO',
                'appellationName' => 'IG/AO',
                'referenceName' => 'Base règlementaire',
                'url' => 'Lien sur base légale'
            ];
        }
    }
    
    /**
     * @return array
     */
    public static function getConfigFields()
    {
        return [
            'id'=>['tab3'],
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo','importBo'],
            'countryCode' => ['form','required'],
            'appellationName' => ['form','filter','tab1','tab2','tab3','export','exportBo','importBo' ,'required','editable','importBoNamingProduct','importBoNamingReference'],
            'appellationCode' => ['form','tab2','tab3','importBo','required','importBoNamingProduct','importBoNamingReference'],
            'parentName' => ['form','filter','tab2','tab3','export','exportBo','importBo','editable'],
            'parentCode' => ['form','filter','tab3','exportBo','importBo'],
            'typeNationalCode' => ['form','filter', 'tab1', 'tab2','tab3','export','exportBo','importBo','required','editable'],
            'typeInternationalCode' => ['form','filter', 'tab1', 'tab2','tab3','export','exportBo','importBo','editable'],
            'productType' => ['filter','tab2','tab3','export','exportBo','editable','importBoNamingProduct'],
            'productCategoryName' => ['filter','export','exportBo','editable','importBoNamingProduct'],
            'referenceName' => ['tab2','tab3','export','exportBo','editable','importBoNamingReference'],
            'url' => ['tab3','export','exportBo','editable','importBoNamingReference'],
            'usableData' => ['editable'],
            'versioning' => ['form','required'],
            'lastDate' => ['form','tab2','tab3'],
            'lastData' => [],
        ];
    }

    /**
     * @param $tag
     * @return array
     */
    public static function getIndexCodeByTag($tag)
    {
        $aHeader = [];
        foreach (NamingData::getConfigFields() as $name => $aTags) {
            if (in_array($tag, $aTags)) {
                $aHeader[$name] = $name;
            }
        }
        $aIndex = [];
        $aHeader = array_values($aHeader);
        $aIndex['indexCountryCode'] = array_search('countryCode', $aHeader);
        $aIndex['indexAppelationCode'] = array_search('appellationCode', $aHeader);
        $aIndex['indexAppelationName'] = array_search('appellationName', $aHeader);
        $aIndex['indexParentCode'] = array_search('parentCode', $aHeader);
        $aIndex['indexParentName'] = array_search('parentName', $aHeader);
        return $aIndex;
    }
}

