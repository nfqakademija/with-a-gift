<?php

namespace App\Service;

use App\Controller\GiftListsController;
use App\Entity\GiftList;
use Symfony\Component\HttpFoundation\Cookie;

class ReservedGiftCookieResolver
{
    public static function addGift(?string $cookieValue, $id, string $reserveToken): Cookie
    {
        $expireTime = time() + (365 * 24 * 60 * 60);

        if (null === $cookieValue) {
            $value = [$id => $reserveToken];

            return new Cookie(GiftListsController::RESERVED_GIFTS_COOKIE, json_encode($value), $expireTime);
        }

        $value = json_decode($cookieValue, true);

        if (!is_array($value)) {
            $value = [$id => $reserveToken];

            return new Cookie(GiftListsController::RESERVED_GIFTS_COOKIE, json_encode($value), $expireTime);
        }

        //var_dump($value);
        $value[$id] = $reserveToken;

        return new Cookie(GiftListsController::RESERVED_GIFTS_COOKIE, json_encode($value), $expireTime);
    }

    public static function removeGift(?string $cookieValue, $id): Cookie
    {
        $value = json_decode($cookieValue, true);
        unset($value[trim($id)]);

        return new Cookie(GiftListsController::RESERVED_GIFTS_COOKIE, json_encode($value));
    }

    public static function isReservedForTime(?string $cookie, $id, string $reserveToken, ?\DateTime $reservedAt): bool
    {
        if (null === $cookie) {
            return false;
        }

        $value = json_decode($cookie, true);

        if (!isset($value[trim($id)])) {
            return false;
        }

        if ($value[trim($id)] !== $reserveToken) {
            return false;
        }

        if (null === $reservedAt) {
            return true;
        }

        return new \DateTime('- 10 minutes') < $reservedAt;
    }

    public static function isReserved(?string $cookie, $id, string $reserveToken): bool
    {
        if (null === $cookie) {
            return false;
        }

        $value = json_decode($cookie, true);

        if (!isset($value[trim($id)])) {
            return false;
        }

        return ($value[trim($id)] === $reserveToken);
    }

    public static function hasReservedGifts(?string $cookie, GiftList $giftList): bool
    {
        foreach ($giftList->getGifts() as $gift) {
            if (null === $gift->getReservationToken()) {
                continue;
            }

            if (self::isReservedForTime($cookie, $gift->getId(), $gift->getReservationToken(), null)) {
                return true;
            }
        }

        return false;
    }
}
