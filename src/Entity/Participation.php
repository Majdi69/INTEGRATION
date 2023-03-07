<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use http\Message;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups ;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    #[Asssert\NotBlank(message:"Email is required")]
    #[Assert\Email(message:"l'email '{{value}}' est non valid.")]
    private ?string $email = null;

    #[ORM\Column(length: 8)]
    #[Asssert\NotBlank(message:"numero is required")]
    #[Assert\Positive(message:"numero doit etre positive")]
    #[Assert\NotNull]
    private ?int $numero = null;



    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\Column]
    private ?int $nbrPersonnes = null;







    public function __construct()
    {
        $this->evenements = new ArrayCollection();
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;

        return $this;
    }

    public function getNbrPersonnes(): ?int
    {
        return $this->nbrPersonnes;
    }

    public function setNbrPersonnes(int $nbrPersonnes): self
    {
        $this->nbrPersonnes = $nbrPersonnes;

        return $this;
    }


}