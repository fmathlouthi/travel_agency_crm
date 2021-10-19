<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $imagepath;

    /**
     * @ORM\Column(type="integer")
     */
    private $contact_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagepath(): ?string
    {
        return $this->imagepath;
    }

    public function setImagepath(string $imagepath): self
    {
        $this->imagepath = $imagepath;

        return $this;
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
}
