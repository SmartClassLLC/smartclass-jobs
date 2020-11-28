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

class PortletPage {
    
    private $boxTag = "div";
    private $boxId = "";
    private $boxType = "primary";
    private $boxTitle = "SmartClass";
    private $bodyClass = "sims-bg-light";
    private $actionsColumn = 2;
    private $actionButtons = array();
    private $filtersColumn = 10;
    private $toggleButtons = array();
    private $navLinks = array();
    private $printButton = "";
    private $settingsButton = "";
    private $ptTemplate = 'portlet_page.html';
    
    function __construct()
    {
    	global $globalUserTypeClass;
    	
    	$this->boxType = $globalUserTypeClass;
    }
    
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
	function setBodyClass($class)
	{
		$this->bodyClass = $class;
	}

    /* function */
	function addNavLink($link)
	{
		$this->navLinks[] = $link;
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
	function addPrintButton($btn)
	{
		$this->printButton = $btn;
	}

    /* function */
	function addSettingsButton($btn)
	{
		$this->settingsButton = $btn;
	}

   /* function */
	function setTemplate($template)
	{
		$this->ptTemplate = $template;
	}

    /* function */
	function getPage()
	{
		return get_object_vars($this);
	}
	
    /* function */
	function generatePage($extra = array())
	{
		global $theme;

		if(empty($this->actionButtons)) $this->filtersColumn = 12;
		
		$loader = new \Twig\Loader\FilesystemLoader('themes/' . $theme . '/templates');
		$twig = new \Twig\Environment($loader, ['cache' => false]);
		
		$portlet = $twig->render($this->ptTemplate, [
			'portlet_tag'		=> $this->boxTag,
			'portlet_id'		=> $this->boxId, 
			'portlet_title' 	=> $this->boxTitle, 
			'portlet_type'		=> $this->boxType, 
			'body_class'		=> $this->bodyClass, 
			'nav_links'			=> $this->navLinks, 
			'actions_title' 	=> _ACTIONS, 
			'action_buttons'	=> $this->actionButtons, 
			'toggle_buttons'	=> $this->toggleButtons, 
			'settings_button'	=> $this->settingsButton,
			'print_button'		=> $this->printButton,
			'extra' 			=> $extra]);
		
		return $portlet;
	}
}