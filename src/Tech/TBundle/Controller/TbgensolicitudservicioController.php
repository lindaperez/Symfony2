<?php

namespace Tech\TBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tech\TBundle\Entity\Tbgensolicitudservicio;
use Tech\TBundle\Form\TbgensolicitudservicioType;
use Tech\TBundle\Entity\Tbdetdetalleusuario;
use Tech\TBundle\Controller\TbdetusuariodatosController;
use Tech\TBundle\Entity\Utilities;
use SimpleXMLElement;
use DateTime;

define("TARGETURLINS", "https://crm.zoho.com/crm/private/xml/Cases/insertRecords");
define("TARGETURLUPD", "https://crm.zoho.com/crm/private/xml/Cases/updateRecords");
define("TARGETURSLGETALL", "https://crm.zoho.com/crm/private/xml/Cases/getRecords");
define("TARGETURSLGETBYID", "https://crm.zoho.com/crm/private/xml/Cases/getRecordById");

//https://crm.zoho.com/crm/private/xml/Cases/
//getRecordById?authtoken=e5ad26c35e964eb149030ae6cfe00363
//&scope=crmapi&id=783489000002678039


/* user related parameter */
define("AUTHTOKEN", "e5ad26c35e964eb149030ae6cfe00363");

define("SCOPE", "crmapi");

/**
 * Tbgensolicitudservicio controller.
 *
 */
class TbgensolicitudservicioController extends Controller {

    public function searchuserAction() {
        //Verificacion del empleado

        $request = $this->getRequest();
        $verif = new TbdetusuariodatosController();
        $verif1 = $verif->verifaccesouserAction($request);
        if ($verif1 == false) {
            return $this->render('TechTBundle:Default:erroracceso.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entity_search = new Tbgensolicitudservicio();

        //Buscar usuario logueado
        $ci = $request->getSession()->get('usuario_ci');
        $usu = $em->getRepository('TechTBundle:Tbdetusuariodatos')
                ->findOneBy(array('pkIci' => $ci));
        $entity_search->setFkIidUsuaDatos($usu);
        $searchForm = $this->createSearchForm($entity_search);
        $searchForm->handleRequest($request);
        //Valores introducidos
        $fecha = $searchForm['dfechaCreacion']->getData();
        $fecha_cierre = $searchForm['dfechaCierre']->getData();

        $estatus = $searchForm['vdescEstatus']->getData();
        $tipo_solicitud = $searchForm['tbgentiposolicitud']->getData();

        $contrato = $searchForm['fkIidContrato']->getData();
        $nroCaso = $searchForm['iidCaso']->getData();


        $qb = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->createQueryBuilder('ss');
        $qb->andwhere('ss.fkIidUsuaDatos=?6')->setParameter(6, $usu);

        if ($tipo_solicitud != null || $fecha != null || $estatus != null ||
                $contrato != null || $fecha_cierre != null || $nroCaso != null) {

            if ($tipo_solicitud != null) {
                $qb->from('TechTBundle:Tbgenespecsolicitud', 'esp')
                        ->join('ss.fkIidEspSol', 'fkIidEspSol');
                $qb->andwhere('fkIidEspSol.fkIidEspSol=?5')->setParameter(5, $tipo_solicitud);
            }
            if ($nroCaso != null) {
                $qb->andwhere('ss.iidCaso LIKE ?8')->setParameter(8, '%' . $nroCaso);
            }

            if ($fecha != null) {
                $qb->andwhere('ss.dfechaCreacion LIKE ?2')->setParameter(2, $fecha->format('Y-m-d') . '%');
            }
            if ($fecha_cierre != null) {
                $qb->andwhere('ss.dfechaCierre LIKE ?7')->setParameter(7, $fecha_cierre->format('Y-m-d') . '%');
            }
            if ($contrato != null) {
                $qb->from('TechTBundle:Tbdetcontratorif', 'cr')
                        ->join('ss.fkIidContrato', 'fk1')
                        ->innerjoin('fk1.fkIci', 'fk2', 'WITH', 'fk2=:per')
                        ->setParameter('per', $usu)
                        ->join('fk1.fkInroContrato', 'fk3')
                        ->andwhere('fk3.pkInroContrato LIKE ?1')
                        ->setParameter(1, '%' . $contrato);
                //$searchForm['fkIidContrato']=null;
            }

            if ($estatus != null and $estatus != 'Seleccionar') {
                $qb->andwhere('ss.vdescEstatus=?4')->setParameter(4, $estatus);
            }
        }
        $qb->orderBy('ss.vdescEstatus', 'ASC');
        $qb->addorderBy('ss.dfechaCreacion', 'ASC');
        $query_pages = $qb->getQuery();
        $entities = $query_pages->execute();

        //Se Crea la Paginacion
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1)/* page number */, 7/* limit per page */
        );

        return $this->render('TechTBundle:Tbgensolicitudservicio:indexuser.html.twig', array(
                    'entities' => $entities,
                    'entity' => $entity_search,
                    'search_form' => $searchForm->createView(),
                    'pagination' => $pagination,
        ));
    }

