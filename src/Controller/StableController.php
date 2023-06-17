<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StableController extends AbstractController
{
    #[Route('/stable-diffusion', name: 'app_stable')]
    public function index(): Response
    {
        return $this->render('stable/index.html.twig', [
            'controller_name' => 'StableController',
        ]);
    }
}
