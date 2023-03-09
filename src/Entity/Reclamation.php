<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    /**
     *@Assert\NotBlank(message="Le nom de reclamation doit etre non vide")
     *@Assert\Length(
     * min = 4,
     * minMessage="nom doit etre superieur à 4 ",
     *   )
     */
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    /**
     *@Assert\NotBlank(message="Le nom de reclamation doit etre non vide")
     *@Assert\Length(
     * min = 4,
     * minMessage="nom doit etre superieur à 4 ",
     *   )
     */
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    /**
     * @Assert\EqualTo("today", message="La date ne peut pas être postérieure à la date actuelle.")
     */
    private ?\DateTimeInterface $date = null;



    #[ORM\Column(length: 100)]
    /**
     *@Assert\NotBlank(message="L'email doit etre non vide")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     */
    private ?string $mail = null;

    #[ORM\ManyToOne(inversedBy: 'reclamation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Articles $article = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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



    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getArticle(): ?Articles
    {
        return $this->article;
    }

    public function setArticle(?Articles $article): self
    {
        $this->article = $article;

        return $this;
    }
}
