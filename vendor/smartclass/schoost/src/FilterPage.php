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

class FilterPage {
    
    private $boxTag = "div";
    private $boxId = "";
    private $boxType = "primary";
    private $boxTitle = "SmartClass";
    private $actionsColumn = 2;
    private $filtersColumn = 10;
    private $actionButtons = array();
    private $toggleButtons = array();
    private $settingsButton = array();
    private $criteriaInputId = "simsDivBoxIdFilterCriteria";
    
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
	function setCriteriaInputId($id)
	{
		$this->criteriaInputId = $id;
	}

    /* function */
	function setActionsColumn($column)
	{
		$this->actionsColumn = $column;
		$this->filtersColumn = 12 - $column;
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
	function addSettingsButton($btn)
	{
		$this->settingsButton = $btn;
	}

    /* function */
	function generatePage($extra = array())
	{
		global $theme;

		if(empty($this->actionButtons)) $this->filtersColumn = 12;
		
		$loader = new \Twig\Loader\FilesystemLoader('themes/' . $theme . '/templates');
		$twig = new \Twig\Environment($loader, ['cache' => false]);
		
		$portlet = $twig->render('pages/filter_page.html', [
			'portlet_tag'		=> $this->boxTag, 
			'portlet_id'		=> $this->boxId, 
			'portlet_title' 	=> $this->boxTitle, 
			'portlet_type'		=> $this->boxType, 
			'actions_title' 	=> _ACTIONS, 
			'actions_column'	=> $this->actionsColumn, 
			'action_buttons'	=> $this->actionButtons, 
			'toggle_buttons'	=> $this->toggleButtons, 
			'settings_button'	=> $this->settingsButton,
			'filter_title'		=> _FILTERS . ' / ' . _LAYOUT, 
			'filters_column'	=> $this->filtersColumn, 
			'criteria_id'		=> $this->criteriaInputId, 
			'extra' 			=> $extra]);
		
		return $portlet;
	}
}