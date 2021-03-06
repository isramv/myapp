<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\SocialGroup;
use AppBundle\Form\SocialGroupType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * SocialGroup controller.
 *
 * @Route("/socialgroup")
 */
class SocialGroupController extends Controller
{
    /**
     * Lists all SocialGroup entities.
     *
     * @Route("/", name="socialgroup_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $uid = $user->getId();

        $socialGroupsRepository = $em->getRepository('AppBundle:SocialGroup');
        $result = $socialGroupsRepository->getSocialGroupsByUserId($uid);
        $results = $result[0]->getSocialGroups();
        $phpArrayOfSocialGroups = $results->getValues();



        if($request->get('format') === 'json') {
            //Json serializer.
            $encoder = array(new JsonEncoder());
            $normalizer = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizer, $encoder);
            $json = $serializer->serialize($results, 'json');
            $response = new Response();
            $response->headers->set('Content-type','application/json');
            $response->setContent($json);
            return $response;
        };

        $socialGroups = $socialGroupsRepository->findAll();
        return $this->render('socialgroup/index.html.twig', array(
            'socialGroups' => $socialGroups,
            'mySocialGroups' => $phpArrayOfSocialGroups,
        ));
    }

    /**
     * Creates a new SocialGroup entity.
     *
     * @Route("/new", name="socialgroup_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $socialGroup = new SocialGroup();
        $form = $this->createForm('AppBundle\Form\SocialGroupType', $socialGroup);
        $form->handleRequest($request);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $socialGroup->setCreatedBy($user);
        $socialGroup->setCreated(new \DateTime('now'));

        $user_object = $this->getDoctrine()->getRepository('AppBundle:User')->find($user->getId());
        
        $user_object->addSocialGroup($socialGroup);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($socialGroup);
            $em->persist($user_object);
            $em->flush();

            return $this->redirectToRoute('socialgroup_index');
        }

        return $this->render('socialgroup/new.html.twig', array(
            'socialGroup' => $socialGroup,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SocialGroup entity.
     *
     * @Route("/{id}", name="socialgroup_show")
     * @Method("GET")
     */
    public function showAction(SocialGroup $socialGroup)
    {
        $deleteForm = $this->createDeleteForm($socialGroup);

        return $this->render('socialgroup/show.html.twig', array(
            'socialGroup' => $socialGroup,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SocialGroup entity.
     *
     * @Route("/{id}/edit", name="socialgroup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SocialGroup $socialGroup)
    {
        $deleteForm = $this->createDeleteForm($socialGroup);
        $editForm = $this->createForm('AppBundle\Form\SocialGroupType', $socialGroup);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($socialGroup);
            $em->flush();

            return $this->redirectToRoute('socialgroup_edit', array('id' => $socialGroup->getId()));
        }

        return $this->render('socialgroup/edit.html.twig', array(
            'socialGroup' => $socialGroup,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SocialGroup entity.
     *
     * @Route("/{id}", name="socialgroup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SocialGroup $socialGroup)
    {
        $form = $this->createDeleteForm($socialGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($socialGroup);
            $em->flush();
        }

        return $this->redirectToRoute('socialgroup_index');
    }

    /**
     * Creates a form to delete a SocialGroup entity.
     *
     * @param SocialGroup $socialGroup The SocialGroup entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(SocialGroup $socialGroup)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('socialgroup_delete', array('id' => $socialGroup->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
