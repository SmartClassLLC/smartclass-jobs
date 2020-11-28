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

class Workflows {

	private $workflowType = "";
	private $parameterId = "";
	private $schoolId = "";
	private $managerId = "";
	private $personnelId = "";
	private $parentId = "";
	private $studentId = "";
    private $messageParameters = array();

	/* function */
	/*
	* $type is workflow type
	* ie. need_request
	*/
	function setWorkflowType($type)
	{
		$this->workflowType = $type;
	}

	/* function */
	/*
	* $Id is the event id which triggers the workflow
	*/
	function setParameterId($Id)
	{
		$this->parameterId = $Id;
	}

	/* function */
	/*
	* $Id is the school id that is supposed to run the workflow
	*/
	function setSchoolId($Id)
	{
		$this->schoolId = $Id;
	}

	/* function */
	/*
	* $Id is the user id corresponding to the manager
	*/
	function setManagerId($Id)
	{
		$this->managerId = $Id;
	}

	/* function */
	/*
	* $Id is the user id corresponding to the personnel which is mostly a teacher
	*/
	function setPersonnelId($Id)
	{
		$this->personnelId = $Id;
	}

	/* function */
	/*
	* $Id is the user id corresponding to the parent
	*/
	function setParentId($Id)
	{
		$this->parentId = $Id;
	}

	/* function */
	/*
	* $Id is the user id corresponding to the student
	*/
	function setStudentId($Id)
	{
		$this->studentId = $Id;
	}

	/* function */
	/*
	* $variable is the message variable set for email or sms templates
	* ie {REQUEST_TITLE}
	* $value is the value which will be replaced for $variable for the real message
	*/
	function setMessageParameter($variable, $value)
	{
		$this->messageParameters[$variable] = $value;
	}

	/* function */	
	function createWorkflow()
	{
		global $db, $ySubeKodu, $aid, $simsDateTime;
		
		if($this->schoolId == "") $this->schoolId = $ySubeKodu;
		
		//message parameters
		$this->messageParameters = serialize($this->messageParameters);
		
		/* create approval and end type workflow */
		
		//get processes
		$qWorkflowApprovals = $db->sql_query("SELECT * FROM "._WORKFLOWS_." WHERE `wfType`='".$this->workflowType."' AND `schoolId`='".$this->schoolId."' AND `processType` IN ('approval', 'end') ORDER BY FIELD(`processType`, 'start', 'approval', 'end') ASC, `approveOrder` ASC");
		while($rWorkflowApprovals = $db->sql_fetchrow($qWorkflowApprovals))
		{
			//approvers as array
			$approvers = array();
			
			//managers
			$managers = $this->managerId != "" ? array($this->managerId) : array();
			
			//get user approver if set
			if($rWorkflowApprovals["approveUser"] != "")
			{
				//add to approvers
				$approvers[] = $rWorkflowApprovals["approveUser"];
				
				//add to managers as well
				$managers[] = $rWorkflowApprovals["approveUser"];
			}
	
			//get user type approver(s) if set
			$qUserTypeApprovers = $db->sql_query("SELECT `aid` FROM "._USERS_." WHERE `userType`='".$rWorkflowApprovals["approveUserType"]."' AND `radminsuper`='0' AND `active`='1' AND `ySubeKodu`='".$this->schoolId."'");
			while($rUserTypeApprovers = $db->sql_fetchrow($qUserTypeApprovers))
			{
				//add to approvers
				$approvers[] = $rUserTypeApprovers["aid"];
				
				//add to managers as well
				$managers[] = $rUserTypeApprovers["aid"];
			}
			
			//approver to save
			$approver = implode(",", $approvers);
			
			//managerId to save
			$manager = implode(",", $managers);
			
			//create the flow
			$add = $db->sql_query("INSERT INTO "._WORKFLOWS_APPROVALS_." (`wfType`, `approver`, `approveCreatedBy`, `approveCreatedOn`, `approveOrder`, `processType`, `messageParameters`, `workflowId`, `parameterId`, `managerId`, `personnelId`, `parentId`, `studentId`, `schoolId`) VALUES ('" . $this->workflowType . "', '" . $approver . "', '" . $aid . "', '" . $simsDateTime . "', '" . $rWorkflowApprovals["approveOrder"] . "', '" . $rWorkflowApprovals["processType"] . "', '" . $this->messageParameters . "', '" . $rWorkflowApprovals["Id"] . "', '" . $this->parameterId . "', '" . $manager . "', '" . $this->personnelId . "', '" . $this->parentId . "', '" . $this->studentId . "', '" . $this->schoolId . "')");
			if(!$add) return _WORKFLOW_COULD_NOT_BE_SET;
		}

		/* run first process */
		$wfRun = $this->runWorkflow();
		
		//return feedback
		if($wfRun) return true;
		else return false;
	}

