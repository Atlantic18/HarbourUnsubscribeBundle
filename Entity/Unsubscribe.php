<?php
namespace Harbour\UnsubscribeBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="harbour_unsubscribe", 
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="EmailGroupIndex", columns={"email","email_group"}),
 *         @ORM\UniqueConstraint(name="HashIndex", columns={"hash"})
 *     }
 * )
 */
class Unsubscribe
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $email_group;

    /**
     * @ORM\Column(type="string", unique=true, length=40, nullable=false)
     */
    private $hash;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $unsubscribed_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $unsubscribed_ip;

    /**
     * @ORM\ManyToOne(targetEntity="Coral\CoreBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Unsubscribe
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Unsubscribe
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set unsubscribed_at
     *
     * @param \DateTime $unsubscribedAt
     * @return Unsubscribe
     */
    public function setUnsubscribedAt($unsubscribedAt)
    {
        $this->unsubscribed_at = $unsubscribedAt;

        return $this;
    }

    /**
     * Get unsubscribed_at
     *
     * @return \DateTime
     */
    public function getUnsubscribedAt()
    {
        return $this->unsubscribed_at;
    }

    /**
     * Set unsubscribed_ip
     *
     * @param integer $unsubscribedIp
     * @return Unsubscribe
     */
    public function setUnsubscribedIp($unsubscribedIp)
    {
        $this->unsubscribed_ip = $unsubscribedIp;

        return $this;
    }

    /**
     * Get unsubscribed_ip
     *
     * @return integer
     */
    public function getUnsubscribedIp()
    {
        return $this->unsubscribed_ip;
    }

    /**
     * Set Account
     *
     * @param \Coral\CoreBundle\Entity\Account $account
     * @return Unsubscribe
     */
    public function setAccount(\Coral\CoreBundle\Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get Account
     *
     * @return \Coral\CoreBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set email_group
     *
     * @param string $emailGroup
     * @return Unsubscribe
     */
    public function setEmailGroup($emailGroup)
    {
        $this->email_group = $emailGroup;

        return $this;
    }

    /**
     * Get email_group
     *
     * @return string
     */
    public function getEmailGroup()
    {
        return $this->email_group;
    }
}