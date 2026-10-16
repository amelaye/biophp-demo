<?php
/**
 * Code samples controller
 * New in biophp 2.0: the Phylogenetics domain - distance matrices, UPGMA and neighbor-joining trees,
 * and the Newick format.
 */
namespace App\Controller;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidNewickException;
use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\NeighborJoiningInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\NewickReaderInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\UpgmaTreeBuilderInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CodeHighlighter;

/**
 * Class PhylogeneticsController
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PhylogeneticsController extends AbstractController
{
    /**
     * Distances read on a tree: the sum of the branch lengths on the path between two leaves
     * @param   PhylogeneticNode    $oTree
     * @return  array               leaf name => leaf name => distance
     */
    private function treeDistances(PhylogeneticNode $oTree): array
    {
        // depth of each leaf, and the depth of each of its ancestors
        $aPaths = [];
        $fWalk = function (PhylogeneticNode $oNode, float $fDepth, array $aAncestors) use (&$fWalk, &$aPaths) {
            $aAncestors[spl_object_id($oNode)] = $fDepth;
            if ($oNode->isLeaf()) {
                $aPaths[$oNode->getName()] = $aAncestors;
            }
            foreach ($oNode->getChildren() as $oChild) {
                $fWalk($oChild, $fDepth + (float) $oChild->getBranchLength(), $aAncestors);
            }
        };
        $fWalk($oTree, 0.0, []);

        $aDistances = [];
        foreach ($aPaths as $sFirst => $aFirst) {
            foreach ($aPaths as $sSecond => $aSecond) {
                // the deepest common ancestor
                $fCommon = max(array_intersect_key($aFirst, $aSecond));
                $aDistances[$sFirst][$sSecond] = end($aFirst) + end($aSecond) - 2 * $fCommon;
            }
        }
        return $aDistances;
    }

    /**
     * @param   UpgmaTreeBuilderInterface   $upgmaTreeBuilder
     * @return  Response
     */
    #[Route('/phylogenetics-upgma', name: 'phylogenetics_upgma')]
    public function upgma(UpgmaTreeBuilderInterface $upgmaTreeBuilder)
    {
        // Distances between the 5S ribosomal RNAs of five bacteria
        $oMatrix = new DistanceMatrix(
            ["Bacillus subtilis", "Bacillus stearothermophilus", "Lactobacillus viridescens",
                "Acholeplasma modicum", "Micrococcus luteus"],
            [
                [ 0, 17, 21, 31, 23],
                [17,  0, 30, 34, 21],
                [21, 30,  0, 28, 39],
                [31, 34, 28,  0, 43],
                [23, 21, 39, 43,  0],
            ]
        );

        $oTree = $upgmaTreeBuilder->build($oMatrix);

        // UPGMA assumes a molecular clock: every leaf is at the same distance from the root
        $aRootToLeaf = [];
        $fWalk = function (PhylogeneticNode $oNode, float $fDepth) use (&$fWalk, &$aRootToLeaf) {
            if ($oNode->isLeaf()) {
                $aRootToLeaf[$oNode->getName()] = $fDepth;
            }
            foreach ($oNode->getChildren() as $oChild) {
                $fWalk($oChild, $fDepth + $oChild->getBranchLength());
            }
        };
        $fWalk($oTree, 0.0);

        $sCode =
'public function upgma(UpgmaTreeBuilderInterface $upgmaTreeBuilder)
{
    // Distances between the 5S ribosomal RNAs of five bacteria
    $oMatrix = new DistanceMatrix(
        ["Bacillus subtilis", "Bacillus stearothermophilus", "Lactobacillus viridescens",
            "Acholeplasma modicum", "Micrococcus luteus"],
        [
            [ 0, 17, 21, 31, 23],
            [17,  0, 30, 34, 21],
            [21, 30,  0, 28, 39],
            [31, 34, 28,  0, 43],
            [23, 21, 39, 43,  0],
        ]
    );

    $oTree = $upgmaTreeBuilder->build($oMatrix);

    // UPGMA assumes a molecular clock: every leaf is at the same distance from the root
    $aRootToLeaf = [];
    $fWalk = function (PhylogeneticNode $oNode, float $fDepth) use (&$fWalk, &$aRootToLeaf) {
        if ($oNode->isLeaf()) {
            $aRootToLeaf[$oNode->getName()] = $fDepth;
        }
        foreach ($oNode->getChildren() as $oChild) {
            $fWalk($oChild, $fDepth + $oChild->getBranchLength());
        }
    };
    $fWalk($oTree, 0.0);

    return $this->render(\'default/phylogeneticsUpgma.html.twig\', [
        \'matrix\'     => $oMatrix,
        \'tree\'       => $oTree,
        \'newick\'     => $oTree->toNewick(),
        \'rootToLeaf\' => $aRootToLeaf,
    ]);
}';

        return $this->render('default/phylogeneticsUpgma.html.twig', [
            'matrix'     => $oMatrix,
            'tree'       => $oTree,
            'newick'     => $oTree->toNewick(),
            'rootToLeaf' => $aRootToLeaf,
            'code'       => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   UpgmaTreeBuilderInterface   $upgmaTreeBuilder
     * @param   NeighborJoiningInterface    $neighborJoining
     * @return  Response
     */
    #[Route('/phylogenetics-neighbor-joining', name: 'phylogenetics_neighbor_joining')]
    public function neighborJoining(
        UpgmaTreeBuilderInterface $upgmaTreeBuilder,
        NeighborJoiningInterface $neighborJoining
    ) {
        // Distances that fit a tree exactly, but where the lineages evolve at different rates
        $oMatrix = new DistanceMatrix(
            ["a", "b", "c", "d", "e"],
            [
                [0,  5,  9,  9, 8],
                [5,  0, 10, 10, 9],
                [9, 10,  0,  8, 7],
                [9, 10,  8,  0, 3],
                [8,  9,  7,  3, 0],
            ]
        );

        $oNjTree = $neighborJoining->build($oMatrix);
        $oUpgmaTree = $upgmaTreeBuilder->build($oMatrix);

        // Read the distances back on each tree: which one gives the matrix back?
        $aNjDistances = $this->treeDistances($oNjTree);
        $aUpgmaDistances = $this->treeDistances($oUpgmaTree);

        $sCode =
'public function neighborJoining(
    UpgmaTreeBuilderInterface $upgmaTreeBuilder,
    NeighborJoiningInterface $neighborJoining
) {
    // Distances that fit a tree exactly, but where the lineages evolve at different rates
    $oMatrix = new DistanceMatrix(
        ["a", "b", "c", "d", "e"],
        [
            [0,  5,  9,  9, 8],
            [5,  0, 10, 10, 9],
            [9, 10,  0,  8, 7],
            [9, 10,  8,  0, 3],
            [8,  9,  7,  3, 0],
        ]
    );

    $oNjTree = $neighborJoining->build($oMatrix);
    $oUpgmaTree = $upgmaTreeBuilder->build($oMatrix);

    // Read the distances back on each tree (sum of the branches between two leaves):
    // which one gives the matrix back?
    $aNjDistances = $this->treeDistances($oNjTree);
    $aUpgmaDistances = $this->treeDistances($oUpgmaTree);

    return $this->render(\'default/phylogeneticsNeighborJoining.html.twig\', [
        \'matrix\'          => $oMatrix,
        \'njTree\'          => $oNjTree,
        \'upgmaTree\'       => $oUpgmaTree,
        \'njDistances\'     => $aNjDistances,
        \'upgmaDistances\'  => $aUpgmaDistances,
    ]);
}';

        return $this->render('default/phylogeneticsNeighborJoining.html.twig', [
            'matrix'          => $oMatrix,
            'njTree'          => $oNjTree,
            'upgmaTree'       => $oUpgmaTree,
            'njDistances'     => $aNjDistances,
            'upgmaDistances'  => $aUpgmaDistances,
            'code'            => CodeHighlighter::php($sCode)
        ]);
    }

    /**
     * @param   NewickReaderInterface   $newickReader
     * @return  Response
     */
    #[Route('/phylogenetics-newick', name: 'phylogenetics_newick')]
    public function newick(NewickReaderInterface $newickReader)
    {
        // The great apes: quoted names, an NHX annotation and a comment are allowed
        $sNewick = "((('Homo sapiens'[&&NHX:S=human],('Pan troglodytes','Pan paniscus')),"
            . "'Gorilla gorilla'),'Pongo abelii')Hominidae; [no branch lengths: topology only]";

        $oTree = $newickReader->read($sNewick);

        // A malformed tree is refused with the position of the error
        $aErrors = [];
        foreach (["((Human,Chimpanzee),Gorilla;", "((Human,Chimpanzee),Gorilla)", "((Human,),Gorilla);"] as $sBroken) {
            try {
                $newickReader->read($sBroken);
            } catch (InvalidNewickException $e) {
                $aErrors[$sBroken] = $e->getMessage();
            }
        }

        $sCode =
'public function newick(NewickReaderInterface $newickReader)
{
    // The great apes: quoted names, an NHX annotation and a comment are allowed
    $sNewick = "(((\'Homo sapiens\'[&&NHX:S=human],(\'Pan troglodytes\',\'Pan paniscus\')),"
        . "\'Gorilla gorilla\'),\'Pongo abelii\')Hominidae; [no branch lengths: topology only]";

    $oTree = $newickReader->read($sNewick);

    // A malformed tree is refused with the position of the error
    $aErrors = [];
    foreach (["((Human,Chimpanzee),Gorilla;", "((Human,Chimpanzee),Gorilla)", "((Human,),Gorilla);"] as $sBroken) {
        try {
            $newickReader->read($sBroken);
        } catch (InvalidNewickException $e) {
            $aErrors[$sBroken] = $e->getMessage();
        }
    }

    return $this->render(\'default/phylogeneticsNewick.html.twig\', [
        \'input\'   => $sNewick,
        \'tree\'    => $oTree,
        \'leaves\'  => $oTree->getLeafNames(),
        \'newick\'  => $oTree->toNewick(),
        \'errors\'  => $aErrors,
    ]);
}';

        return $this->render('default/phylogeneticsNewick.html.twig', [
            'input'   => $sNewick,
            'tree'    => $oTree,
            'leaves'  => $oTree->getLeafNames(),
            'newick'  => $oTree->toNewick(),
            'errors'  => $aErrors,
            'code'    => CodeHighlighter::php($sCode)
        ]);
    }
}