	/* function */	
	function addWorkflowAction($actionQuery, $actionParameters = array(), $actionType = "approved", $processType = "approval") 
	{
		global $db;
		
		$actionParameters = (count($actionParameters) > 0) ? serialize($actionParameters) : "";
		
		$actionQuery = myfilter($actionQuery, "", "1");
		
		//add action
		$addAction = $db->sql_query("INSERT INTO "._WORKFLOWS_ACTIONS_." (`action`, `actionParameters`, `actionType`, `processType`, `parameterId`, `wfType`) VALUES ('" . $actionQuery . "', '" . $actionParameters . "', '" . $actionType . "', '" . $processType . "', '" . $this->parameterId . "', '" . $this->workflowType . "')");
		
		if($addAction) return true;
		else return false;
	}

	/* function */	
	function runWorkflow()
	{
		global $db, $dbi, $ySubeKodu, $aid, $simsDateTime, $adminmail, $sitename;
		
		if($this->schoolId == "") $this->schoolId = $ySubeKodu;
		
		//check if approval process is running
		$qApprovalProcess = $db->sql_query("SELECT a.`Id`, a.`approver`, a.`processType`, a.`messageParameters`, a.`managerId`, a.`personnelId`, a.`parentId`, a.`studentId`, w.`emailManager`, w.`smsManager`, w.`emailTeacher`, w.`smsTeacher`, w.`emailParent`, w.`smsParent`, w.`emailStudent`, w.`smsStudent` FROM "._WORKFLOWS_APPROVALS_." a LEFT JOIN "._WORKFLOWS_." w ON a.`workflowId`=w.`Id` WHERE a.`wfType`='".$this->workflowType."' AND a.`processType` IN ('approval', 'end') AND a.`status`='none' AND a.`parameterId`='".$this->parameterId."' AND a.`schoolId`='".$this->schoolId."' ORDER BY FIELD(a.`processType`, 'start', 'approval', 'end') ASC, a.`approveOrder` ASC");
		while($rApprovalProcess = $db->sql_fetchrow($qApprovalProcess))
		{
			//if meeting request is needed then get start and end date
			if($this->workflowType == "ptm_parent_reserve") $eventInfo = $dbi->where("id", $this->parameterId)->getOne(_PTM_APPOINTMENTS_, "start, end");
			else if($this->workflowType == "ptm_teacher_request") $eventInfo = $dbi->where("id", $this->parameterId)->getOne(_PTM_REQUESTS_, "start, end");
			
			if(empty($eventInfo["start"])) $sendMeetingRequest = false;
			else $sendMeetingRequest = true;

			//set workflows created date and created by values
			$db->sql_query("UPDATE "._WORKFLOWS_APPROVALS_." SET `approveCreatedBy`='".$aid."', `approveCreatedOn`='".$simsDateTime."' WHERE `Id`='".$rApprovalProcess["Id"]."'");
			
			//get names of the users
			$personnelName = YoneticiAdi($rApprovalProcess["personnelId"]);
			$parentName = YoneticiAdi($rApprovalProcess["parentId"]);
			$studentName = YoneticiAdi($rApprovalProcess["studentId"]);
			$studentClassName = sinifAdi(fnStudentUserId2StudentClassId($rApprovalProcess["studentId"]));
			
			//make messageParameters array
			$dbMessageParameters = unserialize($rApprovalProcess["messageParameters"]);
			$dbMessageParameters["{TEACHER_FIRST_LASTNAME}"] = $personnelName;
			$dbMessageParameters["{PARENT_FIRST_LASTNAME}"] = $parentName;
			$dbMessageParameters["{STUDENT_NAME_LASTNAME}"] = $studentName;
			$dbMessageParameters["{STUDENT_CLASS}"] = $studentClassName;
			
			//notification for managers if there is a managerId
			if($rApprovalProcess["managerId"] != "")
			{
				//make managerIds array 
				$managersArray = explode(",", $rApprovalProcess["managerId"]);
				
				foreach ($managersArray as $key => $value)
				{
					$managerName = YoneticiAdi($value);
					
					$myMessageParameters = $dbMessageParameters;
					$myMessageParameters["{FIRST_LASTNAME}"] = $managerName;
					
					//send email to manager
					if(intval($rApprovalProcess["emailManager"]) > 0)
					{
						//fix subject and content 
						$emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailManager"], $myMessageParameters);
						$emailContent = AdoptEMailContent2Template($rApprovalProcess["emailManager"], $myMessageParameters);
						
						//send internal message
						sendInternalMessage($simsDateTime, $emailSubject, $emailContent, $aid, $value);
						
						//send email
						$emailContentFormatted = externalMessageTemplate($value, $emailContent, "system");
						
						if($sendMeetingRequest)
						{
							$requestId = md5("SmartClass VGS Request #" . $this->parameterId);
							sendMeetingRequest($adminmail, $sitename, UserEMail($value), $emailSubject, $emailContentFormatted, $eventInfo["start"], $eventInfo["end"], "", $requestId);
						}
						else
						{
							sendEmail(UserEMail($value), $emailSubject, $emailContentFormatted);
						}
					}
					
					//send sms to manager
					if(intval($rApprovalProcess["smsManager"]) > 0)
					{
						//fix subject and content 
						$smsContent = AdoptMessageToTemplate($rApprovalProcess["smsManager"], $myMessageParameters);
						
						//manager phone number
						$personnelId = fnUserId2OtherId($value);
						$personnelPhoneNumber = fnPerId2PerInfo($personnelId, "ceptel");

						//send sms
						sendSMS($personnelPhoneNumber, $managerName, $smsContent, '', '', $value, $managerName);
					}		
				}
			}
			
			//notification for teacher if there is a personnelId
			if($rApprovalProcess["personnelId"] != "")
			{
				$myMessageParameters = $dbMessageParameters;
				$myMessageParameters["{FIRST_LASTNAME}"] = $personnelName;
				
				//send email to the teacher
				if(intval($rApprovalProcess["emailTeacher"]) > 0)
				{
				
					//fix subject and content 
					$emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailTeacher"], $myMessageParameters);
					$emailContent = AdoptEMailContent2Template($rApprovalProcess["emailTeacher"], $myMessageParameters);

					//send internal message
					sendInternalMessage($simsDateTime, $emailSubject, $emailContent, $aid, $rApprovalProcess["personnelId"]);

					//send email
					$emailContentFormatted = externalMessageTemplate($rApprovalProcess["personnelId"], $emailContent, "system");
					
					if($sendMeetingRequest)
					{
						$requestId = md5("SmartClass VGS Request #" . $this->parameterId);
						sendMeetingRequest($adminmail, $sitename, UserEMail($rApprovalProcess["personnelId"]), $emailSubject, $emailContentFormatted, $eventInfo["start"], $eventInfo["end"], "", $requestId);
					}
					else
					{
						sendEmail(UserEMail($rApprovalProcess["personnelId"]), $emailSubject, $emailContentFormatted);
					}
				}
				
				//send sms to the teacher
				if(intval($rApprovalProcess["smsTeacher"]) > 0)
				{
					//fix content 
					$smsContent = AdoptMessageToTemplate($rApprovalProcess["smsTeacher"], $myMessageParameters);

					//teacher phone number
					$personnelId = fnUserId2OtherId($rApprovalProcess["personnelId"]);
					$personnelPhoneNumber = fnPerId2PerInfo($personnelId, "ceptel");
										
					//send sms
					sendSMS($personnelPhoneNumber, $personnelName, $smsContent, '', '', $rApprovalProcess["personnelId"], $personnelName);
				}		
			}
			
			//notification for parent if there is a parentId
			if($rApprovalProcess["parentId"] != "")
			{
				$myMessageParameters = $dbMessageParameters;
				$myMessageParameters["{FIRST_LASTNAME}"] = $parentName;
				
				//send email to the teacher
				if(intval($rApprovalProcess["emailParent"]) > 0)
				{
					//fix subject and content 
					$emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailParent"], $myMessageParameters);
					$emailContent = AdoptEMailContent2Template($rApprovalProcess["emailParent"], $myMessageParameters);
					
					//send internal message
					sendInternalMessage($simsDateTime, $emailSubject, $emailContent, $aid, $rApprovalProcess["parentId"]);

					//send email
					$emailContentFormatted = externalMessageTemplate($rApprovalProcess["parentId"], $emailContent, "system");
					
					if($sendMeetingRequest)
					{
						$requestId = md5("SmartClass VGS Request #" . $this->parameterId);
						sendMeetingRequest($adminmail, $sitename, UserEMail($rApprovalProcess["parentId"]), $emailSubject, $emailContentFormatted, $eventInfo["start"], $eventInfo["end"], "", $requestId);
					}
					else
					{
						sendEmail(UserEMail($rApprovalProcess["parentId"]), $emailSubject, $emailContentFormatted);
					}
				}
				
				//send sms to manager
				if(intval($rApprovalProcess["smsParent"]) > 0)
				{
					//fix content 
					$smsContent = AdoptMessageToTemplate($rApprovalProcess["smsParent"], $myMessageParameters);
					
					//get parent phone number
					$parentId = fnUserId2OtherId($rApprovalProcess["parentId"], "parent");
					$parentPhoneNumber = fnParentId2ParentInfo($parentId, "v_ceptel");

					//send sms
					$sendParentSMS = sendSMS($parentPhoneNumber, $parentName, $smsContent, '', $rApprovalProcess["parentId"], '', $parentName);
				}		
			}
			
			//notification for student if there is a studentId
			if($rApprovalProcess["studentId"] != "")
			{
				$myMessageParameters = $dbMessageParameters;
				$myMessageParameters["{FIRST_LASTNAME}"] = $studentName;
				
				//send email to the teacher
				if(intval($rApprovalProcess["emailStudent"]) > 0)
				{
					//fix subject and content 
					$emailSubject = AdoptEMailSubject2Template($rApprovalProcess["emailStudent"], $myMessageParameters);
					$emailContent = AdoptEMailContent2Template($rApprovalProcess["emailStudent"], $myMessageParameters);
					
					//send internal message
					sendInternalMessage($simsDateTime, $emailSubject, $emailContent, $aid, $rApprovalProcess["studentId"]);
					
					//send email
					$emailContentFormatted = externalMessageTemplate($rApprovalProcess["studentId"], $emailContent, "system");
					
					if($sendMeetingRequest)
					{
						$requestId = md5("SmartClass VGS Request #" . $this->parameterId);
						sendMeetingRequest($adminmail, $sitename, UserEMail($rApprovalProcess["studentId"]), $emailSubject, $emailContentFormatted, $eventInfo["start"], $eventInfo["end"], "", $requestId);
					}
					else
					{
						sendEmail(UserEMail($rApprovalProcess["studentId"]), $emailSubject, $emailContentFormatted);
					}					
				}
				
				//send sms to manager
				if(intval($rApprovalProcess["smsStudent"]) > 0)
				{
					//fix content 
					$smsContent = AdoptMessageToTemplate($rApprovalProcess["smsStudent"], $myMessageParameters);

					//get student phone number
					$studentId = fnUserId2OtherId($rApprovalProcess["studentId"], "student");
					$studentPhoneNumber = fnStdId2StdInfo($studentId, "OgrenciCepTel");

					//send sms
					sendSMS($studentPhoneNumber, $studentName, $smsContent, $rApprovalProcess["studentId"], '', '', $studentName);
				}		
			}

			//if there is no confirm for the process then update as completed and keep looping
			//otherwise break the loop
			if($rApprovalProcess["approver"] == "") 
			{
				//update as completed
				$updateCompleted = $db->sql_query("UPDATE "._WORKFLOWS_APPROVALS_." SET `status`='completed' WHERE `Id`='".$rApprovalProcess["Id"]."'");
				
				if($updateCompleted)
				{
					//run workflow actions for the status of completed
					$simsRunWfActions = $this->runWorkflowActions("completed", $rApprovalProcess["processType"]);

					//run workflow
					if(!$simsRunWfActions) return false;
				}
				else
				{
					return false;	
				}
			}
			else
			{
				return true;
				break;
				
				//@TODO
				//this was break 3 but gave error so check it out
			}
		}
		
		return true;
	}

