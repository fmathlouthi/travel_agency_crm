<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoyaltyCardsRepository")
 */
class LoyaltyCards
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $customer_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $card_code;

    /**
     * @ORM\Column(type="string", length=340)
     */
    private $qrcode;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_of_issue;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_valid;

    /**
     * @ORM\Column(type="integer")
     */
    private $loyalty_points;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): ?int
    {
        return $this->customer_id;
    }

    public function setCustomerId(int $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getCardCode(): ?int
    {
        return $this->card_code;
    }

    public function setCardCode(int $card_code): self
    {
        $this->card_code = $card_code;

        return $this;
    }

    public function getQrcode(): ?string
    {
        return $this->qrcode;
    }

    public function setQrcode(string $qrcode): self
    {
        $this->qrcode = $qrcode;

        return $this;
    }

    public function getDateOfIssue(): ?\DateTimeInterface
    {
        return $this->date_of_issue;
    }

    public function setDateOfIssue(\DateTimeInterface $date_of_issue): self
    {
        $this->date_of_issue = $date_of_issue;

        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->is_valid;
    }

    public function setIsValid(bool $is_valid): self
    {
        $this->is_valid = $is_valid;

        return $this;
    }

    public function getLoyaltyPoints(): ?int
    {
        return $this->loyalty_points;
    }

    public function setLoyaltyPoints(int $loyalty_points): self
    {
        $this->loyalty_points = $loyalty_points;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
