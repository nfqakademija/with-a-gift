<?php

namespace App\Twig;


use App\Entity\Gift;
use App\Service\ReservedGiftCookieResolver;

class MainExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isGiftReservedByMe', array($this, 'isGiftReservedByMe')),
            new \Twig_SimpleFunction('isGiftReservedForTime', array($this, 'isGiftReservedForTime')),
        );
    }

    public function isGiftReservedByMe(Gift $gift, ?string $cookie): bool
    {
        return ReservedGiftCookieResolver::isReserved($cookie, $gift->getId(), $gift->getReservationToken());
    }

    public function isGiftReservedForTime(Gift $gift, ?string $cookie): bool
    {
        return ReservedGiftCookieResolver::isReservedForTime($cookie, $gift->getId(), $gift->getReservationToken(), $gift->getReservedAt());
    }
}