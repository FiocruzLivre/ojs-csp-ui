<?php

/**
 * @file plugins /generic/cspUI/CspUIPlugin.inc.php
 *
 * Copyright (c) 2020-2025 Lívia Gouvêa
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CspUIPlugin
 * @brief Customizes Vue.js templates and components for the interface of the journal Cadernos de Saúde Pública.
 */

namespace APP\plugins\generic\cspUI;

use PKP\plugins\GenericPlugin;
use APP\template\TemplateManager;
use APP\core\Application;
use PKP\plugins\Hook;



class CspUIPlugin extends GenericPlugin {


    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && $this->getEnabled()) {

            Hook::add('TemplateManager::display', [$this, 'templateManagerDisplay']);
        }

        return $success;
    }
    
    public function getDisplayName()
    {
        return __('plugins.generic.cspUI.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.cspUI.description');
    }

    public function templateManagerDisplay($hookName, $args){

        $templateMgr = $args[0];
        $request = Application::get()->getRequest();

        $templateMgr->addJavaScript(
            'cspJS',
            $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js/build.js',
            [
                'priority' => TemplateManager::STYLE_SEQUENCE_LATE,
                'contexts' => ['backend']
            ]
        );

        return false;
    }


}
