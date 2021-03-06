<?php

namespace Tech\TBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Tech\TBundle\Entity\Tbgentiposolicitud;
use Tech\TBundle\Form\TbgentiposolicitudType;

/**
 * Tbgentiposolicitud controller.
 *
 */
class TbgentiposolicitudController extends Controller
{

    /**
     * Lists all Tbgentiposolicitud entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TechTBundle:Tbgentiposolicitud')->findAll();

        return $this->render('TechTBundle:Tbgentiposolicitud:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Tbgentiposolicitud entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Tbgentiposolicitud();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('Servicios_show', array('id' => $entity->getId())));
        }

        return $this->render('TechTBundle:Tbgentiposolicitud:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Tbgentiposolicitud entity.
    *
    * @param Tbgentiposolicitud $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Tbgentiposolicitud $entity)
    {
        $form = $this->createForm(new TbgentiposolicitudType(), $entity, array(
            'action' => $this->generateUrl('Servicios_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Tbgentiposolicitud entity.
     *
     */
    public function newAction()
    {
        $entity = new Tbgentiposolicitud();
        $form   = $this->createCreateForm($entity);

        return $this->render('TechTBundle:Tbgentiposolicitud:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Tbgentiposolicitud entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TechTBundle:Tbgentiposolicitud')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tbgentiposolicitud entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TechTBundle:Tbgentiposolicitud:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Tbgentiposolicitud entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TechTBundle:Tbgentiposolicitud')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tbgentiposolicitud entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TechTBundle:Tbgentiposolicitud:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Tbgentiposolicitud entity.
    *
    * @param Tbgentiposolicitud $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Tbgentiposolicitud $entity)
    {
        $form = $this->createForm(new TbgentiposolicitudType(), $entity, array(
            'action' => $this->generateUrl('Servicios_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Tbgentiposolicitud entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TechTBundle:Tbgentiposolicitud')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tbgentiposolicitud entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('Servicios_edit', array('id' => $id)));
        }

        return $this->render('TechTBundle:Tbgentiposolicitud:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Tbgentiposolicitud entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TechTBundle:Tbgentiposolicitud')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tbgentiposolicitud entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('Servicios'));
    }

    /**
     * Creates a form to delete a Tbgentiposolicitud entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('Servicios_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
