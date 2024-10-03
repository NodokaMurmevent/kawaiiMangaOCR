<?php

namespace App\Controller;

use App\Form\TranslationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[Route('/translation', name: 'translation_')]
class TranslationController extends AbstractController
{
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    public HttpClientInterface $client;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, ValidatorInterface $validator): Response
    {

        $form = $this->createForm(TranslationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['attachment']->getData();
            $violations = $validator->validate(
                $file,
                new File([
                    'maxSize' => '10M',
                    'mimeTypes' => [
                        'text/plain',
                    ],
                ])
            );

            if ($violations->count() > 0) {
                $violation = $violations[0];
                $this->addFlash('error', $violation->getMessage());

                return $this->render('translation/index.html.twig', [
                    'controller_name' => 'DefaultController',
                    'form' => $form->createView(),
                ]);
            }

            $extension = $file->guessExtension();
            if ('txt' != $extension) {
                exit();
            }

            $file = $file->getContent();
            $formFields = [
                'target_lang' => 'FR',
                'auth_key' => $this->getParameter('app.deepl_key'),
                'formality' => 'less',
                'text' => $file,
            ];

            $formData = new FormDataPart($formFields);

            $response = $this->client->request('POST', 'https://api-free.deepl.com/v2/translate', [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            return $this->render('translation/result.html.twig', [
                'original' => $file,
                'translated' => $response->toArray(),
            ]);
        }

         return $this->render('translation/index.html.twig', [
            'controller_name' => 'TranslationController',
            'form' => $form->createView(),
        ]);
    }
}
