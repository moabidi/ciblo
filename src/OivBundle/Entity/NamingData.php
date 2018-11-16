<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NamingData
 *
 * @ORM\Table(name="naming_data")
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


}

