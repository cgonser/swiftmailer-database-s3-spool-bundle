<?php

namespace Cgonser\SwiftMailerDatabaseS3SpoolBundle\Spool;

use Swift_Mime_Message;
use Swift_Transport;
use Swift_ConfigurableSpool;
use Swift_IoException;
use Aws\S3\S3Client;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

class DatabaseS3Spool extends Swift_ConfigurableSpool
{
    /**
     * @var S3Client
     */
    protected $s3Client;

    /**
     * @var string
     */
    protected $s3Bucket;

    /**
     * @var string
     */
    protected $s3Folder;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Swift_Transport
     */
    protected $transport;

    /**
     * Interval between each retry
     *
     * @var integer
     */
    protected $retryInterval = 0;

    /**
     * @param string    $s3Config
     * @param string    $entityClass
     * @param Registry  $doctrine
     */

    public function __construct($s3Config, $entityClass, Registry $doctrine)
    {
        $this->s3Bucket = $s3Config['bucket'];
        unset ($s3Config['bucket']);

        if (isset($s3Config['folder'])) {
            $this->s3Folder = $s3Config['folder'];
            unset ($s3Config['folder']);
        }

        $this->s3Client = new S3Client($s3Config);
        
        $this->doctrine = $doctrine;
        $this->entityClass = $entityClass;
        $this->entityManager = $this->doctrine->getManagerForClass($this->entityClass);
    }

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Starts this Spool mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Spool mechanism.
     */
    public function stop()
    {
    }

    /**
     * Queues a message.
     *
     * @param Swift_Mime_Message $message The message to store
     *
     * @return bool
     */
    public function queueMessage(Swift_Mime_Message $message)
    {
        $object = new $this->entityClass;

        $from = $this->sanitizeAddresses(array_keys($message->getFrom()))[0];
        $recipient = $this->sanitizeAddresses(array_keys($message->getTo()));

        $object->setSubject($message->getSubject());
        $object->setSender($from);
        $object->setRecipient(implode(';', $recipient));

        if ($cc = $message->getCc()) {
            $object->setCc(implode(';', $this->sanitizeAddresses(array_keys($cc))));
        }
        
        if ($bcc = $message->getBcc()) {
            $object->setBcc(implode(';', $this->sanitizeAddresses(array_keys($bcc))));
        }

        $object->setQueuedAt(new \DateTime());
        
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        return $this->s3StoreMessage($object->getId(), $message);
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param Swift_Transport $transport        A transport instance
     * @param string[]        $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent e-mail's
     */
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        $this->transport = $transport;
        $this->failedRecipients = (array) $failedRecipients;

        $count = $this->sendMessages('unsent');
        $count += $this->sendMessages('retries');

        return $count;
    }

    /**
     * Sends messages using the given transport instance and status.
     *
     * @param string    $status    Message status
     *
     * @return int The number of sent e-mail's
     */
    protected function sendMessages($status)
    {
        $queuedMessages = $this->fetchMessages($status);

        if (!$queuedMessages || count($queuedMessages) == 0) {
            return 0;
        }

        if (!$this->transport->isStarted()) {
            $this->transport->start();
        }

        $startTime = time();
        $count = 0;

        foreach ($queuedMessages as $mailQueueObject) {
            $mailQueueObject->setStartedAt(new \DateTime());
            $this->entityManager->persist($mailQueueObject);
        }
        $this->entityManager->flush();

        foreach ($queuedMessages as $mailQueueObject) {
            $count += $this->sendMessage($mailQueueObject);

            if ($this->getTimeLimit() && (time() - $startTime) >= $this->getTimeLimit()) {
                break;
            }
        }
        $this->entityManager->flush();

        return $count;
    }

    /**
     * Sends a message
     *
     * @param MailQueue   $mailQueueObject
     *
     * @return int The number of sent e-mail's
     */
    protected function sendMessage($mailQueueObject)
    {
        try {
            $message = $this->s3RetrieveMessage($mailQueueObject->getId());

            $count = $this->transport->send($message, $this->failedRecipients);
            $mailQueueObject->setSentAt(new \DateTime());

            $this->entityManager->persist($mailQueueObject);
            $this->s3ArquiveMessage($mailQueueObject->getId());
        } catch (Swift_IoException $e) {
            $mailQueueObject->setErrorMessage($e->getMessage());
            $this->entityManager->persist($mailQueueObject);
            $count = 0;
        }

        return $count;
    }

