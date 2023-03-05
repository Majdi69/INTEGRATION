<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(name:"id_annonce", referencedColumnName:"id", onDelete:"CASCADE",nullable: false)]
    private  $idAnnonce = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Nom est obligatoire")]
    private ?string $nom = null;

    #[ORM\Column(length: 800)]
    #[Assert\NotBlank(message:"Text est obligatoire")]
    #[Assert\Length(min:10,minMessage:"Le commentire doit comporter au moins {{ limit }} caracteres")]
    private ?string $text = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\EqualTo("today")]
    #[Assert\Type(\DateTime::class)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->idAnnonce;
    }

    public function setAnnonce(?Annonce $Annonce): self
    {
        $this->idAnnonce = $Annonce;

        return $this;
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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
}
