<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MailTemplate
 *
 * @ORM\Table(name="mail_template")
 * @ORM\Entity
 */
class MailTemplate
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
     * @var string|null
     *
     * @ORM\Column(name="templateId", type="string", length=50, nullable=true)
     */
    private $templateid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fromName", type="string", length=100, nullable=true)
     */
    private $fromname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fromEmail", type="string", length=100, nullable=true)
     */
    private $fromemail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="template", type="text", length=0, nullable=true)
     */
    private $template;

    /**
     * @var string|null
     *
     * @ORM\Column(name="templateData", type="text", length=0, nullable=true)
     */
    private $templatedata;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=3, nullable=false)
     */
    private $language;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplateid(): ?string
    {
        return $this->templateid;
    }

    public function setTemplateid(?string $templateid): self
    {
        $this->templateid = $templateid;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getFromname(): ?string
    {
        return $this->fromname;
    }

    public function setFromname(?string $fromname): self
    {
        $this->fromname = $fromname;

        return $this;
    }

    public function getFromemail(): ?string
    {
        return $this->fromemail;
    }

    public function setFromemail(?string $fromemail): self
    {
        $this->fromemail = $fromemail;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplatedata(): ?string
    {
        return $this->templatedata;
    }

    public function setTemplatedata(?string $templatedata): self
    {
        $this->templatedata = $templatedata;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }


}