	/* function */
	function runApproveProcess($Id, $processType = "approval", $approverNote = "")
    {
		global $db, $aid, $simsDateTime, $ySubeKodu;
		
		//approve process
		$updateApprove = $db->sql_query("UPDATE "._WORKFLOWS_APPROVALS_." SET `approvedBy`='" . $aid . "',  `approvedOn`='" . $simsDateTime . "', `approverNote`='" . $approverNote . "', `status`='approved' WHERE `Id`='".$Id."'");
		if($updateApprove)
		{
			//run workflow actions
			$simsRunWfActions = $this->runWorkflowActions("approved", $processType);
			
			//run workflow
			if($simsRunWfActions) 
			{
				$simsRunWf = $this->runWorkflow();
				
				if($simsRunWf) return true;
				else return false;
			}
			else
			{
				return false;
			}
		}
				
		return false;
	}
	
	/* function */
	function runDeclineProcess($Id, $processType = "approval", $approverNote = "")
    {
		global $db, $aid, $simsDateTime, $ySubeKodu;
	
		//set workflow as declined
		$updateApprove = $db->sql_query("UPDATE "._WORKFLOWS_APPROVALS_." SET `approvedBy`='" . $aid . "',  `approvedOn`='" . $simsDateTime . "', `approverNote`='" . $approverNote . "', `status`='declined' WHERE `Id`='".$Id."'");
		if($updateApprove)
		{
			//run workflow actions
			$simsRunWfActions = $this->runWorkflowActions("declined", $processType);
			
			//run workflow
			if($simsRunWfActions) 
			{
				$simsRunWf = $this->runWorkflow();
				
				if($simsRunWf) return true;
				else return false;
			}
			else
			{
				return false;
			}
		}
				
		return false;
	}
	
