<?php
/**
 * Code samples controller
 * New in biophp 1.1: 13 database formats were added to Domain/Parser, on top of the 6 already
 * available (GENBANK, SWISSPROT, EMBL, PDB, PROSITE, EXPASY_ENZYME). Each parser is used directly
 * through DatabaseParserFactory, without going through a database - useful for formats whose
 * records don't fit the Sequence entity (PRF is the exception, it still exposes getSequence()).
 */
namespace App\Controller;

use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use GeSHi;

/**
 * Class DatabaseFormatsController
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class DatabaseFormatsController extends AbstractController
{
    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-prf', name: 'read_sequence_prf')]
    public function parsePrfRecord()
    {
        $parser = DatabaseParserFactory::createParser("PRF");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.prf'));

        $sCode =
'public function parsePrfRecord()
{
    $parser = DatabaseParserFactory::createParser("PRF");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.prf\'));

    return $this->render(\'default/parsePrfRecord.html.twig\',
        [
            \'entryCode\'  => $parser->getEntryCode(),
            \'entryName\'  => $parser->getEntryName(),
            \'source\'     => $parser->getSource(),
            \'commonName\' => $parser->getCommonName(),
            \'taxonomy\'   => $parser->getTaxonomy(),
            \'journal\'    => $parser->getJournal(),
            \'authors\'    => $parser->getAuthors(),
            \'recordTitle\'=> $parser->getTitle(),
            \'keywords\'   => $parser->getKeywords(),
            \'sequence\'   => $parser->getSequence(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parsePrfRecord.html.twig',
            [
                'entryCode'   => $parser->getEntryCode(),
                'entryName'   => $parser->getEntryName(),
                'source'      => $parser->getSource(),
                'commonName'  => $parser->getCommonName(),
                'taxonomy'    => $parser->getTaxonomy(),
                'journal'     => $parser->getJournal(),
                'authors'     => $parser->getAuthors(),
                'recordTitle' => $parser->getTitle(),
                'keywords'    => $parser->getKeywords(),
                'sequence'    => $parser->getSequence(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-pir', name: 'read_sequence_pir')]
    public function parsePirRecord()
    {
        $parser = DatabaseParserFactory::createParser("PIR");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.pir'));

        $sCode =
'public function parsePirRecord()
{
    $parser = DatabaseParserFactory::createParser("PIR");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.pir\'));

    return $this->render(\'default/parsePirRecord.html.twig\',
        [
            \'entryName\'  => $parser->getEntryName(),
            \'entryType\'  => $parser->getEntryType(),
            \'recordTitle\'=> $parser->getTitle(),
            \'accessions\' => $parser->getAccessions(),
            \'organism\'   => $parser->getOrganism(),
            \'species\'    => $parser->getSpecies(),
            \'createDate\' => $parser->getCreateDate(),
            \'length\'     => $parser->getLength(),
            \'molwt\'      => $parser->getMolwt(),
            \'checksum\'   => $parser->getChecksum(),
            \'keywords\'   => $parser->getKeywords(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parsePirRecord.html.twig',
            [
                'entryName'   => $parser->getEntryName(),
                'entryType'   => $parser->getEntryType(),
                'recordTitle' => $parser->getTitle(),
                'accessions'  => $parser->getAccessions(),
                'organism'    => $parser->getOrganism(),
                'species'     => $parser->getSpecies(),
                'createDate'  => $parser->getCreateDate(),
                'length'      => $parser->getLength(),
                'molwt'       => $parser->getMolwt(),
                'checksum'    => $parser->getChecksum(),
                'keywords'    => $parser->getKeywords(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-unigene', name: 'read_sequence_unigene')]
    public function parseUnigeneRecord()
    {
        $parser = DatabaseParserFactory::createParser("UNIGENE");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.unigene'));

        $sCode =
'public function parseUnigeneRecord()
{
    $parser = DatabaseParserFactory::createParser("UNIGENE");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.unigene\'));

    return $this->render(\'default/parseUnigeneRecord.html.twig\',
        [
            \'clusterId\'  => $parser->getClusterId(),
            \'recordTitle\'=> $parser->getTitle(),
            \'expression\' => $parser->getExpression(),
            \'protSims\'   => $parser->getProtSims(),
            \'seqCount\'   => $parser->getSeqCount(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parseUnigeneRecord.html.twig',
            [
                'clusterId'   => $parser->getClusterId(),
                'recordTitle' => $parser->getTitle(),
                'expression'  => $parser->getExpression(),
                'protSims'    => $parser->getProtSims(),
                'seqCount'    => $parser->getSeqCount(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-genome', name: 'read_sequence_genome')]
    public function parseGenomeRecord()
    {
        $parser = DatabaseParserFactory::createParser("GENOME");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.genome'));

        $sCode =
'public function parseGenomeRecord()
{
    $parser = DatabaseParserFactory::createParser("GENOME");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.genome\'));

    return $this->render(\'default/parseGenomeRecord.html.twig\',
        [
            \'organism\'      => $parser->getOrganism(),
            \'commonName\'    => $parser->getCommonName(),
            \'taxClass\'      => $parser->getTaxClass(),
            \'isComplete\'    => $parser->getIsComplete(),
            \'gbRelease\'     => $parser->getGbRelease(),
            \'gbEntries\'     => $parser->getGbEntries(),
            \'gbBasepairs\'   => $parser->getGbBasepairs(),
            \'size\'          => $parser->getSize(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parseGenomeRecord.html.twig',
            [
                'organism'    => $parser->getOrganism(),
                'commonName'  => $parser->getCommonName(),
                'taxClass'    => $parser->getTaxClass(),
                'isComplete'  => $parser->getIsComplete(),
                'gbRelease'   => $parser->getGbRelease(),
                'gbEntries'   => $parser->getGbEntries(),
                'gbBasepairs' => $parser->getGbBasepairs(),
                'size'        => $parser->getSize(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-hgbase', name: 'read_sequence_hgbase')]
    public function parseHgbaseRecord()
    {
        $parser = DatabaseParserFactory::createParser("HGBASE");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.hgbase'));

        $sCode =
'public function parseHgbaseRecord()
{
    $parser = DatabaseParserFactory::createParser("HGBASE");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.hgbase\'));

    return $this->render(\'default/parseHgbaseRecord.html.twig\',
        [
            \'haplotypeId\' => $parser->getHaplotypeId(),
            \'allele\'      => $parser->getAllele(),
            \'popName\'     => $parser->getPopName(),
            \'popIndiv\'    => $parser->getPopIndiv(),
            \'freqPerc\'    => $parser->getFreqPerc(),
            \'freqIndiv\'   => $parser->getFreqIndiv(),
            \'citation\'    => $parser->getCitation(),
            \'submitterName\' => $parser->getSubmitterName(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parseHgbaseRecord.html.twig',
            [
                'haplotypeId'   => $parser->getHaplotypeId(),
                'allele'        => $parser->getAllele(),
                'popName'       => $parser->getPopName(),
                'popIndiv'      => $parser->getPopIndiv(),
                'freqPerc'      => $parser->getFreqPerc(),
                'freqIndiv'     => $parser->getFreqIndiv(),
                'citation'      => $parser->getCitation(),
                'submitterName' => $parser->getSubmitterName(),
                'code'          => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-prints', name: 'read_sequence_prints')]
    public function parsePrintsRecord()
    {
        $parser = DatabaseParserFactory::createParser("PRINTS");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.prints'));

        $sCode =
'public function parsePrintsRecord()
{
    $parser = DatabaseParserFactory::createParser("PRINTS");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.prints\'));

    return $this->render(\'default/parsePrintsRecord.html.twig\',
        [
            \'entryName\'  => $parser->getEntryName(),
            \'entryType\'  => $parser->getEntryType(),
            \'createDate\' => $parser->getCreateDate(),
            \'updDate\'    => $parser->getUpdDate(),
            \'description\'=> $parser->getDescription(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parsePrintsRecord.html.twig',
            [
                'entryName'   => $parser->getEntryName(),
                'entryType'   => $parser->getEntryType(),
                'createDate'  => $parser->getCreateDate(),
                'updDate'     => $parser->getUpdDate(),
                'description' => $parser->getDescription(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-prodom', name: 'read_sequence_prodom')]
    public function parseProdomRecord()
    {
        $parser = DatabaseParserFactory::createParser("PRODOM");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.prodom'));

        $sCode =
'public function parseProdomRecord()
{
    $parser = DatabaseParserFactory::createParser("PRODOM");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.prodom\'));

    return $this->render(\'default/parseProdomRecord.html.twig\',
        [
            \'entryNo\'     => $parser->getEntryNo(),
            \'accession\'   => $parser->getAccession(),
            \'release\'     => $parser->getRelease(),
            \'domainCount\' => $parser->getDomainCount(),
            \'freqNames\'   => $parser->getFreqNames(),
            \'keywords\'    => $parser->getKeywords(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parseProdomRecord.html.twig',
            [
                'entryNo'     => $parser->getEntryNo(),
                'accession'   => $parser->getAccession(),
                'release'     => $parser->getRelease(),
                'domainCount' => $parser->getDomainCount(),
                'freqNames'   => $parser->getFreqNames(),
                'keywords'    => $parser->getKeywords(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-aaindex', name: 'read_sequence_aaindex')]
    public function parseAaindexRecord()
    {
        $parser = DatabaseParserFactory::createParser("AAINDEX");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.aaindex'));

        $sCode =
'public function parseAaindexRecord()
{
    $parser = DatabaseParserFactory::createParser("AAINDEX");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.aaindex\'));

    return $this->render(\'default/parseAaindexRecord.html.twig\',
        [
            \'accession\'  => $parser->getAccession(),
            \'description\'=> $parser->getDescription(),
            \'author\'     => $parser->getAuthor(),
            \'recordTitle\'=> $parser->getTitle(),
            \'journal\'    => $parser->getJournal(),
            \'litRefs\'    => $parser->getLitRefs(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parseAaindexRecord.html.twig',
            [
                'accession'   => $parser->getAccession(),
                'description' => $parser->getDescription(),
                'author'      => $parser->getAuthor(),
                'recordTitle' => $parser->getTitle(),
                'journal'     => $parser->getJournal(),
                'litRefs'     => $parser->getLitRefs(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }

    /**
     * @return Response
     * @throws \Exception
     */
    #[Route('/read-sequence-epd', name: 'read_sequence_epd')]
    public function parseEpdRecord()
    {
        $parser = DatabaseParserFactory::createParser("EPD");
        $parser->parseDataFile(file($this->getParameter('kernel.project_dir') . '/public/data/sample.epd'));

        $sCode =
'public function parseEpdRecord()
{
    $parser = DatabaseParserFactory::createParser("EPD");
    $parser->parseDataFile(file($this->getParameter(\'kernel.project_dir\') . \'/public/data/sample.epd\'));

    return $this->render(\'default/parseEpdRecord.html.twig\',
        [
            \'entryName\'  => $parser->getEntryName(),
            \'dataClass\'  => $parser->getDataClass(),
            \'taxDiv\'     => $parser->getTaxDiv(),
            \'accessions\' => $parser->getAccessions(),
            \'createDate\' => $parser->getCreateDate(),
            \'description\'=> $parser->getDescription(),
            \'comments\'   => $parser->getComments(),
        ]
    );
}';
        $oGeshi = new GeSHi($sCode, 'php');

        return $this->render('default/parseEpdRecord.html.twig',
            [
                'entryName'   => $parser->getEntryName(),
                'dataClass'   => $parser->getDataClass(),
                'taxDiv'      => $parser->getTaxDiv(),
                'accessions'  => $parser->getAccessions(),
                'createDate'  => $parser->getCreateDate(),
                'description' => $parser->getDescription(),
                'comments'    => $parser->getComments(),
                'code'        => $oGeshi->parse_code()
            ]
        );
    }
}
