<?php

namespace OivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersRoles
 *
 * @ORM\Table(name="users_roles", indexes={@ORM\Index(name="FK_USERS_ROLES_ROLE_ID", columns={"ROLE_ID"}), @ORM\Index(name="FK_USERS_ROLES_USER_ID", columns={"USER_ID"})})
 * @ORM\Entity
 */
class UsersRoles
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
     * @var integer
     *
     * @ORM\Column(name="VERSIONING", type="bigint", nullable=true)
     */
    private $versioning = '1';

    /**
     * @var \OivBundle\Entity\Roles
     *
     * @ORM\ManyToOne(targetEntity="OivBundle\Entity\Roles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ROLE_ID", referencedColumnName="ID")
     * })
     */
    private $role;

    /**
     * @var \OivBundle\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="OivBundle\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="USER_ID", referencedColumnName="ID")
     * })
     */
    private $user;


}

