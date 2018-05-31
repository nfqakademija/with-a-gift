<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\GiftList;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index()
    {
        $giftLists = $this->getDoctrine()
            ->getRepository(GiftList::class)
            ->getPublicGiftLists(3);

         return $this->render('home/index.html.twig', ['data' => $giftLists]);
    }
}
