<?php

declare(strict_types=1);

namespace ExAkt\CtmHeader\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ResponseContext\Csp\CspHandler;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;

/**
 * Gibt Inline-Skripten aus $GLOBALS['TL_HEAD'] und $GLOBALS['TL_BODY'] den
 * CSP-Nonce, den die Templates selbst nicht anfordern.
 *
 * Contao vergibt den Nonce nur auf Anforderung ($this->nonce() bzw.
 * csp_nonce()). Die Skript-Templates des ThemeManagers (js_ctm_core,
 * js_ctm_stickyheader, js_ctm_a11y) tun das nicht. Mit scharfer CSP laufen
 * sie deshalb nicht, und das mobile Menue bleibt ohne jede Meldung tot.
 *
 * Der Hook replaceDynamicScriptTags laeuft, bevor Contao die beiden Listen
 * zusammensetzt. Beruehrt werden deshalb nur Eintraege, die Code oder
 * Templates registriert haben, nie der Seiteninhalt. Alle Inline-Skripte der
 * fertigen Seite zu noncen wuerde eingeschleustes Skript mit freischalten und
 * die CSP entwerten.
 *
 * Nur script-src: Ein Nonce in style-src liesse den Browser 'unsafe-inline'
 * ignorieren und braeche jedes style-Attribut.
 *
 * Ohne aktive CSP (Startseite -> enableCsp) passiert nichts. Mit Contao 6.1
 * (HtmlHeadBag/HtmlTag, contao/contao#10116) wird der Listener ueberfluessig.
 */
#[AsHook('replaceDynamicScriptTags')]
final class CspNonceListener
{
    public function __construct(private readonly ResponseContextAccessor $responseContextAccessor)
    {
    }

    public function __invoke(string $buffer): string
    {
        $context = $this->responseContextAccessor->getResponseContext();

        if (!$context?->has(CspHandler::class)) {
            return $buffer;
        }

        $nonce = $context->get(CspHandler::class)->getNonce('script-src');

        if (null === $nonce) {
            return $buffer;
        }

        foreach (['TL_HEAD', 'TL_BODY'] as $key) {
            if (empty($GLOBALS[$key]) || !\is_array($GLOBALS[$key])) {
                continue;
            }

            foreach ($GLOBALS[$key] as $i => $entry) {
                if (\is_string($entry) && str_contains($entry, '<script')) {
                    $GLOBALS[$key][$i] = $this->addNonce($entry, $nonce);
                }
            }
        }

        return $buffer;
    }

    private function addNonce(string $html, string $nonce): string
    {
        // Nur Inline-Skripte ohne eigenen Nonce. Externe Dateien deckt 'self' ab.
        return preg_replace_callback(
            '/<script\b(?![^>]*\b(?:nonce|src)=)([^>]*)>/i',
            static fn (array $m): string => '<script'.$m[1].' nonce="'.htmlspecialchars($nonce, ENT_QUOTES).'">',
            $html,
        ) ?? $html;
    }
}
