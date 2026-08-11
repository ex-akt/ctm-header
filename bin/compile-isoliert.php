<?php
// Baut nach, was FileCompiler::compile() fuer eine Datei aus TC_SOURCES['files']
// tut: configFiles aller Pakete voranstellen, dann uebersetzen. Der
// ThemeCompileCommand verschluckt die Exception (Undefined variable $io),
// deshalb hier direkt.
//
// Aufruf aus einem Contao-Projekt heraus:
//   php vendor/ex-akt/ctm-header/bin/compile-isoliert.php [projektwurzel]

$root = rtrim($argv[1] ?? getcwd(), '/');

if (!is_file($root.'/vendor/autoload.php')) {
    exit("Keine Projektwurzel: $root (vendor/autoload.php fehlt)\n");
}

require $root.'/vendor/autoload.php';

$configFiles = [
    'public/bundles/contaothememanagercore/framework/scss/_config.scss',
    'public/bundles/contaothememanagerpushnavigation/framework/scss/_config.scss',
    'public/bundles/exaktctmheader/framework/scss/_config.scss',
];

$target = 'public/bundles/exaktctmheader/framework/scss/_header.scss';

$content = '';

foreach ($configFiles as $f) {
    $content .= file_get_contents($root.'/'.$f);
}

$content .= file_get_contents($root.'/'.$target);

$compiler = new ScssPhp\ScssPhp\Compiler();
$compiler->setImportPaths([$root.'/'.\dirname($target)]);

try {
    $css = $compiler->compileString($content)->getCss();
    echo "ERFOLG – ".\strlen($css)." Bytes CSS\n";

    if (preg_match('/@media[^{]*\{/', $css, $m)) {
        echo 'erste Media Query: '.trim($m[0])."\n";
    }
} catch (\Throwable $e) {
    echo 'FEHLER ('.$e::class."):\n  ".explode("\n", $e->getMessage())[0]."\n";
}