    /**
     * Lists all Tbgensolicitudservicio entities.
     *
     */
    public function indexuserAction() {
        //Verificacion del empleado

        $request = $this->getRequest();
        $verif = new TbdetusuariodatosController();
        $verif1 = $verif->verifaccesouserAction($request);
        if ($verif1 == false) {
            return $this->render('TechTBundle:Default:erroracceso.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->findAll();
        $entity_search = new Tbgensolicitudservicio();

        //Buscar usuario logueado
        $ci = $request->getSession()->get('usuario_ci');
        $usu = $em->getRepository('TechTBundle:Tbdetusuariodatos')
                ->findOneBy(array('pkIci' => $ci));

        $entity_search->setFkIidUsuaDatos($usu);
        $searchForm = $this->createSearchForm($entity_search);

        $qb = $em->getRepository('TechTBundle:tbgensolicitudservicio')->createQueryBuilder('ss');
        $qb->andwhere('ss.fkIidUsuaDatos=?6')->setParameter(6, $usu);
        $qb->orderBy('ss.vdescEstatus', 'ASC');
        $qb->addorderBy('ss.dfechaCreacion', 'DESC');
        $query_pages = $qb->getQuery();
        $entities = $query_pages->execute();
        //Se Crea la Paginacion
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1)/* page number */, 7/* limit per page */
        );
        //Actualizacion de los campos fecha cierre, estatus
        //Creacion de Campos en CRM
        /* create a object */
        $utilObj = new Utilities();
        /* set parameters */



        foreach ($entities as &$entity) {
            $parameter = '';
            $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", '1', $parameter);
            $parameter = $utilObj->setParameter("id", ($entity->getIidCaso() - 1), $parameter);
            /* Call API */
            //print $parameter;
            //print '-----/n';
            $responseINS = $utilObj->sendCurlRequest(TARGETURSLGETBYID, $parameter);
            $stringResp = <<<XML
$responseINS
XML;
            $resp = simplexml_load_string($stringResp);
            //print_r((string)$resp->result->Cases->row[0]->FL[4]);
            /* FIN CRM        * */
            $estatus = (string) $resp->result->Cases->row[0]->FL[4];
            $entity->setVdescEstatus($estatus);
            $fecha = DateTime::createFromFormat('Y-m-d H:i:s', (string) $resp->result->Cases->row[0]->FL[17]);
            $entity->setDfechaCierre($fecha);
            $em->persist($entity);
            $em->flush();
            $parameter = "";
        }

        return $this->render('TechTBundle:Tbgensolicitudservicio:indexuser.html.twig', array(
                    'entities' => $entities,
                    'entity' => $entity_search,
                    'search_form' => $searchForm->createView(),
                    'pagination' => $pagination,
        ));
    }

