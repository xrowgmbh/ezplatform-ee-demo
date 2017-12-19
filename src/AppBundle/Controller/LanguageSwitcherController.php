<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use eZ\Publish\Core\Helper\TranslationHelper;

class LanguageSwitcherController
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController */
    private $viewController;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    private $translationHelper;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController $viewController
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(
        ViewController $viewController,
        LocaleConverterInterface $localeConverter,
        TranslationHelper $translationHelper
    ) {
        $this->viewController = $viewController;
        $this->localeConverter = $localeConverter;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Renders language switcher view.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $template)
    {
        $currentLanguage = $this->localeConverter->convertToEz($request->get('_locale'));

        $languages = [];
        foreach ($this->translationHelper->getAvailableLanguages() as $lang) {
            $siteaccess = $this->translationHelper->getTranslationSiteAccess($lang);

            $languages[] = [
                'siteaccess' => $siteaccess,
                'name' => ucfirst(locale_get_display_language(
                    $this->localeConverter->convertToPOSIX($lang),
                    $this->localeConverter->convertToPOSIX($lang)
                )),
            ];
        }

        return $this->viewController->render(
            $template,
            [
                'currentLanguage' => ucfirst(locale_get_display_language(
                    $this->localeConverter->convertToPOSIX($currentLanguage),
                    $this->localeConverter->convertToPOSIX($currentLanguage)
                )),
                'languages' => $languages,
            ]
        );
    }
}
