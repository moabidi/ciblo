<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EducationData
 *
 * @ORM\Table(name="EDUCATION_DATA")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\EducationDataRepository")
 */
class EducationData
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
     * @ORM\Column(name="FORMATION_TITLE", type="string", length=255, nullable=false)
     */
    private $formationTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="UNIVERSITY", type="string", length=1000, nullable=false)
     */
    private $university;

    /**
     * @var string
     *
     * @ORM\Column(name="TUTELLE", type="string", length=1000, nullable=true)
     */
    private $tutelle;

    /**
     * @var string
     *
     * @ORM\Column(name="LEVEL", type="string", length=1000, nullable=true)
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="DIPLOMA", type="string", length=1000, nullable=false)
     */
    private $diploma;

    /**
     * @var string
     *
     * @ORM\Column(name="COOPERATION", type="string", length=2000, nullable=false)
     */
    private $cooperation;

    /**
     * @var string
     *
     * @ORM\Column(name="MONTH", type="string", length=1000, nullable=false)
     */
    private $month;

    /**
     * @var string
     *
     * @ORM\Column(name="HOUR_COURSES", type="string", length=255, nullable=true)
     */
    private $hourCourses;

    /**
     * @var string
     *
     * @ORM\Column(name="CREDITS", type="string", length=255, nullable=true)
     */
    private $credits;

    /**
     * @var string
     *
     * @ORM\Column(name="PRIOR", type="string", length=1000, nullable=false)
     */
    private $prior;

    /**
     * @var string
     *
     * @ORM\Column(name="DEADLINE", type="string", length=255, nullable=false)
     */
    private $deadline;

    /**
     * @var string
     *
     * @ORM\Column(name="CONTACT", type="string", length=255, nullable=false)
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="ADRESS", type="string", length=4000, nullable=true)
     */
    private $adress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LAST_DATE", type="datetime", nullable=false)
     */
    private $lastDate;

    /**
     * @var string
     *
     * @ORM\Column(name="INTERNET_ADRESS", type="string", length=4000, nullable=true)
     */
    private $internetAdress;


}

