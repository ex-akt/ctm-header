<?php
// Erzeugt style-manager-exakt-header.xml mit korrekt serialisierten Feldern.

$archiveId = 900; // hoch genug, um nicht mit style-manager-core.xml zu kollidieren

$children = [
    [
        'alias'       => 'size',
        'title'       => 'Größe des Symbols',
        'description' => 'Skaliert das Burger-Symbol des Menü-Umschalters.',
        'cssClasses'  => [
            ['key' => 'tgl-small', 'value' => 'Klein'],
            ['key' => 'tgl-large', 'value' => 'Groß'],
        ],
        'modules'     => ['exakt_toggle'],
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

  Nur Klassen eintragen, fuer die dieses Paket auch eine CSS-Regel mitbringt
  (ctm_modules-xh/_toggle.scss). Eine Gruppe ohne Regel waere im Backend
  waehlbar und im Frontend wirkungslos.

  extendModule/modules gehoeren ans CHILD, nicht ans Archiv - ohne die
  Whitelist bleibt die Gruppe im Modul unsichtbar, waehrend clearClasses() die
  Klasse trotzdem aus dem CSS-Feld filtert. Sie waere dann nirgends mehr
  pflegbar.
-->

HEAD;

$out .= "<archives>\n";
$out .= '  <archive identifier="mHeaderToggle">'."\n";
$out .= '    <field title="id">'.$archiveId."</field>\n";
$out .= '    <field title="title">'.$esc('Menü-Umschalter')."</field>\n";
$out .= '    <field title="identifier">mHeaderToggle</field>'."\n";
// Nur Global/Component/Element sind in style-manager-core.xml belegt; ein
// erfundener Wert legt im Backend eine eigene, unerwartete Gruppe an.
$out .= '    <field title="groupAlias">'.$esc('Component')."</field>\n";
$out .= '    <field title="sorting">128</field>'."\n";
$out .= "    <children>\n";

$sorting = 128;

foreach ($children as $c) {
    $out .= '      <child alias="'.$esc($c['alias']).'">'."\n";
    $out .= '        <field title="pid">'.$archiveId."</field>\n";
    $out .= '        <field title="sorting">'.$sorting."</field>\n";
    $out .= '        <field title="alias">'.$esc($c['alias'])."</field>\n";
    $out .= '        <field title="title">'.$esc($c['title'])."</field>\n";
    $out .= '        <field title="description">'.$esc($c['description'])."</field>\n";
    $out .= '        <field title="cssClasses">'.$esc(serialize($c['cssClasses']))."</field>\n";
    $out .= '        <field title="blankOption">1</field>'."\n";
    $out .= '        <field title="extendModule">1</field>'."\n";
    $out .= '        <field title="modules">'.$esc(serialize($c['modules']))."</field>\n";
    $out .= "      </child>\n";

    $sorting += 128;
}

$out .= "    </children>\n";
$out .= "  </archive>\n";
$out .= "</archives>\n";

$target = getenv('HOME').'/dev/repos/ctm-header/contao/templates/style-manager-exakt-header.xml';
file_put_contents($target, $out);

echo "geschrieben: $target\n";

// Gegenprobe: laesst sich die Datei parsen und sind die Felder deserialisierbar?
$xml = simplexml_load_file($target);

if (false === $xml) {
    exit("FEHLER: XML nicht parsebar\n");
}

foreach ($xml->archive->children->child as $child) {
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
