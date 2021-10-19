<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactServiceRepository")
 */
class ContactService
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
    private $contact_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $service_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sccore;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContactId(): ?int
    {
        return $this->contact_id;
    }

    public function setContactId(int $contact_id): self
    {
        $this->contact_id = $contact_id;

        return $this;
    }

    public function getServiceId(): ?int
    {
        return $this->service_id;
    }

    public function setServiceId(int $service_id): self
    {
        $this->service_id = $service_id;

        return $this;
    }

    public function getSccore(): ?int
    {
        return $this->sccore;
    }

    public function setSccore(?int $sccore): self
    {
        $this->sccore = $sccore;

        return $this;
    }


}
