<?php

/*
 * This file is part of Schoost.
 *
 * (c) SmartClass, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoost;

use Twig\Loader;

class DatatablesPage {
    
    private $boxTag = "div";
    private $boxId = "";
    private $boxType = "";
    private $boxTitle = "SmartClass";
    private $datatablesHeader = "";
    private $actionsTitle = _ACTIONS;
    private $exportTitle = _EXPORT;
    private $actionButtons = array();
    private $exportButtons = array();
    private $toggleButtons = array();
    private $settingsButton = array();
    private $printButton = "";
    private $alerts = array();
    private $dtTemplate = "full_table.html";
    private $dtTemplateContent = "";
    
	/* function */
	function setBoxTag($tag)
	{
		$this->boxTag = $tag;
	}
	
	/* function */
	function setBoxId($id)
	{
		$this->boxId = $id;
	}
	
	/* function */
	function setBoxType($type)
	{
		$this->boxType = $type;
	}
	
	/* function */
	function setBoxTitle($title)
	{
		$this->boxTitle = $title;
	}

    /* function */
	function setHeader($header)
	{
		$this->datatablesHeader = $header;
	}

    /* function */
	function setActionsTitle($title)
	{
		$this->actionsTitle = $title;
	}

    /* function */
	function addActionButton($btn)
	{
		$this->actionButtons[] = $btn;
	}

    /* function */
	function addToggleButton($btn)
	{
		$this->toggleButtons[] = $btn;
	}

    /* function */
	function setExportTitle($title)
	{
		$this->exportTitle = $title;
	}

    /* function */
	function addExportButton($btn)
	{
		$this->exportButtons[] = $btn;
	}

    /* function */
	function addSettingsButton($btn)
	{
		$this->settingsButton = $btn;
	}

    /* function */
	function addAlert($alert)
	{
		$this->alerts[] = $alert;
	}

    /* function */
	function setTemplate($template)
	{
		$this->dtTemplate = $template;
	}

    /* function */
	function setTemplateContent($content)
	{
		$this->dtTemplateContent = $content;
	}

    /* function */
	function getPage()
	{
		return get_object_vars($this);
	}
	
    /* function */
	function generatePage()
	{
		global $theme;

		if(empty($this->actionButtons)) $this->filtersColumn = 12;
		
		$loader = new \Twig\Loader\FilesystemLoader('themes/' . $theme . '/templates');
		$twig = new \Twig\Environment($loader, ['cache' => false]);
		
		$portlet = $twig->render($this->dtTemplate, [
			'portlet_tag'		=> $this->boxTag, 
			'portlet_id'		=> $this->boxId, 
			'portlet_title' 	=> $this->boxTitle, 
			'portlet_type'		=> $this->boxType, 
			'datatables_header' => $this->datatablesHeader, 
			'template_content'	=> $this->dtTemplateContent, 
			'actions_title' 	=> $this->actionsTitle, 
			'action_buttons'	=> $this->actionButtons, 
			'export_title'		=> $this->exportTitle, 
			'export_buttons'	=> $this->exportButtons, 
			'toggle_buttons'	=> $this->toggleButtons, 
			'settings_button'	=> $this->settingsButton,
			'alerts'			=> $this->alerts]);
		
		return $portlet;
	}
}