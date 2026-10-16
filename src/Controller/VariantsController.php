<?php
/**
 * Code samples controller
 * New in biophp 2.0: the Variants domain - reading and writing VCF files, and storing variants
 * with Doctrine.
 */
namespace App\Controller;

use Amelaye\BioPHP\Domain\Variants\Entity\VcfVariantRecord;
use Amelaye\BioPHP\Domain\Variants\Interfaces\VcfReaderInterface;
use Amelaye\BioPHP\Domain\Variants\Interfaces\VcfVariantRecordMapperInterface;
use Amelaye\BioPHP\Domain\Variants\Interfaces\VcfWriterInterface;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CodeHighlighter;

/**
 * Class VariantsController
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class VariantsController extends AbstractController
{
    /**
     * What an alternate allele does to the reference
     * @param   string  $sReference
     * @param   string  $sAlternate
     * @return  string  A key of the doc.variants translations
     */
    private function variantType(string $sReference, string $sAlternate): string
    {
        if (str_contains($sAlternate, "[") || str_contains($sAlternate, "]")) {
            return "breakend";
        }
        if (str_starts_with($sAlternate, "<")) {
            return "symbolic";
        }
        if (strlen($sReference) === strlen($sAlternate)) {
            return strlen($sReference) === 1 ? "snv" : "mnv";
        }
        if (strlen($sAlternate) > strlen($sReference) && str_starts_with($sAlternate, $sReference)) {
            return "insertion";
        }
        if (strlen($sAlternate) < strlen($sReference) && str_starts_with($sReference, $sAlternate)) {
            return "deletion";
        }
        return "complex";
    }

    /**
     * @param   VcfVariant[]    $aVariants
     * @return  array           One row per variant, each alternate allele with its type
     */
    private function describe(array $aVariants): array
    {
        $aRows = [];
        foreach ($aVariants as $oVariant) {
            $aAlleles = [];
            foreach ($oVariant->getAlternates() as $sAlternate) {
                $aAlleles[$sAlternate] = $this->variantType($oVariant->getReference(), $sAlternate);
            }
            $aRows[] = ["variant" => $oVariant, "alleles" => $aAlleles];
        }
        return $aRows;
    }

    /**
     * @param   VcfReaderInterface  $vcfReader
     * @return  Response
     */
    #[Route('/variants-read-vcf', name: 'variants_read_vcf')]
    public function readVcf(VcfReaderInterface $vcfReader)
    {
        // The example file of the VCF 4.3 specification
        $oResult = $vcfReader->read(file($this->getParameter('kernel.project_dir') . '/public/data/sample.vcf'));
        $aVariants = $oResult->getVariants();

        // Only the variants that passed every filter
        $aPassing = array_filter($aVariants, fn(VcfVariant $oVariant) => $oVariant->isPass());

        // Malformed lines are reported and skipped, the others are read
        $oFaulty = $vcfReader->read([
            "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO",
            "20\t14370\trs6054257\tG\tA\t29\tPASS\tDP=14",
            "20\tabc\t.\tT\tA\t3\tq10\tDP=11",
            "20\t17330\t.\tT\tA\t3\tq10",
        ]);

        $sCode =
'public function readVcf(VcfReaderInterface $vcfReader)
{
    // The example file of the VCF 4.3 specification
    $oResult = $vcfReader->read(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.vcf\'));
    $aVariants = $oResult->getVariants();

    // Only the variants that passed every filter
    $aPassing = array_filter($aVariants, fn(VcfVariant $oVariant) => $oVariant->isPass());

    // Malformed lines are reported and skipped, the others are read
    $oFaulty = $vcfReader->read([
        "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO",
        "20\t14370\trs6054257\tG\tA\t29\tPASS\tDP=14",
        "20\tabc\t.\tT\tA\t3\tq10\tDP=11",
        "20\t17330\t.\tT\tA\t3\tq10",
    ]);

    return $this->render(\'default/variantsReadVcf.html.twig\', [
        \'variants\'  => $this->describe($aVariants), // type of each alternate allele (SNV, insertion...)
        \'passing\'   => count($aPassing),
        \'faulty\'    => $oFaulty,
    ]);
}';

        return $this->render('default/variantsReadVcf.html.twig', [
            'variants'  => $this->describe($aVariants),
            'passing'   => count($aPassing),
            'faulty'    => $oFaulty,
            'code'      => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   VcfWriterInterface  $vcfWriter
     * @param   VcfReaderInterface  $vcfReader
     * @return  Response
     */
    #[Route('/variants-write-vcf', name: 'variants_write_vcf')]
    public function writeVcf(VcfWriterInterface $vcfWriter, VcfReaderInterface $vcfReader)
    {
        $aVariants = [
            // BRAF V600E, the most frequent mutation of melanoma (GRCh38)
            new VcfVariant("7", 140753336, "rs113488022", "A", ["T"], null, "PASS",
                ["GENE" => "BRAF", "HGVSP" => "p.Val600Glu", "NOTE" => "c.1799T>A; on the minus strand"]),
            // A deletion of 205 bp, written as a symbolic allele (VCF 4.3 specification)
            new VcfVariant("2", 321682, null, "T", ["<DEL>"], 6.0, "PASS",
                ["SVTYPE" => "DEL", "END" => "321887", "SVLEN" => "-205", "IMPRECISE" => true]),
            // A translocation between chromosomes 2 and 17: two breakends, each naming its mate
            new VcfVariant("2", 321681, "bnd_W", "G", ["G]17:198982]"], 6.0, "PASS",
                ["SVTYPE" => "BND", "MATEID" => "bnd_Y"]),
            new VcfVariant("17", 198982, "bnd_Y", "A", ["A]2:321681]"], 6.0, "PASS",
                ["SVTYPE" => "BND", "MATEID" => "bnd_W"]),
        ];

        $sVcf = $vcfWriter->write($aVariants, [
            "##reference=GRCh38",
            '##INFO=<ID=GENE,Number=1,Type=String,Description="Gene symbol">',
        ]);

        // Read back, every variant comes out as it went in
        $aReadBack = $vcfReader->read(explode("\n", $sVcf))->getVariants();
        $bIdentical = $aReadBack == $aVariants;

        $sCode =
'public function writeVcf(VcfWriterInterface $vcfWriter, VcfReaderInterface $vcfReader)
{
    $aVariants = [
        // BRAF V600E, the most frequent mutation of melanoma (GRCh38)
        new VcfVariant("7", 140753336, "rs113488022", "A", ["T"], null, "PASS",
            ["GENE" => "BRAF", "HGVSP" => "p.Val600Glu", "NOTE" => "c.1799T>A; on the minus strand"]),
        // A deletion of 205 bp, written as a symbolic allele (VCF 4.3 specification)
        new VcfVariant("2", 321682, null, "T", ["<DEL>"], 6.0, "PASS",
            ["SVTYPE" => "DEL", "END" => "321887", "SVLEN" => "-205", "IMPRECISE" => true]),
        // A translocation between chromosomes 2 and 17: two breakends, each naming its mate
        new VcfVariant("2", 321681, "bnd_W", "G", ["G]17:198982]"], 6.0, "PASS",
            ["SVTYPE" => "BND", "MATEID" => "bnd_Y"]),
        new VcfVariant("17", 198982, "bnd_Y", "A", ["A]2:321681]"], 6.0, "PASS",
            ["SVTYPE" => "BND", "MATEID" => "bnd_W"]),
    ];

    $sVcf = $vcfWriter->write($aVariants, [
        "##reference=GRCh38",
        \'##INFO=<ID=GENE,Number=1,Type=String,Description="Gene symbol">\',
    ]);

    // Read back, every variant comes out as it went in
    $aReadBack = $vcfReader->read(explode("\n", $sVcf))->getVariants();
    $bIdentical = $aReadBack == $aVariants;

    return $this->render(\'default/variantsWriteVcf.html.twig\', [
        \'vcf\'       => $sVcf,
        \'readBack\'  => $this->describe($aReadBack),
        \'identical\' => $bIdentical,
    ]);
}';

        return $this->render('default/variantsWriteVcf.html.twig', [
            'vcf'       => $sVcf,
            'readBack'  => $this->describe($aReadBack),
            'identical' => $bIdentical,
            'code'      => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   VcfReaderInterface                  $vcfReader
     * @param   VcfVariantRecordMapperInterface     $vcfRecordMapper
     * @param   EntityManagerInterface              $entityManager
     * @return  Response
     */
    #[Route('/variants-database', name: 'variants_database')]
    public function database(
        VcfReaderInterface $vcfReader,
        VcfVariantRecordMapperInterface $vcfRecordMapper,
        EntityManagerInterface $entityManager
    ) {
        $aVariants = $vcfReader->read(file($this->getParameter('kernel.project_dir') . '/public/data/sample.vcf'))
            ->getVariants();

        // Start again from an empty table at each visit of the demo
        $entityManager->createQuery('DELETE FROM ' . VcfVariantRecord::class . ' r WHERE r.chrom = :chrom')
            ->setParameter('chrom', '20')
            ->execute();

        // The domain objects stay independent of Doctrine: the mapper turns them into entities
        foreach ($aVariants as $oVariant) {
            $entityManager->persist($vcfRecordMapper->toRecord($oVariant));
        }
        $entityManager->flush();
        $entityManager->clear();

        // Ask the database: which variants passed every filter with a quality of 30 or more?
        $aRecords = $entityManager->createQueryBuilder()
            ->select('r')
            ->from(VcfVariantRecord::class, 'r')
            ->where('r.filter = :pass')
            ->andWhere('r.quality >= :quality')
            ->setParameter('pass', 'PASS')
            ->setParameter('quality', 30)
            ->orderBy('r.position', 'ASC')
            ->getQuery()
            ->getResult();

        // Back to domain objects: rebuilding a variant runs its validation again
        $aStored = array_map(fn(VcfVariantRecord $oRecord) => $vcfRecordMapper->toVariant($oRecord), $aRecords);

        $sCode =
'public function database(
    VcfReaderInterface $vcfReader,
    VcfVariantRecordMapperInterface $vcfRecordMapper,
    EntityManagerInterface $entityManager
) {
    $aVariants = $vcfReader->read(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.vcf\'))
        ->getVariants();

    // Start again from an empty table at each visit of the demo
    $entityManager->createQuery(\'DELETE FROM \' . VcfVariantRecord::class . \' r WHERE r.chrom = :chrom\')
        ->setParameter(\'chrom\', \'20\')
        ->execute();

    // The domain objects stay independent of Doctrine: the mapper turns them into entities
    foreach ($aVariants as $oVariant) {
        $entityManager->persist($vcfRecordMapper->toRecord($oVariant));
    }
    $entityManager->flush();
    $entityManager->clear();

    // Ask the database: which variants passed every filter with a quality of 30 or more?
    $aRecords = $entityManager->createQueryBuilder()
        ->select(\'r\')
        ->from(VcfVariantRecord::class, \'r\')
        ->where(\'r.filter = :pass\')
        ->andWhere(\'r.quality >= :quality\')
        ->setParameter(\'pass\', \'PASS\')
        ->setParameter(\'quality\', 30)
        ->orderBy(\'r.position\', \'ASC\')
        ->getQuery()
        ->getResult();

    // Back to domain objects: rebuilding a variant runs its validation again
    $aStored = array_map(fn(VcfVariantRecord $oRecord) => $vcfRecordMapper->toVariant($oRecord), $aRecords);

    return $this->render(\'default/variantsDatabase.html.twig\', [
        \'stored\'   => count($aVariants),
        \'records\'  => $aRecords,
        \'variants\' => $this->describe($aStored),
    ]);
}';

        return $this->render('default/variantsDatabase.html.twig', [
            'stored'   => count($aVariants),
            'records'  => $aRecords,
            'variants' => $this->describe($aStored),
            'code'     => CodeHighlighter::php($sCode)
        ]);
    }
}
