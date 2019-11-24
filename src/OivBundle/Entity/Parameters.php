<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 09/07/19
 * Time: 23:18
 */

namespace OivBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Parameters
 *
 * @ORM\Table(name="PARAMETERS", uniqueConstraints={@ORM\UniqueConstraint(name="NAME", columns={"NAME"})})
 * @ORM\Entity(repositoryClass="OivBundle\Repository\ParametersRepository")
 */
class Parameters
{

    const LAST_STAT_YEAR = 'LAST_STAT_YEAR';
    
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="NAME", type="string", length=100, nullable=false, unique=true)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="VALUE", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


}