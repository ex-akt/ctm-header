# ex-akt/ctm-header

Header-Bausteine für Contao-Projekte mit dem [Theme-Manager](https://github.com/oveleon)
– zentral gepflegt statt in jedem Kundenprojekt neu aufgebaut.

## Warum das Paket existiert

`ctm-push-navigation` initialisiert sich nur, wenn im DOM etwas auf seinen
Default-Selektor `.mod_toggle` passt. Contao bringt für diesen Zweck keinen
Modultyp mit, also stand das Markup in den Projekten bisher je als **HTML-Modul**
in `tl_module.html`:

```html
<div class="mod_toggle pn-toggle pn-show"><span class="burger-box"><span class="burger-inner"></span></span></div>
```

Das sind 114 Zeichen in der Datenbank – nicht im Git, nicht im Deploy
(`database:release` überschreibt, es merged nicht), nicht zwischen Projekten
diffbar und nicht per `composer update` zu reparieren. Eine Korrektur in einem
Projekt erreichte die anderen nie. Beim Toggle-CSS war die Drift entsprechend
schon messbar: dieselbe Datei, in drei Regeln auseinandergelaufen.

Dieses Paket macht daraus Code.

## Inhalt

| Baustein | Was er löst |
|---|---|
| Modultyp **Menü-Umschalter** (`exakt_toggle`) | Markup im Template statt in der Datenbank |
| `ctm_modules-xh/_toggle.scss` | zeichnet das Burger-Symbol – `ctm-push-navigation` liefert dafür nur Sichtbarkeit und Button-Reset |
| `ctm_modules-xh/_navigation.scss` | Umschaltung Hauptnavigation ↔ Push-Navigation inkl. der `:not(.pn-init)`-Spezifitätsfalle, dazu die Listen-Rücksetzung |
| `ctm_modules-xh/_submenu.scss` | Fläche und Farben des Desktop-Untermenüs – Core und a11y-Paket liefern keine |
| `ctm_modules-xh/_navigation-icons.scss` | die Aufklapp-Pfeile als CSS-Form, weil ihre Glyphen nicht im Projekt-Font landen |
| `ctm_modules-xh/_pushnavigation.scss` | zweite Erscheinungsform des mobilen Menüs: Panel statt Vollbild-Overlay |
| Skript **`exakt_navigation`** (`js_exakt_navigation`) | Init der Push-Navigation mit Scroll-Sperre, „Klick daneben schließt", Schließen bei Sprungmarken und Escape |
| `ctm_modules-xh/_container.scss` | Breite von Header und Footer – der Core begrenzt nur Artikel |
| `style-manager-exakt-header.xml` | Style-Manager-Gruppen für Symbolgröße (am Modul), Inhaltsbreite und mobiles Menü (am Layout), ohne Kopie ins Projekt |

## Installation

```bash
composer require ex-akt/ctm-header
```

In `layout/styles/projekt.scss` (die Datei aus `tl_theme.skinSourceFiles`)
einbinden – **nach** `pushnavigation`, dessen `.pn-toggle` das Paket ergänzt:

```scss
@import "theme";
@import "accessibility";
@import "pushnavigation";
@import "stickyheader";
@import "header";        // ex-akt/ctm-header
```

Ohne Pfadangabe: Der Theme-Compiler setzt das Verzeichnis jeder in
`TC_SOURCES` registrierten Datei als Import-Pfad.

Danach im Backend:

1. Das bisherige HTML-Modul durch ein Modul vom Typ **Menü-Umschalter** ersetzen
   und im Layout an dessen Stelle setzen (Bereich `header`).
2. In `tl_layout.scripts` muss `js_ctm_pushnavigation` stehen – **`scripts`, nicht
   `script`.** Das Singular-Feld ist ein Freitextfeld für eigenen Code, dort
   wirken Template-Namen nicht.
3. `contao-thememanager/ctm-accessibility` muss installiert sein. Das
   Push-Navigation-JS liest `navAriaLabels` ungeschützt, definiert die Variable
   aber nie – ohne das a11y-Paket bricht der Konstruktor mit `ReferenceError` ab
   und das Menü tut kommentarlos nichts.
4. Unter *Layout → Inhaltsbreite* die Breite von Header und Footer wählen. Ohne
   Auswahl bleibt es beim bisherigen Verhalten (volle Fensterbreite) – das
   Aktualisieren des Pakets ändert an einer bestehenden Seite also nichts.
5. Für ein Panel statt des Vollbild-Overlays: unter *Layout → Mobiles Menü* die
   Richtung wählen und in `tl_layout.scripts` `ctm_pushnavigation` durch
   `exakt_navigation` ersetzen (siehe unten).
6. Theme neu kompilieren.

Das Toggle-CSS, die `header .mod_navigation`-Regeln und die
Breitenbegrenzung von Header und Footer aus dem Projekt-SCSS entfernen – sie
kommen jetzt aus dem Paket.

Zwei weitere Regeln, die in Projekten oft danebenstehen, gehören ebenfalls nicht
hierher, sondern sind Framework-Sache:

- **Platz unter dem fixierten Header.** Der Core liefert
  `body.fixed.undock { padding-top: var(--hdr-hght) }`, geschaltet über
  *Layout → Header-Verhalten → Undocked*. Ein eigenes
  `#wrapper { padding-top }` baut das nach. Grund für die Verwechslung:
  `ctm-sticky-header` setzt `position: fixed` unabhängig von der Klasse – ohne
  die Backend-Auswahl fehlt der Platzhalter also trotzdem, und der Reflex ist,
  ihn selbst zu schreiben.
- **Logo und Navigation nebeneinander.** Der Core setzt `header > .inside`
  bereits auf `display: flex`; Ausrichtung und Innenabstand kommen aus
  `$header-alignment-vertical`, `$header-alignment-horizontal` und
  `$header-padding`. Achtung: In `tl_theme.themeConfig` können abweichende
  Werte stehen, die den Core-Default überschreiben (gemessen: `center` statt
  `space-between`). Wer die Projektregel entfernt, ohne das zu prüfen, rückt
  den Header in die Mitte.

## Untermenüs

Fällt erst auf, wenn eine Seite zum ersten Mal Unterseiten bekommt — dann ist
plötzlich nichts erkennbar und nichts lesbar. Das **Aufklappen** selbst liefert
`ctm-accessibility` vollständig (Button `.btn-toggle-submenu`, Schalter
`.nav-expanded` am `li`); es legt dabei die Hover-Variante des Themes still.
Diese Mechanik also nicht nachbauen. Was fehlt, ist die Erscheinung, und die
kommt aus diesem Paket.

**Voraussetzung: `nav nav-x` am Navigationsmodul** (Style-Manager → *Navigation*,
Wert *Horizontal*). Der Core setzt `.submenu { position: relative }` und das
absolute Dropdown ausschließlich unter `.nav-x`. Fehlt die Klasse, findet der
Aufklapp-Button keinen positionierten Vorfahren — gemessen bei 1440px
Fensterbreite: Pfeil bei `x = 1415` statt bei `673`. Ein `nav` allein, wie es
oft von Hand in der cssID steht, genügt nicht.

Was das Paket ergänzt:

- **Fläche.** Weder Core noch a11y-Paket geben der `.level_2` einen Hintergrund.
  Über einem Hero-Bild sind die Unterpunkte damit schlicht nicht lesbar.
- **Farbe ausdrücklich statt geerbt.** `ctm-sticky-header` färbt
  `header .level_1 > li:is(.active, .trail)` und setzt an `> li > a`
  `color: inherit` — die zweite Ebene wechselt sonst ihre Farbe, je nachdem ob
  der Elternpunkt gerade aktiv ist.
- **Platz für den Pfeil**, der absolut am rechten Rand des Elternpunkts sitzt
  und sonst auf dessen letztem Buchstaben klebt.
- **Die Pfeile selbst**, als CSS-Form. Der ThemeCompiler erzeugt `_icon.css` und
  den Icomoon-Font nur aus den im Projekt ausgewählten Icons; die UI-Glyphen der
  Pakete sind nie dabei. Ohne Auswahl entsteht gar keine `_icon.css` (gemessen).
  Betroffen sind `.btn-toggle-submenu::before` (Desktop, `\e985` aus
  `"ctm-icon"`), `.pn-handle.i-navigate_forward` und `.pn-breadcrumb > i`
  (mobil) — die beiden `.i-*`-Klassen sind überhaupt nirgends definiert. Der
  Button ist dabei da, trägt sein `aria-label` und funktioniert; man sieht ihn
  nur nicht.

  Die native Rotation bleibt erhalten: `--btsz` (0deg zu, −180deg offen,
  −90/−270deg im `.level_2`) wird zum Grundwinkel addiert statt ihn zu ersetzen.

```scss
header {
  --xh-sub-bg:    var(--body-bg);        // Fläche des Dropdowns
  --xh-sub-clr:   var(--text-clr-base);
  --xh-sub-clr-a: var(--clr-primary);    // aktiv/hover
  --xh-sub-p:     .5rem 0;               // Innenabstand des Panels
  --xh-sub-i-p:   .5rem 1.25rem;         // je Eintrag
  --xh-sub-shdw:  0 .5rem 1.25rem rgba(0, 0, 0, .14);
  --xh-sub-bdr-r: 0;
  --xh-sub-gap:   1.6em;                 // Platz für den Pfeil
}
```

Hat ein Projekt die Glyphen doch im eigenen Icomoon-Satz, holt es sie mit
`content: '\e985'` und `border: 0` an derselben Stelle zurück.

## Mobiles Menü: Overlay oder Panel

`ctm-push-navigation` zeichnet das mobile Menü als **Vollbild-Overlay**:
`.pn-init` liegt `position: fixed` über `100vw/100vh`, halbtransparent
(`rgba(var(--body-bg-rgb),.85)`) mit `backdrop-filter`, eingeblendet über
`opacity`. „Push" heißt dort nur, dass die *Unterebenen* seitlich hereinfahren
(`.pn-next { translateX(-100%) }`) — nicht das Menü selbst.

Das Paket-JS verwaltet ausschließlich Zustandsklassen; Geometrie steht dort an
keiner Stelle. Ein von der Seite einfahrendes Panel braucht deshalb **keinen
Fork**, nur einen zweiten CSS-Satz auf denselben Klassen. Genau den bringt
`ctm_modules-xh/_pushnavigation.scss` mit, geschaltet über eine Klasse an
`<body>`:

| Layout → Mobiles Menü | Klasse | Ergebnis |
|---|---|---|
| *(ohne Auswahl)* | – | Vollbild-Overlay wie bisher |
| Panel von links | `nav-pnl-left` | Panel fester Breite, fährt von links ein |
| Panel von rechts | `nav-pnl-right` | dasselbe von rechts |

Dazu gehört das Skript **`exakt_navigation`** in `tl_layout.scripts` —
**anstelle** von `ctm_pushnavigation`, nicht zusätzlich: Beide erzeugen eine
Instanz auf `window.pn`, und das Standard-Template käme zuerst zum Zug. Es lädt
dasselbe Paket-JS und nutzt nur dessen vorgesehene Optionen
(`onShow`/`onHide`/`onDestroy`) sowie `isOpen()`; alles Weitere läuft über den
Umschalter, den das Paket selbst erzeugt (`button.pn-btn`). Keine
minifizierten Interna.

Was es ergänzt, und warum das nicht ins Projekt-JS gehört:

- **`nav-open` an `<body>`** — Grundlage für den abgedunkelten Grund und die
  Scroll-Sperre. Als Klasse und nicht als Inline-Style, damit ein Projekt beides
  überschreiben kann.
- **Klick daneben schließt.** Beim Overlay gibt es kein „daneben", beim Panel
  schon.
- **Klick auf einen Menüpunkt schließt.** Führt ein Punkt auf dieselbe Seite
  (Sprungmarke `#anker`), lädt nichts neu — ohne das bleibt das Menü offen über
  dem Ziel stehen. Der häufigste Beschwerdegrund auf One-Pagern.
- **Escape schließt** und gibt den Fokus an den Umschalter zurück. Das Paket
  baut einen Fokus-Zyklus auf; ohne Tastaturausstieg ist das ein Keyboard-Trap.

Drei Dinge, die beim Nachbauen schiefgehen — alle gemessen:

1. **Der Panel-Transform gehört an `.pn-init`, nicht an die `ul`.** Die trägt
   beim Absteigen in die zweite Ebene bereits `.pn-next { translateX(-100%) }`.
   Ein zweiter Transform dort zieht den Panel-Zustand mit: Das Panel führe beim
   Öffnen eines Untermenüs zur Hälfte wieder hinaus.
2. **Der abgedunkelte Grund kann kein Pseudoelement des Panels sein.** Sowohl
   `transform` als auch `backdrop-filter` machen `.pn-init` zum containing block
   für `position: fixed` — der Grund säße im Panel fest. Er hängt deshalb an
   `<body>::after`, mit `z-index: 98` unter dem `header { z-index: 99 }` des
   Core.
3. **Die gestaffelte Einblendung des Pakets braucht `!important`.** Die Regel
   `.pn-active:not(.pn-next) li:nth-of-type(N)` hat Spezifität 0,5,1; ein
   realistischer Selektor von außen bleibt darunter. Im Panel wirkt die
   Staffelung nachgezogen (die Punkte poppen einzeln auf, nachdem das Panel
   schon steht), deshalb ist sie dort auf `0s` gesetzt.

Feinjustage über CSS-Variablen, am besten an `body` oder `header`:

```scss
body {
  --xh-pnl-w:       min(85vw, 21rem);   // Panelbreite
  --xh-pnl-bg:      var(--body-bg);     // Panelgrund
  --xh-pnl-p-x:     1.75rem;            // Innenabstand, wandert mit dem Pfeil
  --xh-pnl-p-y:     1.5rem;
  --xh-pnl-dur:     .3s;                // Fahrtdauer, gilt auch für den Grund
  --xh-pnl-shdw:    0 0 2rem rgba(0, 0, 0, .18);
  --xh-pnl-bd:      rgba(0, 0, 0, .45); // abgedunkelter Grund
  --xh-pnl-bd-blur: 2px;
  --xh-pnl-bd-z:    98;
}
```

**Logo über dem Overlay.** Im Overlay-Modus bleibt das Logo beim Aufklappen
sichtbar – es liegt auf derselben Ebene wie der Umschalter (`--xh-logo-z: 2`).
Ohne das deckte das Overlay den Header-Streifen mit ab und das Logo verschwand.
Im Panel-Modus bleibt es bewusst darunter (das Panel fährt über den
Logo-Bereich). Wer das Logo auch im Overlay verdecken will: `--xh-logo-z: 0`.

Die Theme-Manager-Einstellung *Push-Navigation → Hintergrund* (`--pn-bg`) greift
im Panel-Modus nicht: Ihr Default ist halbtransparent, was für ein Panel nicht
taugt. Wer sie trotzdem will: `--xh-pnl-bg: var(--pn-bg)`.

## Anpassen im Projekt

Das Paket wird **zweimal** übersetzt, und davon hängt ab, was greift:

| Weg | Ergebnis | `$`-Overrides |
|---|---|---|
| `@import "header"` in `projekt.scss` | landet in `projekt.css` | greifen, wenn `_config-overrides.scss` **vor** dem Import steht |
| `TC_SOURCES['files']` (automatisch) | eigene `_header.css` | greifen nicht – eigenes Kompilat ohne Projekt-SCSS |

Da beide Dateien entstehen, aber nur die eingebundene zählt: Wer nach dem
Muster oben installiert, kann `$xh-toggle-width` in `_config-overrides.scss`
setzen. Verlässlicher – und unabhängig vom Einbindungsweg – sind die
CSS-Variablen:

```scss
.mod_toggle {
  --xh-tgl-w: 2.5rem;      // Breite
  --xh-tgl-h: 1.5rem;      // Höhe
  --xh-tgl-bar-h: .25rem;  // Balkenstärke
  --xh-tgl-bar-r: 0;       // Balkenradius
  --xh-tgl-clr: #333;      // Farbe
  --xh-tgl-dur: .22s;      // Dauer der Öffnen-Animation
}

header .mod_navigation {
  --xh-nav-gap: 2rem;                    // Abstand der Hauptpunkte
  --xh-nav-clr-a: var(--clr-secondary);  // aktiv/hover
}

// Breite: normalerweise über die Layout-Auswahl. Für einen Wert, den keine
// Stufe trifft, oder für Header ≠ Footer:
header { --xh-hdr-wdth: 1400px; }
footer { --xh-ftr-wdth: 100%; }
```

Zur Breite im Einzelnen: Die Backend-Auswahl setzt `--xh-cnt-wdth` an `<body>`,
von dort erbt es an beide. `--xh-hdr-wdth` und `--xh-ftr-wdth` stehen davor und
übersteuern es einzeln. Ohne jeden Wert bleibt `max-width` auf `none`.

Die drei Stufen entsprechen den Artikelbreiten `art-px-1/2/3`, damit Header,
Inhalt und Footer bündig stehen – gemessen bei 1440px: Header und ein Artikel
mit `art-px-2` beide bei `x = 104`, Breite `1232px`. Dass die kleinere Ziffer
die größere Breite bedeutet, ist eine Eigenart des Core (`art-px` beschreibt den
Außenabstand, nicht die Breite); das Paket spiegelt sie, statt eine zweite
Zählweise einzuführen.

Am Element gesetzt gewinnen sie immer über die `:root`-Defaults des Pakets –
unabhängig davon, welche CSS-Datei zuerst geladen wurde.

Merkhilfe: `$…` gilt zur Compile-Zeit und nur im selben Kompilat, `--…` zur
Laufzeit und überall.

## Style-Manager

`Config::loadBundleConfiguration()` parst alle `style-manager-*.xml` unter
`vendor/*/*/contao/templates/` **zur Laufzeit**. Das Paket zu installieren
genügt – nichts kopieren, nichts im Backend importieren. Dass
`tl_style_manager` leer bleibt, ist dabei kein Fehler.

Die XML wird generiert, nicht von Hand gepflegt:

```bash
php bin/generate-style-manager-xml.php
```

Die `cssClasses`- und `modules`-Felder sind serialisierte PHP-Arrays; von Hand
getippt stimmen die Längenpräfixe erfahrungsgemäß nicht (`s:12:"exakt_toggle"`,
nicht `s:13:`), und ein falsches Präfix macht die Gruppe still unbrauchbar.

Zwei Gruppen mit unterschiedlichem Ziel:

| Gruppe | Ziel | Klassen |
|---|---|---|
| `mHeaderToggle` → Größe des Symbols | `extendModule`, nur `exakt_toggle` | `tgl-small`, `tgl-large` |
| `xhHeaderFooter` → Inhaltsbreite | `extendLayout` | `cnt-w-1`, `cnt-w-2`, `cnt-w-3` |

Bei `extendLayout` schreibt Contao die Klasse an `<body>` – genauso arbeitet die
Core-Gruppe „Header behaviour", deren Regel `body.fixed.undock` lautet. Die
Klasse steht dabei sowohl in `tl_layout.cssClass` als auch, unter dem Schlüssel
`{archivIdentifier}_{gruppenAlias}`, im serialisierten Feld
`tl_layout.styleManager`. Wer sie nur in `cssClass` einträgt, verliert sie beim
nächsten Speichern: `StyleManager::clearClasses()` filtert jede Klasse aus dem
Feld, die zu einer Gruppe gehört, aber dort nicht ausgewählt ist.

Eigene Archive statt Andocken an die leeren Core-Archive `gHeader`/`gFooter`:
Für YAML-Konfigurationen ist das Zusammenführen nach `identifier` vorgesehen
(`Config::parseYamlConfiguration` prüft `isset($styleArchives[$archiveIdent])`),
für XML läuft der Import über `ImportController::importXmlFiles` – ohne
zugesicherte Merge-Semantik und abhängig von der Dateireihenfolge.

## Was gegenüber den bisherigen Einzelkopien korrigiert wurde

**Die Striche standen versetzt.** In `.burger-inner::before/::after` fehlten
`display: block` und `left: 0`. Absolut positionierte Pseudoelemente behalten
dann ihre statische Position – gemessen 16px, also die halbe Symbolbreite.
Folge: oberer und unterer Strich nach rechts verschoben (der obere ragte über
den Rand), und beim Aufklappen erschien statt eines X ein Winkel. Hier stehen
jetzt beide Angaben.

**`pn-toggle` und `pn-show` gehören nicht ins Markup.** Das Paket-JS pflegt
beide selbst (`this.s.classList.add(this.e.s)`, `pn-show` beim Ein- und
Ausblenden) und richtet sich dabei nach `--nav-bhr`. Gemessen bei 1440px:
`pn-toggle` gesetzt, `pn-show` nicht, Umschalter unsichtbar. Hartkodiert
verdoppelt das Markup diese Logik – und erzwingt eine Sonderregel, die den
Umschalter oberhalb des Breakpoints wieder ausblenden muss. Beides entfällt.

**Die Animation lief einstufig.** Ein gemeinsames `transition-property:
transform, top, bottom, opacity` bewegt alles gleichzeitig. Bekommen
`top`/`bottom`/`opacity` eigene Übergänge mit Verzögerung, fahren erst die
äußeren Striche zusammen und dann dreht sich das Kreuz – dazu ein anderes
Easing beim Öffnen (`cubic-bezier(.215, .61, .355, 1)`) als beim Schließen.

Kleinere Angleichungen: `line-height: 1`, `z-index`, `:hover { opacity: .7 }`
im geöffneten Zustand, `<div>` statt `<span>` für die Boxen.

**Die Breitenbegrenzung stand dreimal da, in drei Schreibweisen.** Der Core
begrenzt die Inhaltsbreite über `[class*=art-px] > .inside` – eine Klasse, die
nur Artikel und Container tragen. Header und Footer haben keine Artikel, also
lief ihr Inhalt bis an den Fensterrand, während der Seiteninhalt mittig stand.
Gemessen:

| Projekt | Wert | Umsetzung |
|---|---|---|
| A, Header + Footer | `--art-wdth` | `max-width` + `margin-left/right: auto !important` |
| B, Header | derselbe Wert | dieselben vier Zeilen, plus `position: relative` |
| B, Footer | derselbe Wert | **nur die Variable, ohne Regel – wirkungslos** |
| C, Header + Footer | anderer Wert, hart notiert | zusätzlich `box-sizing` und `width` |

In Projekt A stand der Wunsch bereits als Kommentar im Code („Als Einstellung
ins Repo übernehmen!"), in Projekt B war die Kopie im Footer schon
auseinandergefallen. `box-sizing` ist überdies global im Core
(`ctm_base/_defaults.scss`).

**Der Listen-Reset nahm dem mobilen Menü seinen Innenabstand.** Zwei Projekte
setzten `header .mod_navigation ul { margin: 0; padding: 0; list-style: none }`.
`ctm-push-navigation` klont die Navigation aber nicht – es hängt `.pn-init` an
dasselbe Element, das Overlay bleibt im `<header>` und wird von diesem Selektor
mitgetroffen. Mit Spezifität 0,1,2 schlägt er `.pn-init ul { padding: 30px }`
(0,1,1), das aufgeklappte Menü klebte also am Rand. Umgekehrt gilt für
`list-style` das Gegenteil: Der Core setzt es nur an `.nav` und nur oberhalb von
`$navigation-behaviour-min-width`, die Push-Navigation gar nicht – ohne eine
Regel stehen im mobilen Menü Aufzählungspunkte. Das Paket trennt beides
entsprechend auf.

## Fallen beim Weiterentwickeln

- **`contao/templates/twig/.twig-root`** muss existieren. Ohne diesen Marker
  behandelt Contao nicht `twig/`, sondern jeden Unterordner als
  Namespace-Wurzel; das Template hieße dann `exakt_toggle` statt
  `frontend_module/exakt_toggle` und der Aufruf endet in
  „Could neither find template …".
- **Twig-Templates gehören unter `contao/templates/twig/`**, nicht direkt nach
  `contao/templates/`. Die XML-Dateien dagegen schon – der ResourceFinder des
  Style-Managers sucht rekursiv.
- **`config/services.yaml` wird nur über die DI-Extension geladen.** Fehlt
  `ExAktCtmHeaderExtension`, ist der Controller kein Fragment und der Modultyp
  fehlt kommentarlos in der Auswahl.
- **Media Queries ausschreiben.** Die Dateien werden zweimal übersetzt: über
  `@import "header"` im Projekt (dort hat `_theme.scss` vorher `ctm_utils`
  geladen) *und* als eigenes `_header.css` über `TC_SOURCES`. Im zweiten
  Kontext fehlen die Mixins. Gemessen:

  | Schreibweise | Paket-Kompilat |
  |---|---|
  | `@media (min-width: $navigation-behaviour-min-width)` | ✓ 768px |
  | `@media (min-width: map-get($breakpoints, 's'))` | ✓ 768px |
  | `@include media-breakpoint('s')` | ✗ „Undefined mixin." |

  `$breakpoints` und alle `$`-Werte aus `core/_config.scss` sind verfügbar, weil
  der Compiler jeder Datei die `configFiles` aller Pakete voranstellt – nur
  `ctm_utils/` nicht. Für benannte Breakpoints deshalb `map-get($breakpoints, …)`
  statt des Mixins.

  Ein Abbruch hier beendet den **gesamten** Compile-Lauf, auch `projekt.css`
  wird dann nicht mehr geschrieben – und der `ThemeCompileCommand` zeigt die
  Ursache nicht an, sondern scheitert im catch-Block an einem undefinierten
  `$io`. Fehler also isoliert nachstellen (siehe `bin/`).

  So halten es auch `ctm-push-navigation` und `ctm-accessibility`: dort steht
  ausschließlich ausgeschriebenes `@media` mit derselben Variable. Nur der Core
  darf die Mixins nutzen – er wird über `_theme.scss` übersetzt.

- **Inline-Skript nur mit Nonce.** Der Initialisierungsblock in
  `js_exakt_navigation.html5` holt sich `$this->nonce('script-src')`. Mit
  aktiver CSP auf der Startseite (`enableCsp`) läuft er sonst nicht und das
  Menü bleibt tot, ohne Fehler auf der Seite (nur als CSP-Verstoß in der
  Konsole). Ohne CSP entfällt das Attribut. Neue Inline-Skripte genauso bauen.

## Anforderungen

- PHP ≥ 8.2, Contao ≥ 5.3
- `contao-thememanager/core` ≥ 2.2
- `contao-thememanager/ctm-push-navigation` ≥ 1.1
- `contao-thememanager/ctm-accessibility` (nicht als Abhängigkeit erzwungen, für
  das mobile Menü aber faktisch nötig – siehe oben)
