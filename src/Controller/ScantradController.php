<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MatomoTracker;

class ScantradController extends AbstractController
{
    #[Route('/tuto-scantrad-traduction-manga', name: 'app_scantrad_index')]
    public function index(): Response
    {

  
        if ('prod' == $_ENV['APP_ENV']) {
            $matomoSiteId = 61;
            $matomoUrl = 'https://piwik.imago-design.net';
            $matomoToken = 'adf36114f2965ae516ac5cdd9e3a24e1';

            $matomoPageTitle = 'Juste un autre tuto de scantrad pour traduire ce que dis votre Waifu | Ni~ Coni Traduiiiit !';
            $matomoTracker = new MatomoTracker((int) $matomoSiteId, $matomoUrl);
            $matomoTracker->setTokenAuth($matomoToken);
            $matomoTracker->doTrackPageView($matomoPageTitle);
        }
        
        return $this->render('scantrad/index.html.twig');
    }
}
