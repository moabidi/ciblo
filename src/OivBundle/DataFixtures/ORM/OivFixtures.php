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
use OivBundle\Entity\Roles;
use OivBundle\Entity\Users;

class OivFixtures extends Fixture
{


    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        //$this->loadRoles($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadUsers(ObjectManager $manager)
    {
        $encoder = $this->container->get('security.password_encoder');
        for ($i = 0; $i < 4; $i++) {
            $user = new Users();
            $user->setName('admin '.$i);
            $user->setUsername('admin'.$i);
            switch($i) {
                case 0: $user->setRole($manager->getRepository('OivBundle:Roles')->findOneBy(['name'=>'manager_stat']));
                case 1: $user->setRole($manager->getRepository('OivBundle:Roles')->findOneBy(['name'=>'manager_education']));
                case 2: $user->setRole($manager->getRepository('OivBundle:Roles')->findOneBy(['name'=>'manager_variety']));
                case 3: $user->setRole($manager->getRepository('OivBundle:Roles')->findOneBy(['name'=>'manager_naming']));
            }
            $encoded = $encoder->encodePassword($user, 'pass'.$i);
            $user->setPassword($encoded);
            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadRoles(ObjectManager $manager)
    {
        $role = new Roles();
        $role->setVersioning(1);
        $role->setName('role_admin');
        $manager->persist($role);
        $role1 = clone $role;
        $role1->setName('manager_stat');
        $manager->persist($role1);
        $role2 = clone $role;
        $role2->setName('manager_education');
        $manager->persist($role2);
        $role3 = clone $role;
        $role3->setName('manager_variety');
        $manager->persist($role3);
        $role4 = clone $role;
        $role4->setName('manager_naming');
        $manager->persist($role4);
        $manager->flush();
    }
}