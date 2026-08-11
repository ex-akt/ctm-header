<?php
// Erzeugt style-manager-exakt-header.xml mit korrekt serialisierten Feldern.
//
// Eigene Archive statt Andocken an die leeren Core-Archive gHeader/gFooter:
// Fuer YAML-Konfigurationen ist das Zusammenfuehren nach identifier
// ausdruecklich vorgesehen (Config::parseYamlConfiguration prueft
// isset($styleArchives[$archiveIdent])), fuer XML laeuft der Import ueber
// ImportController::importXmlFiles – ohne zugesicherte Merge-Semantik und
// abhaengig von der Dateireihenfolge. Ein eigener identifier kann damit nicht
// kollidieren.

$archives = [
    [
        'id'         => 900,
        'identifier' => 'mHeaderToggle',
        'title'      => 'Menü-Umschalter',
        // Nur Global/Component/Element sind in style-manager-core.xml belegt; ein
        // erfundener Wert legt im Backend eine eigene, unerwartete Gruppe an.
        'groupAlias' => 'Component',
        'sorting'    => 128,
        'children'   => [
            [
                'alias'       => 'size',
                'title'       => 'Größe des Symbols',
                'description' => 'Skaliert das Burger-Symbol des Menü-Umschalters.',
                'cssClasses'  => [
                    ['key' => 'tgl-small', 'value' => 'Klein'],
                    ['key' => 'tgl-large', 'value' => 'Groß'],
                ],
                'extend'      => ['extendModule'],
                'modules'     => ['exakt_toggle'],
            ],
        ],
    ],
    [
        'id'         => 901,
        'identifier' => 'xhHeaderFooter',
        'title'      => 'Header & Footer',
        // Global, weil die Auswahl am Layout haengt und nicht an einem Modul –
        // dieselbe Einordnung wie die Core-Gruppe "Header behaviour".
        'groupAlias' => 'Global',
        'sorting'    => 256,
        'children'   => [
            [
                'alias'       => 'width',
                'title'       => 'Inhaltsbreite',
                'description' => 'Begrenzt die Inhaltsbreite von Header und Footer. Ohne Auswahl laufen beide bis an den Fensterrand, während der Seiteninhalt mittig steht. Die Stufen entsprechen den Artikel-Außenabständen (art-px), damit Header, Inhalt und Footer bündig stehen.',
                'cssClasses'  => [
                    ['key' => 'cnt-w-1', 'value' => 'Breit (wie art-px-1)'],
                    ['key' => 'cnt-w-2', 'value' => 'Mittel (wie art-px-2)'],
                    ['key' => 'cnt-w-3', 'value' => 'Schmal (wie art-px-3)'],
                ],
                // Nur das Layout. extendPage waere denkbar, haette aber keine
                // Entsprechung im CSS des Pakets: --xh-cnt-wdth vererbt von
                // <body>, und die Seitenklasse landet am selben Element – die
                // Auswahl liesse sich also pro Seite setzen, ohne dass jemand
                // erwartet, dass Header und Footer je Seite anders breit sind.
                'extend'      => ['extendLayout'],
            ],
        ],
    ],
];

$esc = static fn (string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

$out  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$out .= <<<'HEAD'
<!--
  Style-Manager-Konfiguration des Pakets.

  Contao\StyleManager\Config::loadBundleConfiguration() parst alle
  style-manager-*.xml unter vendor/*/*/contao/templates/ ZUR LAUFZEIT. Das Paket
  zu installieren genuegt also - die Datei muss weder in ein Projekt kopiert noch
  im Backend importiert werden, und tl_style_manager bleibt dabei leer.

  Diese Datei wird generiert, nicht von Hand gepflegt:
  php bin/generate-style-manager-xml.php

  Nur Klassen eintragen, fuer die dieses Paket auch eine CSS-Regel mitbringt
  (ctm_modules-xh/). Eine Gruppe ohne Regel waere im Backend waehlbar und im
  Frontend wirkungslos.

  extendModule/modules gehoeren ans CHILD, nicht ans Archiv - ohne die
  Whitelist bleibt die Gruppe im Modul unsichtbar, waehrend clearClasses() die
  Klasse trotzdem aus dem CSS-Feld filtert. Sie waere dann nirgends mehr
  pflegbar.
-->

HEAD;

$out .= "<archives>\n";

foreach ($archives as $archive) {
    $out .= '  <archive identifier="'.$esc($archive['identifier']).'">'."\n";
    $out .= '    <field title="id">'.$archive['id']."</field>\n";
    $out .= '    <field title="title">'.$esc($archive['title'])."</field>\n";
    $out .= '    <field title="identifier">'.$esc($archive['identifier'])."</field>\n";
    $out .= '    <field title="groupAlias">'.$esc($archive['groupAlias'])."</field>\n";
    $out .= '    <field title="sorting">'.$archive['sorting']."</field>\n";
    $out .= "    <children>\n";

    $sorting = 128;

    foreach ($archive['children'] as $c) {
        $out .= '      <child alias="'.$esc($c['alias']).'">'."\n";
        $out .= '        <field title="pid">'.$archive['id']."</field>\n";
        $out .= '        <field title="sorting">'.$sorting."</field>\n";
        $out .= '        <field title="alias">'.$esc($c['alias'])."</field>\n";
        $out .= '        <field title="title">'.$esc($c['title'])."</field>\n";
        $out .= '        <field title="description">'.$esc($c['description'])."</field>\n";
        $out .= '        <field title="cssClasses">'.$esc(serialize($c['cssClasses']))."</field>\n";
        $out .= '        <field title="blankOption">1</field>'."\n";

        foreach ($c['extend'] as $flag) {
            $out .= '        <field title="'.$esc($flag).'">1</field>'."\n";
        }

        if (isset($c['modules'])) {
            $out .= '        <field title="modules">'.$esc(serialize($c['modules']))."</field>\n";
        }

        $out .= "      </child>\n";

        $sorting += 128;
    }

    $out .= "    </children>\n";
    $out .= "  </archive>\n";
}

$out .= "</archives>\n";

$target = getenv('HOME').'/dev/repos/ctm-header/contao/templates/style-manager-exakt-header.xml';
file_put_contents($target, $out);

echo "geschrieben: $target\n";

// Gegenprobe: laesst sich die Datei parsen und sind die Felder deserialisierbar?
$xml = simplexml_load_file($target);

if (false === $xml) {
    exit("FEHLER: XML nicht parsebar\n");
}

foreach ($xml->archive as $archive) {
    echo (string) $archive['identifier']."\n";

    foreach ($archive->children->child as $child) {
        foreach ($child->field as $field) {
            if (\in_array((string) $field['title'], ['cssClasses', 'modules'], true)) {
                $val = @unserialize((string) $field, ['allowed_classes' => false]);

                if (false === $val) {
                    exit('FEHLER: '.$field['title']." nicht deserialisierbar\n");
                }

                echo '  ok  '.$field['title'].' = '.json_encode($val, JSON_UNESCAPED_UNICODE)."\n";
            }
        }
    }
}
