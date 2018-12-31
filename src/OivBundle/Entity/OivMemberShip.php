<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OivMemberShip
 *
 * @ORM\Table(name="OIV_MEMBER_SHIP")
 * @ORM\Entity(repositoryClass="OivBundle\Repository\OivMemberShipRepository")
 */
class OivMemberShip
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
     * @ORM\Column(name="ISO3", type="string", length=3, nullable=false)
     */
    private $iso3;

    /**
     * @var string
     *
     * @ORM\Column(name="YEAR", type="string", length=10, nullable=false)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="OIV_MEMBERSHIP", type="string", length=1, nullable=false)
     */
    private $oivMembership;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LAST_DATE", type="datetime", nullable=false)
     */
    private $lastDate;


}

