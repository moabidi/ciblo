<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Produits
 *
 * @ORM\Table(name="produits", uniqueConstraints={@ORM\UniqueConstraint(name="NOM", columns={"NOM"})}, indexes={@ORM\Index(name="FK_PRODUITS_CATEGORIE_ID", columns={"CATEGORIE_ID"})})
 * @ORM\Entity
 */
class Produits
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
     * @ORM\Column(name="DESCRIPTION", type="string", length=100, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="NOM", type="string", length=30, nullable=false)
     */
    private $nom;

    /**
     * @var float
     *
     * @ORM\Column(name="PRIX", type="float", precision=10, scale=0, nullable=false)
     */
    private $prix;

    /**
     * @var integer
     *
     * @ORM\Column(name="VERSIONING", type="bigint", nullable=true)
     */
    private $versioning = '1';

    /**
     * @var \OivBundle\Entity\Categories
     *
     * @ORM\ManyToOne(targetEntity="OivBundle\Entity\Categories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="CATEGORIE_ID", referencedColumnName="ID")
     * })
     */
    private $categorie;


}

