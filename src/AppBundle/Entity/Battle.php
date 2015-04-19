<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="battle_battle")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BattleRepository")
 */
class Battle
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Programmer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $programmer;

    /**
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="boolean")
     */
    private $didProgrammerWin;

    /**
     * @ORM\Column(type="datetime")
     */
    private $foughtAt;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * Battle constructor.
     * @param $programmer
     * @param $project
     */
    public function __construct(Programmer $programmer, Project $project)
    {
        $this->programmer = $programmer;
        $this->project = $project;
        $this->foughtAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setBattleWonByProgrammer($notes)
    {
        $this->didProgrammerWin = true;
        $this->notes = $notes;
    }

    public function setBattleLostByProgrammer($notes)
    {
        $this->didProgrammerWin = false;
        $this->notes = $notes;
    }

    /**
     * @return Programmer
     */
    public function getProgrammer()
    {
        return $this->programmer;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    public function getDidProgrammerWin()
    {
        return $this->didProgrammerWin;
    }

    public function getFoughtAt()
    {
        return $this->foughtAt;
    }

    public function getNotes()
    {
        return $this->notes;
    }
}
