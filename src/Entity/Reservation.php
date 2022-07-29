<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $idEventGoogle;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservations", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idUser;

    /**
     * @ORM\Column(type="datetime")
     */
    private $debut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fin;

    /**
     * @ORM\ManyToOne(targetEntity=Materiel::class, inversedBy="reservations", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idMateriel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdEventGoogle(): ?string
    {
        return $this->idEventGoogle;
    }

    public function setIdEventGoogle(string $idEventGoogle): self
    {
        $this->idEventGoogle = $idEventGoogle;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getIdMateriel(): ?Materiel
    {
        return $this->idMateriel;
    }

    public function setIdMateriel(?Materiel $idMateriel): self
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    public function __toString(): string{
        return $this->getIdEventGoogle();
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'idEventGoogle'=> $this->idEventGoogle,
            'debut' => $this->debut,
            'fin' => $this->fin,
            'utilisateur' => $this->idUser,
            'materiel' => $this->idMateriel
        );
    }
}
