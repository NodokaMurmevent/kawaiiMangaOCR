<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MatomoTracker;

class StableController extends AbstractController
{
    #[Route('/stable-diffusion', name: 'app_stable')]
    public function index(): Response
    {

        $matomoSiteId = 61;
        $matomoUrl = "https://piwik.imago-design.net"; 
        $matomoToken = "adf36114f2965ae516ac5cdd9e3a24e1";        
  
        $matomoPageTitle = "Another beginner guide to generate your local Waifu | Ni~ Coni CoNiiiiiifusion";
        $matomoTracker = new MatomoTracker((int)$matomoSiteId, $matomoUrl);
        $matomoTracker->setTokenAuth($matomoToken);
        $matomoTracker->doTrackPageView($matomoPageTitle);

        return $this->render('stable/index.html.twig', [
            'controller_name' => 'StableController',
        ]);
    }
}
