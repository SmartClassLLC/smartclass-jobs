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

use \Twig\Loader;

class ModalPage {
    
    private $id = "sims-modal";
    private $type = "primary";
    private $title = "SmartClass";
    private $titleClass = "";
    private $titleIcon = "square";
    private $form = array();
    private $mdTemplate = "modal_page.html";
    
    function __construct()
    {
    	global $globalUserTypeClass;
    	
    	$this->type = $globalUserTypeClass;
    }
    
    /* function */
	function setId($id)
	{
		$this->id = $id;
	}

    /* function */
	function setType($type)
	{
		$this->type = $type;
	}

    /* function */
	function setTitle($title)
	{
		$this->title = $title;
	}

    /* function */
	function setTitleClass($class)
	{
		$this->titleClass = $class;
	}

    /* function */
	function setTitleIcon($icon)
	{
		$this->titleIcon = $icon;
	}

    /* function */
	function addForm($id, $action, $method)
	{
		$this->form = array("id" => $id, "action" => $action, "method" => $method);
	}

    /* function */
	function addData($i, $data)
	{
		$this->data[$i] = $data;
	}

    /* function */
	function setTemplate($template)
	{
		$this->mdTemplate = $template;
	}

    /* function */
	function generateModal($extra = array())
	{
		global $theme;

		$loader = new \Twig\Loader\FilesystemLoader('themes/' . $theme . '/templates');
		$twig = new \Twig\Environment($loader, ['cache' => false]);
		
		$modal = $twig->render($this->mdTemplate, [
			'modal' => [
				'id' => $this->id, 
				'type' => $this->type, 
				'title' => $this->title, 
				'titleIcon' => $this->titleIcon, 
				'titleClass' => $this->titleClass
			], 
			'form' => $this->form, 
			'extra' => $extra
		]);
		
		return $modal;
	}
}

?>