    /**
     * Sends a message
     *
     * @param string   $status   status of the messages to fetch
     *
     * @return MailQueue[]
     */
    protected function fetchMessages($status = 'unsent')
    {
        $qb = $this->entityManager->getRepository($this->entityClass)
            ->createQueryBuilder('m')
            ->addOrderBy('m.queuedAt', 'ASC');

        if ($this->getMessageLimit()) {
            $qb->setMaxResults($this->getMessageLimit());
        }

        switch ($status) {
            case 'unsent':
                $qb->where('m.sentAt IS NULL')
                    ->andWhere('( m.sendAt IS NULL OR m.sendAt <= :now )')
                    ->andWhere('m.startedAt IS NULL')
                    ->setParameter('now', new \DateTime);
                break;
            case 'retries':
                $errorThreshold = new \DateTime($this->retryInterval.' minutes ago');
                $qb->where('m.sentAt IS NULL')
                    ->andWhere('m.startedAt IS NOT NULL')
                    ->andWhere('m.startedAt <= :errorThreshold')
                    ->setParameter('errorThreshold', $errorThreshold);
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Stores serialized message on S3.
     *
     * @param Integer $messageId The message ID
     * @param Swift_Mime_Message $message The message to store
     *
     * @return bool
     */
    protected function s3StoreMessage($messageId, $message)
    {
        $key = $messageId.'.msg';

        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $this->s3Folder.'/'.$key,
                'Body'   => serialize($message),
                'ACL'    => 'private'
            ]);
        } catch (Exception $e) {
            throw new Swift_IoException(sprintf('Unable to store message "%s" in S3 Bucket "%s".', 
                $messageId, $this->s3Bucket));
        }

        return true;
    }

    /**
     * Retrieves serialized message from S3.
     *
     * @param Integer $messageId The message ID
     *
     * @return Swift_Message
     */
    protected function s3RetrieveMessage($messageId)
    {
        $key = $messageId.'.msg';

        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $this->s3Folder.'/'.$key
            ]);

            return unserialize($result['Body']);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            throw new Swift_IoException(sprintf('Unable to retrieve message "%s" from S3 Bucket "%s".', 
                $messageId, $this->s3Bucket));            
        } catch (Exception $e) {
            throw new Swift_IoException(sprintf('Unable to retrieve message "%s" from S3 Bucket "%s".', 
                $messageId, $this->s3Bucket));
        }
    }

    /**
     * Arquives serialized message on S3 sent messages folder.
     *
     * @param Integer $messageId The message ID
     */
    protected function s3ArquiveMessage($messageId)
    {
        $sourceKey = $messageId.'.msg';
        $targetKey = 'sent/'.date('Y/m/d').'/'.$messageId.'.msg';

        try {
            $copySource = $this->s3Bucket.'/';
            if ($this->s3Folder) {
                $copySource .= $this->s3Folder.'/';
            }
            $copySource .= $sourceKey;

            $this->s3Client->copyObject([
                'Bucket'     => $this->s3Bucket,
                'Key'        => $this->s3Folder.'/'.$targetKey,
                'CopySource' => $copySource,
            ]);

            $this->s3Client->deleteObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $this->s3Folder.'/'.$sourceKey
            ]);
        } catch (Exception $e) {
            throw new Swift_IoException(sprintf('Unable to arquive message "%s" in S3 Bucket "%s".', 
                $messageId, $this->s3Bucket));
        }
    }

    /**
     * Sanitizes addresses and filters out invalid emails
     *
     * @param string[] $addresses
     *
     * @return string[]
     */
    protected function sanitizeAddresses($addresses)
    {
        // returns resulting array, excluding invalid addresses
        return array_filter(array_map(
            function($email) {
                // sanitizes emails and excludes the invalid ones
                return filter_var(filter_var(trim($email), FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) ?: false;
            },
            (array) $addresses
        ));
    }
}
