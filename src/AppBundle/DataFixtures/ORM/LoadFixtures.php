<?php

namespace AppBundle\DataFixtures\ORM;

use Hautelook\AliceBundle\Alice\DataFixtureLoader;
use Nelmio\Alice\Fixtures;

class LoadFixtures extends DataFixtureLoader
{
    protected function getFixtures()
    {
        return  array(
            __DIR__ . '/fixtures.yml',
        );
    }
}