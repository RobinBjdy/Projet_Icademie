<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Materiel;
use App\Entity\Categorie;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Utils\GoogleCalendar;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MainController extends AbstractController
{
    /**
     * Page d'accueil
     * @Route("/", name="accueil")
     */
    public function accueil(Request $request, PaginatorInterface $paginator)
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
            5
        );

        return $this->render('accueil.html.twig', [
            "titre" => "Accueil",
            "materiels" => $materiels,
            "keyword" => $keyword,
            "categorie" => $categorie,
            "dispo" => $dispo,
            "reserv" => $reserv,
            "lesCateg" => $lesCateg
        ]);
    }

    /**
     * Page avec tous les matériels de la catégorie en question
     * @Route("/categorie/{id}", name="categorie")
     * @ParamConverter("categorie", class="App\Entity\Categorie")
     * @param id - L'id de la catégorie
     */
    public function categorie(Request $request, PaginatorInterface $paginator, Categorie $categorie)
    {
        $materiels = $this->getDoctrine()->getRepository(Materiel::class)->findBy(['categorie' => $categorie->getId()],[]);

        $materiels = $paginator->paginate(
            $materiels,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('categorie.html.twig', [
            "titre" => $categorie->getLibelle(),
            "materiels" => $materiels
        ]);
    }

    /**
     * Page de détail d'un matériel
     * @Route("/materiel/{id}", name="materiel")
     * @ParamConverter("materiel", class="App\Entity\Materiel")
     * @param id - L'id du matériel
     */
    public function materiel(Request $request, Materiel $materiel)
    {
        $materiel = $this->getDoctrine()->getRepository(Materiel::class)->find($materiel->getId());

        if($request->isMethod('GET')){

            $form = $this->createForm(ReservationType::class, null, [
                "attr" => [
                    "class" => "ajax-submit"
                ]
            ]);

            return $this->renderForm('materiel.html.twig', [
                'form' => $form,
                "titre" => $materiel->getLibelle(),
                "materiel" => $materiel
            ]);
        }

        if($request->isMethod('POST')){

            $form = $this->createForm(ReservationType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $reservation = $form->getData();

                // Créer l'event sur le calendar google
                $debut = $reservation->getDebut();
                $fin = $reservation->getFin();
                $calendarHelper = new GoogleCalendar();

                $interval = $debut->diff($fin);

                if($interval->format('%a') > 7){
                    return new Response("Un matériel ne peut être réservé plus de 7 jours", 500);
                }

                if(!$this->creneauDispo($materiel, $debut, $fin)){
                    return new Response("Ce materiel possède déjà une réservation dans le créneau souhaité", 500);
                }

                try{
                    $googleEvent = $calendarHelper->createEvent('robin.projet.icademie@gmail.com', $materiel, 'Réservé par ' . $this->getUser(), $debut, $fin);
                }catch(Exception $e){
                    return new Response($e->getMessage(), 500);
                }

                // Ajout l'id de l'event dans la reservation
                $reservation->setIdEventGoogle($googleEvent->id);

                $reservation->setIdUser($this->getUser());
                $reservation->setIdMateriel($materiel);

                $em = $this->getDoctrine()->getManager();
                $em->persist($reservation);
                $em->flush();

                return new Response("Réservation faite avec succès", 200);

            }

            return new Response($form->getErrors(true));
        }

        throw new BadRequestHttpException("Method not supported");
    }

    /**
     * Page de projet n°2 avec toutes les fonctionnalités en js
     * @Route("/projet-2", name="projet_2")
     */
    public function projet_2()
    {

        return $this->render('projet-2.html.twig', [
            "titre" => "Projet 2"
        ]);
    }

    /**
     * Page avec les informations du compte
     * @Route("/mon-compte", name="mon_compte")
     */
    public function monCompte(Request $request, PaginatorInterface $paginator)
    {
        $reservations = $this->getDoctrine()->getRepository(Reservation::class)->findBy(["idUser" => $this->getUser()], []);

        $reservations = $paginator->paginate(
            $reservations,
            $request->query->getInt('page', 1),
            4
        );
        
        return $this->renderForm('mon-compte.html.twig', [
            "titre" => "Mon compte",
            "reservations" => $reservations
        ]);
    }

    /**
     * @Route("/reset-password/utilisateur/{id}", name="reset_password")
     * @ParamConverter("utilisateur", class="App\Entity\User")
     */
    public function resetPasswordUtilisateur(Request $request, User $utilisateur, UserPasswordEncoderInterface $encoder): Response
    {   
        if($request->isMethod('GET')){
            return $this->renderForm('reset-password.html.twig', [
                "user" => $utilisateur,
                "titre" => "Mon Compte"
            ]);
        }

        if($request->isMethod('POST')){
            $password = $request->request->get('new_password');

            $confirmPassword = $request->request->get('new_password_confirm');

            if($password !== $confirmPassword){
                throw new \Exception("Veuillez indiquer des mots de passes identiques");
            }else{
                $plainPassword = $password;
                $encoded = $encoder->encodePassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($encoded);

                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }

            return $this->redirectToRoute('mon_compte');
        }
       
        throw new \Exception("The method is not supported"); 
    }

    /**
     * @Route("/delete/event-google/{id}", name="delete_event_google", options={"expose"=true})
     * @ParamConverter("reservation", class="App\Entity\Reservation")
     */
    public function deleteEventGoogle(Reservation $reservation): Response
    {   
        // Supprime l'event google calendar
        $calendarHelper = new GoogleCalendar();

        try{
            $calendarHelper->deleteEvent($reservation->getIdEventGoogle(), 'robin.projet.icademie@gmail.com');
        }catch(Exception $e){
            return new Response($e->getMessage(), 500);
        }

        // Delete la réservation
        $em = $this->getDoctrine()->getManager();
        $em->remove($reservation);
        $em->flush();

        return new Response('Réservation suprimée avec succès');
    }

    /**
     * @Route("/edit/event-google/{id}", name="edit_event_google", options={"expose"=true})
     * @ParamConverter("reservation", class="App\Entity\Reservation")
     */
    public function editEventGoogle(Request $request, Reservation $reservation): Response
    {   
        if($request->isMethod('GET')){

            $form = $this->createForm(ReservationType::class, $reservation, [
                "attr" => [
                    "class" => "ajax-submit"
                ],
                'action' => $this->generateUrl('edit_event_google',  array(
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

                $interval = $debut->diff($fin);

                if($interval->format('%a') > 7){
                    return new Response("Un matériel ne peut être réservé plus de 7 jours", 500);
                }
                
                if(!$this->creneauDispo($laReservation->getIdMateriel(), $debut, $fin)){
                    return new Response("Ce materiel possède déjà une réservation dans le créneau souhaité", 500);
                }

                try{
                    $calendarHelper->updateEvent($laReservation->getIdEventGoogle(), 'robin.projet.icademie@gmail.com', $laReservation->getIdMateriel()->getLibelle(), 'Réservé par ' . $this->getUser(), $debut, $fin);
                }catch(Exception $e){
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