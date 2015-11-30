<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Programmer;
use AppBundle\Form\ProgrammerType;
use AppBundle\Form\UpdateProgrammerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgrammerController extends BaseController
{
    /**
     * @Route("/api/programmers")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $programmer = new Programmer();
        $form = $this->createForm(new ProgrammerType(), $programmer);
        $this->processForm($request, $form);

        $programmer->setUser($this->findUserByUsername('weaverryan'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->getNickname()]
        );
        $response->headers->set('Location', $programmerUrl);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}", name="api_programmers_show")
     * @Method("GET")
     */
    public function showAction($nickname)
    {
        $programmer = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findOneByNickname($nickname);

        if (!$programmer) {
            throw $this->createNotFoundException(sprintf(
                'No programmer found with nickname "%s"',
                $nickname
            ));
        }

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * @Route("/api/programmers")
     * @Method("GET")
     */
    public function listAction()
    {
        $programmers = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findAll();
        $data = array('programmers' => array());
        foreach ($programmers as $programmer) {
            $data['programmers'][] = $this->serializeProgrammer($programmer);
        }

        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}")
     * @Method("PUT")
     */
    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findOneByNickname($nickname);

        if (!$programmer) {
            throw $this->createNotFoundException(sprintf(
                'No programmer found with nickname "%s"',
                $nickname
            ));
        }

        $form = $this->createForm(new UpdateProgrammerType(), $programmer);
        $this->processForm($request, $form);

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $data = $this->serializeProgrammer($programmer);
        $response = new JsonResponse($data, 200);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}")
     * @Method("DELETE")
     */
    public function deleteAction($nickname)
    {
        $programmer = $this->getDoctrine()
            ->getRepository('AppBundle:Programmer')
            ->findOneByNickname($nickname);

        if (!$programmer) {
            throw $this->createNotFoundException(sprintf(
                'No programmer found with nickname "%s"',
                $nickname
            ));
        }

        // todo ...
    }

    private function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
    }

    private function serializeProgrammer(Programmer $programmer)
    {
        return array(
            'nickname' => $programmer->getNickname(),
            'avatarNumber' => $programmer->getAvatarNumber(),
            'powerLevel' => $programmer->getPowerLevel(),
            'tagLine' => $programmer->getTagLine(),
        );
    }
}
