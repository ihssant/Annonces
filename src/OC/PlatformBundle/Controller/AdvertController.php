<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\AdvertSkill;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\OC\PlatformBundle\Form;

// Controlleur principal du projet
class AdvertController extends Controller
{
	public function indexAction($page)
	{
		if ($page < 1) {
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		
		$em = $this->getDoctrine()->getManager();
		$nbPerPage = 3;
	    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->getAdverts($page, $nbPerPage);	
	    
	    $nbPages = ceil(count($listAdverts)/$nbPerPage);	// count the number of pages
	    
	    if ($page > $nbPages) {
	    	throw $this->createNotFoundException("La page ".$page." n'existe pas.");
	    }
	    	
	    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(	
	      'listAdverts' => $listAdverts,
	      'nbPages'     => $nbPages,
	      'page'        => $page
	    ));
	}
	
	public function viewAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
			
	    // On récupère l'annonce $id	
	    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
	
	    if (null === $advert) {
	      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
	    }
	
	    // On avait déjà récupéré la liste des candidatures
	    $listApplications = $em
	      ->getRepository('OCPlatformBundle:Application')
	      ->findBy(array('advert' => $advert));
	
	    // On récupère maintenant la liste des AdvertSkill	
	    $listAdvertSkills = $em
	      ->getRepository('OCPlatformBundle:AdvertSkill')	
	      ->findBy(array('advert' => $advert));
	
	    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(	
	      'advert'           => $advert,	
	      'listApplications' => $listApplications,	
	      'listAdvertSkills' => $listAdvertSkills	
	    ));
	}
	
	// On récupère tous les paramètres en arguments de la méthode
	public function viewSlugAction($slug, $year, $format)
	{
		return new Response(
				"On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$format."."
		);
	}
	
	public function addAction(Request $request)
	{
		$advert = new Advert;
		
		// On crée le formulaire de Advert a partir de AdverType
		$form = $this->createForm(new AdvertType, $advert);

		// À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
		$form->handleRequest($request);
		
		if($form->isValid()) {	    // On vérifie que les valeurs entrées sont correctes
			$em = $this->getDoctrine()->getEntityManager();
			
			$em->persist($advert);
			$em->flush();
			
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
			
			// On redirige vers la page de visualisation de l'annonce nouvellement créée
			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
		}
		
		// À ce stade, le formulaire n'est pas valide car :
		// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
	    return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
	    		'form' => $form->createView(),
	    ));
	}
	
	public function deleteAction($id, Request $request)
	{		
		$em = $this->getDoctrine()->getManager();

	    // On récupère l'annonce $id
	    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
	    	
	    if (null === $advert) {	
	      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");	
	    }
	
	    // On crée un formulaire vide, qui ne contiendra que le champ CSRF	
	    // Cela permet de protéger la suppression d'annonce contre cette faille	
	    $form = $this->createFormBuilder()->getForm();
	
	    if ($form->handleRequest($request)->isValid()) {	
	      $em->remove($advert);
	
	      $em->flush();
	
	      $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");
	
	      return $this->redirect($this->generateUrl('oc_platform_home'));	
	    }
	
	    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer	
	    return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(	
	      'advert' => $advert,	
	      'form'   => $form->createView()	
	    ));
	}
	
	public function editAction($id, Request $request)	
	{	
		$em = $this->getDoctrine()->getEntityManager();
		// On récupère l'annonce $id		
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
		
		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");		
		}

		// On cree le formulaire
		$form = $this->createForm(new AdvertEditType, $advert);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {	// si il es valide on enregistre les modifications dans la B.D			
			$em->persist($advert);
			$em->flush();
			
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
			
			// On redirige vers la page de visualisation de l'annonce nouvellement créée
			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
		}
			
		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
				'form'   => $form->createView(),
				'advert' => $advert		// ou cas ou on veut annuler la modofication	
		));	
	}
	
	public function menuAction($limit)
	{
		$em = $this->getDoctrine()->getManager();
		$listAdverts =  $em->getRepository('OCPlatformBundle:Advert')->findBy(
				array(),
				array('date' => 'desc'),
				$limit,
				0
				);
		
		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
				// Tout l'intérêt est ici : le contrôleur passe
				// les variables nécessaires au template !
				'listAdverts' => $listAdverts
		));
	}
	
	
	
	
}
