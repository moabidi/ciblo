<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank()
     */
    private $versioning = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="COUNTRY_CODE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="FORMATION_TITLE", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $formationTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="UNIVERSITY", type="string", length=1000, nullable=false)
     */
    private $university ='';

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
    private $diploma ='';

    /**
     * @var string
     *
     * @ORM\Column(name="COOPERATION", type="string", length=2000, nullable=false)
     */
    private $cooperation ='';

    /**
     * @var string
     *
     * @ORM\Column(name="MONTH", type="string", length=1000, nullable=false)
     */
    private $month ='';

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
     *
     */
    private $credits;

    /**
     * @var string
     *
     * @ORM\Column(name="PRIOR", type="string", length=1000, nullable=false)
     */
    private $prior='';

    /**
     * @var string
     *
     * @ORM\Column(name="DEADLINE", type="string", length=255, nullable=false)
     */
    private $deadline='';

    /**
     * @var string
     *
     * @ORM\Column(name="CONTACT", type="string", length=255, nullable=false)
     */
    private $contact='';

    /**
     * @var string
     *
     * @ORM\Column(name="CITY", type="string", length=255, nullable=false)
     */
    private $city='';

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
     *
     */
    private $lastDate;

    /**
     * @var string
     *
     * @ORM\Column(name="INTERNET_ADRESS", type="string", length=4000, nullable=true)
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
    private $lastData='1';

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
    public function getFormationTitle()
    {
        return $this->formationTitle;
    }

    /**
     * @param string $formationTitle
     */
    public function setFormationTitle($formationTitle)
    {
        $this->formationTitle = $formationTitle;
    }

    /**
     * @return string
     */
    public function getUniversity()
    {
        return $this->university;
    }

    /**
     * @param string $university
     */
    public function setUniversity($university)
    {
        $this->university = $university;
    }

    /**
     * @return string
     */
    public function getTutelle()
    {
        return $this->tutelle;
    }

    /**
     * @param string $tutelle
     */
    public function setTutelle($tutelle)
    {
        $this->tutelle = $tutelle;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getDiploma()
    {
        return $this->diploma;
    }

    /**
     * @param string $diploma
     */
    public function setDiploma($diploma)
    {
        $this->diploma = $diploma;
    }

    /**
     * @return string
     */
    public function getCooperation()
    {
        return $this->cooperation;
    }

    /**
     * @param string $cooperation
     */
    public function setCooperation($cooperation)
    {
        $this->cooperation = $cooperation;
    }

    /**
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param string $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return string
     */
    public function getHourCourses()
    {
        return $this->hourCourses;
    }

    /**
     * @param string $hourCourses
     */
    public function setHourCourses($hourCourses)
    {
        $this->hourCourses = $hourCourses;
    }

    /**
     * @return string
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * @param string $credits
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;
    }

    /**
     * @return string
     */
    public function getPrior()
    {
        return $this->prior;
    }

    /**
     * @param string $prior
     */
    public function setPrior($prior)
    {
        $this->prior = $prior;
    }

    /**
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * @param string $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param string $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * @param string $adress
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;
    }

    /**
     * @return \DateTime
     */
    public function getLastDate()
    {
        if ($this->lastDate) {
            return $this->lastDate->format('Y-m-d H:i:s');
        }
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
     * @param $aHeader
     * @return array|null
     */
    public static function getImportFieldsIdentifier()
    {
        return [
            'countryCode' => 'Pays',
            'city' => 'Ville',
            'formationTitle' => 'Intitulé de la formation',
            'university' => 'Établissement',
             'tutelle' => 'Tutelle',
            'level' => 'Niveau et catégorie',
            'diploma' => 'Diplôme(s) octroyé(s)',
             'cooperation' => 'Coopérations universitaires éventuelles',
             'month' => 'Durée de formation',
             'hourCourses' => 'Nombre d\'heures de formation',
             'credits' => 'Crédits ECTS',
             'prior' => 'Pré-requis',
             'deadline' => 'Date d\'inscription',
             'contact' => 'Contact',
             'adress' => 'Adresse',
             'internetAdress' => 'Site internet',
//             'versioning' => 'Version',
//             'lastDate' => 'dernière date de mise à jour',
//             'usableData' => 'Utilisé',
            ];
    }
    
    /**
     * @return array
     */
    public static function getConfigFields() {
        return [
            'id'=>['tab3'],
            'countryNameFr' => ['tab1','tab2','tab3','export','exportBo','importBo'],
            'countryCode' => ['form','required','editable'],
            'city' => ['form','tab2','tab3','export','exportBo','editable','importBo'],
            'formationTitle' => ['form','filter','tab1','tab2','tab3','export','exportBo','required','editable','importBo'],
            'university' => ['form','filter','tab1','tab2','tab3','export','exportBo','editable','importBo'],
            'tutelle' => ['form','tab2','tab3','export','exportBo','editable','importBo'],
            'level' => ['form','filter','tab1','tab2','tab3','export','exportBo','editable','importBo'],
            'diploma' => ['form','tab1','tab2','tab3','export','exportBo','editable','importBo'],
            'cooperation' => ['form','tab2','tab3','export','exportBo','editable','importBo'],
            'month'=> ['form','tab2','tab3','export','exportBo','editable','importBo'],
            'hourCourses'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'credits'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'prior'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'deadline'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'contact'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'adress'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'internetAdress'=>['form','tab2','tab3','export','exportBo','editable','importBo'],
            'versioning' => ['form','required'],
            'usableData' => ['form','editable'],
            'lastDate'=>['form','tab2','tab3','export','exportBo'],
            'lastData' => [],
        ];
    }
}

