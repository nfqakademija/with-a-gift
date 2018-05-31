<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GiftRepository")
 */
class Gift
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reservedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $reservationToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reservable = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GiftList", inversedBy="gifts")
     * @ORM\JoinColumn(name="gift_list_id", referencedColumnName="id", nullable=false)
     */
    private $giftList;

    /**
     * @return GiftList|null
     */
    public function getGiftList(): ?GiftList
    {
        return $this->giftList;
    }

    /**
     * @param GiftList|null $giftList
     * @return Gift
     */
    public function setGiftList(?GiftList $giftList): self
    {
        $this->giftList = $giftList;
        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getReservedAt()
    {
        return $this->reservedAt;
    }

    /**
     * @param mixed $reservedAt
     */
    public function setReservedAt($reservedAt): void
    {
        $this->reservedAt = $reservedAt;
    }

    /**
     * @return mixed
     */
    public function getReservationToken()
    {
        return $this->reservationToken;
    }

    /**
     * @param mixed $reservationToken
     */
    public function setReservationToken($reservationToken): void
    {
        $this->reservationToken = $reservationToken;
    }

    /**
     * @return mixed
     */
    public function getReservable()
    {
        return $this->reservable;
    }

    /**
     * @param mixed $reservable
     */
    public function setReservable($reservable): void
    {
        $this->reservable = $reservable;
    }

}