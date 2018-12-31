<?php
/**
 * Created by PhpStorm.
 * User: abidi
 * Date: 29/12/18
 * Time: 15:04
 */

namespace OivBundle\Repository;


use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UsersRepository extends BaseRepository implements UserLoaderInterface
{

    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            //->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}