<?php

namespace App\Controller;

use App\Form\GiftListType;
use App\Entity\GiftList;
use App\Entity\Gift;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WishaGiftController extends Controller
{
    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        // build the form
        $giftList = new GiftList();
        $giftList->addGift(new Gift());

        $form = $this->createForm(GiftListType::class, $giftList);

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftList = $form->getData();
            // save data
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($giftList);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your create gift list.'
            );

            return $this->redirectToRoute('giftlist-admin', ['uuidadmin' => $giftList->getUuidAdmin()]);
        }

        return $this->render('giftlist/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{uuidadmin}", name="edit")
     * @param Request $request
     * @param string $uuidadmin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, string $uuidadmin)
    {

        $giftListEntity = $this->getDoctrine()
            ->getRepository(GiftList::class)
            ->findOneBy(['uuidAdmin' => $uuidadmin]);

        if (null === $giftListEntity) {
            throw $this->createNotFoundException();
        }

        // build the form
        $form = $this->createForm(GiftListType::class, $giftListEntity, [
            'allow_gift_editing' => $this->isEditingAllowed($giftListEntity)
        ]);

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $giftListEntity = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($giftListEntity);
            $entityManager->flush();

            return $this->redirectToRoute('giftlist-admin', ['uuidadmin' => $giftListEntity->getUuidAdmin()]);
        }

        return $this->render('giftlist/edit.html.twig', ['form' => $form->createView()]);
    }

    private function isEditingAllowed(GiftList $giftList): bool
    {
        foreach ($giftList->getGifts() as $gift) {
            if (null !== $gift->getReservedAt()) {
                return false;
            }
        }

        return true;
    }
}
