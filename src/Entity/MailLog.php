<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MailLog
 *
 * @ORM\Table(name="mail_log", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_932447AFCD7D381A", columns={"mailId"})})
 * @ORM\Entity
 */
class MailLog
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
     * @ORM\Column(name="mailId", type="string", length=130, nullable=false)
     */
    private $mailid;

    /**
     * @var array
     *
     * @ORM\Column(name="mTo", type="array", length=0, nullable=false)
     */
    private $mto;

    /**
     * @var array
     *
     * @ORM\Column(name="mFrom", type="array", length=0, nullable=false)
     */
    private $mfrom;

    /**
     * @var string|null
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var string|null
     *
     * @ORM\Column(name="body", type="text", length=0, nullable=true)
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="contentType", type="string", length=75, nullable=false)
     */
    private $contenttype;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var array|null
     *
     * @ORM\Column(name="replyTo", type="array", length=0, nullable=true)
     */
    private $replyto;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="text", length=0, nullable=false)
     */
    private $header;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="exception", type="text", length=0, nullable=true)
     */
    private $exception;

    /**
     * @var string|null
     *
     * @ORM\Column(name="templateId", type="string", length=50, nullable=true)
     */
    private $templateid;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=5, nullable=false)
     */
    private $language;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMailid(): ?string
    {
        return $this->mailid;
    }

    public function setMailid(string $mailid): self
    {
        $this->mailid = $mailid;

        return $this;
    }

    public function getMto(): ?array
    {
        return $this->mto;
    }

    public function setMto(array $mto): self
    {
        $this->mto = $mto;

        return $this;
    }

    public function getMfrom(): ?array
    {
        return $this->mfrom;
    }

    public function setMfrom(array $mfrom): self
    {
        $this->mfrom = $mfrom;

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

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getContenttype(): ?string
    {
        return $this->contenttype;
    }

    public function setContenttype(string $contenttype): self
    {
        $this->contenttype = $contenttype;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getReplyto(): ?array
    {
        return $this->replyto;
    }

    public function setReplyto(?array $replyto): self
    {
        $this->replyto = $replyto;

        return $this;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;

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

    public function getException(): ?string
    {
        return $this->exception;
    }

    public function setException(?string $exception): self
    {
        $this->exception = $exception;

        return $this;
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

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }


}