	/* function */
	function workflowNeedConfirm($wfType, $schoolId = "", $processType = "approval")
    {
		global $db, $ySubeKodu;
		
		if($schoolId == "") $schoolId = $ySubeKodu;
		
		//check approval process
		$qApprovalProcessDefinition = $db->sql_query("SELECT `approveUser`, `approveUserType` FROM " . _WORKFLOWS_ . " WHERE `wfType`='" . $wfType . "' AND `schoolId`='" . $schoolId . "' AND `processType`='" . $processType . "' ORDER BY FIELD(`processType`, 'start', 'approval', 'end') ASC, `approveOrder` ASC");
		while($rApprovalProcessDefinition = $db->sql_fetchrow($qApprovalProcessDefinition))
		{
			if($rApprovalProcessDefinition["approveUser"] != "") return true;
			else if($rApprovalProcessDefinition["approveUserType"] != "0") return true; 
		}
		
		return false;
	}
	
	/* function */
	private function runWorkflowActions($workflowAction = "approved", $processType = "approval")
	{
		global $db, $ySubeKodu, $aid, $simsDateTime;
		
		$hata = 0;
		
		//check process actions
		$qWorkflowActions = $db->sql_query("SELECT `Id`, `action`, `actionParameters` FROM " . _WORKFLOWS_ACTIONS_ . " WHERE `wfType`='" . $this->workflowType . "' AND `parameterId`='" . $this->parameterId . "' AND `actionType`='" . $workflowAction . "' AND `processType`='" . $processType . "' ");
		while($rWorkflowActions = $db->sql_fetchrow($qWorkflowActions))
		{
			$actionQuery = myfilter($rWorkflowActions["action"]);
			$actionParameters = ($rWorkflowActions["actionParameters"] != "") ? unserialize($rWorkflowActions["actionParameters"]) : "";
			
			if($actionParameters != "")
			{
				//fix the query in order to get the real query
				foreach ($actionParameters as $key => $value) {
					$realValue = $$value;
					$actionQuery = str_replace("{" . $value . "}", $realValue, $actionQuery);
				}
			}
			
			//run the query
			$runActionQuery = $db->sql_query($actionQuery);
			if($runActionQuery)
			{
				//save action log
				$runLog = $db->sql_query("INSERT INTO " . _WORKFLOWS_ACTION_RUNS_ . " (`runBy`, `runOn`, `actionId`) VALUES ('" . $aid . "', '" . $simsDateTime . "', '" . $rWorkflowActions["Id"] . "')");
				
				if(!$runLog) $hata = 1;
			}
			else
			{
				$hata = 1;
			}
		}
		
		if($hata == 1) return false;
		else return true;
	}
}