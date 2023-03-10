<?php

namespace App\Entity;

use App\Repository\ArticlesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use http\Message;
use phpDocumentor\Reflection\Type;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticlesRepository::class)]
class Articles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
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
    #[ORM\OneToMany(mappedBy: 'articles', targetEntity: Reclamation::class)]
    private Collection $reclamation;


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


    public function __toString(): string
    {
        return $this->nom;
    }
    }
