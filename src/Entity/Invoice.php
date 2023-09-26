<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeId = null;

    #[ORM\Column]
    private ?int $amountPaid = null;

    #[ORM\Column(length: 255)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $hostedInvoiceUrl = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Subscription $Subscription = null;

    public function __construct()
    {
        
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getAmountPaid(): ?int
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(int $amountPaid): static
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getHostedInvoiceUrl(): ?string
    {
        return $this->hostedInvoiceUrl;
    }

    public function setHostedInvoiceUrl(string $hostedInvoiceUrl): static
    {
        $this->hostedInvoiceUrl = $hostedInvoiceUrl;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->Subscription;
    }

    public function setSubscription(?Subscription $Subscription): static
    {
        $this->Subscription = $Subscription;

        return $this;
    }




}
