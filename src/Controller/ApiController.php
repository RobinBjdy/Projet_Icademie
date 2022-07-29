<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Entity\Categorie;
use App\Entity\User;
use App\Entity\Reservation;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/materiels", name="api_materiels")
     */
    public function getAllMateriels(): Response
    {
        $materiels = $this->getDoctrine()->getRepository(Materiel::class)->findAll();

        return new Response(json_encode($materiels, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/materiel/{id}", name="api_materiel")
     * @ParamConverter("materiel", class="App\Entity\Materiel")
     */
    public function getMateriel(Materiel $materiel): Response
    {
        return new Response(json_encode($materiel, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/materiel-complete/{id}", name="api_materiel_complete")
     * @ParamConverter("materiel", class="App\Entity\Materiel")
     */
    public function getMaterielComplete(Materiel $materiel): Response
    {
        return new Response(json_encode($materiel->jsonSerializeComplete(), JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/materiels/categorie/{id}", name="api_materiels_categorie")
     * @ParamConverter("categorie", class="App\Entity\Categorie")
     */
    public function getMaterielsCategorie(Categorie $categorie): Response
    {
        $materiel = $this->getDoctrine()->getRepository(Materiel::class)->findBy(["categorie" => $categorie->getId()], []);

        return new Response(json_encode($materiel, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/utilisateurs", name="api_utilisateurs")
     */
    public function getAllUtilisateurs(): Response
    {
        $utilisateurs = $this->getDoctrine()->getRepository(User::class)->findAll();

        return new Response(json_encode($utilisateurs, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/utilisateur/{id}", name="api_utilisateur")
     * @ParamConverter("utilisateur", class="App\Entity\User")
     */
    public function getUtilisateur(User $utilisateur): Response
    {
        return new Response(json_encode($utilisateur, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/utilisateur-complete/{id}", name="api_utilisateur_complete")
     * @ParamConverter("utilisateur", class="App\Entity\User")
     */
    public function getUtilisateurComplete(User $utilisateur): Response
    {
        return new Response(json_encode($utilisateur->jsonSerializeComplete(), JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/reservations", name="api_reservations")
     */
    public function getAllReservations(): Response
    {
        $reservation = $this->getDoctrine()->getRepository(Reservation::class)->findAll();

        return new Response(json_encode($reservation, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @Route("/api/reservation/{id}", name="api_reservation")
     * @ParamConverter("reservation", class="App\Entity\Reservation")
     */
    public function getReservation(Reservation $reservation): Response
    {
        return new Response(json_encode($reservation, JSON_UNESCAPED_UNICODE));
    }
}
