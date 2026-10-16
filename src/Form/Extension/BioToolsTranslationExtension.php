<?php
/**
 * Traduction des formulaires de amelaye/biotools.
 */
namespace App\Form\Extension;

use Amelaye\BioTools\Form\ChaosGameRepresentationType;
use Amelaye\BioTools\Form\DistanceAmongSequencesType;
use Amelaye\BioTools\Form\DnaToProteinType;
use Amelaye\BioTools\Form\FastaUploaderType;
use Amelaye\BioTools\Form\FindPalindromesType;
use Amelaye\BioTools\Form\FormulasType;
use Amelaye\BioTools\Form\MeltingTemperatureType;
use Amelaye\BioTools\Form\MicroArrayDataAnalysisType;
use Amelaye\BioTools\Form\MicrosatelliteRepeatsFinderType;
use Amelaye\BioTools\Form\OligoNucleotideFrequencyType;
use Amelaye\BioTools\Form\PcrAmplificationType;
use Amelaye\BioTools\Form\ProteinPropertiesType;
use Amelaye\BioTools\Form\ProteinToDnaType;
use Amelaye\BioTools\Form\RandomSequencesType;
use Amelaye\BioTools\Form\ReduceAlphabetType;
use Amelaye\BioTools\Form\RestrictionEnzymeDigestType;
use Amelaye\BioTools\Form\SequenceAlignmentType;
use Amelaye\BioTools\Form\SequenceManipulationType;
use Amelaye\BioTools\Form\SkewsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BioToolsTranslationExtension
 *
 * Les formulaires de biotools écrivent leurs libellés en anglais, en dur. Cette extension les
 * range dans le domaine de traduction « biotools », dont les clés sont ces libellés anglais
 * (translations/biotools.fr.yaml) : les champs et les choix en héritent. Un libellé absent du
 * catalogue s'affiche tel quel, donc en anglais.
 *
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class BioToolsTranslationExtension extends AbstractTypeExtension
{
    /**
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [
            ChaosGameRepresentationType::class,
            DistanceAmongSequencesType::class,
            DnaToProteinType::class,
            FastaUploaderType::class,
            FindPalindromesType::class,
            FormulasType::class,
            MeltingTemperatureType::class,
            MicroArrayDataAnalysisType::class,
            MicrosatelliteRepeatsFinderType::class,
            OligoNucleotideFrequencyType::class,
            PcrAmplificationType::class,
            ProteinPropertiesType::class,
            ProteinToDnaType::class,
            RandomSequencesType::class,
            ReduceAlphabetType::class,
            RestrictionEnzymeDigestType::class,
            SequenceAlignmentType::class,
            SequenceManipulationType::class,
            SkewsType::class,
        ];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'biotools');
    }
}
