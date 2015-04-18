<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;

class ProgrammerRepository extends EntityRepository
{
    public function findAllForUser(User $user)
    {
        return $this->find(array('user' => $user));
    }

    public function findOneByNickname($nickname)
    {
        return $this->findOneBy(array('nickname' => $nickname));
    }
}
