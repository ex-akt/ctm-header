<?php

declare(strict_types=1);

/*
 * Der ThemeCompiler uebersetzt jede Datei aus TC_SOURCES['files'] EINZELN und
 * stellt ihr dabei die configFiles ALLER Pakete voran (FileCompiler::compile()).
 * Deshalb gilt:
 *
 *   - $-Variablen aus core/_config.scss (z.B. $navigation-behaviour-min-width)
 *     stehen hier zur Verfuegung.
 *   - Mixins aus ctm_utils/ (media-breakpoint()) NICHT – die kommen erst ueber
 *     _theme.scss und damit nur im Core-Kompilat an. Media Queries hier also
 *     ausgeschrieben.
 *   - Das Projekt-SCSS sieht dieses Paket nie. Alles, was ein Projekt anpassen
 *     koennen soll, muss als CSS-Variable herauskommen (ctm_base-xh/_vars.scss).
 */

// SCSS-Konfiguration des Pakets bekannt machen
$GLOBALS['TC_SOURCES']['configFiles'][] = 'bundles/exaktctmheader/framework/scss/_config.scss';

// Einstiegsdatei anhaengen statt voranstellen: Die Regeln greifen bewusst
// nach ctm-push-navigation, dessen .pn-toggle sie ergaenzen.
$GLOBALS['TC_SOURCES']['files'][] = 'bundles/exaktctmheader/framework/scss/_header.scss';
