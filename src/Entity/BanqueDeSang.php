<?php

namespace App\Entity;

use App\Repository\BanqueDeSangRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: BanqueDeSangRepository::class)]
class BanqueDeSang
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Nom is required")]

    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"adresse is required")]
    #[Assert\Length(min:10,minMessage:"La adresse doit etre supérieur a 10 charactére")]
    private ?string $adresse = null;


    #[ORM\Column]
    #[Assert\NotBlank(message:"Tel is required")]
    #[Assert\Positive(message:"Le numéro de téléphone doit etre positive")]
    private ?int $tel = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"longitude is required")]
    private ?float $longitude = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"altitude is required")]
    private ?float $latitude = null;


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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

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

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }
}
