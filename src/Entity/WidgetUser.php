<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WidgetUser
 *
 * @ORM\Table(name="widget_user", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_1564DDF67E3C61F9", columns={"owner_id"})})
 * @ORM\Entity
 */
class WidgetUser
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
     * @var array
     *
     * @ORM\Column(name="config", type="array", length=0, nullable=false)
     */
    private $config;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     * })
     */
    private $owner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }


}