    public function searchAction() {
        //Verificacion del empleado

        $request = $this->getRequest();
        $verif = new TbdetusuariodatosController();
        $verif1 = $verif->verifaccesoemplAction($request);
        $verif2 = $verif->verifaccesoAdminAction($request);

        if ($verif1 == false && $verif2 == false) {
            return $this->render('TechTBundle:Default:erroracceso.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entity_search = new Tbgensolicitudservicio();
        $searchForm = $this->createSearchForm($entity_search);
        $searchForm->handleRequest($request);
        //Valores introducidos
        $fecha = $searchForm['dfechaCreacion']->getData();
        $fecha_cierre = $searchForm['dfechaCierre']->getData();
        $nroCaso = $searchForm['iidCaso']->getData();
        //toma ultimos 4 numeros del arreglo 
        $estatus = $searchForm['vdescEstatus']->getData();
        $contrato = $searchForm['fkIidContrato']->getData();
        $tipo_solicitud = $searchForm['tbgentiposolicitud']->getData();

        $qb = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->createQueryBuilder('ss');

        if ($tipo_solicitud != null || $contrato != null ||
                $fecha != null || $estatus != null ||
                $fecha_cierre != null || $nroCaso != null) {

            if ($tipo_solicitud != null) {
                $qb->from('TechTBundle:Tbgenespecsolicitud', 'esp')
                        ->join('ss.fkIidEspSol', 'fkIidEspSol');
                $qb->andwhere('fkIidEspSol.fkIidEspSol=?5')->setParameter(5, $tipo_solicitud);
            }

            if ($fecha != null) {

                $qb->andwhere('ss.dfechaCreacion LIKE ?2')->setParameter(2, $fecha->format('Y-m-d') . '%');
            }
            if ($fecha_cierre != null) {
                $qb->andwhere('ss.dfechaCierre LIKE ?6')->setParameter(6, $fecha_cierre->format('Y-m-d') . '%');
            }
            if ($nroCaso != null) {
                $qb->andwhere('ss.iidCaso LIKE ?7')->setParameter(7, '%' . $nroCaso);
            }

            if ($contrato != null) {
                $qb->from('TechTBundle:Tbdetcontratorif', 'cr')
                        ->join('ss.fkIidContrato', 'fk1')
                        //        ->innerjoin('fk1.fkIci','fk2','WITH','fk2=:per')
                        //                  ->setParameter('per', $usu)
                        ->join('fk1.fkInroContrato', 'fk3')
                        ->andwhere('fk3.pkInroContrato LIKE ?1')
                        ->setParameter(1, '%' . $contrato);
                //$searchForm['fkIidContrato']=null;
            }

            if ($estatus != null and $estatus != 'Seleccionar') {
                $qb->andwhere('ss.vdescEstatus=?4')->setParameter(4, $estatus);
            }
        }
        $qb->addorderBy('ss.vdescEstatus', 'ASC');
        $qb->addorderBy('ss.dfechaCierre', 'DESC');
        $qb->addorderBy('ss.iidCaso', 'ASC');

        $query_pages = $qb->getQuery();
        $entities = $query_pages->execute();

        //Se Crea la Paginacion
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1)/* page number */, 7/* limit per page */
        );

        return $this->render('TechTBundle:Tbgensolicitudservicio:index.html.twig', array(
                    'entities' => $entities,
                    'entity' => $entity_search,
                    'search_form' => $searchForm->createView(),
                    'pagination' => $pagination,
        ));
    }

