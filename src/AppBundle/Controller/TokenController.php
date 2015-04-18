<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ApiToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class TokenController extends BaseController
{
    /**
     * Displays all of the user's tokens
     *
     * @Route("/tokens", name="user_tokens")
     * @Method("GET")
     */
    public function indexAction()
    {
        $tokens = $this->getApiTokenRepository()->findAllForUser($this->getUser());

        return $this->render('tokens/index.twig', array(
            'tokens' => $tokens,
        ));
    }

    /**
     * @Route("/tokens/new", name="user_tokens_new")
     * @Route("/tokens/new", name="user_tokens_new_process")
     */
    public function newAction(Request $request)
    {
        $token = new ApiToken($this->getUser());
        $errors = array();
        if ($request->isMethod('POST')) {
            $token->setNotes($request->request->get('notes'));

            $errors = $this->validate($token);
            if (empty($errors)) {
                $this->getApiTokenRepository()->save($token);

                $this->addFlash('Yeehaw! You just created an API token');
                $url = $this->generateUrl('user_tokens');

                return $this->redirect($url);
            }
        }

        return $this->render('tokens/new.twig', array(
            'errors' => $errors,
            'token' => $token,
        ));
    }

    /**
     * @Route("/tokens/{token}/delete", name="user_tokens_delete")
     * @Method("POST")
     */
    public function deleteAction($token)
    {
        /** @var ApiToken $apiToken */
        $apiToken = $this->getApiTokenRepository()->findOneByToken($token);
        if (!$apiToken) {
            throw $this->createNotFoundException('That token doesn\'t exist!');
        }

        if ($apiToken->getUser() != $this->getUser()) {
            throw new AccessDeniedException('Not your token!');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($apiToken);
        $em->flush();

        $this->addFlash('The token was shown the proverbial "door" (i.e. deleted).');
        $url = $this->generateUrl('user_tokens');

        return $this->redirect($url);
    }
}
