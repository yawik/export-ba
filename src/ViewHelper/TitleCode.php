<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\ViewHelper;

use Laminas\View\Helper\AbstractHelper;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class TitleCode extends AbstractHelper
{
    public function __invoke(string $title)
    {
        return $this->guessTitleCode($title);
    }


    /*

    == Oberarzt ==

    14763 : Assistenzarzt/-ärztin
    28019 : Chefarzt/-ärztin
    28020 : Stationsarzt/-ärztin
    58709 : Arzt/Ärztin
    100518 : Sportpsychologe/-psychologin
    129305 : Weiterbildungsassistent/in (Arzt/Ärztin

    */
    private function guessTitleCode($title) {

        $titleCode = 58709; // Arzt/Ärztin

        if (preg_match('/Assistenzarzt/', $title)) {
            $titleCode=14763;
        } elseif (preg_match('/(Chefarzt|Chefärztin)/', $title)) {
            $titleCode=28019;
        } elseif (preg_match('/(Stationsarzt|Stationsärztin)/', $title)) {
            $titleCode=28020;
        } elseif (preg_match('/Sportpsychologe/', $title)) {
            $titleCode=100518;
        } elseif (preg_match('/(Facharzt|Oberarzt)/', $title)) {
            $titleCode=$this->guessTitleCodeForFacharzt($title);
        }
        return $titleCode;
    }

    /*

    == Facharzt ==

    8650 : Facharzt/-ärztin - Innere Medizin
    8651 : Facharzt/-ärztin - Kinder- und Jugendmedizin
    8652 : Facharzt/-ärztin - Allgemeinmedizin (Hausarzt/-ärztin)
    8654 : Facharzt/-ärztin - Allgemeinchirurgie
    8655 : Facharzt/-ärztin - Herzchirurgie
    8656 : Facharzt/-ärztin - Kinderchirurgie
    8657 : Facharzt/-ärztin - Mund-Kiefer-Gesichtschirurgie
    8658 : Facharzt/-ärztin - Neurochirurgie
    8659 : Facharzt/-ärztin - Orthopädie und Unfallchirurgie
    8660 : Facharzt/-ärztin - Plastische und Ästhetische Chirurgie
    8662 : Facharzt/-ärztin - Hals-Nasen-Ohrenheilkunde
    8664 : Facharzt/-ärztin - Augenheilkunde
    8666 : Facharzt/-ärztin - Frauenheilkunde und Geburtshilfe
    8668 : Facharzt/-ärztin - Radiologie
    8669 : Facharzt/-ärztin - Nuklearmedizin
    8670 : Facharzt/-ärztin - Strahlentherapie
    8676 : Facharzt/-ärztin - Kinder- u. Jugendpsychiat. u. -psychoth.
    8678 : Facharzt/-ärztin - Neurologie
    8679 : Facharzt/-ärztin - Psychiatrie und Psychotherapie
    8680 : Facharzt/-ärztin - Psychosom. Medizin u. Psychotherapie
    8682 : Facharzt/-ärztin - Anästhesiologie
    8683 : Facharzt/-ärztin - Anatomie
    8684 : Facharzt/-ärztin - Arbeitsmedizin
    8685 : Facharzt/-ärztin - Biochemie
    8686 : Facharzt/-ärztin - Haut- und Geschlechtskrankheiten
    8687 : Facharzt/-ärztin - Humangenetik
    8688 : Facharzt/-ärztin - Hygiene und Umweltmedizin
    8689 : Facharzt/-ärztin - Klinische Pharmakologie
    8690 : Facharzt/-ärztin - Laboratoriumsmedizin
    8691 : Facharzt/-ärztin - Mikrobiol./Virolog./Infektionsepi.
    8692 : Facharzt/-ärztin - Neuropathologie
    8693 : Facharzt/-ärztin - Öffentliches Gesundheitswesen
    8694 : Facharzt/-ärztin - Pathologie
    8695 : Facharzt/-ärztin - Physikalische/Rehabilitative Medizin
    8696 : Facharzt/-ärztin - Physiologie
    8697 : Facharzt/-ärztin - Rechtsmedizin
    8698 : Facharzt/-ärztin - Transfusionsmedizin
    8699 : Facharzt/-ärztin - Urologie
    27434 : Facharzt/-ärztin - Gefäßchirurgie
    27435 : Facharzt/-ärztin - Thoraxchirurgie
    27436 : Facharzt/-ärztin - Viszeralchirurgie
    27437 : Facharzt/-ärztin - Sprach-, Stimm- u. kindl. Hörstörungen
    27438 : Facharzt/-ärztin - Pharmakologie und Toxikologie


    58709 : Arzt/Ärztin
    */
    private function guessTitleCodeForFacharzt($title = null)
    {

        $specialty = [
            'Innere Medizin' => 8650,
            'Jugendmedizin' => 8651,
            '(Allgemeinmedizin|Hausarzt)' => 8652,
            'Allgemeinchirurgie' => 8654,
            'Herzchirurgie' => 8655,
            'Kinderchirurgie' => 8656,
            'Mund-Kiefer-Gesichtschirurgie' => 8657,
            'Neurochirurgie' => 8657,
            '(Orthopädie|Unfallchirurgie)' => 8659,
            'Plastische und Ästhetische Chirurgie' => 8660,
            '(Hals-Nasen-Ohrenheilkunde|HNO)' => 8662,
            'Augen' => 8664,
            '(Frauenheilkunde|Geburtshilfe)' => 8666,
            'Radiologie' => 8668,
            'Nuklearmedizin' => 8669,
            'Strahlentherapie' => 8670,
            '(Kinderpsych|Jugendpsych)' => 8676,
            'Neurologie' => 8678,
            '(Psychiatrie)' => 8679,
            'Psychosom' => 8680,
            'Anästhesiologie' => 8682,
            'Anatomie' => 8683,
            'Arbeitsmedizin' => 8684,
            'Biochemie' => 8685,
            '(Haut|Geschlechtskrankheiten|Derma)' => 8686,
            'Humangenetik' => 8687,
            '(Hygiene|Umweltmedizin)' => 8688,
            'Pharmakologie' => 8689,
            'Labor' => 8690,
            '(Mikrobiol|Virolog|Infektionsepi)' => 8691,
            'Neuropathologie' => 8692,
            '(Öffentlich|Gesundheitswesen)' => 8693,
            'Pathologie' => 8694,
            '(Physikalische|Rehabili)' => 8695,
            'Physiologie' => 8696,
            'Rechtsmedizin' => 8697,
            'Transfusionsmedizin' => 8698,
            'Urologie' => 8699,
            'Gefäßchirurgie' => 27434,
            'Thoraxchirurgie' => 27435,
            'Viszeralchirurgie' => 27436,
            '(Sprachstörung|Hörstörungen)' => 27437,
            'Toxikologie' => 27438
        ];

        foreach($specialty as $key => $val) {
            if (preg_match('/' . $key . '/', $title)) return $val;
        }

        return 58709; // Arzt/Ärztin;
    }

}
