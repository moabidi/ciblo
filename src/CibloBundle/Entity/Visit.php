<?php

namespace CibloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Visit
 *
 * @ORM\Table(name="visit", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})})
 * @ORM\Entity(repositoryClass="CibloBundle\Repository\VisitRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Visit
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="navigateur", type="string", length=100, nullable=false)
     */
    private $navigateur;

    /**
     * @var string
     *
     * @ORM\Column(name="os", type="string", length=100, nullable=false)
     */
    private $os;

    /**
     * @var string
     *
     * @ORM\Column(name="resolution", type="string", length=20, nullable=false)
     */
    private $resolution;

    /**
     * @var string
     *
     * @ORM\Column(name="prepherique", type="string", length=100, nullable=false)
     */
    private $prepherique;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_vist", type="integer", nullable=false)
     */
    private $nbVist;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Visit
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set navigateur
     *
     * @param string $navigateur
     *
     * @return Visit
     */
    public function setNavigateur($navigateur)
    {
        $this->navigateur = $navigateur;

        return $this;
    }

    /**
     * Get navigateur
     *
     * @return string
     */
    public function getNavigateur()
    {
        return $this->navigateur;
    }

    /**
     * Set os
     *
     * @param string $os
     *
     * @return Visit
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Get os
     *
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set resolution
     *
     * @param string $resolution
     *
     * @return Visit
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get resolution
     *
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * Set prepherique
     *
     * @param string $prepherique
     *
     * @return Visit
     */
    public function setPrepherique($prepherique)
    {
        $this->prepherique = $prepherique;

        return $this;
    }

    /**
     * Get prepherique
     *
     * @return string
     */
    public function getPrepherique()
    {
        return $this->prepherique;
    }

    /**
     * Set nbVist
     *
     * @param integer $nbVist
     *
     * @return Visit
     */
    public function setNbVist($nbVist)
    {
        $this->nbVist = $nbVist;

        return $this;
    }

    /**
     * Get nbVist
     *
     * @return integer
     */
    public function getNbVist()
    {
        return $this->nbVist;
    }
}
