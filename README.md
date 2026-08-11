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
| `ctm_modules-xh/_navigation.scss` | Umschaltung Hauptnavigation ↔ Push-Navigation inkl. der `:not(.pn-init)`-Spezifitätsfalle |
| `style-manager-exakt-header.xml` | Style-Manager-Gruppe zur Symbolgröße, ohne Kopie ins Projekt |

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
4. Theme neu kompilieren.

Das Toggle-CSS und die `header .mod_navigation`-Regeln aus dem Projekt-SCSS
entfernen – sie kommen jetzt aus dem Paket.

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
```

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

## Anforderungen

- PHP ≥ 8.2, Contao ≥ 5.3
- `contao-thememanager/core` ≥ 2.2
- `contao-thememanager/ctm-push-navigation` ≥ 1.1
- `contao-thememanager/ctm-accessibility` (nicht als Abhängigkeit erzwungen, für
  das mobile Menü aber faktisch nötig – siehe oben)
