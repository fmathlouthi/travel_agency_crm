<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TauxRepository")
 */
class Taux
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
    private $pointnuber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPointnuber(): ?int
    {
        return $this->pointnuber;
    }

    public function setPointnuber(int $pointnuber): self
    {
        $this->pointnuber = $pointnuber;

        return $this;
    }
}
