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

class Dashboard {
    
    private $widget_files = array();
    private $boxes = array();
    private $mandatory_widgets = array();
    private $widgets = array();
    private $updatesWidget = array();
    private $todaylocale = '';
    
    /* function */
	function getWidgetFiles()
	{
		global $dbi, $aid;
		
		//get all user widget files
		$dbi->join(_WIDGETS_. " w", "w.Id=o.wId", "INNER");
		$dbi->where("w.active", "on");
		$dbi->where("o.userId", $aid);
		$dbi->orderBy("o.wOrder", "ASC");
		$this->widget_files = $dbi->getValue(_WIDGETS_MY_ORDERS_. " o", "w.wFile", null);
	}

    /* function */
	function getBoxes()
	{
		global $dbi, $aid;
		
		//get boxes
		$dbi->join(_WIDGETS_. " w", "w.Id=o.wId", "INNER");
		$dbi->join(_WIDGET_FILES_. " wf", "wf.wFile=w.wFile", "LEFT");
		$dbi->where("w.active", "on");
		$dbi->where("o.wPosition", "top");
		$dbi->where("o.userId", $aid);
		$dbi->orderBy("o.wOrder", "ASC");
		$this->boxes = $dbi->get(_WIDGETS_MY_ORDERS_. " o", null, "w.Id as Id, w.title, w.wContent as content, w.wFile as file, wf.icon, wf.url, wf.urlIcon, wf.urlTitle, wf.template, wf.template_class, wf.chart_line_color, wf.chart_fill_color");
		
		$nofBoxes = sizeof($this->boxes);
		
		//$this->boxClass = $nofBoxes == 1 ? "col-sm-12" : ($nofBoxes == 2 ? "col-lg-6 col-md-6 col-sm-12" : ($nofBoxes == 3 ? "col-lg-4 col-md-4 col-sm-12" : "col-lg-3 col-md-3 col-sm-12"));
	}

    /* function */
	function getMandatoryWidgets()
	{
		global $dbi, $aid, $globalUserFolder;

		$mandatoryWidgetFiles = array();
		
		$this->getWidgets();
		$widgetFiles = array_column($this->widgets, "file");
		
		if($globalUserFolder == "headquarters" || $globalUserFolder == "campus") $mandatoryWidgetFiles[] = "schoolUsage.php";
		else if($globalUserFolder == "school") $mandatoryWidgetFiles[] = "productivity.php";

		if(in_array("enrollmentOverview.php", $widgetFiles)) $mandatoryWidgetFiles[] = "enrollmentOverview.php";
		
		//get boxes
		$dbi->join(_WIDGET_FILES_. " wf", "wf.wFile=w.wFile", "LEFT");
		if(!empty($mandatoryWidgetFiles)) $dbi->where("w.wFile", $mandatoryWidgetFiles, "IN");
		$this->mandatory_widgets = $dbi->get(_WIDGETS_. " w", null, "w.Id as Id, w.title, w.wContent as content, w.wFile as file, wf.icon, wf.url, wf.urlIcon, wf.urlTitle, wf.template, wf.template_class, wf.chart_line_color, wf.chart_fill_color");
	}
	
    /* function */
	function getWidgets()
	{
		global $dbi, $aid;
		
		//get boxes
		$dbi->join(_WIDGETS_. " w", "w.Id=o.wId", "INNER");
		$dbi->join(_WIDGET_FILES_. " wf", "wf.wFile=w.wFile", "LEFT");
		$dbi->where("w.active", "on");
		$dbi->where("w.wFile", array("schoolUsage.php", "productivity.php"), "NOT IN");
		$dbi->where("o.wPosition", array("middle", "middle_left", "middle_right"), "IN");
		$dbi->where("o.userId", $aid);
		$dbi->orderBy("o.wOrder", "ASC");
		$this->widgets = $dbi->get(_WIDGETS_MY_ORDERS_. " o", null, "w.Id as Id, w.title, w.wContent as content, w.wFile as file, wf.icon, wf.url, wf.urlIcon, wf.urlTitle, wf.template, wf.template_class, wf.chart_line_color, wf.chart_fill_color");
	}

    /* function */
	function getUpdatesWidget()
	{
		//get boxes
		$this->updatesWidget[] = array(
			"Id"			=> "10001",
			"title"			=> _RECENT_UPDATES,
			"file"			=> "recentUpdates.php",
			"template"		=> "6.html"
		);
	}

    /* function */
	function bootstrap()
	{
		global $theme, $simsDate;

		//get all widget files
		$this->getWidgetFiles();
		
		//get boxes
		$this->getBoxes();
		
		//get mandatory widgets
		$this->getMandatoryWidgets();
		
		//get widgets
		$this->getWidgets();
		
		//get updates widget
		$this->getUpdatesWidget();
		
	    //today locale
	    $this->todaylocale = FormatDateNumeric2Local($simsDate);
		
		//get all variables
	    $vars = get_object_vars($this);
	    
		$loader = new \Twig\Loader\FilesystemLoader('themes/' . $theme . '/templates');
		$twig = new \Twig\Environment($loader, ['cache' => false]);
		
		$dashboard = $twig->render("dashboard/dashboard.html", ['sims' => $vars]);
		
		return $dashboard;
	}
}

?>