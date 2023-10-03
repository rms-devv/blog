<?php

namespace App\Entity;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'categorie:item']),
        new GetCollection(normalizationContext: ['groups' => 'categorie:list'])
    ],
 order: ['name' => 'ASC'],
 paginationEnabled: false,
 )]
class Categorie
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categorie:list', 'categorie:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['article:collection'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'Categorie', targetEntity: Article::class, orphanRemoval: true)]
    private Collection $articles;

    #[ORM\Column(length: 255)]
    #[Groups(['categorie:list', 'categorie:item'])]
    #[Gedmo\Slug(
        fields: ['name'],
        updatable: false,
        unique: true,
    )]
    private ?string $slug = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setCategorie($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getCategorie() === $this) {
                $article->setCategorie(null);
            }
        }

        return $this;
    }
    public function __toString() : string
    {
        return $this->getName();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
