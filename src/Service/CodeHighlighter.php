<?php
/**
 * Coloration syntaxique des extraits de code affichés par les pages d'exemples.
 */
namespace App\Service;

use GeSHi;

/**
 * Class CodeHighlighter
 *
 * Enveloppe GeSHi avec une configuration unique pour toute l'application :
 * sortie en classes CSS (et non en styles inline) pour que le thème sombre de
 * public/css/biophp-doc.css puisse s'appliquer, plus une gouttière de numéros
 * de ligne. Les classes produites (kw1, st0, co1, re0...) sont celles de GeSHi.
 *
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class CodeHighlighter
{
    /**
     * Colore un extrait de code PHP.
     *
     * @param string $sCode Code source à colorer.
     * @return string HTML prêt à être inséré (filtre |raw côté Twig).
     */
    public static function php(string $sCode): string
    {
        return self::render($sCode, 'php');
    }

    /**
     * @param string $sCode    Code source à colorer.
     * @param string $sLanguage Langage reconnu par GeSHi.
     * @return string
     */
    public static function render(string $sCode, string $sLanguage = 'php'): string
    {
        $oGeshi = new GeSHi($sCode, $sLanguage);
        $oGeshi->enable_classes();
        $oGeshi->set_overall_class('bp-code');
        $oGeshi->set_header_type(GESHI_HEADER_PRE_TABLE);
        $oGeshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
        $oGeshi->set_tab_width(4);

        return $oGeshi->parse_code();
    }
}
