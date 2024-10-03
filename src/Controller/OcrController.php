<?php

namespace App\Controller;

use App\Form\ZipUploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use \Knp\Snappy\Pdf;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Likelihood;
use Gregwar\ImageBundle\Services\ImageHandling;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ZipArchive;

#[Route('/ocr', name: 'ocr_')]
class OcrController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(Request $request, ValidatorInterface $validator): Response
    {
        $path = $this->getParameter('kernel.project_dir');

        $form = $this->createForm(ZipUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['attachment']->getData();

            $violations = $validator->validate(
                $file,
                new File([
                    'maxSize' => '50M',
                    'mimeTypes' => [
                        'application/zip'
                    ]
                ])
            );

            if ($violations->count() > 0) {
                /** @var ConstraintViolation $violation */
                $violation = $violations[0];
                $this->addFlash('error', $violation->getMessage());
                return $this->render('ocr/index.html.twig', [
                    'controller_name' => 'DefaultController',
                    'form' => $form->createView(),
                ]);
            }

            $extension = $file->guessExtension();
            if ($extension != "zip") {
                exit();
            }

            $uniqid = uniqid();     
            $file->move($path . "/public/uploads/", $uniqid. '.zip');
            return $this->redirectToRoute('ocr_request', ["fileName" => $uniqid,"extension" => $extension,"originalNameFile"=>$file->getClientOriginalName()]);
        }

        return $this->render('ocr/index.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView(),
        ]);
    }


    #[Route('/request/{fileName}/{extension}/{originalNameFile}', name: 'request')]
    public function request(ImageHandling $imageHandling, string $fileName, string $extension, string $originalNameFile, Pdf $knp): Response
    {       
        $path = $this->getParameter('kernel.project_dir');
        $filePath = $path . "/public/uploads/";
        $extractPath = $path . "/public/images/".$fileName;
        $archiveZip = $filePath.$fileName.".zip";
        $unidid = uniqid();

        $filesystem = new Filesystem();
        $filesystem->remove($path . "/public/images/generated/");  
        $filesystem->mkdir($path . "/public/images/generated/");

        $zip = new ZipArchive;
        if ($zip->open($archiveZip) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();           
        }

        $finder = new Finder();
        $finder->files()->in($path . "/public/images/".$fileName)->depth('== 0')->name(['*.png', '*.jpeg', '*.jpg'])->sortByName();

        $credentials = json_decode($this->getParameter('app.google_credentials_file'), true);
        $client = new ImageAnnotatorClient(["credentials"=>$credentials]);
        $pagesResult = [];

        $i=0;
        foreach ($finder as $file) {

            $image = file_get_contents($file->getRealPath());
            $response = $client->textDetection($image);
            $fichier = $response->getFullTextAnnotation();
            $pages = $fichier->getPages();

            foreach ($pages as $page) {
                $pagesResult[$i]["idPage"]= $i;
                $box = [];

                foreach ($page->getBlocks() as $key =>  $block) {

                    foreach ($block->getBoundingBox()->getVertices() as $k => $value) {

                        if ($k == 0) {
                            $box[$key]["start"]["X"] = $value->getX();
                            $box[$key]["start"]["Y"] = $value->getY();
                        } elseif ($k == 2) {
                            $box[$key]["end"]["X"] = $value->getX();
                            $box[$key]["end"]["Y"] = $value->getY();
                        }
                    }


                    foreach ($block->getParagraphs() as $klklt => $paragraph) {
                        $ligne = "";
                        foreach ($paragraph->getWords() as $klkld => $word) {

                            foreach ($word->getSymbols() as $klkl => $symbol) {
                                if ($klkld == 0 && $klkl == 0 && $klklt == 0) {

                                    $ligne .= $symbol->getText();
                                } else {

                                    $ligne .= strtolower($symbol->getText());
                                }
                            }

                            $ligne .= " ";                            
                        }

                        $ligne = str_replace("- ","",$ligne);
                        $pagesResult[$i]["blocks"][$key] = $ligne;
                    }

                }

                $imageFinale = $imageHandling->open($file->getRealPath());

                foreach ($box as $key => $coo) {
                    $imageFinale->write($path . "/public/impact.ttf", "Bloc N° " . $key, $coo["start"]["X"] - 2, $coo["start"]["Y"] + 12, 16, 0, "#FF0000", "right");
                    $imageFinale->rectangle($coo["start"]["X"], $coo["start"]["Y"], $coo["end"]["X"], $coo["end"]["Y"], "#FF0000");
                }

                $imageFinalPath = $path . "/public/images/generated/0" . $i . "-" . $unidid . '.jpeg';
                $imagePubilcFinalPath = "/images/generated/0" . $i . "-" . $unidid . '.jpeg';

                $imageFinale->save($imageFinalPath);

                $pagesResult[$i]["publicPathImage"] = $imagePubilcFinalPath;

            }

            $i++;
        }

        $client->close();

        $filesystem->remove($extractPath);   
        $date = new \DateTime("NOW");
        $filesystem->remove($archiveZip);               
        $uniqid = uniqid();

        $publicPath = "/static-page/". $date->format("Y") . "/" . $date->format("m") . "/";
        $localPath = $path . "/public". $publicPath;
        $nameFile = $uniqid;
        $url =  $publicPath . $nameFile;

        $originalNameFileWithoutExtension = substr($originalNameFile, 0, strrpos($originalNameFile, "."));
        $contents = $this->renderView('ocr/result.html.twig', [
            'originalNameFile'=> $originalNameFileWithoutExtension,
            'fileName' => $fileName,
            'extension' => $extension,
            'uniqid' => $uniqid,
            'filePath' => $filePath,
            'pages' => $pagesResult,
            "url" => $url
        ]);

        $contentsTxt = $this->renderView('ocr/result.txt.twig', [         
            'pages' => $pagesResult,
        ]);       

        $filesystem->mkdir($localPath);

        $filesystem->appendToFile($localPath . $nameFile . '.html', $contents);
        $filesystem->appendToFile($localPath . $nameFile . '.txt', $contentsTxt);

        return $this->redirect($url . '.html');
  
    }
}
