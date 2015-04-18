<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\ApiToken;

class ApiTokenRepository extends EntityRepository
{
    /**
     * @param $token
     * @return ApiToken
     */
    public function findOneByToken($token)
    {
        return $this->findOneBy(array('token' => $token));
    }

    public function findAllForUser(User $user)
    {
        return $this->findBy(array('user' => $user->getId()));
    }
}
