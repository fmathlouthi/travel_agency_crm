<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppConfig
 *
 * @ORM\Table(name="app_config", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_318942FC5E237E06", columns={"name"})})
 * @ORM\Entity
 */
class AppConfig
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=190, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="text", length=0, nullable=true)
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }


}
