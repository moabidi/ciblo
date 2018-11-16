<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categories
 *
 * @ORM\Table(name="categories", uniqueConstraints={@ORM\UniqueConstraint(name="NOM", columns={"NOM"})})
 * @ORM\Entity
 * @
 */
class Categories
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NOM", type="string", length=30, nullable=false)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="VERSIONING", type="bigint", nullable=true)
     */
    private $versioning = '1';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
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


}

