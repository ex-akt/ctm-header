<?php

declare(strict_types=1);

namespace ExAkt\CtmHeader\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Umschalter fuer das mobile Menue.
 *
 * ctm-push-navigation initialisiert sich nur, wenn es im DOM einen Treffer auf
 * seinen Default-Selektor `.mod_toggle` findet, und Contao bringt fuer diesen
 * Zweck keinen eigenen Modultyp mit. In den bisherigen Projekten stand das
 * Markup deshalb je als HTML-Modul in tl_module.html – also in der Datenbank,
 * ausserhalb von Git und Deploy und ohne Weg, eine Korrektur an alle Kunden
 * auszuliefern. Dieses Modul macht daraus Code.
 *
 * Das Markup selbst ist bewusst leer: Gezeichnet wird der Burger komplett in
 * _toggle.scss ueber .burger-box/.burger-inner. Den umschliessenden
 * <button class="pn-btn"> setzt das Paket-JS zur Laufzeit selbst.
 */
#[AsFrontendModule(
    type: 'exakt_toggle',
    category: 'navigationMenu',
    template: 'frontend_module/exakt_toggle',
)]
class ToggleController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        return $template->getResponse();
    }
}
