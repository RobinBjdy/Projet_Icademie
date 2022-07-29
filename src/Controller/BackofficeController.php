<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Materiel;
use App\Entity\Categorie;
use App\Form\MaterielType;
use App\Form\UserEditType;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Utils\GoogleCalendar;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BackofficeController extends AbstractController
{
    /**
     * @Route("/backoffice", name="backoffice")
     */
    public function accueil(): Response
    {
        return $this->render('backoffice/accueil.html.twig');
    }

    /**
     * @Route("/backoffice/materiels", name="backoffice_materiels")
     */
    public function backofficeMateriels(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = null;
        $categorie = null;
        $dispo = null;
        $reserv = null;

        $lesCateg = $this->getDoctrine()->getRepository(Categorie::class)->findAll();

        if($request->query->get('keyword')){
            $keyword = $request->query->get('keyword');
        }

        if($request->query->get('categorie')){
            $categorie = $request->query->get('categorie');
        }
        
        if($request->query->get('dispo') !== null){
            $dispo = $request->query->get('dispo');
        }

        if($request->query->get('reserv') !== null){
            $reserv = $request->query->get('reserv');
        }

        $materiels = $this->getDoctrine()->getRepository(Materiel::class)->findMateriels($keyword, $categorie, $dispo, $reserv);

        $materiels = $paginator->paginate(
            $materiels,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('backoffice/materiels.html.twig', [
            "materiels" => $materiels,
            "keyword" => $keyword,
            "categorie" => $categorie,
            "dispo" => $dispo,
            "reserv" => $reserv,
            "lesCateg" => $lesCateg
        ]);
    }

    /**
     * @Route("/backoffice/create/materiel", name="backoffice_create_materiel")
     */
    public function backofficeCreateMateriel(Request $request): Response
    {
        if($request->isMethod('GET')){
            $materiel = new Materiel();

            $form = $this->createForm(MaterielType::class, $materiel);
    
            return $this->renderForm('backoffice/create-materiel.html.twig', [
                'form' => $form,
            ]);
        }

        if($request->isMethod('POST')){
            $materiel = new Materiel();

            $form = $this->createForm(MaterielType::class, $materiel);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $materiel = $form->getData();
                
                if($request->files->get('materiel')['file']){
                    // Creation d'un nom pour l'image
                    $filename = md5(uniqid()) . '.' . $request->files->get('materiel')['file']->guessExtension();
                    $materiel->SetImage($filename);

                    // Upload de l'image
                    $image = $form->get('file')->getData();
                    $image->move($this->getParameter('uploads_images'), $filename);
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($materiel);
                $em->flush();
        
                return $this->redirectToRoute("backoffice_materiels");
            }else{
                throw new \Exception("Veuillez remplir correctement le formulaire", 404);
            }
        }
            
        throw new \Exception("The method is not supported");   
    }
    
    /**
     * @Route("/backoffice/edit/materiel/{id}", name="backoffice_edit_materiel")
     * @ParamConverter("materiel", class="App\Entity\Materiel")
     */
    public function backofficeEditMateriel(Request $request, Materiel $materiel): Response
    {
        if($request->isMethod('GET')){

            $form = $this->createForm(MaterielType::class, $materiel);

            return $this->renderForm('backoffice/edit-materiel.html.twig', [
                'form' => $form,
                'materiel' => $materiel
            ]);
        }

        if($request->isMethod('POST')){

            $form = $this->createForm(MaterielType::class, $materiel);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $materiel = $form->getData();
                
                if($request->files->get('materiel')['file']){
                    $image = $form->get('file')->getData();

                    if($materiel->getImage()){
                        // Delete l'ancienne image
                        unlink($this->getParameter('uploads_images'). $materiel->getImage());
                    }

                    // Creation d'un nom pour la nouvelle image
                    $filename = md5(uniqid()) . '.' . $request->files->get('materiel')['file']->guessExtension();
                    $materiel->SetImage($filename);

                    // Upload de la nouvelle image
                    $image->move($this->getParameter('uploads_images'), $filename);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($materiel);
                $em->flush();
        
                return $this->redirectToRoute("backoffice_materiels");
            }else{
                throw new \Exception("Veuillez remplir correctement le formulaire", 404);
            }
        }
            
        throw new \Exception("The method is not supported");   
    }

    /**
     * @Route("/backoffice/delete/materiel/{id}", name="backoffice_delete_materiel")
     * @ParamConverter("materiel", class="App\Entity\Materiel")
     */
    public function backofficeDeleteMateriel(Request $request, Materiel $materiel): Response
    {
        if($materiel->getImage()){
            // Delete l'image des fichiers
            unlink($this->getParameter('uploads_images'). $materiel->getImage());
        }
        
        // Remove de l'image
        $em = $this->getDoctrine()->getManager();
        $em->remove($materiel);
        $em->flush();

        return $this->redirectToRoute('backoffice_materiels');
    }

    /**
     * @Route("/backoffice/utilisateurs", name="backoffice_utilisateurs")
     */
    public function backofficeUtilisateurs(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = null;
        $role = null;

        $lesRoles = ["ROLE_ADMIN" => "Admin", "ROLE_USER" => "Utilisateur"];

        if($request->query->get('keyword')){
            $keyword = $request->query->get('keyword');
        }

        if($request->query->get('role')){
            $role = $request->query->get('role');
        }
        
        $users = $this->getDoctrine()->getRepository(User::class)->findUsers($keyword, $role);

        $users = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('backoffice/users.html.twig', [
            "users" => $users,
            "keyword" => $keyword,
            'role' => $role,
            "lesRoles" => $lesRoles
        ]);
    }

    /**
     * @Route("/backoffice/create/utilisateur", name="backoffice_create_utilisateur")
     */
    public function backofficeCreateUtilisateur(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        if($request->isMethod('GET')){
            $utilisateur = new User();

            $form = $this->createForm(UserType::class, $utilisateur);
    
            return $this->renderForm('backoffice/create-user.html.twig', [
                'form' => $form,
            ]);
        }

        if($request->isMethod('POST')){
            $utilisateur = new User();

            $form = $this->createForm(UserType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $utilisateur = $form->getData();
                
                $plainPassword = $utilisateur->getPassword();
                $encoded = $encoder->encodePassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($encoded);

                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();
        
                return $this->redirectToRoute("backoffice_utilisateurs");
            }else{
                throw new \Exception("Veuillez remplir correctement le formulaire (Email unique et mots de passes identiques)", 404);
            }
        }
            
        throw new \Exception("The method is not supported");   
    }

    /**
     * @Route("/backoffice/edit/utilisateur/{id}", name="backoffice_edit_utilisateur")
     * @ParamConverter("utilisateur", class="App\Entity\User")
     */
    public function backofficeEditUtilisateur(Request $request, User $utilisateur): Response
    {
        if($request->isMethod('GET')){

            $form = $this->createForm(UserEditType::class, $utilisateur);

            return $this->renderForm('backoffice/edit-user.html.twig', [
                'form' => $form,
                'user' => $utilisateur
            ]);
        }

        if($request->isMethod('POST')){

            $form = $this->createForm(UserEditType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $utilisateur = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();
        
                return $this->redirectToRoute("backoffice_utilisateurs");
            }else{
                throw new \Exception("Veuillez remplir correctement le formulaire", 404);
            }
        }
            
        throw new \Exception("The method is not supported");   
    }

    /**
     * @Route("/backoffice/delete/utilisateur/{id}", name="backoffice_delete_utilisateur")
     * @ParamConverter("utilisateur", class="App\Entity\User")
     */
    public function backofficeDeleteUtilisateur(Request $request, User $utilisateur): Response
    {   
        $em = $this->getDoctrine()->getManager();
        $em->remove($utilisateur);
        $em->flush();

        return $this->redirectToRoute('backoffice_utilisateurs');
    }

    /**
     * @Route("/backoffice/reservations", name="backoffice_reservations")
     */
    public function backofficeReservations(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = null;
        $categorie = null;
        $utilisateur = null;

        $lesCateg = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        $lesUsers = $this->getDoctrine()->getRepository(User::class)->findAll();

        if($request->query->get('keyword')){
            $keyword = $request->query->get('keyword');
        }

        if($request->query->get('categorie')){
            $categorie = $request->query->get('categorie');
        }
        
        if($request->query->get('utilisateur')){
            $utilisateur = $request->query->get('utilisateur');
        }

        $reservations = $this->getDoctrine()->getRepository(Reservation::class)->findReservations($keyword, $categorie, $utilisateur);

        $reservations = $paginator->paginate(
            $reservations,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('backoffice/reservations.html.twig', [
            "reservations" => $reservations,
            "keyword" => $keyword,
            "categorie" => $categorie,
            "utilisateur" => $utilisateur,
            "lesCateg" => $lesCateg,
            "lesUsers" => $lesUsers
        ]);
    }

    /**
     * @Route("/backoffice/edit/reservation/{id}", name="backoffice_edit_reservation")
     * @ParamConverter("reservation", class="App\Entity\Reservation")
     */
    public function backofficeEditReservation(Request $request, Reservation $reservation): Response
    {   
        if($request->isMethod('GET')){

            $form = $this->createForm(ReservationType::class, $reservation, [
                "attr" => [
                    "class" => "ajax-submit"
                ],
                'action' => $this->generateUrl('backoffice_edit_reservation',  array(
                    'id' => $reservation->getId()
                ),),
            ]);

            return $this->renderForm('modal-edit-event-google.html.twig', [
                'form' => $form
            ]);
        }
        if($request->isMethod('POST')){

            $laReservation = $reservation;

            $form = $this->createForm(ReservationType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $reservation = $form->getData();

                // Créer l'event sur le calendar google
                $debut = $reservation->getDebut();
                $fin = $reservation->getFin();

                $laReservation->setDebut($debut);
                $laReservation->setFin($fin);

                $calendarHelper = new GoogleCalendar();
                
                if(!$this->creneauDispo($laReservation->getIdMateriel(), $debut, $fin)){
                    return new Response("Ce materiel possède déjà une réservation dans le créneau souhaité", 500);
                }

                try{
                    $calendarHelper->updateEvent($laReservation->getIdEventGoogle(), 'robin.projet.icademie@gmail.com', $laReservation->getIdMateriel()->getLibelle(), 'Réservé par ' . $this->getUser(), $debut, $fin);
                }catch(\Exception $e){
                    return new Response($e->getMessage(), 500);
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($laReservation);
                $em->flush();

                $response = [];

                $response["response"] =  ["Modification de la réservation faite avec succès"];
                $response["reservation"] =  $laReservation;

                return new Response(json_encode($response));
            }

            return new Response($form->getErrors(true));
        }

        throw new BadRequestHttpException("Method not supported");
    }

    /**
     * @Route("/backoffice/delete/reservation/{id}", name="backoffice_delete_reservation")
     * @ParamConverter("reservation", class="App\Entity\Reservation")
     */
    public function backofficeDeleteReservation(Request $request, Reservation $reservation): Response
    {
        // Supprime l'event google calendar
        $calendarHelper = new GoogleCalendar();
        $calendarHelper->deleteEvent($reservation->getIdEventGoogle(), 'robin.projet.icademie@gmail.com');

        // Delete la réservation
        $em = $this->getDoctrine()->getManager();
        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('backoffice_reservations');
    }

    public function creneauDispo(Materiel $materiel, $debut, $fin){
        $reservs = $materiel->getReservations();

        foreach ($reservs as $reserv) {
            if($debut > $reserv->getDebut() && $debut < $reserv->getFin()){
                return false;
            }
            if($fin > $reserv->getDebut() && $fin < $reserv->getFin()){
                return false;
            }
            if($debut < $reserv->getDebut() && $fin > $reserv->getFin()){
                return false;
            }
        }

        return true;
    }
}
