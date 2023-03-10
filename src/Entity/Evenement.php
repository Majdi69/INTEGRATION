<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups ;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("post:read")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min :2 ,
        max :15,
        minMessage: 'le nom doit etre supperieur à {{ limit }} caracteres',
        maxMessage: 'le nom ne doit pas dépasser {{ limit }} caracteres',)]
    #[Assert\NotNull]
    #[Assert\NotBlank(message:"Nom est obligatoire")]
    #[Groups("post:read")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups("post:read")]
    #[Assert\NotBlank(message:"Lieu est obligatoire")]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThan("today")]
    #[Groups("post:read")]
    #[Assert\NotBlank(message:"Date est obligatoire")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min :5 ,
        max :50,
        minMessage: 'la description doit etre supperieur à {{ limit }} caracteres',
        maxMessage: 'la description ne doit pas dépasser {{ limit }} caracteres',)]
    #[Assert\NotNull]
    #[Assert\NotBlank( message:"Votre description ne doit pas etre vide")]
    #[Groups("post:read")]
    private ?string $description = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThan("today")]
    #[Assert\NotBlank(message:"Date est obligatoire")]
    #[Groups("post:read")]
    private ?\DateTimeInterface $datefin = null;


    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Participation::class)]
    #[Groups("post:read")]
    private Collection $participations;

    #[ORM\Column(length: 255)]
    #[Groups("post:read")]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups("post:read")]
    #[Assert\NotBlank(message:"nombre de participation est obligatoire")]
    private ?int $nbrPersonnes = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    private ?Users $user = null;




    public function __construct()
    {
        $this->participations = new ArrayCollection();

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

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): self
    {
        $this->datefin = $datefin;

        return $this;
    }
    public function __toString()
    {
        return(string)$this->getDate();
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setEvenement($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getEvenement() === $this) {
                $participation->setEvenement(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }





}
