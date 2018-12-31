<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 28/12/18
 * Time: 20:06
 */

namespace OivBundle\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use OivBundle\Entity\Users;

class OivFixtures extends Fixture
{


    public function load(ObjectManager $manager)
    {
        $encoder = $this->container->get('security.password_encoder');

        for ($i = 0; $i < 5; $i++) {
            $user = new Users('admin'.$i, 'pass'.$i);
            $user->setName('admin '.$i);
            $user->setUsername('admin'.$i);
            $encoded = $encoder->encodePassword($user, 'pass'.$i);
            $user->setPassword($encoded);
            $manager->persist($user);
        }

        $manager->flush();
    }
}