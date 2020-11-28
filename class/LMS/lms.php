<?php

//get lms provider
if($globalZone != "admin")
{
    if($ySubeKodu > 0) $dbi->where("schoolId", array("0", "$ySubeKodu"), "IN");
    else $dbi->where("schoolId", "0");
    $dbi->orderBy("schoolId", "desc");
    $lmsProvider = $dbi->getValue(_LMS_CONFIG_, "lms");
}

if(empty($lmsProvider)) $lmsProvider = "moodle";

//include the lms provider class
include __DIR__ . "/" . $lmsProvider . ".php";

class SmartClass_LMS extends SmartClass_LMS_Provider {

    private $batchId = "";
    
    function simsSetBatchId($batchId)
    {
        $this->batchId = $batchId;    
    }

    function simsCreateBatchInLMS($batches)
    {
        $response = $this->lmsCreateBatches($batches);
        
        return $response;
    }

    function simsAssignCourse2Class()
    {
        
    }
    
    function simsAssignCourse2BatchInLMS($course)
    {
        $response = $this->lmsAssignCourseToBatch($this->batchId, $course);
        
        return $response;
    }

    function simsAssignStds2BatchInLMS($stds)
    {
        global $dbi, $ySubeKodu;
        
        $batchMembers = array();
        $lmsUserIds = array();
        
        $getBatchMembers = $this->lmsGetBatchmembers($this->batchId);

        foreach($getBatchMembers as $batchMember)
        {
        	$batchMembers[] = $batchMember["id"];
        }
        
        $dbi->where("aid", $stds, "IN");
        $dbi->where("ySubeKodu", $ySubeKodu);
        $dbi->where("lmsUserId", "0", "!=");
        $getLmsUserId = $dbi->get(_USERS_, NULL, "lmsUserId");
        
        foreach($getLmsUserId as $lmsUser)
        {
        	if(!in_array($lmsUser["lmsUserId"], $batchMembers))
        	{
        		$lmsUserIds[] = array('userid' => $lmsUser["lmsUserId"]);
        	}
        }
        
        if(!empty($lmsUserIds))
        {
            $response = $this->lmsBulkAssignBatchmembers($this->batchId, $lmsUserIds);
        }
        
        return $response;
    }
    
    function simsAssignStdToBatchInLMS($stdId)
    {
        global $dbi, $ySubeKodu;
        
        $getBatchMembers = $this->lmsGetBatchmembers($this->batchId);

        foreach($getBatchMembers as $batchMember)
        {
        	$batchMembers[] = $batchMember["id"];
        }
        
        $dbi->where("ogrID", $stdId);
		$dbi->where("SubeKodu", $ySubeKodu);
		$stdTc = $dbi->getValue(_OGRENCILER_, "TCKimlikNo");

		$dbi->where("aid", $stdTc);
        $dbi->where("ySubeKodu", $ySubeKodu);
        $dbi->where("lmsUserId", "0", "!=");
        $getLmsUserId = $dbi->getValue(_USERS_, "lmsUserId");

        if(!in_array($getLmsUserId, $batchMembers))
        {
            $lmsUserId = array('userid' => $getLmsUserId);
        
            $response = $this->lmsAssignBatchmembers($lmsUserId, $this->batchId);
        }
        
        return $response;
    }
    
    function simsUnassignStdToBatchInLMS($stdId, $oldBatchId)
    {
        global $dbi, $ySubeKodu;
        
        $dbi->where("sinifID", $oldBatchId);
    	$dbi->where("subeKodu", $ySubeKodu);
    	$lmsBatchId = $dbi->getValue(_BATCHES_, "lmsBatchId");

        $getBatchMembers = $this->lmsGetBatchmembers($lmsBatchId);

        foreach($getBatchMembers as $batchMember)
        {
        	$batchMembers[] = $batchMember["id"];
        }

        $dbi->where("ogrID", $stdId);
		$dbi->where("SubeKodu", $ySubeKodu);
		$stdTc = $dbi->getValue(_OGRENCILER_, "TCKimlikNo");

		$dbi->where("aid", $stdTc);
        $dbi->where("ySubeKodu", $ySubeKodu);
        $dbi->where("lmsUserId", "0", "!=");
        $getLmsUserId = $dbi->getValue(_USERS_, "lmsUserId");
        
        if(in_array($getLmsUserId, $batchMembers))
        {
            $lmsUserId = array('userid' => $getLmsUserId);
            $response = $this->lmsUnassignBatchmembers($lmsBatchId, $lmsUserId);
        }
        
    }
    
    function simsAssignTeacher2BatchInLMS($classId)
    {
        global $dbi, $ySubeKodu;
        
        $batchMembersId = array();
        $batchMembers = $this->lmsGetBatchmembers($this->batchId);
        
        foreach($batchMembers as $batchMember)
        {
            $batchMembersId[] = $batchMember["id"];
        }
        
        $batchMemberUserId = array();
        $dbi->join(_PERSONEL_ . " p","p.perID=ct.teacherId", "LEFT");
        
        $dbi->where("ct.classId", $classId);
        $dbi->where("ct.schoolId", $ySubeKodu);
        $getTeachers = $dbi->get(_CLASS_TEACHERS_ . " ct", null, "p.tckimlikno");
        
        foreach($getTeachers as $teacher)
        {
            $dbi->where("aid", $teacher["tckimlikno"]);
            $dbi->where("lmsUserId", "", "!=");
            $lmsUserIDs = $dbi->get(_USERS_, null, "lmsUserId");
        
            foreach($lmsUserIDs as $userIds)
            {
                if(!in_array($userIds["lmsUserId"], $batchMembersId))
                {
                    $batchMemberUserId[] = array(
                        'userid'        => $userIds["lmsUserId"]
                    );
                }
            }
        }
        
        if(!empty($batchMemberUserId))
        {
            $response = $this->lmsBulkAssignBatchmembers($this->batchId, $batchMemberUserId);
        }
        
        
    }
    
    function simsTeacherManuelEnrolInLMS($lmsCourseId, $lmsBatchId, $userTc)
    {
        global $dbi, $ySubeKodu;
        
        $dbi->where("aid", $userTc);
        $dbi->where("lmsUserId", "", "!=");
        $lmsUserId = $dbi->getValue($this->usersTable, "lmsUserId");
        
        $enrolData = array(
            'userid'        => $lmsUserId,
            'courseid'      => $lmsCourseId,
            'batchid'       => $lmsBatchId,
            'roleshortname' => "teacher"
        );
        
        $response = $this->lmsManuelEnrol($enrolData);
        
        return $response;
        
    }
    
    function simsGetLMSCourse()
    {
        $response = $this->lmsGetCourses();
        
        return $response;
    }
    
    function simsGetLMSBatchCourses()
    {
        $response = $this->lmsGetBatchCourses($this->batchId);
        
        return $response;
    }
    
    function simsUnassignCourse2BatchInLMS($course)
    {
        $response = $this->lmsUnassignCourseToBatch($this->batchId, $course);
        
        return $response;
    }
    
    function createStudentNo($stdno)
    {
        $strlen = strlen($stdno);
    					
    	if($strlen < 6)
    	{
    	    $eksikSayi = 6 - $strlen;
            for($i = 0; $i < $eksikSayi; $i++)
    		{
    		   $ekle = $ekle . "0";
    		}
    		$studentNo = $ekle . $stdno;
    	}
    	else $studentNo = $stdno;
    	
    	return $studentNo;
    }
    
    

}
?>
