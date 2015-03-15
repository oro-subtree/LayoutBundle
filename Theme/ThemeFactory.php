<?php

namespace Oro\Bundle\LayoutBundle\Theme;

use Oro\Bundle\LayoutBundle\Model\Theme;

class ThemeFactory implements ThemeFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($themeName, array $themeDefinition)
    {
        $theme = new Theme(
            $themeName,
            isset($themeDefinition['parent']) ? $themeDefinition['parent'] : null
        );

        if (isset($themeDefinition['label'])) {
            $theme->setLabel($themeDefinition['label']);
        }
        if (isset($themeDefinition['screenshot'])) {
            $theme->setScreenshot($themeDefinition['screenshot']);
        }
        if (isset($themeDefinition['icon'])) {
            $theme->setIcon($themeDefinition['icon']);
        }
        if (isset($themeDefinition['logo'])) {
            $theme->setLogo($themeDefinition['logo']);
        }
        if (isset($themeDefinition['directory'])) {
            $theme->setDirectory($themeDefinition['directory']);
        }
        if (isset($themeDefinition['groups'])) {
            $theme->setGroups((array)$themeDefinition['groups']);
        }

        return $theme;
    }
}
