<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Programmer;

class BattleExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('powerLevelClass', array($this, 'getPowerLevelClass')),
            new \Twig_SimpleFilter('avatar_path', array($this, 'getAvatarPath')),
        );
    }

    public function getAvatarPath($number)
    {
        return sprintf('img/avatar%s.png', $number);
    }

    public function getPowerLevelClass(Programmer $programmer)
    {
        $powerLevel = $programmer->getPowerLevel();
        switch (true) {
            case ($powerLevel <= 3):
                return 'danger';
                break;
            case ($powerLevel <= 7):
                return 'warning';
                break;
            default:
                return 'success';
        }
    }

    public function getName()
    {
        return 'code_battle';
    }


}