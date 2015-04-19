<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Project;

class ProjectRepository extends EntityRepository
{
    /**
     * @param $name
     * @return Project
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }


    /**
     * @param $limit
     * @return Project[]
     */
    public function findRandom($limit)
    {
        $projects = $this->createQueryBuilder('p')
            ->setMaxResults($limit)
            ->getQuery()
            ->execute()
        ;

        shuffle($projects);

        return array_slice($projects, 0, $limit);
    }
}
