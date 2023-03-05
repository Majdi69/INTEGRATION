<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    /**
     *@Assert\NotBlank(message="Le type d'article doit etre non vide")
     *@Assert\Length(
     * min = 4,
     * max = 15,
     * minMessage="evenement doit etre  >=4",
     * maxMessage="evenement doit etre <=15"
     *   )
     */
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Reclamation::class)]
    private Collection $reclamation;

    public function __construct()
    {
        $this->reclamation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, reclamation>
     */
    public function getReclamation(): Collection
    {
        return $this->reclamation;
    }

    public function addReclamation(reclamation $reclamation): self
    {
        if (!$this->reclamation->contains($reclamation)) {
            $this->reclamation->add($reclamation);
            $reclamation->setArticle($this);
        }

        return $this;
    }

    public function removeReclamation(reclamation $reclamation): self
    {
        if ($this->reclamation->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getArticle() === $this) {
                $reclamation->setArticle(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->type;
    }

}
