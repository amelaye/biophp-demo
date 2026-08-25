<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Amelaye\BioPHP\Domain\Tools\Interfaces\OligosInterface;

use Amelaye\BioTools\Form\ChaosGameRepresentationType;
use Amelaye\BioTools\Service\ChaosGameRepresentationManager;


/**
 * Class ChaosGameRepresentationController
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ChaosGameRepresentationController extends AbstractController
{
    /**
     * @param   string                          $schema
     * @param   Request                         $request
     * @param   ChaosGameRepresentationManager  $chaosGameReprentationManager
     * @param   OligosInterface                 $oligosManager
     * @return  Response
     * @throws  \Exception
     */
    #[Route('/minitools/chaos-game-representation/{schema}', name: 'chaos_game_representation')]
    public function chaosGameRepresentationAction(
        string $schema,
        Request $request,
        ChaosGameRepresentationManager $chaosGameReprentationManager,
        OligosInterface $oligosManager
    )
    {
        if ($schema != "FCGR" && $schema != "CGR") {
            throw new \Exception("Please enter a valid format !");
        }

        $form = $this->createForm(ChaosGameRepresentationType::class);

        if ($schema == "FCGR") {
            return $this->fcgrCompute($request, $chaosGameReprentationManager, $form, $oligosManager);
        }

        return $this->cgrCompute($request, $chaosGameReprentationManager, $form);
    }


    /**
     * @param Request $request
     * @param ChaosGameRepresentationManager $chaosGameReprentationManager
     * @param $form
     * @return Response
     * @throws \Exception
     */
    public function cgrCompute(Request $request, ChaosGameRepresentationManager $chaosGameReprentationManager,
                               $form)
    {
        $isComputed = false;
        $image = null;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $image = basename(
                $chaosGameReprentationManager->CGRCompute($formData["seq_name"], $formData["seq"], $formData["size"])
            );
            $isComputed = true;
        }

        return $this->render(
            'minitools/chaosGameRepresentationCGR.html.twig',
            [
                'form'              => $form->createView(),
                'is_computed'       => $isComputed,
                'image'             => $image
            ]
        );
    }


    /**
     * @param Request                           $request
     * @param ChaosGameRepresentationManager    $chaosGameReprentationManager
     * @param OligosInterface                   $oligosManager
     * @param $form
     * @return Response
     * @throws \Exception
     */
    public function fcgrCompute(Request $request,
                                ChaosGameRepresentationManager $chaosGameReprentationManager, $form, OligosInterface $oligosManager)
    {
        $aOligos = $for_map = $image = null;
        $isMap = $isFreq = false;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $aSeqData = $chaosGameReprentationManager->FCGRCompute($formData["seq"], $formData["len"], $formData["s"]);
            $aNucleotides = $chaosGameReprentationManager->numberNucleos($aSeqData);

            // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
            //      frequencies are saved to an array named $aOligos
            $aOligos = $oligosManager->findOligos($aSeqData["sequence"], $aSeqData["length"]);

            // CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES IMAGE
            //      check the function for more info on parameters
            //      $result["map"] contains the data to be used to create the image map,
            //      $result["file"] the path of the written SVG
            $result = $chaosGameReprentationManager->createFCGRImage(
                $aOligos,
                $formData["seq_name"],
                $aNucleotides,
                strlen($formData["seq"]),
                $formData["s"],
                $formData["len"]
            );
            $for_map = $result["map"];
            $image = basename($result["file"]);

            $isMap = $formData["map"];
            $isFreq = $formData["freq"];

        }

        return $this->render(
            'minitools/chaosGameRepresentationFCGR.html.twig',
            [
                'form'              => $form->createView(),
                'oligos'            => $aOligos,
                'areas'             => $for_map,
                'image'             => $image,
                'is_map'            => $isMap,
                'show_as_freq'      => $isFreq
            ]
        );
    }
}
