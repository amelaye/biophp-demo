<?php
namespace App\Controller;

use Amelaye\BioPHP\Domain\Database\Interfaces\DatabaseInterface;
use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;
use Amelaye\BioPHP\Domain\Sequence\Entity\SubMatrix;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\ProteinInterface;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\RestrictionEnzymeInterface;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceAlignmentInterface;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceMatchInterface;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceAlignmentManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\MolecularSequenceFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CodeHighlighter;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;


class DefaultController extends AbstractController
{
    public function index()
    {
        return $this->render('default/index.html.twig', []);
    }

    /**
     * @param SequenceInterface $sequenceManager
     * @return Response
     */
    public function sequenceanalysis(SequenceInterface $sequenceManager)
    {
        $oSequence = new Sequence();
        $oSequence->setSequence("AGGGAATTAAGTAAATGGTAGTGG");
        $sequenceManager->setSequence($oSequence);

        $aMirrors = $sequenceManager->findMirror(null, 6, 8, "E");

        $sCode =
'public function sequenceanalysis(SequenceInterface $sequenceManager)
{
    $oSequence = new Sequence();
    $oSequence->setSequence("AGGGAATTAAGTAAATGGTAGTGG");
    $sequenceManager->setSequence($oSequence);
    
    $aMirrors = $sequenceManager->findMirror(null, 6, 8, "E");
    
    return $this->render(\'default/sequenceanalysis.html.twig\',
        array(\'mirrors\' => $aMirrors)
    );
}';

        return $this->render('default/sequenceanalysis.html.twig',
            array('mirrors' => $aMirrors, 'code' => CodeHighlighter::php($sCode))
        );
    }

    /**
     * Read a sequence from a database
     * @param DatabaseInterface $databaseManager
     * @return Response
     * @throws \Exception
     */
    public function parseaseqdb(DatabaseInterface $databaseManager)
    {
        $databaseManager->recording("humandb", "GENBANK", "human.seq", "demo.seq");
        // fetch() returns the parser that read the record, not the Sequence itself
        $oSequence = $databaseManager->fetch("NM_031438", "data/")->getSequence();

        // New in biophp 1.1: wrap the parsed record into a validated DNA/RNA value object
        $oMolecularSequence = MolecularSequenceFactory::fromEntity($oSequence);
        $fGcContent = $oMolecularSequence->getGcContent();

        $sCode =
'public function parseaseqdb(DatabaseInterface $databaseManager)
{
    $databaseManager->recording("humandb", "GENBANK", "human.seq", "demo.seq");
    // fetch() returns the parser that read the record, not the Sequence itself
    $oSequence = $databaseManager->fetch("NM_031438", "data/")->getSequence();

    // New in biophp 1.1: wrap the parsed record into a validated DNA/RNA value object
    $oMolecularSequence = MolecularSequenceFactory::fromEntity($oSequence);
    $fGcContent = $oMolecularSequence->getGcContent();

    return $this->render(\'default/parseseqdb.html.twig\',
        ["sequence" => $oSequence, "gcContent" => $fGcContent]
    );
}';
        return $this->render('default/parseseqdb.html.twig',
            ["sequence" => $oSequence, "gcContent" => $fGcContent, 'code' => CodeHighlighter::php($sCode)]
        );
    }

    /**
     * Read a sequence from a database
     * @param DatabaseInterface $databaseManager
     * @return Response
     * @throws \Exception
     */
    public function parseaswissprotdb(DatabaseInterface $databaseManager)
    {
        $databaseManager->recording("humandbSwiss", "SWISSPROT", "basicswiss.txt");
        // fetch() returns the parser that read the record, not the Sequence itself
        $oSequence = $databaseManager->fetch("1375", "data/")->getSequence();

        // New in biophp 1.1: wrap the parsed record into a validated amino acid value object
        $oMolecularSequence = MolecularSequenceFactory::fromEntity($oSequence);
        $bHasUnknownResidue = $oMolecularSequence->hasUnknownResidue();

        $sCode =
'public function parseaswissprotdb(DatabaseInterface $databaseManager)
{
    $databaseManager->recording("humandbSwiss", "SWISSPROT", "basicswiss.txt");
    // fetch() returns the parser that read the record, not the Sequence itself
    $oSequence = $databaseManager->fetch("1375", "data/")->getSequence();

    // New in biophp 1.1: wrap the parsed record into a validated amino acid value object
    $oMolecularSequence = MolecularSequenceFactory::fromEntity($oSequence);
    $bHasUnknownResidue = $oMolecularSequence->hasUnknownResidue();

    return $this->render(\'default/parseswissprotdb.html.twig\',
        ["sequence" => $oSequence, "hasUnknownResidue" => $bHasUnknownResidue]
    );
}';
        return $this->render('default/parseswissprotdb.html.twig',
            ["sequence" => $oSequence, "hasUnknownResidue" => $bHasUnknownResidue, 'code' => CodeHighlighter::php($sCode)]
        );
    }

    /**
     * @param SequenceAlignmentInterface $sequenceAlignmentManager
     * @return Response
     */
    public function fastaseqalignment(SequenceAlignmentInterface $sequenceAlignmentManager)
    {
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();

        $sCode =
'/**
 * @param SequenceAlignmentInterface $sequenceAlignmentManager
 * @return Response
 */
public function fastaseqalignment(SequenceAlignmentInterface $sequenceAlignmentManager)
{
    $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
    $sequenceAlignmentManager->setFormat("FASTA");
    $sequenceAlignmentManager->parseFile();

    return $this->render(\'default/parseseqalignment.html.twig\',
        [\'sequences\' => $sequenceAlignmentManager->getSeqSet()]
    );
}';

        return $this->render('default/parseseqalignment.html.twig',
            ['sequences' => $sequenceAlignmentManager->getSeqSet(), 'code' => CodeHighlighter::php($sCode)]
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @param SequenceAlignmentInterface $sequenceAlignmentManager
     * @return Response
     * @throws \Exception
     */
    public function clustalseqalignment(SequenceAlignmentInterface $sequenceAlignmentManager)
    {
        set_time_limit(0); // we never know ...
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();

        // You wanna sort your array ? :)
        $sequenceAlignmentManager->sortAlpha("ASC");
        $sequenceAlSort = $sequenceAlignmentManager->getSeqSet();
        // You wanna fetch something ?
        $oOffset13 = $sequenceAlignmentManager->getSeqSet()->offsetGet(13);
        // You wanna know the longest sequence ?
        $iMaxLength = $sequenceAlignmentManager->getMaxiLength();
        // You wanna know the number of gaps ?
        $iNumberGaps = $sequenceAlignmentManager->getGapCount();
        // Have the same length ?
        $bIsFlush = $sequenceAlignmentManager->getIsFlush();
        // Char at res 10 (10th sequence)
        $sCharAtRes = $sequenceAlignmentManager->charAtRes(10, 10);
        // Substring between two residues in a sequence
        $sSubstrBwRes = $sequenceAlignmentManager->substrBwRes(10,10);
        // Converts a column number to a residue number in a sequence
        $iColToRes = $sequenceAlignmentManager->colToRes(10, 50);
        // Converts a residue number to a column number in a sequence
        $iResToCol = $sequenceAlignmentManager->resToCol(10, 47);
        // Creates a new alignment set from index 5 to 10
        $sequenceAlignmentManager->subalign(5, 10);
        $oSubALign = $sequenceAlignmentManager;
        // Creates a new alignment with selected indexes
        $sequenceAlignmentManager->select(1,2,3);
        $oSelectAlign = $sequenceAlignmentManager;

        // Determines the index position of both variant and invariant residues according
        // to a given "percentage threshold" similar to that in the consensus() method.
        $aResVar = $sequenceAlignmentManager->resVar();

        // Returns the consensus string for an alignment set
        $aConsensus = $sequenceAlignmentManager->consensus();

        // Adding a new sequence object
        $sequenceAlignmentManager->addSequence($oOffset13);
        $sequenceAlignmentManagerAdd = $sequenceAlignmentManager;
        // Dropping a sequence
        $sequenceAlignmentManager->deleteSequence("sp|P04637|P53_HUMAN");
        $sequenceAlignmentManagerDel = $sequenceAlignmentManager;

        $sCode =
'/**
  * Here is some samples of how to use the functions
  * @param SequenceAlignmentInterface $sequenceAlignmentManager
  * @return Response
  * @throws \Exception
  */
public function clustalseqalignment(SequenceAlignmentInterface $sequenceAlignmentManager)
{
    set_time_limit(0); // we never know ...
    $sequenceAlignmentManager->setFilename("data/clustal.txt");
    $sequenceAlignmentManager->setFormat("CLUSTAL");
    $sequenceAlignmentManager->parseFile();

    // You wanna sort your array ? :)
    $sequenceAlignmentManager->sortAlpha("ASC");
    $sequenceAlSort = $sequenceAlignmentManager->getSeqSet();
    // You wanna fetch something ?
    $oOffset13 = $sequenceAlignmentManager->getSeqSet()->offsetGet(13);
    // You wanna know the longest sequence ?
    $iMaxLength = $sequenceAlignmentManager->getMaxiLength();
    // You wanna know the number of gaps ?
    $iNumberGaps = $sequenceAlignmentManager->getGapCount();
    // Have the same length ?
    $bIsFlush = $sequenceAlignmentManager->getIsFlush();
    // Char at res 10 (10th sequence)
    $sCharAtRes = $sequenceAlignmentManager->charAtRes(10, 10);
    // Substring between two residues in a sequence
    $sSubstrBwRes = $sequenceAlignmentManager->substrBwRes(10,10);
    // Converts a column number to a residue number in a sequence
    $iColToRes = $sequenceAlignmentManager->colToRes(10, 50);
    // Converts a residue number to a column number in a sequence
    $iResToCol = $sequenceAlignmentManager->resToCol(10, 47);
    // Creates a new alignment set from index 5 to 10
    $sequenceAlignmentManager->subalign(5, 10);
    $oSubALign = $sequenceAlignmentManager;
    // Creates a new alignment with selected indexes
    $sequenceAlignmentManager->select(1,2,3);
    $oSelectAlign = $sequenceAlignmentManager;

    // Determines the index position of both variant and invariant residues according
    // to a given "percentage threshold" similar to that in the consensus() method.
    $aResVar = $sequenceAlignmentManager->resVar();

    // Returns the consensus string for an alignment set
    $aConsensus = $sequenceAlignmentManager->consensus();

    // Adding a new sequence object
    $sequenceAlignmentManager->addSequence($oOffset13);
    $sequenceAlignmentManagerAdd = $sequenceAlignmentManager;
    // Dropping a sequence
    $sequenceAlignmentManager->deleteSequence("sp|P04637|P53_HUMAN");
    $sequenceAlignmentManagerDel = $sequenceAlignmentManager;


    return $this->render(\'default/clustalseqalignment.html.twig\',
        [
            \'sequenceAlSort\'              => $sequenceAlSort,
            \'offset13\'                    => $oOffset13,
            \'maxLength\'                   => $iMaxLength,
            \'numberGaps\'                  => $iNumberGaps,
            \'isFlush\'                     => $bIsFlush,
            \'sCharAtRes\'                  => $sCharAtRes,
            \'sSubstrBwRes\'                => $sSubstrBwRes,
            \'colToRes\'                    => $iColToRes,
            \'resToCol\'                    => $iResToCol,
            \'subALign\'                    => $oSubALign,
            \'selectAlign\'                 => $oSelectAlign,
            \'resVar\'                      => $aResVar,
            \'consensus\'                   => $aConsensus,
            \'sequenceAlignmentManagerAdd\' => $sequenceAlignmentManagerAdd,
            \'sequenceAlignmentManagerDel\' => $sequenceAlignmentManagerDel
        ]
    );
}';


        return $this->render('default/clustalseqalignment.html.twig',
            [
                'sequenceAlSort'              => $sequenceAlSort,
                'offset13'                    => $oOffset13,
                'maxLength'                   => $iMaxLength,
                'numberGaps'                  => $iNumberGaps,
                'isFlush'                     => $bIsFlush,
                'sCharAtRes'                  => $sCharAtRes,
                'sSubstrBwRes'                => $sSubstrBwRes,
                'colToRes'                    => $iColToRes,
                'resToCol'                    => $iResToCol,
                'subALign'                    => $oSubALign,
                'selectAlign'                 => $oSelectAlign,
                'resVar'                      => $aResVar,
                'consensus'                   => $aConsensus,
                'sequenceAlignmentManagerAdd' => $sequenceAlignmentManagerAdd,
                'sequenceAlignmentManagerDel' => $sequenceAlignmentManagerDel,
                'code' => CodeHighlighter::php($sCode)
            ]
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @param SequenceAlignmentInterface $sequenceAlignmentManager
     * @param SequenceInterface $sequenceManager
     * @return Response
     * @throws \Exception
     */
    public function playwithsequencies(
        SequenceAlignmentInterface $sequenceAlignmentManager,
        SequenceInterface $sequenceManager
    ) {
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();

        // We take this sequence as example
        $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        $oSequence->setMolType("DNA");
        $sequenceManager->setSequence($oSequence);

        // Complement of the demo sequence
        $aComplement = $sequenceManager->complement("DNA");

        // Shows halfstring for pattern "GATTAG"
        $sHalfStr = $sequenceManager->halfSequence(0, "GATTAG");

        // Returns the sequence located between two palindromic halves of a palindromic string
        //$sBridge = $sequenceManager->getBridge("ATGcacgtcCAT");
        //dump($sBridge);

        // Returns the expansion of a nucleic acid sequence
        $sExpandNa = $sequenceManager->expandNa("GATTAGSW");

        // Computes the molecular weight of a particular sequence.
        $sMolWt = $sequenceManager->molwt();

        // Creates a new sequence object with a sequence that is a substring of another.
        $sCoupe = $sequenceManager->subSeq(2,100);

        // Array where each key is a substring matching a given pattern
        $aPatpos = $sequenceManager->patPos("TTT");

        // Similar to patPos() except that this allows for overlapping patterns.
        $aPatPoso = $sequenceManager->patPoso("TTT");

        // Returns the frequency of a given symbol in the sequence property string
        $iSymfreq = $sequenceManager->symFreq("A");

        // Returns the n-th codon in a sequence, with numbering starting at 0
        $sCodon = $sequenceManager->getCodon(3);

        // Translates a particular DNA sequence into its protein product sequence
        $sTranslate = $sequenceManager->translate();

        // Translates an amino acid sequence into its equivalent "charge sequence".
        $sCharge = $sequenceManager->charge("GAVLIFYWKRH");

        // Returns a string of symbols from an 8-letter alphabet: A, L, M, R, C, H, I, S.
        $sChemicalGroup = $sequenceManager->chemicalGroup("GAVLIFYWKRH");

        // Returns a two-dimensional array containing palindromic substrings found in a sequence
        $aTestPalindrome = $sequenceManager->findPalindrome(null, 2, 2);

        // New in biophp 1.1: the sequence as a validated, immutable value object
        $oDnaSeq = $sequenceManager->getMolecularSequence();
        $sVoComplement = (string) $oDnaSeq->complement();
        $sVoReverseComplement = (string) $oDnaSeq->reverseComplement();
        $fVoGcContent = $oDnaSeq->getGcContent();
        $sVoToRna = (string) $oDnaSeq->toRna();
        $sVoSubSequence = (string) $oDnaSeq->subSequence(2, 100);
        $bVoEquals = $oDnaSeq->equals($sequenceManager->getMolecularSequence());

        $sCode =
'/**
  * Here is some samples of how to use the functions
  * @param SequenceAlignmentInterface $sequenceAlignmentManager
  * @param SequenceInterface $sequenceManager
  * @return Response
  * @throws \Exception
  */
public function playwithsequencies(
    SequenceAlignmentInterface $sequenceAlignmentManager,
    SequenceInterface $sequenceManager
) {
    $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
    $sequenceAlignmentManager->setFormat("FASTA");
    $sequenceAlignmentManager->parseFile();

    // We take this sequence as example
    $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
    $oSequence->setMolType("DNA");
    $sequenceManager->setSequence($oSequence);

    // Complement of the demo sequence
    $aComplement = $sequenceManager->complement("DNA");

    // Shows halfstring for pattern "GATTAG"
    $sHalfStr = $sequenceManager->halfSequence(0, "GATTAG");

     // Returns the sequence located between two palindromic halves of a palindromic string
     //$sBridge = $sequenceManager->getBridge("ATGcacgtcCAT");
     //dump($sBridge);

     // Returns the expansion of a nucleic acid sequence
     $sExpandNa = $sequenceManager->expandNa("GATTAGSW");

     // Computes the molecular weight of a particular sequence.
     $sMolWt = $sequenceManager->molwt();

     // Creates a new sequence object with a sequence that is a substring of another.
     $sCoupe = $sequenceManager->subSeq(2,100);

     // Array where each key is a substring matching a given pattern
     $aPatpos = $sequenceManager->patPos("TTT");

     // Similar to patPos() except that this allows for overlapping patterns.
     $aPatPoso = $sequenceManager->patPoso("TTT");

     // Returns the frequency of a given symbol in the sequence property string
     $iSymfreq = $sequenceManager->symFreq("A");

     // Returns the n-th codon in a sequence, with numbering starting at 0
     $sCodon = $sequenceManager->getCodon(3);

     // Translates a particular DNA sequence into its protein product sequence
     $sTranslate = $sequenceManager->translate();

     // Translates an amino acid sequence into its equivalent "charge sequence".
     $sCharge = $sequenceManager->charge("GAVLIFYWKRH");

     // Returns a string of symbols from an 8-letter alphabet: A, L, M, R, C, H, I, S.
     $sChemicalGroup = $sequenceManager->chemicalGroup("GAVLIFYWKRH");

     // Returns a two-dimensional array containing palindromic substrings found in a sequence
     $aTestPalindrome = $sequenceManager->findPalindrome(null, 2, 2);

     // New in biophp 1.1: the sequence as a validated, immutable value object
     $oDnaSeq = $sequenceManager->getMolecularSequence();
     $sVoComplement = (string) $oDnaSeq->complement();
     $sVoReverseComplement = (string) $oDnaSeq->reverseComplement();
     $fVoGcContent = $oDnaSeq->getGcContent();
     $sVoToRna = (string) $oDnaSeq->toRna();
     $sVoSubSequence = (string) $oDnaSeq->subSequence(2, 100);
     $bVoEquals = $oDnaSeq->equals($sequenceManager->getMolecularSequence());

     return $this->render(\'default/playwithsequencies.html.twig\',
        [
            \'complement\'        => $aComplement,
            \'halfStr\'           => $sHalfStr,
            \'expandNa\'          => $sExpandNa,
            \'molWt\'             => $sMolWt,
            \'coupe\'             => $sCoupe,
            \'patpos\'            => $aPatpos,
            \'patposo\'           => $aPatPoso,
            \'symfreq\'           => $iSymfreq,
            \'codon\'             => $sCodon,
            \'translate\'         => $sTranslate,
            \'charge\'            => $sCharge,
            \'chemicalGroup\'     => $sChemicalGroup,
            \'testPalindrome1\'   => $aTestPalindrome,
            \'voComplement\'          => $sVoComplement,
            \'voReverseComplement\'   => $sVoReverseComplement,
            \'voGcContent\'           => $fVoGcContent,
            \'voToRna\'               => $sVoToRna,
            \'voSubSequence\'         => $sVoSubSequence,
            \'voEquals\'              => $bVoEquals
        ]
     );
}';


        return $this->render('default/playwithsequencies.html.twig',
            [
                'complement'        => $aComplement,
                'halfStr'           => $sHalfStr,
                'expandNa'          => $sExpandNa,
                'molWt'             => $sMolWt,
                'coupe'             => $sCoupe,
                'patpos'            => $aPatpos,
                'patposo'           => $aPatPoso,
                'symfreq'           => $iSymfreq,
                'codon'             => $sCodon,
                'translate'         => $sTranslate,
                'charge'            => $sCharge,
                'chemicalGroup'     => $sChemicalGroup,
                'testPalindrome1'   => $aTestPalindrome,
                'voComplement'          => $sVoComplement,
                'voReverseComplement'   => $sVoReverseComplement,
                'voGcContent'           => $fVoGcContent,
                'voToRna'               => $sVoToRna,
                'voSubSequence'         => $sVoSubSequence,
                'voEquals'              => $bVoEquals,
                'code'              => CodeHighlighter::php($sCode)
            ]
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @param ProteinInterface $proteinManager
     * @return Response
     * @throws \Exception
     */
    public function playwithproteins(ProteinInterface $proteinManager)
    {
        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        $iLength = $proteinManager->seqlen();
        $iMolwt = $proteinManager->molwt();

        // New in biophp 1.1: the protein as a validated, immutable value object
        $oAminoSeq = new AminoAcidSequence($sProtein);
        $iVoLength = $oAminoSeq->getLength();
        $bVoHasStop = $oAminoSeq->hasStop();
        $bVoHasUnknownResidue = $oAminoSeq->hasUnknownResidue();
        $iVoCountX = $oAminoSeq->countSymbol('X');

        $sCode =
'/**
  * Here is some samples of how to use the functions
  * @param ProteinInterface $proteinManager
  * @return Response
  * @throws \Exception
  */
public function playwithproteins(ProteinInterface $proteinManager)
{
   $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
   $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
   $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

   $oProtein = new Protein();
   $oProtein->setName("toto");
   $oProtein->setSequence($sProtein);
   $proteinManager->setProtein($oProtein);

   $iLength = $proteinManager->seqlen();
   $iMolwt = $proteinManager->molwt();

   // New in biophp 1.1: the protein as a validated, immutable value object
   $oAminoSeq = new AminoAcidSequence($sProtein);
   $iVoLength = $oAminoSeq->getLength();
   $bVoHasStop = $oAminoSeq->hasStop();
   $bVoHasUnknownResidue = $oAminoSeq->hasUnknownResidue();
   $iVoCountX = $oAminoSeq->countSymbol(\'X\');

   return $this->render(\'default/playwithproteins.html.twig\',
       [
           \'length\'              => $iLength,
           \'molwt\'               => $iMolwt,
           \'voLength\'            => $iVoLength,
           \'voHasStop\'           => $bVoHasStop,
           \'voHasUnknownResidue\' => $bVoHasUnknownResidue,
           \'voCountX\'            => $iVoCountX
       ]
   );
}';

        return $this->render('default/playwithproteins.html.twig',
            [
                'length' => $iLength,
                'molwt'  => $iMolwt,
                'voLength'            => $iVoLength,
                'voHasStop'           => $bVoHasStop,
                'voHasUnknownResidue' => $bVoHasUnknownResidue,
                'voCountX'            => $iVoCountX,
                'code'   => CodeHighlighter::php($sCode)
            ]
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @param   SequenceMatchInterface $sequenceMatchManager
     * @param   SequenceAlignmentInterface $sequenceAlignmentManager
     * @param   SequenceInterface $sequenceManager
     * @return  Response
     * @throws  \Exception
     */
    public function sequencematch(
        SequenceMatchInterface $sequenceMatchManager,
        SequenceAlignmentInterface $sequenceAlignmentManager,
        SequenceInterface $sequenceManager
    ){
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();
        $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        $oSequence->setMolType("DNA");
        $sequenceManager->setSequence($oSequence);

        // Setting the matrix
        $oSubMatrix = new SubMatrix();
        $oSubMatrix->addrule('D', 'E');
        $oSubMatrix->addrule('K', 'R', 'H');
        $oSubMatrix->addrule('X');
        $sequenceMatchManager->setSubMatrix($oSubMatrix);

        // Setting the 2 sequences
        $sSeq1 = $sequenceManager->subSeq(2,100);
        $sSeq2 = $sequenceManager->subSeq(100,100);

        // Computes the Hamming Distance between two sequences
        $iDistance = $sequenceMatchManager->hamdist($sSeq1, $sSeq2);

        // Compares two letters $let1 and $let2 and returns another letter
        // indicating if the two were exact matches, partial matches, or non-matches
        $sCompare1 = $sequenceMatchManager->compareLetter('A', 'T');
        $sCompare2 = $sequenceMatchManager->compareLetter('A', 'A');

        // Computes the Levenshtein Distance between two sequences with equal/unequal lengths
        $iLevdist = $sequenceMatchManager->levdist($sSeq1, $sSeq2);

        // Extended version of levdist() which accepts strings with length greater than 255 but not to exceed 1024
        $iXLevdist = $sequenceMatchManager->xlevdist($sSeq1, $sSeq2);

        // Matching results
        $sMatch = $sequenceMatchManager->match($sSeq1, $sSeq2);

        $sCode =
'/**
  * Here is some samples of how to use the functions
  * @param   SequenceMatchInterface $sequenceMatchManager
  * @param   SequenceAlignmentInterface $sequenceAlignmentManager
  * @param   SequenceInterface $sequenceManager
  * @return  Response
  * @throws  \Exception
  */
public function sequencematch(
    SequenceMatchInterface $sequenceMatchManager,
    SequenceAlignmentInterface $sequenceAlignmentManager,
    SequenceInterface $sequenceManager
){
    $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
    $sequenceAlignmentManager->setFormat("FASTA");
    $sequenceAlignmentManager->parseFile();
    $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
    $oSequence->setMolType("DNA");
    $sequenceManager->setSequence($oSequence);

    // Setting the matrix
    $oSubMatrix = new SubMatrix();
    $oSubMatrix->addrule(\'D\', \'E\');
    $oSubMatrix->addrule(\'K\', \'R\', \'H\');
    $oSubMatrix->addrule(\'X\');
    $sequenceMatchManager->setSubMatrix($oSubMatrix);

    // Setting the 2 sequences
    $sSeq1 = $sequenceManager->subSeq(2,100);
    $sSeq2 = $sequenceManager->subSeq(100,100);

    // Computes the Hamming Distance between two sequences
    $iDistance = $sequenceMatchManager->hamdist($sSeq1, $sSeq2);

    // Compares two letters $let1 and $let2 and returns another letter
    // indicating if the two were exact matches, partial matches, or non-matches
    $sCompare1 = $sequenceMatchManager->compareLetter(\'A\', \'T\');
    $sCompare2 = $sequenceMatchManager->compareLetter(\'A\', \'A\');

    // Computes the Levenshtein Distance between two sequences with equal/unequal lengths
    $iLevdist = $sequenceMatchManager->levdist($sSeq1, $sSeq2);

    // Extended version of levdist() which accepts strings with length greater than 255 but not to exceed 1024
    $iXLevdist = $sequenceMatchManager->xlevdist($sSeq1, $sSeq2);

    // Matching results
    $sMatch = $sequenceMatchManager->match($sSeq1, $sSeq2);


    return $this->render(\'default/sequencematch.html.twig\',
        [
            \'submatrix\' => $oSubMatrix,
            \'sequence1\' => $sSeq1,
            \'sequence2\' => $sSeq2,
            \'distance\' => $iDistance,
            \'compare1\' => $sCompare1,
            \'compare2\' => $sCompare2,
            \'levdist\' => $iLevdist,
            \'xlevdist\' => $iXLevdist,
            \'match\' => $sMatch
        ]
    );
}';


        return $this->render('default/sequencematch.html.twig',
            [
                'submatrix'     => $oSubMatrix,
                'sequence1'     => $sSeq1,
                'sequence2'     => $sSeq2,
                'distance'      => $iDistance,
                'compare1'      => $sCompare1,
                'compare2'      => $sCompare2,
                'levdist'       => $iLevdist,
                'xlevdist'      => $iXLevdist,
                'match'         => $sMatch,
                'code'          => CodeHighlighter::php($sCode)
            ]
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @param   RestrictionEnzymeInterface  $restrictionEnzymeManager
     * @param   SequenceInterface           $sequenceManager
     * @param   SequenceAlignmentInterface  $sequenceAlignmentManager
     * @return  Response
     * @throws  \Exception
     */
    public function restrictionenzyme(
        RestrictionEnzymeInterface $restrictionEnzymeManager,
        SequenceInterface $sequenceManager,
        SequenceAlignmentInterface $sequenceAlignmentManager
    ) {
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();
        $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        $oSequence->setMolType("DNA");
        $sequenceManager->setSequence($oSequence);
        $restrictionEnzymeManager->setSequenceManager($sequenceManager);

        // Gets the enzyme "AatI" properties
        $restrictionEnzymeManager->parseEnzyme('AatI', null, null, "inner");
        $oEnzymeParsed = $restrictionEnzymeManager->getEnzyme();

        // Cuts a DNA sequence into fragments using the restriction enzyme object - patpos group
        $sCutseq = $restrictionEnzymeManager->cutSeq();
        // Patposo group
        $sCutseq2 = $restrictionEnzymeManager->cutSeq('O');

        // Searching the database (api) of endonucleases for a particular restriction enzyme
        $aList = $restrictionEnzymeManager->findRestEn("AGGCCT"); // fetch pattern only
        $aList2 = $restrictionEnzymeManager->findRestEn("AGGCCT",3); // fetch pattern and cutpos
        $aList3 = $restrictionEnzymeManager->findRestEn(null,3); // fetch Cutpos
        $aList4 = $restrictionEnzymeManager->findRestEn(null,null, 6); // fetch Length
        $aList5 = $restrictionEnzymeManager->findRestEn(null,3, 6); // fetch Cutpos And Plen

        $sCode =
'/**
  * Here is some samples of how to use the functions
  * @param   RestrictionEnzymeInterface  $restrictionEnzymeManager
  * @param   SequenceInterface           $sequenceManager
  * @param   SequenceAlignmentInterface  $sequenceAlignmentManager
  * @return  Response
  * @throws  \Exception
  */
public function restrictionenzyme(
    RestrictionEnzymeInterface $restrictionEnzymeManager,
    SequenceInterface $sequenceManager,
    SequenceAlignmentInterface $sequenceAlignmentManager
) {
    $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
    $sequenceAlignmentManager->setFormat("FASTA");
    $sequenceAlignmentManager->parseFile();
    $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
    $oSequence->setMolType("DNA");
    $sequenceManager->setSequence($oSequence);
    $restrictionEnzymeManager->setSequenceManager($sequenceManager);

    // Gets the enzyme "AatI" properties
    $restrictionEnzymeManager->parseEnzyme(\'AatI\', null, null, "inner");
    $oEnzymeParsed = $restrictionEnzymeManager->getEnzyme();

    // Cuts a DNA sequence into fragments using the restriction enzyme object - patpos group
    $sCutseq = $restrictionEnzymeManager->cutSeq();
    // Patposo group
    $sCutseq2 = $restrictionEnzymeManager->cutSeq(\'O\');

    // Searching the database (api) of endonucleases for a particular restriction enzyme
    $aList = $restrictionEnzymeManager->findRestEn("AGGCCT"); // fetch pattern only
    $aList2 = $restrictionEnzymeManager->findRestEn("AGGCCT",3); // fetch pattern and cutpos
    $aList3 = $restrictionEnzymeManager->findRestEn(null,3); // fetch Cutpos
    $aList4 = $restrictionEnzymeManager->findRestEn(null,null, 6); // fetch Length
    $aList5 = $restrictionEnzymeManager->findRestEn(null,3, 6); // fetch Cutpos And Plen

    return $this->render(\'default/restrictionenzyme.html.twig\',
        [
            "enzymeParsed"  => $oEnzymeParsed,
            "cutseq"        => $sCutseq,
            "cutseq2"       => $sCutseq2,
            "list"          => $aList,
            "list2"         => $aList2,
            "list3"         => $aList3,
            "list4"         => $aList4,
            "list5"         => $aList5
        ]
    );
}';

        return $this->render('default/restrictionenzyme.html.twig',
            [
                "enzymeParsed"  => $oEnzymeParsed,
                "cutseq"        => $sCutseq,
                "cutseq2"       => $sCutseq2,
                "list"          => $aList,
                "list2"         => $aList2,
                "list3"         => $aList3,
                "list4"         => $aList4,
                "list5"         => $aList5,
                'code'          => CodeHighlighter::php($sCode)
            ]
        );
    }
}