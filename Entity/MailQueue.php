<?php

namespace Cgonser\SwiftMailerDatabaseS3SpoolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * MailQueue
 *
 * @ORM\Table(name="cgonser_mail_queue", indexes={
 *      @ORM\Index(name="cgonser_mail_queue_queued_at_idx", columns={"queued_at"}),
 *      @ORM\Index(name="cgonser_mail_queue_sent_at_idx", columns={"sent_at"}),
 *      @ORM\Index(name="cgonser_mail_queue_send_at_idx", columns={"send_at"}),
 *      @ORM\Index(name="cgonser_mail_queue_started_at_idx", columns={"started_at"})
 * })
 * @ORM\Entity(repositoryClass="Cgonser\SwiftMailerDatabaseS3SpoolBundle\Repository\MailQueueRepository")
 */
class MailQueue
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sender", type="text")
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient", type="string", length=255)
     */
    private $recipient;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="text", nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="cc", type="text", nullable=true)
     */
    private $cc;

    /**
     * @var string
     *
     * @ORM\Column(name="bcc", type="text", nullable=true)
     */
    private $bcc;

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="text", nullable=true)
     */
    private $errorMessage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="queued_at", type="datetime", nullable=true)
     */
    private $queuedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     */
    private $startedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="send_at", type="datetime", nullable=true)
     */
    private $sendAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    private $sentAt;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sender
     *
     * @param string $sender
     *
     * @return MailQueue
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     *
     * @return MailQueue
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return MailQueue
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set cc
     *
     * @param string $cc
     *
     * @return MailQueue
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * Get cc
     *
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set bcc
     *
     * @param string $bcc
     *
     * @return MailQueue
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * Get bcc
     *
     * @return string
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set startedAt
     *
     * @param \DateTime $startedAt
     *
     * @return MailQueue
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set sendAt
     *
     * @param \DateTime $sendAt
     *
     * @return MailQueue
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    /**
     * Get sendAt
     *
     * @return \DateTime
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * Set sentAt
     *
     * @param \DateTime $sentAt
     *
     * @return MailQueue
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set errorMessage
     *
     * @param string $errorMessage
     *
     * @return MailQueue
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * Get errorMessage
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set queuedAt
     *
     * @param \DateTime $queuedAt
     *
     * @return MailQueue
     */
    public function setQueuedAt($queuedAt)
    {
        $this->queuedAt = $queuedAt;

        return $this;
    }

    /**
     * Get queuedAt
     *
     * @return \DateTime
     */
    public function getQueuedAt()
    {
        return $this->queuedAt;
    }
}
