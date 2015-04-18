<?php

namespace AppBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Programmer;
use AppBundle\Entity\Project;
use AppBundle\Entity\Battle;


class BattleController extends BaseController
{
    /**
     * @Route("/battles/new", name="battle_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $programmerId = $request->request->get('programmer_id');
        $projectId = $request->request->get('project_id');
        /** @var Programmer $programmer */
        $programmer = $this->getProgrammerRepository()->find($programmerId);
        /** @var Project $project */
        $project = $this->getProjectRepository()->find($projectId);

        if ($programmer->getUser() != $this->getUser()) {
            throw new AccessDeniedException();
        }

        $battle = $this->getBattleManager()->battle($programmer, $project);

        return $this->redirect($this->generateUrl('battle_show', array('id' => $battle->getId())));
    }

    /**
     * @Route("/battles/{id}", name="battle_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
        /** @var Battle $battle */
        $battle = $this->getBattleRepository()->find($id);

        return $this->render('battle/show.twig', array(
            'battle' => $battle,
            'programmer' => $battle->getProgrammer(),
            'project' => $battle->getProject(),
        ));
    }

    /**
     * @Route("/battles", name="battle_list")
     */
    public function listAction()
    {
        $battles = $this->getBattleRepository()->findAll();

        return $this->render('battle/list.twig', array(
            'battles' => $battles,
        ));
    }
}
