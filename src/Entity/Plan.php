<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeId = null;

    #[ORM\OneToMany(mappedBy: 'Plan', targetEntity: Subcription::class, orphanRemoval: true)]
    private Collection $subcriptions;

    #[ORM\Column(length: 255)]
    private ?string $paymentLink = null;

    public function __construct()
    {
        $this->subcriptions = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): static
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * @return Collection<int, Subcription>
     */
    public function getSubcriptions(): Collection
    {
        return $this->subcriptions;
    }

    public function addSubcription(Subcription $subcription): static
    {
        if (!$this->subcriptions->contains($subcription)) {
            $this->subcriptions->add($subcription);
            $subcription->setPlan($this);
        }

        return $this;
    }

    public function removeSubcription(Subcription $subcription): static
    {
        if ($this->subcriptions->removeElement($subcription)) {
            // set the owning side to null (unless already changed)
            if ($subcription->getPlan() === $this) {
                $subcription->setPlan(null);
            }
        }

        return $this;
    }

    public function getPaymentLink(): ?string
    {
        return $this->paymentLink;
    }

    public function setPaymentLink(string $paymentLink): static
    {
        $this->paymentLink = $paymentLink;

        return $this;
    }
}
