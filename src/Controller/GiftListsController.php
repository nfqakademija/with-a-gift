<?php

namespace App\Controller;

use App\Entity\GiftList;
use App\Entity\Gift;
use App\Form\EmailsType;
use App\Service\ReservedGiftCookieResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ramsey\Uuid\Uuid;

class GiftListsController extends Controller
{
    public const RESERVED_GIFTS_COOKIE = 'reserved_gifts';

    public function uuidUser(string $uuiduser)
    {
        $getUuidUser = $this->getDoctrine()
            ->getRepository(GiftList::class)
            ->findOneBy(['uuid' => $uuiduser]);
        return $getUuidUser;
    }

    /**
     * @Route("/giftlist/admin/{uuidadmin}", name="giftlist-admin")
     * @param Request $request
     * @param string $uuidadmin
     * @return Response
     */
    public function admin(Request $request, string $uuidadmin)
    {
        $giftListEntity = $this->getDoctrine()
            ->getRepository(GiftList::class)
            ->findOneBy(['uuidAdmin' => $uuidadmin]);

        if (!$giftListEntity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EmailsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data['url'] = $this->generateUrl('giftlist-user', ['uuiduser' => $giftListEntity->getUuid()]);
            $data['subject'] = 'Gifts';
            $data['admin'] = $giftListEntity;
            $data['emails'] = $form['emails']->getData();

            foreach ($data['emails'] as $email) {
                $this->shareWithFriends($email, $data);
            }
            $this->sendToAdmin($giftListEntity->getEmail(), $data);

            return $this->redirectToRoute('giftlist-admin', ['uuidadmin' => $uuidadmin]);
        }

        return $this->render('giftlist/admin.html.twig', [
            'data' => $giftListEntity,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/giftlist/{uuiduser}", name="giftlist-user")
     * @param string $uuiduser
     * @return Response
     */
    public function user(string $uuiduser)
    {
        $giftListEntity = $this->uuidUser($uuiduser);
        if (!$giftListEntity) {
            $this->addFlash(
                'danger',
                'Wishlist does not exist! Please, 
                check the URL and try again, if you believe the URL has a valid format.'
            );
            return $this->redirectToRoute('home');
        }

        return $this->render('giftlist/user.html.twig', ['data' => $giftListEntity]);
    }

    /**
     * @Route("/giftlist/{uuiduser}/reserve/{id}", name="gift-reserve")
     * @param $id
     * @param $uuiduser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function reserve(Request $request, $id, $uuiduser)
    {
        if (!$this->uuidUser($uuiduser)) {
            $this->addFlash(
                'warning',
                'Gift list was not found.'
            );
            return $this->redirectToRoute('home');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $giftById = $entityManager->getRepository(Gift::class)->findOneBy(['id' => $id]);
        if (!$giftById) {
            $this->addFlash(
                'warning',
                'This gift was not found.'
            );
            return $this->redirectToRoute('giftlist-user', ['uuiduser' => $uuiduser]);
        }

        $giftByReservedAt = $entityManager->getRepository(Gift::class)->findOneBy(['reservedAt' => null]);
        if (!$giftByReservedAt->getReservedAt() === null) {
            $this->addFlash(
                'warning',
                'This gift is already reserved.'
            );
            return $this->redirectToRoute('giftlist-user', ['uuiduser' => $uuiduser]);
        }

        $this->addFlash(
            'warning',
            'Be careful and think twice! You have 10 minutes to undo your reservation.'
        );

        $reservationToken = Uuid::uuid4()->toString();
        $response = new RedirectResponse($this->generateUrl('giftlist-user', ['uuiduser' => $uuiduser]));

        $cookie = $request->cookies->get(self::RESERVED_GIFTS_COOKIE);

        $giftList = $giftById->getGiftList();

        if (ReservedGiftCookieResolver::hasReservedGifts($cookie, $giftList)) {
            $this->addFlash(
                'warning',
                'You have already reserved more than one gift. Leave some for others!'
            );
        }
        $cookie = ReservedGiftCookieResolver::addGift($cookie, $id, $reservationToken);
        $response->headers->setCookie($cookie);

        $giftById->setReservedAt(new \DateTime());
        $giftById->setReservationToken($reservationToken);
        $entityManager->flush();

        return $response;
    }

    /**
     * @Route("/giftlist/{uuiduser}/unreserve/{id}", name="gift-unreserve")
     * @param $id
     * @param $uuiduser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unreserve(Request $request, $id, $uuiduser)
    {
        if (!$this->uuidUser($uuiduser)) {
            $this->addFlash(
                'warning',
                'Gift list was not found.'
            );
            return $this->redirectToRoute('home');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $giftById = $entityManager->getRepository(Gift::class)->find($id);
        if (!$giftById) {
            $this->addFlash(
                'warning',
                'No gift found.'
            );
            return $this->redirectToRoute('giftlist-user', ['uuiduser' => $uuiduser]);
        }

        $cookie = $request->cookies->get(self::RESERVED_GIFTS_COOKIE);
        if (!ReservedGiftCookieResolver::
        isReservedForTime($cookie, $id, $giftById->getReservationToken(), $giftById->getReservedAt())) {
            $this->addFlash(
                'warning',
                'This is not yours reservation.'
            );
            return $this->redirectToRoute('giftlist-user', ['uuiduser' => $uuiduser]);
        }
        $cookie = ReservedGiftCookieResolver::removeGift($cookie, $id);


        $response = new RedirectResponse($this->generateUrl('giftlist-user', ['uuiduser' => $uuiduser]));

        $response->headers->setCookie($cookie);
        $giftById->setReservedAt(null);
        $giftById->setReservationToken(null);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your reservation has been canceled! Pick another gift!'
        );

        return $response;
    }

    private function shareWithFriends($emails, $data)
    {
        $message = (new \Swift_Message($data['subject']))
            ->setFrom('wishagift@gmail.com')
            ->setTo($emails)
            ->setBody(
                $this->renderView(
                    'emails/sharewithfriends.html.twig',
                    ['data' => $data]
                ),
                'text/html'
            );

        $this->get('mailer')->send($message);
    }

    private function sendToAdmin($emails, $data)
    {
        $message = (new \Swift_Message($data['subject']))
            ->setFrom('wishagift@gmail.com')
            ->setTo($emails)
            ->setBody(
                $this->renderView(
                    'emails/sendtoadmin.html.twig',
                    ['data' => $data]
                ),
                'text/html'
            );

        $this->get('mailer')->send($message);
    }
}
