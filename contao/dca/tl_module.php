<?php

declare(strict_types=1);

/*
 * Ohne Palette zeigt Contao fuer einen per #[AsFrontendModule] registrierten
 * Typ nur ein leeres Formular – der Typ ist waehlbar, aber cssID und customTpl
 * fehlen und das Modul laesst sich nicht einordnen.
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['exakt_toggle'] =
    '{title_legend},name,type;'.
    '{template_legend:hide},customTpl;'.
    '{expert_legend:hide},cssID';
