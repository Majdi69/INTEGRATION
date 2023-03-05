<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use http\Message;
use phpDocumentor\Reflection\Type;
use Symfony\Component\Validator\Constraints as Assert;

use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
class Annonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("annonces")]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message:"Nom est obligatoire")]
    #[Groups("annonces")]

    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups("annonces")]
   // #[Assert\NotBlank(message:"Image est obligatoire")]
    private  $image ;

    #[ORM\Column(length: 800)]
    #[Assert\NotBlank(message:"Descreption est obligatoire")]
    #[Assert\Length(min:15,minMessage:"La descreption doit comporter au moins {{ limit }} caracteres")]
    #[Groups("annonces")]
    private ?string $descreption = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Titre est obligatoire")]
    #[Assert\Length(min:10,minMessage:"Le titre doit comporter au moins {{ limit }} caracteres")]
    #[Groups("annonces")]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Tag est obligatoire")]
    #[Groups("annonces")]
    private ?string $tag = null;

    #[ORM\Column]
    #[Assert\Positive(message:"Le numéro ne peut pas etre negatif")]
    #[Groups("annonces")]
    //#[Assert\Range(min:8,max:8,notInRangeMessage:"Le numéro doit comporter 8 chiffre ")]
    private ?int $tel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Email est obligatoire")]
    #[Assert\Email(message:"L'E-mail '{{ value }}' n'est pas valide ")]
    #[Groups("annonces")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]

    #[Assert\NotBlank(message:"Local est obligatoire")]
    #[Groups("annonces")]
    private ?string $local = null;

    #[ORM\Column(name:"etat", type:"string", columnDefinition:"enum('dispo', 'indispo')")]
    #[Groups("annonces")]
    private  $etat ;

    #[ORM\Column(length: 255)]
    #[Groups("annonces")]
    private $categorie ;

    #[ORM\OneToMany(mappedBy: 'idAnnonce', targetEntity: Commentaire::class)]
    #[Groups("annonces")]
    private Collection $commentaires;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
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

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getDescreption(): ?string
    {
        return $this->descreption;
    }

    public function setDescreption(string $descreption): self
    {
        $this->descreption = $descreption;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTel(): ?int
    {
        return $this->tel;
    }

    public function setTel(int $tel): self
    {
        $this->tel = $tel;

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

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(string $local): self
    {
        $this->local = $local;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setAnnonce($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getAnnonce() === $this) {
                $commentaire->setAnnonce(null);
            }
        }

        return $this;
    }
     public function __toString()
    {
         return (string)$this->getNom(); //sinon fel twig nforciw naamlou f.annonce.name
    }
}
