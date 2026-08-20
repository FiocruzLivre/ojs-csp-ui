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

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\security\Role;



class CspUIPlugin extends GenericPlugin {
	private const CSS_VERSION = '202603311146';



    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if ($success && $this->getEnabled()) {
            $request = Application::get()->getRequest();
            $url = $request->getBaseUrl() . '/' . $this->getPluginPath() . '/styles/style.css';
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->addStyleSheet('CspUI' . self::CSS_VERSION, $url, ['contexts' => 'backend']);

            Hook::add('LoadHandler', [$this, 'loadHandler']);
            Hook::add('TemplateManager::display', [$this, 'templateManagerDisplay']);
            Hook::add('TemplateManager::fetch', [$this, 'templateManagerFetch']);
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

    // Permite acesso público a materiais suplementares sem autenticação
    public function loadHandler(string $_hookName, array $args): bool
    {
        if ($args[0] === 'article') {
            $args[3] = new \APP\plugins\generic\cspUI\pages\CspArticleHandler();
            return true;
        }
        return false;
    }

    public function templateManagerFetch(string $_hookName, array $args): bool
    {
        if ($args[1] !== 'controllers/grid/gridRow.tpl') {
            return false;
        }

        $templateVars = $args[0]->getTemplateVars();

        if (!isset($templateVars['grid']) || !isset($templateVars['row'])) {
            return false;
        }

        if ($templateVars['grid']->_id !== 'grid-articlegalleys-articlegalleygrid') {
            return false;
        }

        $galley = $templateVars['row']->getData();

        if (!$galley) {
            return false;
        }

        $request = Application::get()->getRequest();
        $submission = $templateVars['row']->getSubmission();

        $viewUrl = $request->getDispatcher()->url(
            $request,
            Application::ROUTE_PAGE,
            null,
            'article',
            'view',
            [$submission->getBestId(), $galley->getBestGalleyId()]
        );

        // Em arquivos de Composição Final, substitui link de download por link para abrir arquivo para ser usado no processo de inserção de Material suplementar
        if (isset($args[0]->tpl_vars['cells']->value[0])) {
            $cell = $args[0]->tpl_vars['cells']->value[0];
            $cell = preg_replace('/\bhref="#"/', 'href="' . htmlspecialchars($viewUrl) . '" target="_blank"', $cell, 1);
            $cell = preg_replace('/<script\b[^>]*>.*?<\/script>/s', '', $cell);
            $args[0]->tpl_vars['cells']->value[0] = $cell;
        }

        return false;
    }

    public function templateManagerDisplay($hookName, $args){

        $templateMgr = $args[0];
        $request = Application::get()->getRequest();

        // Esconde botão SetPrimaryContact de usuários com nível de permissão menor do que Gerente da Revista
        $user    = $request->getUser();
        $context = $request->getContext();
        if ($user && $context && !$user->hasRole([Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN], $context->getId())) {
            $templateMgr->addStyleSheet(
                'CspUIHidePrimaryContact',
                '.contributorsListPanel .listPanel__itemActions .pkpButton:first-child { display: none !important; }',
                ['contexts' => 'backend', 'inline' => true]
            );
        }

        return false;
    }


}
