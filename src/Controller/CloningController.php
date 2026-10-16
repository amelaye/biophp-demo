<?php
/**
 * Code samples controller
 * New in biophp 2.0: the Cloning domain - circular plasmids and their features, restriction digests
 * and the ends they leave, Gibson assembly, and GenBank / GFF3 / BED export.
 * Every sample works on pUC19 (GenBank L09137, public/data/pUC19.gb).
 */
namespace App\Controller;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\BedFeatureWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\FeatureOverlapInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\GenbankPlasmidMapperInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\GenbankWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\GffFeatureWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\GibsonAssemblyInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\RestrictionDigestInterface;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\RestrictionEndCompatibilityInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\RestrictionEnzymeCatalogInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\AlternateGeneticCodeTranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CodeHighlighter;

/**
 * Class CloningController
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CloningController extends AbstractController
{
    /**
     * Start of the EGFP coding sequence (MVSKGEELFTGVVPILVELD), the insert of the Gibson sample
     */
    private const EGFP_START = "ATGGTGAGCAAGGGCGAGGAGCTGTTCACCGGGGTGGTGCCCATCCTGGTCGAGCTGGAC";

    /**
     * pUC19 read from its GenBank record, then annotated
     * L09137 only annotates its source: the features below are those its COMMENT describes
     * (polylinker 396-452, lac region, Ap-R), with the full open reading frames of lacZ-alpha
     * and of the beta-lactamase, both on the complementary strand.
     * @param   GenbankPlasmidMapperInterface   $genbankPlasmidMapper
     * @return  Plasmid
     * @throws  \Exception
     */
    private function loadPuc19(GenbankPlasmidMapperInterface $genbankPlasmidMapper): Plasmid
    {
        $parser = DatabaseParserFactory::createParser("GENBANK");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/pUC19.gb'));

        $oPlasmid = $genbankPlasmidMapper
            ->map($parser->getSequence(), $parser->getGbSequence(), $parser->getFeatures())
            ->getPlasmid();

        return $oPlasmid
            ->withFeature(new PlasmidFeature("lacZ-alpha", FeatureType::CDS, 146, 469, Strand::REVERSE, "#2E86C1", null, null, null, 0))
            ->withFeature(new PlasmidFeature("MCS", FeatureType::MISC_FEATURE, 396, 452, Strand::NONE, "#F39C12",
                "HindIII-SphI-PstI-SalI-XbaI-BamHI-SmaI-KpnI-SacI-EcoRI"))
            ->withFeature(new PlasmidFeature("bla", FeatureType::CDS, 1626, 2486, Strand::REVERSE, "#C0392B",
                "Ampicillin resistance, beta-lactamase TEM-1", null, null, 0));
    }

    /**
     * @param   GenbankPlasmidMapperInterface               $genbankPlasmidMapper
     * @param   FeatureOverlapInterface                     $featureOverlapManager
     * @param   AlternateGeneticCodeTranslatorInterface     $geneticCodeTranslator
     * @return  Response
     * @throws  \Exception
     */
    #[Route('/cloning-plasmid', name: 'cloning_plasmid')]
    public function plasmid(
        GenbankPlasmidMapperInterface $genbankPlasmidMapper,
        FeatureOverlapInterface $featureOverlapManager,
        AlternateGeneticCodeTranslatorInterface $geneticCodeTranslator
    ) {
        $oPlasmid = $this->loadPuc19($genbankPlasmidMapper);

        // Translate the beta-lactamase with the bacterial genetic code (NCBI table 11)
        $oBla = $oPlasmid->getFeaturesByType(FeatureType::CDS)[1];
        $sBlaDna = $oPlasmid->extractFeatureSequence($oBla)->getValue();
        $sBlaProtein = "";
        foreach (str_split($sBlaDna, 3) as $sCodon) {
            $sBlaProtein .= $geneticCodeTranslator->translateCodon($sCodon, 11);
        }

        // Which features cover the EcoRI site (396), and which ones overlap each other?
        $aAtEcoRI = $featureOverlapManager->findFeaturesAtPosition($oPlasmid, 396);
        $aOverlaps = $featureOverlapManager->findOverlappingPairs($oPlasmid);

        // Move the origin into bla: the feature now crosses it, and keeps its sequence
        $oRotated = $oPlasmid->rotateToOrigin(2000);

        $sCode =
'private function loadPuc19(GenbankPlasmidMapperInterface $genbankPlasmidMapper): Plasmid
{
    $parser = DatabaseParserFactory::createParser("GENBANK");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/pUC19.gb\'));

    // A circular GenBank record becomes a Plasmid
    $oPlasmid = $genbankPlasmidMapper
        ->map($parser->getSequence(), $parser->getGbSequence(), $parser->getFeatures())
        ->getPlasmid();

    // L09137 only annotates its source: add the features its COMMENT describes
    return $oPlasmid
        ->withFeature(new PlasmidFeature("lacZ-alpha", FeatureType::CDS, 146, 469, Strand::REVERSE, "#2E86C1", null, null, null, 0))
        ->withFeature(new PlasmidFeature("MCS", FeatureType::MISC_FEATURE, 396, 452, Strand::NONE, "#F39C12",
            "HindIII-SphI-PstI-SalI-XbaI-BamHI-SmaI-KpnI-SacI-EcoRI"))
        ->withFeature(new PlasmidFeature("bla", FeatureType::CDS, 1626, 2486, Strand::REVERSE, "#C0392B",
            "Ampicillin resistance, beta-lactamase TEM-1", null, null, 0));
}

public function plasmid(
    GenbankPlasmidMapperInterface $genbankPlasmidMapper,
    FeatureOverlapInterface $featureOverlapManager,
    AlternateGeneticCodeTranslatorInterface $geneticCodeTranslator
) {
    $oPlasmid = $this->loadPuc19($genbankPlasmidMapper);

    // Translate the beta-lactamase with the bacterial genetic code (NCBI table 11)
    $oBla = $oPlasmid->getFeaturesByType(FeatureType::CDS)[1];
    $sBlaDna = $oPlasmid->extractFeatureSequence($oBla)->getValue(); // reverse complemented
    $sBlaProtein = "";
    foreach (str_split($sBlaDna, 3) as $sCodon) {
        $sBlaProtein .= $geneticCodeTranslator->translateCodon($sCodon, 11);
    }

    // Which features cover the EcoRI site (396), and which ones overlap each other?
    $aAtEcoRI = $featureOverlapManager->findFeaturesAtPosition($oPlasmid, 396);
    $aOverlaps = $featureOverlapManager->findOverlappingPairs($oPlasmid);

    // Move the origin into bla: the feature now crosses it, and keeps its sequence
    $oRotated = $oPlasmid->rotateToOrigin(2000);

    return $this->render(\'default/cloningPlasmid.html.twig\', [
        \'plasmid\'     => $oPlasmid,
        \'blaProtein\'  => $sBlaProtein,
        \'atEcoRI\'     => $aAtEcoRI,
        \'overlaps\'    => $aOverlaps,
        \'rotated\'     => $oRotated,
    ]);
}';

        return $this->render('default/cloningPlasmid.html.twig', [
            'plasmid'     => $oPlasmid,
            'blaProtein'  => $sBlaProtein,
            'atEcoRI'     => $aAtEcoRI,
            'overlaps'    => $aOverlaps,
            'rotated'     => $oRotated,
            'code'        => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   GenbankPlasmidMapperInterface           $genbankPlasmidMapper
     * @param   RestrictionEnzymeCatalogInterface       $enzymeCatalog
     * @param   RestrictionDigestInterface              $restrictionDigest
     * @param   RestrictionEndCompatibilityInterface    $endCompatibility
     * @return  Response
     * @throws  \Exception
     */
    #[Route('/cloning-restriction-digest', name: 'cloning_restriction_digest')]
    public function restrictionDigest(
        GenbankPlasmidMapperInterface $genbankPlasmidMapper,
        RestrictionEnzymeCatalogInterface $enzymeCatalog,
        RestrictionDigestInterface $restrictionDigest,
        RestrictionEndCompatibilityInterface $endCompatibility
    ) {
        $oPlasmid = $this->loadPuc19($genbankPlasmidMapper);

        // Opening the polylinker with two enzymes leaves two fragments with different sticky ends
        $oDouble = $restrictionDigest->digest($oPlasmid, [
            $enzymeCatalog->getByName("EcoRI"),
            $enzymeCatalog->getByName("HindIII"),
        ]);

        // PvuII cuts twice, on both sides of the polylinker, and leaves blunt ends
        $oPvuII = $restrictionDigest->digest($oPlasmid, [$enzymeCatalog->getByName("PvuII")]);

        // Can these ends be ligated together?
        $oEcoRIEnd = $oDouble->getCuts()[0]->getEnd();
        $oHindIIIEnd = $oDouble->getCuts()[1]->getEnd();
        $aCompatibility = [
            "EcoRI / HindIII" => $endCompatibility->checkCompatibility($oEcoRIEnd, $oHindIIIEnd),
            "EcoRI / MfeI"    => $endCompatibility->checkCompatibility($oEcoRIEnd, new RestrictionEnd(RestrictionEnd::FIVE_PRIME, "AATT")),
            "BamHI / BglII"   => $endCompatibility->checkCompatibility(
                new RestrictionEnd(RestrictionEnd::FIVE_PRIME, "GATC"),
                new RestrictionEnd(RestrictionEnd::FIVE_PRIME, "GATC")
            ),
            "PvuII / EcoRV"   => $endCompatibility->checkCompatibility(
                $oPvuII->getCuts()[0]->getEnd(),
                new RestrictionEnd(RestrictionEnd::BLUNT)
            ),
        ];

        $sCode =
'public function restrictionDigest(
    GenbankPlasmidMapperInterface $genbankPlasmidMapper,
    RestrictionEnzymeCatalogInterface $enzymeCatalog,
    RestrictionDigestInterface $restrictionDigest,
    RestrictionEndCompatibilityInterface $endCompatibility
) {
    $oPlasmid = $this->loadPuc19($genbankPlasmidMapper); // see "Plasmid from GenBank"

    // Opening the polylinker with two enzymes leaves two fragments with different sticky ends
    $oDouble = $restrictionDigest->digest($oPlasmid, [
        $enzymeCatalog->getByName("EcoRI"),
        $enzymeCatalog->getByName("HindIII"),
    ]);

    // PvuII cuts twice, on both sides of the polylinker, and leaves blunt ends
    $oPvuII = $restrictionDigest->digest($oPlasmid, [$enzymeCatalog->getByName("PvuII")]);

    // Can these ends be ligated together?
    $oEcoRIEnd = $oDouble->getCuts()[0]->getEnd();
    $oHindIIIEnd = $oDouble->getCuts()[1]->getEnd();
    $aCompatibility = [
        "EcoRI / HindIII" => $endCompatibility->checkCompatibility($oEcoRIEnd, $oHindIIIEnd),
        "EcoRI / MfeI"    => $endCompatibility->checkCompatibility($oEcoRIEnd, new RestrictionEnd(RestrictionEnd::FIVE_PRIME, "AATT")),
        "BamHI / BglII"   => $endCompatibility->checkCompatibility(
            new RestrictionEnd(RestrictionEnd::FIVE_PRIME, "GATC"),
            new RestrictionEnd(RestrictionEnd::FIVE_PRIME, "GATC")
        ),
        "PvuII / EcoRV"   => $endCompatibility->checkCompatibility(
            $oPvuII->getCuts()[0]->getEnd(),
            new RestrictionEnd(RestrictionEnd::BLUNT)
        ),
    ];

    return $this->render(\'default/cloningRestrictionDigest.html.twig\', [
        \'digests\'       => ["EcoRI + HindIII" => $oDouble, "PvuII" => $oPvuII],
        \'compatibility\' => $aCompatibility,
    ]);
}';

        return $this->render('default/cloningRestrictionDigest.html.twig', [
            'digests'       => ["EcoRI + HindIII" => $oDouble, "PvuII" => $oPvuII],
            'compatibility' => $aCompatibility,
            'code'          => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   GenbankPlasmidMapperInterface   $genbankPlasmidMapper
     * @param   GibsonAssemblyInterface         $gibsonAssembly
     * @return  Response
     * @throws  \Exception
     */
    #[Route('/cloning-gibson-assembly', name: 'cloning_gibson_assembly')]
    public function gibsonAssembly(
        GenbankPlasmidMapperInterface $genbankPlasmidMapper,
        GibsonAssemblyInterface $gibsonAssembly
    ) {
        $oPlasmid = $this->loadPuc19($genbankPlasmidMapper);

        // The vector, read up to the EcoRI site of the polylinker (position 396), and the insert
        $oVector = $oPlasmid->getSequence()->sliceCircular(395 - 40, 40);
        $oInsert = new DnaSequence(self::EGFP_START);

        // Before PCR, the two pieces share nothing
        $oBefore = $gibsonAssembly->checkJunction($oVector, $oInsert, 15, 40);

        // 20 bp of homology, half carried by each primer
        $oArms = $gibsonAssembly->designHomologyArms($oVector, $oInsert, 20);

        // The PCR products: the tails are added at the 5' end of each primer
        $oVectorPcr = new DnaSequence($oVector->getValue()
            . (new DnaSequence($oArms->getUpstreamReversePrimerTail()))->reverseComplement()->getValue());
        $oInsertPcr = new DnaSequence($oArms->getDownstreamForwardPrimerTail() . $oInsert->getValue());

        $oAfter = $gibsonAssembly->checkJunction($oVectorPcr, $oInsertPcr, 15, 40);

        $sCode =
'public function gibsonAssembly(
    GenbankPlasmidMapperInterface $genbankPlasmidMapper,
    GibsonAssemblyInterface $gibsonAssembly
) {
    $oPlasmid = $this->loadPuc19($genbankPlasmidMapper); // see "Plasmid from GenBank"

    // The vector, read up to the EcoRI site of the polylinker (position 396), and the insert
    $oVector = $oPlasmid->getSequence()->sliceCircular(395 - 40, 40);
    $oInsert = new DnaSequence("ATGGTGAGCAAGGGCGAGGAGCTGTTCACCGGGGTGGTGCCCATCCTGGTCGAGCTGGAC"); // EGFP

    // Before PCR, the two pieces share nothing
    $oBefore = $gibsonAssembly->checkJunction($oVector, $oInsert, 15, 40);

    // 20 bp of homology, half carried by each primer
    $oArms = $gibsonAssembly->designHomologyArms($oVector, $oInsert, 20);

    // The PCR products: the tails are added at the 5\' end of each primer
    $oVectorPcr = new DnaSequence($oVector->getValue()
        . (new DnaSequence($oArms->getUpstreamReversePrimerTail()))->reverseComplement()->getValue());
    $oInsertPcr = new DnaSequence($oArms->getDownstreamForwardPrimerTail() . $oInsert->getValue());

    $oAfter = $gibsonAssembly->checkJunction($oVectorPcr, $oInsertPcr, 15, 40);

    return $this->render(\'default/cloningGibsonAssembly.html.twig\', [
        \'vector\'     => $oVector,
        \'insert\'     => $oInsert,
        \'before\'     => $oBefore,
        \'arms\'       => $oArms,
        \'vectorPcr\'  => $oVectorPcr,
        \'insertPcr\'  => $oInsertPcr,
        \'after\'      => $oAfter,
    ]);
}';

        return $this->render('default/cloningGibsonAssembly.html.twig', [
            'vector'     => $oVector,
            'insert'     => $oInsert,
            'before'     => $oBefore,
            'arms'       => $oArms,
            'vectorPcr'  => $oVectorPcr,
            'insertPcr'  => $oInsertPcr,
            'after'      => $oAfter,
            'code'       => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   GenbankPlasmidMapperInterface   $genbankPlasmidMapper
     * @param   GenbankWriterInterface          $genbankWriter
     * @param   GffFeatureWriterInterface       $gffWriter
     * @param   BedFeatureWriterInterface       $bedWriter
     * @return  Response
     * @throws  \Exception
     */
    #[Route('/cloning-export', name: 'cloning_export')]
    public function export(
        GenbankPlasmidMapperInterface $genbankPlasmidMapper,
        GenbankWriterInterface $genbankWriter,
        GffFeatureWriterInterface $gffWriter,
        BedFeatureWriterInterface $bedWriter
    ) {
        $oPlasmid = $this->loadPuc19($genbankPlasmidMapper);

        $sGenbank = $genbankWriter->write($oPlasmid);
        $sGff = $gffWriter->write("L09137", $oPlasmid->getFeatures(), $oPlasmid->getLength());
        $sBed = $bedWriter->write("L09137", $oPlasmid->getFeatures());

        $sCode =
'public function export(
    GenbankPlasmidMapperInterface $genbankPlasmidMapper,
    GenbankWriterInterface $genbankWriter,
    GffFeatureWriterInterface $gffWriter,
    BedFeatureWriterInterface $bedWriter
) {
    $oPlasmid = $this->loadPuc19($genbankPlasmidMapper); // see "Plasmid from GenBank"

    $sGenbank = $genbankWriter->write($oPlasmid);
    $sGff = $gffWriter->write("L09137", $oPlasmid->getFeatures(), $oPlasmid->getLength());
    $sBed = $bedWriter->write("L09137", $oPlasmid->getFeatures());

    return $this->render(\'default/cloningExport.html.twig\', [
        \'genbank\' => $sGenbank,
        \'gff\'     => $sGff,
        \'bed\'     => $sBed,
    ]);
}';

        return $this->render('default/cloningExport.html.twig', [
            'genbank' => $sGenbank,
            'gff'     => $sGff,
            'bed'     => $sBed,
            'code'    => CodeHighlighter::php($sCode)
        ]);
    }
}