    /**
     * Creates a form to edit a Tbgensolicitudservicio entity.
     *
     * @param Tbgensolicitudservicio $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm(Tbgensolicitudservicio $entity) {
        $form = $this->createForm(new TbgensolicitudservicioType(), $entity, array(
            'action' => $this->generateUrl('SolicitudServicio_index'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Buscar'));
        $form->add('reset', 'button', array('label' => 'Limpiar'));
        return $form;
    }

    public function searchdayAction() {
        //Verificacion del empleado

        $request = $this->getRequest();
        $verif = new TbdetusuariodatosController();
        $verif1 = $verif->verifaccesoemplAction($request);
        $verif2 = $verif->verifaccesoAdminAction($request);

        if ($verif1 == false && $verif2 == false) {
            return $this->render('TechTBundle:Default:erroracceso.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entity_search = new Tbgensolicitudservicio();
        $searchForm = $this->createSearchForm($entity_search);
        $searchForm->handleRequest($request);
        //Valores introducidos
        date_default_timezone_set('America/Caracas');
        $fecha = new DateTime('NOW');
        $fecha_cierre = $searchForm['dfechaCierre']->getData();
        $nroCaso = $searchForm['iidCaso']->getData();
        $estatus = $searchForm['vdescEstatus']->getData();
        $tipo_solicitud = $searchForm['tbgentiposolicitud']->getData();
        $contrato= $searchForm['fkIidContrato']->getData();

        $qb = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->createQueryBuilder('ss');

        if ($tipo_solicitud != null  ||
                $fecha != null || $nroCaso != null || $estatus != null ||
                $fecha_cierre != null || $contrato!=null) {

            if ($tipo_solicitud != null ) {
                $qb->from('TechTBundle:Tbgenespecsolicitud', 'esp')
                        ->join('ss.fkIidEspSol', 'fkIidEspSol');
                    $qb->andwhere('fkIidEspSol.fkIidEspSol=?5')->setParameter(5, $tipo_solicitud);
            }
            if ($nroCaso != null) {
                $qb->andwhere('ss.iidCaso LIKE ?1')->setParameter(1, '%' . $nroCaso);
            }
            if ($fecha != null) {

                $qb->andwhere('ss.dfechaCreacion LIKE ?2')->setParameter(2, $fecha->format('Y-m-d') . '%');
            }
            if ($fecha_cierre != null) {
                $qb->andwhere('ss.dfechaCierre LIKE ?6')->setParameter(6, $fecha_cierre->format('Y-m-d') . '%');
            }

            if ($estatus != null) {
                $qb->andwhere('ss.vdescEstatus=?4')->setParameter(4, $estatus);
            }
               if ($contrato != null) {
                $qb->from('TechTBundle:Tbdetcontratorif', 'cr')
                        ->join('ss.fkIidContrato', 'fk1')
               //         ->innerjoin('fk1.fkIci', 'fk2', 'WITH', 'fk2=:per')
             //           ->setParameter('per', $usu)
                        ->join('fk1.fkInroContrato', 'fk3')
                        ->andwhere('fk3.pkInroContrato LIKE ?11')
                        ->setParameter(11, '%' . $contrato);
                //$searchForm['fkIidContrato']=null;
            }
        }

        $qb->addorderBy('ss.vdescEstatus', 'ASC');
        $qb->addorderBy('ss.dfechaCierre', 'DESC');
        $qb->addorderBy('ss.iidCaso', 'ASC');
        $query_pages = $qb->getQuery();

        $entities = $query_pages->execute();

        //Se Crea la Paginacion
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1)/* page number */, 7/* limit per page */
        );

        return $this->render('TechTBundle:Tbgensolicitudservicio:indexday.html.twig', array(
                    'entities' => $entities,
                    'entity' => $entity_search,
                    'search_form' => $searchForm->createView(),
                    'pagination' => $pagination,
        ));
    }

    /**
     * Lists all Tbgensolicitudservicio entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->findAll();
        $entity_search = new Tbgensolicitudservicio();
        $searchForm = $this->createSearchForm($entity_search);
        $qb = $em->getRepository('TechTBundle:tbgensolicitudservicio')->createQueryBuilder('ss');

        $qb->addorderBy('ss.dfechaCreacion', 'DESC');
        $qb->addorderBy('ss.vdescEstatus', 'ASC');
        $qb->addorderBy('ss.iidCaso', 'ASC');

        $query_pages = $qb->getQuery();
        $entities = $query_pages->execute();
        //Se Crea la Paginacion
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1)/* page number */, 7/* limit per page */
        );

        //Actualizacion de los campos fecha cierre, estatus
        //Creacion de Campos en CRM
        /* create a object */
        $utilObj = new Utilities();
        /* set parameters */

        foreach ($entities as &$entity) {
            $parameter = '';
            $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", '1', $parameter);
            $parameter = $utilObj->setParameter("id", ($entity->getIidCaso() - 1), $parameter);
            /* Call API */
            //print $parameter;
            //print '-----/n';
            $responseINS = $utilObj->sendCurlRequest(TARGETURSLGETBYID, $parameter);
            $stringResp = <<<XML
$responseINS
XML;
            $resp = simplexml_load_string($stringResp);
            //print_r((string)$resp->result->Cases->row[0]->FL[4]);
            /* FIN CRM        * */
            $estatus = (string) $resp->result->Cases->row[0]->FL[4];
            $entity->setVdescEstatus($estatus);
            $fecha = DateTime::createFromFormat('Y-m-d H:i:s', (string) $resp->result->Cases->row[0]->FL[17]);
            $entity->setDfechaCierre($fecha);
            $em->persist($entity);
            $em->flush();
            $parameter = "";
        }
        return $this->render('TechTBundle:Tbgensolicitudservicio:index.html.twig', array(
                    'entities' => $entities,
                    'entity' => $entity_search,
                    'search_form' => $searchForm->createView(),
                    'pagination' => $pagination,
        ));
    }

    /**
     * Lists all Tbgensolicitudservicio entities.
     *
     */
    public function indexdayAction() {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->findAll();
        $entity_search = new Tbgensolicitudservicio();
        $searchForm = $this->createSearchForm($entity_search);
        date_default_timezone_set('America/Caracas');
        $date_changes = new DateTime('NOW');
        $entity_search->setDfechaCreacion($date_changes);


        $qb = $em->getRepository('TechTBundle:tbgensolicitudservicio')->createQueryBuilder('ss');
        $qb->andwhere('ss.dfechaCreacion LIKE ?1')->setParameter(1, $date_changes->format('Y-m-d') . '%');
        $qb->orderBy('ss.vdescEstatus', 'DESC');
        $qb->addorderBy('ss.dfechaCreacion', 'ASC');
        $query_pages = $qb->getQuery();
        $entities = $query_pages->execute();
        //Se Crea la Paginacion
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1)/* page number */, 7/* limit per page */
        );
        //Actualizacion de los campos fecha cierre, estatus
        //Creacion de Campos en CRM
        /* create a object */
        $utilObj = new Utilities();
        foreach ($entities as &$entity) {
            $parameter = '';
            $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", '1', $parameter);
            $parameter = $utilObj->setParameter("id", ($entity->getIidCaso() - 1), $parameter);
            /* Call API */
            //print $parameter;
            //print '-----/n';
            $responseINS = $utilObj->sendCurlRequest(TARGETURSLGETBYID, $parameter);
            $stringResp = <<<XML
$responseINS
XML;
            $resp = simplexml_load_string($stringResp);
            //print_r((string)$resp->result->Cases->row[0]->FL[4]);
            /* FIN CRM        * */
            $estatus = (string) $resp->result->Cases->row[0]->FL[4];
            $entity->setVdescEstatus($estatus);
            $fecha = DateTime::createFromFormat('Y-m-d H:i:s', (string) $resp->result->Cases->row[0]->FL[17]);
            $entity->setDfechaCierre($fecha);
            $em->persist($entity);
            $em->flush();
            $parameter = "";
        }

        return $this->render('TechTBundle:Tbgensolicitudservicio:indexday.html.twig', array(
                    'entities' => $entities,
                    'entity' => $entity_search,
                    'search_form' => $searchForm->createView(),
                    'pagination' => $pagination,
        ));
    }

    /**
     * Creates a new Tbgensolicitudservicio entity.
     *
     */
    public function createAction(Request $request) {

        $entity = new Tbgensolicitudservicio();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();


        if ($form->isValid()) {
            //El contrato existe o no      
            $ci = $request->getSession()->get('usuario_ci');
            $usu = $em->getRepository('TechTBundle:Tbdetusuariodatos')
                    ->findOneBy(array('pkIci' => $ci));

            $contrato = $em->getRepository('TechTBundle:Tbdetcontratorif')
                    ->find($entity->getFkIidContrato()->getFkInroContrato());
            if ($contrato == null) {

                $message_error = "Introdujo un contrato inexistente." . $contrato;
                $this->get('session')->getFlashBag()->add('flash_error', $message_error);
                return $this->render('TechTBundle:Tbgensolicitudservicio:new.html.twig', array('id' => $entity->getId(),
                            'form' => $form->createView(),
                ));
            }
            //print_r($usu);
            $entity->setFkIidUsuaDatos($usu);

            $esp = $form['fkIidEspSol']->getData();

            if ($esp != null) {
                $idEsp = $esp->getId();
                //  print $idEsp;
                $especif = $em->getRepository('TechTBundle:Tbgenespecsolicitud')
                        ->find($esp);
                $entity->setFkIidEspSol($especif);
            }


            //Creacion de Campos en CRM
            /* create a object */
            $utilObj = new Utilities();
            /* set parameters */

            $parameter = "";
            $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", '1', $parameter);
            $records = array(
                'Case Owner' => 'SAC',
                'Priority' => 'RnX',
                'Case Reason' => $entity->getFkIidEspSol()->getFkIidEspSol()->getVnombreTipoSol(),
                'Case Origin' => 'Web',
                'Subject' => 'CASO WEB. ' . $entity->getFkIidEspSol()->getFkIidEspSol()->getVnombreTipoSol(),
                'Reported By' => $usu->getVnombre() . "  " . $usu->getVapellido(),
                'Email' => $usu->getVcorreoEmail(),
                'Phone' => $usu->getVtelfLocal(),
                'Account Name' => "Prueba Nombre Cuenta",
                'Número de Contrato' => $entity->getFkIidContrato()->getFkInroContrato()->getPkInroContrato(),
                'Tipo de Contrato' => 'Alquiler CCTV',
                'Dpto. Encargado' => 'Servicios y Atención al Cliente',
                'Nombre de contacto' => 'CRISBET',
                'Status' => 'Abierto'
            );
            $dataXml = $utilObj->parseXMLandInsertInBd($records);
            if ($dataXml != null) {
                //       print $dataXml;
            }
            $parameter = $utilObj->setParameter("xmlData", $dataXml, $parameter);
            /* Call API */
            $responseINS = $utilObj->sendCurlRequest(TARGETURLINS, $parameter);

            $stringResp = <<<XML
$responseINS
XML;

            $resp = simplexml_load_string($stringResp);
            $Idresp = $resp->result->recorddetail->FL;
            $entity->setIidCaso($Idresp + 1);

            //Actualizar Nro Solicitud
            $em->persist($entity);
            $em->flush();
            $message_info = "Recuerde revisar su correo electrónico.";
            $message_success = "Su solicitud ha sido registrada correctamente.";
            $this->get('session')->getFlashBag()->add('flash_success', $message_success);
            $this->get('session')->getFlashBag()->add('flash_info', $message_info);
            //Envio de correo de registro de caso 
            $this->mailer($entity, 'atencionalcliente@techtrol.com.ve', $usu->getVnombre(), 'TechTBundle:Tbgensolicitudservicio:mail.html.twig');
            $this->mailer($entity, $usu->getVcorreoEmail(), $usu->getVnombre(), 'TechTBundle:Tbgensolicitudservicio:mail.html.twig');
            return $this->render('TechTBundle:Tbgensolicitudservicio:confirm.html.twig', array('id' => $entity->getId(),
                        'form' => $form->createView(),
            ));
        }
        //print $form->getErrorsAsString();
        return $this->render('TechTBundle:Tbgensolicitudservicio:new.html.twig', array('id' => $entity->getId(),
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Tbgensolicitudservicio entity.
     *
     * @param Tbgensolicitudservicio $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tbgensolicitudservicio $entity) {
        $form = $this->createForm(new TbgensolicitudservicioType(), $entity, array(
            'action' => $this->generateUrl('SolicitudServicio_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Tbgensolicitudservicio entity.
     *
     */
    public function newAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $method = new TbdetusuariodatosController();
        $verif = $method->verifaccesouserAction($request);
        $verif2 = $method->verifaccesoAdminEmplAction($request);
        if ($verif == false && $verif2 == false) {
            return $this->render('TechTBundle:Default:erroracceso.html.twig');
        }

        $entity = new Tbgensolicitudservicio();

        date_default_timezone_set('America/Caracas');
        $date_changes = new DateTime('NOW');
        $entity->setDfechaCreacion($date_changes);

        $entity->setVdescEstatus("Abierto");

        $ci = $request->getSession()->get('usuario_ci');

        $usu = $em->getRepository('TechTBundle:Tbdetusuariodatos')
                ->findOneBy(array('pkIci' => $ci));
        $entity->setFkIidUsuaDatos($usu);


        $form = $this->createCreateForm($entity);

        return $this->render('TechTBundle:Tbgensolicitudservicio:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Tbgensolicitudservicio entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tbgensolicitudservicio entity.');
        }

        $idEsp = $entity->getFkIidEspSol()->getId();



        $deleteForm = $this->createDeleteForm($id);
        //Creacion de Campos en CRM
        /* create a object */
        $utilObj = new Utilities();
        /* set parameters */

        $parameter = "";
        $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
        $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
        $parameter = $utilObj->setParameter("newFormat", '1', $parameter);
        $parameter = $utilObj->setParameter("id", ($entity->getIidCaso() - 1), $parameter);
        /* Call API */
        $responseINS = $utilObj->sendCurlRequest(TARGETURSLGETBYID, $parameter);
        $stringResp = <<<XML
$responseINS
XML;
        $resp = simplexml_load_string($stringResp);
        //print_r((string)$resp->result->Cases->row[0]->FL[4]);

        $entity->setVdescEstatus((string) $resp->result->Cases->row[0]->FL[4]);




        //print($resp->attributes());
        //print('\n');        
        //print($resp->children());
        /* FIN CRM        * */

        return $this->render('TechTBundle:Tbgensolicitudservicio:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),));
    }

    /**
     * Displays a form to edit an existing Tbgensolicitudservicio entity.
     *
     */
    public function editAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tbgensolicitudservicio entity.');
        }
        //Actualizacion de los campos fecha cierre, estatus
        //Creacion de Campos en CRM
        /* create a object */
        $utilObj = new Utilities();
        /* set parameters */
            $parameter = '';
            $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("newFormat", '1', $parameter);
            $parameter = $utilObj->setParameter("id", ($entity->getIidCaso() - 1), $parameter);
            
            /* Call API */
            //print $parameter;
            //print '-----/n';
            $responseINS = $utilObj->sendCurlRequest(TARGETURSLGETBYID, $parameter);
            $stringResp = <<<XML
$responseINS
XML;
            $resp = simplexml_load_string($stringResp);
            //print_r((string)$resp->result->Cases->row[0]->FL[4]);
            /* FIN CRM        * */
            $estatus = (string) $resp->result->Cases->row[0]->FL[4];
            $entity->setVdescEstatus($estatus);
            $fecha = DateTime::createFromFormat('Y-m-d H:i:s', (string) $resp->result->Cases->row[0]->FL[17]);
            $entity->setDfechaCierre($fecha);
            
        if ($entity->getDfechaCierre() != null and $estatus == 'Culminado') {
            $dias = (strtotime($entity->getDfechaCierre()->format('d-m-Y')) - strtotime($entity->getDfechaCreacion()->format('d-m-Y'))) / 3600 / 24;

            $request->getSession()->set('tiempo_servicio_dias', $dias);
            //$request->getSession()->set('tiempo_servicio_horas',$horas);
        }
        
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TechTBundle:Tbgensolicitudservicio:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Tbgensolicitudservicio entity.
     *
     * @param Tbgensolicitudservicio $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Tbgensolicitudservicio $entity) {
        $form = $this->createForm(new TbgensolicitudservicioType(), $entity, array(
            'action' => $this->generateUrl('SolicitudServicio_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Tbgensolicitudservicio entity.
     *
     */
         public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->find($id);
        $entity_old = $entity;

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tbgensolicitudservicio entity.');
        }


        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $estatus=$editForm['vdescEstatus']->getData();
        $editForm->handleRequest($request);



        if ($editForm->isValid()) {
            if ($estatus!=$entity_old->getVdescEstatus()){
                //print "true";
                $usu=$entity_old->getFkIidUsuaDatos();

         $this->mailer($entity_old,$usu->getVcorreoEmail(),
                            $usu->getVnombre(),
                            'TechTBundle:Tbgensolicitudservicio:mailCamEstado.html.twig');
                }
                //si estatus cerrado setear fecha fin
                if ($editForm['vdescEstatus']->getData()=='Culminado'){

                date_default_timezone_set('America/Caracas');
                $date_changes = new DateTime('NOW');
                $entity->setDfechaCierre($date_changes);

                }else{

                $entity->setDfechaCierre(null);

                }

            $em->flush();
                   //Creacion de Campos en CRM
            /* create a object */
            $utilObj = new Utilities();
            /* set parameters */

            $parameter = "";
            $parameter = $utilObj->setParameter("scope", SCOPE, $parameter);
            $parameter = $utilObj->setParameter("authtoken", AUTHTOKEN, $parameter);
            $parameter = $utilObj->setParameter("id",($entity->getIidCaso()-1),$parameter);
  $records = array(
            'Status'=> $entity->getVdescEstatus()
                );
            $dataXml=$utilObj->parseXMLandInsertInBd($records);
            if ($dataXml!=null){
        //       print $dataXml;
             }
            $parameter = $utilObj->setParameter("xmlData",$dataXml, $parameter);
            /* Call API */
            $responseINS = $utilObj->sendCurlRequest(TARGETURLUPD, $parameter);
            /*FIN CRM        * */

        $message_info = "Recuerde revisar su correo electrónico.";
            $message_success= "Su solicitud ha sido editada correctamente.";
                $this->get('session')->getFlashBag()->add('flash_success', $message_success);
                $this->get('session')->getFlashBag()->add('flash_info', $message_info);
            return $this->redirect($this->generateUrl('SolicitudServicio_edit', array('id' => $id)));
        }

        return $this->render('TechTBundle:Tbgensolicitudservicio:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Tbgensolicitudservicio entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TechTBundle:Tbgensolicitudservicio')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tbgensolicitudservicio entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('SolicitudServicio'));
    }

    /**
     * Creates a form to delete a Tbgensolicitudservicio entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('SolicitudServicio_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    public function mailer($entity, $to, $vnombre, $url) {

        $message = \Swift_Message::newInstance()
                ->setSubject('Techtrol Registro de caso exitoso')
                ->setFrom('techtroll.ve@gmail.com')
                ->setTo($to)
                ->setBody(
                $this->renderView(
                        $url, array('entity' => $entity, 'vnombre' => $vnombre)
                )
                , 'text/html')
        ;
        $this->get('mailer')->send($message);
    }

}
