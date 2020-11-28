<?php

namespace Schoost\LMS\Providers;

use Schoost\LMS\Providers\LmsProvider;

class Moodle implements LmsProvider {

    private $returnformat = "json"; 
    private $restformat = ""; 
    private $setServerAddress = "";
    private $setToken = "";
    private $setInstitutionKey = "";
    private $serverUrl = "";
    private $schoolId = "";
    private $configTable = _MOODLE_CONFIG_;
    
	function __construct()
	{
		global $dbi, $ySubeKodu;
		
		//if(empty($this->schoolId)) $this->schoolId = $ySubeKodu;

        //$this->lmsSetSchoolInfo($this->schoolId);
        $this->restformat = ($this->returnformat == 'json') ? '&moodlewsrestformat=' . $this->returnformat : '';
	}
	
	function setConfigTable($table)
	{
	    $this->configTable = $table;    
	}
	
	function lmsSetSchoolInfo($scId)
	{
		global $dbi, $globalZone;

        if(!empty($scId)) $this->schoolId = $scId;
        
        $moodleSettings = array();        
        if($globalZone != "admin")
        {
    		//if($scId > 0) $dbi->where("branchID", array("0", $scId), "IN");
            //else $dbi->where("branchID", $scId);
            if($this->schoolId > 0) $dbi->where("branchID", $this->schoolId);
            $dbi->orderBy("branchID", "desc");
            $moodleSettings = $dbi->getOne($this->configTable);
        }
        
        if(empty($moodleSettings)) return "noconfig";
        
        $this->setServerAddress = $moodleSettings["moodleUrl"];
        $this->setToken = $moodleSettings["moodleWsdlToken"];
        $this->setInstitutionKey = $moodleSettings["moodleInsKey"];
	}
	
	function lmsCheckInstution()
	{
        $functionname = 'local_providerapi_checkinstitution';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
	}
	
	function lmsCreateBatches($batches)
	{
        $functionname = 'local_providerapi_create_batches';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batches' => array($batches)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;

	}
	
	function lmsUpdateBatches($batches) 
    { 
        $functionname = 'local_providerapi_update_batches';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batches' => array($batches)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsDeleteBatches($batchIds) 
    { 
        
        $functionname = 'local_providerapi_delete_batches';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batches' => array($batchIds)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
        
    }
    
    function lmsAssignBatchmembers($usersID, $batchId) 
    { 
        
        $functionname = 'local_providerapi_assign_batchmembers';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId, 'users' => array($usersID)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsBulkAssignBatchmembers($batchId, $usersIDs) 
    { 
        
        $functionname = 'local_providerapi_assign_batchmembers';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId, 'users' => $usersIDs));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetAllBatches() 
    { 
        
        $functionname = 'local_providerapi_get_batches';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsCreateUsers($users) 
    { 
        $functionname = 'local_providerapi_create_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'users' => array($users)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsCreateManagerUsers($insKeys, $users) 
    { 
        $functionname = 'local_providerapi_create_manager_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutions' => $insKeys, 'users' => array($users)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetAllUsers() 
    { 
        $functionname = 'local_providerapi_get_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
        
    }
    
    /**
     * $criteria = array(
     *      'key'   => 'id',
     *      'value' => 1
     * );
     * 
     * 
     */
    function lmsGetUser($criteria)
    {
        $functionname = 'local_providerapi_get_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'criteria' => array($criteria)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsUpdateUsers($users)
    {
        $functionname = 'local_providerapi_update_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'users' => array($users)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetBatchmembers($batchId)
    {
        $functionname = 'local_providerapi_get_batchmembers';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetCourses()
    {
        $functionname = 'local_providerapi_get_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetBatchCourses($batchId)
    {
        
        $functionname = 'local_providerapi_get_batch_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsAssignCourseToBatch($batchId, $courses)
    {
        
        $functionname = 'local_providerapi_assign_course_to_batch';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId, 'courses' => array($courses)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsBulkAssignCourseToBatch($batchId, $courses)
    {
        
        $functionname = 'local_providerapi_assign_course_to_batch';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId, 'courses' => $courses));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsUnassignCourseToBatch($batchId, $coursesId)
    {
        
        $functionname = 'local_providerapi_unassign_course_to_batch';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId, 'courses' => array($coursesId)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsUnassignBatchmembers($batchId, $userId)
    {
        
        $functionname = 'local_providerapi_unassign_batchmembers';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'batchid' => $batchId, 'users' => array($userId)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsDeleteUsers($userId)
    {
        
        $functionname = 'local_providerapi_delete_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'users' => array($userId)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetLtiInfo($coursesId)
    {
        
        $functionname = 'local_providerapi_get_lti_info';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'courseid' => $coursesId));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsManuelEnrol($users)
    {
        
        $functionname = 'local_providerapi_manual_enrol';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'users' => array($users)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsBulkManuelEnrol($users)
    {
        
        $functionname = 'local_providerapi_manual_enrol';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'users' => $users));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsManuelUnenrol($users)
    {
        
        $functionname = 'local_providerapi_manual_unenrol';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'users' => array($users)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    function lmsGetGradeItems($courseId, $userId, $batchId)
    {
        
        $functionname = 'local_providerapi_get_grade_items';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('institutionkey' => $this->setInstitutionKey, 'courseid' => $courseId, 'userid' => $userId, 'batchid' => $batchId));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [competencyframework] => array(
     *                   shortname           => string   //shortname
     *                   idnumber            => string   //idnumber
     *                   description         => string  Varsayılan değer "" //description
     *                   descriptionformat   => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *                   visible             => int  Varsayılan değer "1" //visible
     *                   scaleid             => int   //scaleid
     *                   scaleconfiguration  => string   //scaleconfiguration
     *                   contextid           => int  İsteğe bağlı //The context id
     *                   contextlevel        => string  İsteğe bağlı //The context level
     *                   instanceid          => int İsteğe bağlı //The Instance id
     *                   taxonomies          => string  Varsayılan değer "" //taxonomies
     *                   timecreated         => int  Varsayılan değer "0" //timecreated
     *                   timemodified        => int  Varsayılan değer "0" //timemodified
     *                   usermodified        => int  Varsayılan değer "0" //usermodified
     * 
     *              )
     * 
     */
    function lmscoreCompetencyCreateCompetencyFramework($competencyframework)
    {

        $functionname = 'core_competency_create_competency_framework';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyframework' => $competencyframework));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [competency] => array(
     *          
     *           shortname              => string   //shortname
     *           idnumber               => string   //idnumber
     *           description            => string  Varsayılan değer "" //description
     *           descriptionformat      => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           sortorder              => int  Varsayılan değer "0" //sortorder
     *           parentid               => int  Varsayılan değer "0" //parentid
     *           path                   => string  Varsayılan değer "/0/" //path
     *           ruleoutcome            => int  Varsayılan değer "0" //ruleoutcome
     *           ruletype               => string  Varsayılan değer "null" //ruletype
     *           ruleconfig             => string  Varsayılan değer "null" //ruleconfig
     *           scaleid                => int  Varsayılan değer "null" //scaleid
     *           scaleconfiguration     => string  Varsayılan değer "null" //scaleconfiguration
     *           competencyframeworkid  => int  Varsayılan değer "0" //competencyframeworkid
     *           timecreated            => int  Varsayılan değer "0" //timecreated
     *           timemodified           => int  Varsayılan değer "0" //timemodified
     *           usermodified           => int  Varsayılan değer "0" //usermodified
     *   )
     * 
     */
    function lmscoreCompetencyCreateCompetency($competency)
    {

        $functionname = 'core_competency_create_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competency' => $competency));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [sort] => string Varsayılan değer "shortname" //Column to sort by.
     * 
     * [order] => string Varsayılan değer "" //Sort direction. Should be either ASC or DESC
     * 
     * [skip] => int Varsayılan değer "0" //Skip this number of records before returning results
     * 
     * [limit] => int Varsayılan değer "0" //Return this number of records at most.
     * 
     * [context] => array(
     *          'contextid'    => int Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
                'contextlevel' => string  Varsayılan değer "" //Context level. To be used with instanceid.
                'instanceid'   => int  Varsayılan değer "0" //Context instance ID. To be used with level
     *      )
     * 
     * [includes] => string Varsayılan değer "children" //What other contextes to fetch the frameworks from. (children, parents, self)
     * 
     * [onlyvisible] => int Varsayılan değer "" //Only visible frameworks will be returned if visible true
     * 
     * [query] => string Varsayılan değer "" //A query string to filter the results
     * 
     */
    function lmscoreCompetencyListCompetencyFrameworks($sort = "shortname", $order = "", $skip = 0, $limit = 0, $context, $includes = "children", $onlyvisible, $query = "")
    {

        $functionname = 'core_competency_list_competency_frameworks';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('sort' => $sort, 'order' => $order, 'skip' => $skip, 'limit' => $limit, 'context' => $context, 'includes' => $includes, 'onlyvisible' => $onlyvisible, 'query' => $query)); 
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [competency] => array(
     *          
     *           shortname              => string  İsteğe bağlı //shortname
     *           idnumber               => string  İsteğe bağlı //idnumber
     *           description            => string  İsteğe bağlı //description
     *           descriptionformat      => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           sortorder              => int  İsteğe bağlı //sortorder
     *           parentid               => int  İsteğe bağlı //parentid
     *           path                   => string  İsteğe bağlı //path
     *           ruleoutcome            => int  İsteğe bağlı //ruleoutcome
     *           ruletype               => string  İsteğe bağlı //ruletype
     *           ruleconfig             => string  İsteğe bağlı //ruleconfig
     *           scaleid                => int  İsteğe bağlı //scaleid
     *           scaleconfiguration     => string  İsteğe bağlı //scaleconfiguration
     *           competencyframeworkid  => int  İsteğe bağlı //competencyframeworkid
     *           id                     => int   //id
     *           timecreated            => int  İsteğe bağlı //timecreated
     *           timemodified           => int  İsteğe bağlı //timemodified
     *           usermodified           => int  İsteğe bağlı //usermodified
     *              
     *      )
     * 
     */
    function lmscoreCompetencyUpdateCompetency($competency)
    {

        $functionname = 'core_competency_update_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competency' => $competency));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [id] => int //Data base record id for the competency
     * 
     */
    function lmscoreCompetencyDeleteCompetency($id)
    {

        $functionname = 'core_competency_delete_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [limit] => int Varsayılan değer "0" //result set limit
     * 
     */
    function lmsBlockRecentlyAccessedItemsGetRecentItems($limit = 0)
    {

        $functionname = 'block_recentlyaccesseditems_get_recent_items';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('limit' => $limit));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [limit] => int Varsayılan değer "0" //result set limit
     * 
     * [offset] => int Varsayılan değer "0" //Offset
     * 
     */
    function lmsblockStarredCoursesGetStarredCourses($limit = 0, $offset = 0)
    {

        $functionname = 'block_starredcourses_get_starred_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('limit' => $limit, 'offset' => $offset));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * 
     * [username] => string //User name
     * 
     * [secret] => string //Confirmation secret
     * 
     */
    function lmscoreAuthConfirmUser($username, $secret)
    {

        $functionname = 'core_auth_confirm_user';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('username' => $username, 'secret' => $secret));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * 
     * [age] => int //Age
     * 
     * [country] => string //Country of residence
     * 
     */
    function lmscoreAuthIsMinor($age, $country)
    {

        $functionname = 'core_auth_is_minor';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('age' => $age, 'country' => $country));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * 
     * [username] => string Varsayılan değer "" //User name
     * 
     * [email] => string Varsayılan değer "" //User email
     * 
     */
    function lmscoreAuthRequestPasswordReset($username = "", $email = "")
    {

        $functionname = 'core_auth_request_password_reset';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('username' => $username, 'email' => $email));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * 
     * [username] => string //User name
     * 
     * [password] => string //Plain text password.
     * 
     * [redirect] => string Varsayılan değer "" //Redirect the user to this site url after confirmation.
     * 
     */
    function lmscoreAuthResendConfirmationEmail($username, $password, $redirect = "")
    {

        $functionname = 'core_auth_resend_confirmation_email';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('username' => $username, 'password' => $password, 'redirect' => $redirect));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //Badges only for this user id, empty for current user
     * 
     * [courseid] => int Varsayılan değer "0" //Filter badges by course id, empty all the courses
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int Varsayılan değer "0" //The number of records to return per page
     * 
     * [search] => string Varsayılan değer "" //A simple string to search for
     * 
     * [onlypublic] => int Varsayılan değer "" //Whether to return only public badges
     * 
     */
    function lmscoreBadgesGetUserBadges($userid = 0, $courseid = 0, $page = 0, $perpage = 0, $search = "", $onlypublic)
    {

        $functionname = 'core_badges_get_user_badges';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'courseid' => $courseid, 'page' => $page, 'perpage' => $perpage, 'search' => $search, 'onlypublic' => $onlypublic));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [courseid] => int //course id
     * 
     * [returncontents] => int Varsayılan değer "" //Whether to return the block contents.
     * 
     */
    function lmscoreBlockGetCourseBlocks($courseid, $returncontents)
    {

        $functionname = 'core_block_get_course_blocks';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'returncontents' => $returncontents));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [userid] => int Varsayılan değer "0" //User id (optional), default is current user.
     * 
     * [returncontents] => int Varsayılan değer "" //Whether to return the block contents
     * 
     */
    function lmscoreBlockGetDashboardBlocks($userid = 0, $returncontents)
    {

        $functionname = 'core_block_get_dashboard_blocks';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'returncontents' => $returncontents));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [filters] => array(
     *          'name' => string //The expected keys (value format) are:
     *                           tag      PARAM_NOTAGS blog tag
     *                           tagid    PARAM_INT    blog tag id
     *                           userid   PARAM_INT    blog author (userid)
     *                           cmid    PARAM_INT    course module id
     *                           entryid  PARAM_INT    entry id
     *                           groupid  PARAM_INT    group id
     *                           courseid PARAM_INT    course id
     *                           search   PARAM_RAW    search term
     *          'value' => string //The value of the filter.
     *  )
     * 
     * [page] => int Varsayılan değer "0" //The blog page to return.
     * 
     * [perpage] => int Varsayılan değer "10" //The number of posts to return per page.
     * 
     */
    function lmscoreBlogGetEntries($filters, $page = 0, $perpage = 10)
    {

        $functionname = 'core_blog_get_entries';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('filters' => array($filters), 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [filters] => array(
     *          'name' => string //The expected keys (value format) are:
     *                           tag      PARAM_NOTAGS blog tag
     *                           tagid    PARAM_INT    blog tag id
     *                           userid   PARAM_INT    blog author (userid)
     *                           cmid     PARAM_INT    course module id
     *                           entryid  PARAM_INT    entry id
     *                           groupid  PARAM_INT    group id
     *                           courseid PARAM_INT    course id
     *                           search   PARAM_RAW    search term
     *          'value' => string //The value of the filter.
     *  )
     * 
     */
    function lmscoreBlogViewEntries($filters)
    {

        $functionname = 'core_blog_view_entries';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('filters' => array($filters)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [events] => array(
     *           name         => string   //event name
     *           description  => string  Varsayılan değer "null" //Description
     *           format       => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           courseid     => int  Varsayılan değer "0" //course id
     *           groupid      => int  Varsayılan değer "0" //group id
     *           repeats      => int  Varsayılan değer "0" //number of repeats
     *           eventtype    => string  Varsayılan değer "user" //Event type
     *           timestart    => int  Varsayılan değer "1561850270" //timestart
     *           timeduration => int  Varsayılan değer "0" //time duration
     *           visible      => int  Varsayılan değer "1" //visible
     *           sequence     => int  Varsayılan değer "1" //sequence
     * 
     *      )
     * 
     */
    function lmscoreCalendarCreateCalendarEvents($events)
    {

        $functionname = 'core_calendar_create_calendar_events';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('events' => array($events)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    
    /**
     * [events] => array(
     *          eventid =>  int   //Event ID,
     *          repeat int   //Delete comeplete series if repeated event
     *      )
     * 
     */
    function lmscoreCalendarDeleteCalendarEvents($events)
    {

        $functionname = 'core_calendar_delete_calendar_events';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('events' => array($events)));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [courseid] => int //course id.
     * 
     * [timesortfrom] => int Varsayılan değer "null" //Time sort from
     * 
     * [timesortto] => int Varsayılan değer "null" //Time sort to
     * 
     * [aftereventid] => int Varsayılan değer "0" //The last seen event id
     * 
     * [limitnum] => int Varsayılan değer "20" //Limit number
     * 
     */
    function lmscoreCalendarGetActionEventsCourse($courseid, $timesortfrom = null, $timesortto = null, $aftereventid = 0, $limitnum = 20)
    {

        $functionname = 'core_calendar_get_action_events_by_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'timesortfrom' => $timesortfrom, 'timesortto' => $timesortto, 'aftereventid' => $aftereventid, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [courseids] => int //course id.
     * 
     * [timesortfrom] => int Varsayılan değer "null" //Time sort from
     * 
     * [timesortto] => int Varsayılan değer "null" //Time sort to
     * 
     * [limitnum] => int Varsayılan değer "10" //Limit number
     * 
     * 
     */
    function lmscoreCalendarGetActionEventsCourses($courseids, $timesortfrom = null, $timesortto = null, $limitnum = 10)
    {

        $functionname = 'core_calendar_get_action_events_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids), 'timesortfrom' => $timesortfrom, 'timesortto' => $timesortto, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [timesortfrom] => int Varsayılan değer "0" //Time sort from
     * 
     * [timesortto] => int Varsayılan değer "null" //Time sort to
     * 
     * [aftereventid] => int Varsayılan değer "0" //The last seen event id
     * 
     * [limitnum] => int Varsayılan değer "20" //Limit number
     * 
     * [limittononsuspendedevents] => int Varsayılan değer "" //Limit the events to courses the user is not suspended in
     * 
     */
    function lmscoreCalendarGetActionEventsTimesort($timesortfrom = 0, $timesortto = null, $aftereventid = 0, $limitnum = 20, $limittononsuspendedevents)
    {

        $functionname = 'core_calendar_get_action_events_by_timesort';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('timesortfrom' => $timesortfrom, 'timesortto' => $timesortto, 'aftereventid' => $aftereventid, 'limitnum' => $limitnum, 'limittononsuspendedevents' => $limittononsuspendedevents));
        
        $resp = json_decode($resp);
        
        $convert = $this->objectToArray($resp);
        
        return $convert;
    }
    
    /**
     * [year] => int //Year to be viewed
     * 
     * [month] => int //Month to be viewed
     * 
     * [day] => int //Day to be viewed
     * 
     * [courseid] => int Varsayılan değer "1" //Course being viewed
     * 
     * [categoryid] => int Varsayılan değer "null" //Category being viewed
     * 
     */
    function lmscoreCalendarGetCalendarDayView($year, $month, $day, $courseid = 1, $categoryid = null)
    {

        $functionname = 'core_calendar_get_calendar_day_view';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('year' => $year, 'month' => $month, 'day' => $day, 'courseid' => $courseid, 'categoryid' => $categoryid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [eventid] => int //The event id to be retrieved
     * 
     */
    function lmscoreCalendarGetCalendarEventById($eventid)
    {

        $functionname = 'core_calendar_get_calendar_event_by_id';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('eventid' => $eventid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [events] => array(
     *              'eventids' => Varsayılan değer "Array"
     *                              array([0] => int //event ids)
     * 
     *              'courseids' => Varsayılan değer "Array"
     *                              array([0] => int //course ids)
     * 
     *              'groupids' => Varsayılan değer "Array"
     *                              array([0] => int //group ids)
     * 
     *              'categoryids' => Varsayılan değer "Array"
     *                              array([0] => int //Category ids)
     *          )
     * 
     * [options] => array(
     *               userevents   => int  Varsayılan değer "1" //Set to true to return current user's user events
     *               siteevents   => int  Varsayılan değer "1" //Set to true to return global events
     *               timestart    => int  Varsayılan değer "0" //Time from which events should be returned
     *               timeend      => int  Varsayılan değer "0" //Time to which the events should be returned. We treat 0 and null as no end
     *               ignorehidden => int  Varsayılan değer "1" //Ignore hidden events or not
     *          )
     * 
     */
    function lmscoreCalendarGetCalendarEvents($events, $options)
    {

        $functionname = 'core_calendar_get_calendar_events';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('events' => array($events), 'options' => $options));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    
    /**
     * [year] => int //Month to be viewed
     * 
     * [month] => int //Year to be viewed
     * 
     * [courseid] => int Varsayılan değer "1" //Course being viewed
     * 
     * [categoryid] => int Varsayılan değer "null" //Category being viewed
     * 
     * [includenavigation] => int Varsayılan değer "1" //Whether to show course navigation
     * 
     * [mini] => int Varsayılan değer "" //Whether to return the mini month view or not
     * 
     */
    function lmscoreCalendarGetCalendarMontlyView($year, $month, $courseid = 1, $categoryid = null, $includenavigation = 1, $mini)
    {

        $functionname = 'core_calendar_get_calendar_monthly_view';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('year' => $year, 'month' => $month, 'courseid' => $courseid, 'categoryid' => $categoryid, 'includenavigation' => $includenavigation, 'mini' => $mini));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    
    /**
     * [courseid] => int Varsayılan değer "1" //Course being viewed
     * 
     * [categoryid] => int Varsayılan değer "null" //Category being viewed
     * 
     */
    function lmscoreCalendarGetCalendarUpcomingView($courseid = 1, $categoryid = null)
    {

        $functionname = 'core_calendar_get_calendar_upcoming_view';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'categoryid' => $categoryid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [formdata] => string //The data from the event form
     * 
     */
    function lmscoreCalendarSubmitCreateUpdateForm($formdata)
    {

        $functionname = 'core_calendar_submit_create_update_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('formdata' => $formdata));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [eventid] => int //Id of event to be updated
     * 
     * [daytimestamp] => int //Timestamp for the new start day
     * 
     */
    function lmscoreCalendarUpdateEventStartDay($eventid, $daytimestamp)
    {

        $functionname = 'core_calendar_update_event_start_day';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('eventid' => $eventid, 'daytimestamp' => $daytimestamp));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [members] => array(
     *              'cohorttype' => array(
     *                                  type  => string   //The name of the field: id (numeric value of cohortid) or idnumber (alphanumeric value of idnumber) 
     *                                  value => string   //The value of the cohort
     *                              )
     *              'usertype' => array(
     *                              type string   //The name of the field: id (numeric value of id) or username (alphanumeric value of username)
     *                              value => string   //The value of the cohort
     *                           )
     *          )
     * 
     */
    function lmscoreChortAddCohortMembers($members)
    {

        $functionname = 'core_cohort_add_cohort_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('members' => array($members)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    
    /**
     * [cohorts] => array(
     *                  'cohorttype' => array(
     *                                  type  => string   //The name of the field: id (numeric value of cohortid) or idnumber (alphanumeric value of idnumber) 
     *                                  value => string   //The value of the cohort
     *                              )
     *                  name              => string   //cohort name
     *                  idnumber          => string   //cohort idnumber
     *                  description       => string  İsteğe bağlı //cohort description
     *                  descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *                  visible           => int  İsteğe bağlı //cohort visible
     *                  theme             => string  İsteğe bağlı //the cohort theme. The allowcohortthemes setting must be enabled on Moodle
     *              
     *          )
     * 
     */
    function lmscoreChortCreateCohorts($cohorts)
    {

        $functionname = 'core_cohort_create_cohorts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cohorts' => array($cohorts)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [members] => array(
     *          cohortid => int   //cohort record id
     *          userid   => int   //user id
     *      )
     * 
     */
    function lmscoreChortDeleteCohortMembers($members)
    {

        $functionname = 'core_cohort_delete_cohort_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('members' => array($members)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [cohortids] => array(
     *                  [0] => int //cohort ID
     *                )
     * 
     */
    function lmscoreChortDeleteCohorts($cohortids)
    {

        $functionname = 'core_cohort_delete_cohorts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cohortids' => array($cohortids)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [cohortids] => array(
     *                  [0] => int //cohort ID
     *                )
     * 
     */
    function lmscoreChortGetCohortMembers($cohortids)
    {

        $functionname = 'core_cohort_get_cohort_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cohortids' => array($cohortids)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [cohortids] => array(
     *                  [0] => int //cohort ID
     *                )
     *              //List of cohort id. A cohort id is an integer.
     * 
     */
    function lmscoreChortGetCohorts($cohortids)
    {

        $functionname = 'core_cohort_get_cohorts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cohortids' => array($cohortids)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [query] => string //Query string
     * 
     * [context] => array(
     *               contextid      => int  Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *               contextlevel   => string  Varsayılan değer "" //Context level. To be used with instanceid.
     *               instanceid     => int  Varsayılan değer "0" //Context instance ID. To be used with level
     *          )
     * 
     * [includes] => string Varsayılan değer "parents" //What other contexts to fetch the frameworks from. (all, parents, self)
     * 
     * [limitfrom] => int Varsayılan değer "0" //limitfrom we are fetching the records from
     * 
     * [limitnum] => int Varsayılan değer "25" //Number of records to fetch
     * 
     */
    function lmscoreChortSearchCohorts($query, $context, $includes = "parents", $limitfrom = 0, $limitnum = 25)
    {

        $functionname = 'core_cohort_search_cohorts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('query' => $query, 'context' => $context, 'includes' => $includes, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [cohorts] => array(
     *                  'cohorttype' => array(
     *                                  type  => string   //The name of the field: id (numeric value of cohortid) or idnumber (alphanumeric value of idnumber) 
     *                                  value => string   //The value of the cohort
     *                              )
     *                  name              => string   //cohort name
     *                  idnumber          => string   //cohort idnumber
     *                  description       => string  İsteğe bağlı //cohort description
     *                  descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *                  visible           => int  İsteğe bağlı //cohort visible
     *                  theme             => string  İsteğe bağlı //the cohort theme. The allowcohortthemes setting must be enabled on Moodle
     *              
     *          )
     * 
     */
    function lmscoreChortUpdateCohorts($cohorts)
    {

        $functionname = 'core_cohort_update_cohorts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cohorts' => array($cohorts)));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [contextlevel] => string //contextlevel system, course, user...
     * 
     * [instanceid] => int //the Instance id of item associated with the context level
     * 
     * [component] => string //component
     * 
     * [itemid] => int //associated id
     * 
     * [area] => string Varsayılan değer "" //string comment area
     * 
     * [page] => int Varsayılan değer "0" //page number (0 based)
     * 
     */
    function lmscoreCommentGetComments($contextlevel, $instanceid, $component, $itemid, $area = "", $page = 0)
    {

        $functionname = 'core_comment_get_comments';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextlevel' => $contextlevel, 'instanceid' => $instanceid, 'component' => $component, 'itemid' => $itemid, 'area' => $area, 'page' => $page));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [courseid] => int //The course id
     * 
     * [competencyid] => int //The competency id
     * 
     */
    function lmscoreCompetencyAddCompetencyToCourse($courseid, $competencyid)
    {

        $functionname = 'core_competency_add_competency_to_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [planid] => int //The plan id
     * 
     * [competencyid] => int //The competency id
     * 
     */
    function lmscoreCompetencyAddCompetencyToPlan($planid, $competencyid)
    {

        $functionname = 'core_competency_add_competency_to_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [templateid] => int //The template id
     * 
     * [competencyid] => int //The competency id
     * 
     */
    function lmscoreCompetencyAddCompetencyToTemplate($templateid, $competencyid)
    {

        $functionname = 'core_competency_add_competency_to_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('templateid' => $templateid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [relatedcompetencyid] => int //The related competency id
     * 
     */
    function lmscoreCompetencyAddRelatedCompetency($competencyid)
    {

        $functionname = 'core_competency_add_related_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'relatedcompetencyid' => $relatedcompetencyid));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    
    /**
     * [id] => int //The plan id
     * 
     */
    function lmscoreCompetencyApprovePlan($id)
    {

        $functionname = 'core_competency_approve_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [id] => int //The competency framework id
     * 
     */
    function lmscoreCompetencyFrameworkViewed($id)
    {

        $functionname = 'core_competency_competency_framework_viewed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [id] => int //The competency id
     * 
     */
    function lmscoreCompetencyViewed($id)
    {

        $functionname = 'core_competency_competency_viewed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        if(is_object($resp))
        {    
            $convertToArray = json_decode(json_encode($resp), true);
            return $convertToArray;
        }
        else return $resp;
    }
    
    /**
     * [planid] => int //The plan id.
     * 
     */
    function lmscoreCompetencyCompletePlan($planid)
    {

        $functionname = 'core_competency_complete_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [filters] => array(
     *              'column' => string //Column name to filter by
     *              'value'  => string //Value to filter by. Must be exact match
     *          )
     * 
     */
    function lmscoreCompetencyCountCompetencies($filters)
    {

        $functionname = 'core_competency_count_competencies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('filters' => array($filters)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The course id
     * 
     */
    function lmscoreCompetencyCountCompetenciesInCourse($id)
    {

        $functionname = 'core_competency_count_competencies_in_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The template id
     * 
     */
    function lmscoreCompetencyCountCompetenciesInTemplate($id)
    {

        $functionname = 'core_competency_count_competencies_in_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [context] => array(
     *          contextid    => int  Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *          contextlevel => string  Varsayılan değer "" //Context level. To be used with instanceid.
     *          instanceid   => int  Varsayılan değer "0" //Context instance ID. To be used with level
     *      )
     * 
     * [includes] => string Varsayılan değer "children" //What other contextes to fetch the frameworks from. (children, parents, self)
     * 
     */
    function lmscoreCompetencyCountCompetencyFrameworks($context, $includes = "children")
    {

        $functionname = 'core_competency_count_competency_frameworks';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('context' => $context, 'includes' => $includes));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => //The competency id
     * 
     */
    function lmscoreCompetencyCountCoursesUsingCompetency($id)
    {

        $functionname = 'core_competency_count_courses_using_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [context] => array(
     *               contextid    => int  Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *               contextlevel => string  Varsayılan değer "" //Context level. To be used with instanceid.
     *               instanceid   => int  Varsayılan değer "0" //Context instance ID. To be used with level
     *          )
     * 
     * [includes] => string Varsayılan değer "children" //What other contextes to fetch the frameworks from. (children, parents, self)
     * 
     */
    function lmscoreCompetencyCountTemplates($context, $includes = "children")
    {

        $functionname = 'core_competency_count_templates';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('context' => $context, 'includes' => $includes));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The competency id
     * 
     */
    function lmscoreCompetencyCountTemplatesUsingCompetency($id)
    {

        $functionname = 'core_competency_count_templates_using_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [plan] => array(
     *           name              => string  //name
     *           description       => string Varsayılan değer "" //description
     *           descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           userid            => int  //userid
     *           templateid        => int  Varsayılan değer "null" //templateid
     *           origtemplateid    => int  Varsayılan değer "null" //origtemplateid
     *           status            => int  Varsayılan değer "0" //status
     *           duedate           => int  Varsayılan değer "0" //duedate
     *           reviewerid        => int  Varsayılan değer "null" //reviewerid
     *           timecreated       => int  Varsayılan değer "0" //timecreated
     *           timemodified      => int  Varsayılan değer "0" //timemodified
     *           usermodified      => int  Varsayılan değer "0" //usermodified
     *     )
     * 
     */
    function lmscoreCompetencyCreatePlan($plan)
    {

        $functionname = 'core_competency_create_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('plan' => $plan));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [template] => array(
     *           shortname         => string   //shortname
     *           description       => string  Varsay��lan değer "" //description
     *           descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           duedate           => int  Varsayılan değer "0" //duedate
     *           visible           => int  Varsayılan değer "1" //visible
     *           contextid         => int  İsteğe bağlı //The context id
     *           contextlevel      => string  İsteğe bağlı //The context level
     *           instanceid        => int  İsteğe bağlı //The Instance id
     *           timecreated       => int  Varsayılan değer "0" //timecreated
     *           timemodified      => int  Varsayılan değer "0" //timemodified
     *           usermodified      => int  Varsayılan değer "0" //usermodified
     *      
     *      )
     * 
     */
    function lmscoreCompetencyCreateTemplate($template)
    {

        $functionname = 'core_competency_create_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('template' => $template));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userevidenceid] => int //The user evidence ID.
     * 
     * [competencyid] => int //The competency ID.
     * 
     */
    function lmscoreCompetencyCreateUserEvidenceCompetency($userevidenceid, $competencyid)
    {

        $functionname = 'core_competency_create_user_evidence_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userevidenceid' => $userevidenceid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //Data base record id for the framework
     * 
     */
    function lmscoreCompetencyDeleteCompetencyFramework($id)
    {

        $functionname = 'core_competency_delete_competency_framework';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id]  => int  //The evidence ID
     * 
     */
    function lmscoreCompetencyDeleteEvidence($id)
    {

        $functionname = 'core_competency_delete_evidence';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the learning plan
     * 
     */
    function lmscoreCompetencyDeletePlan($id)
    {

        $functionname = 'core_competency_delete_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the template
     * 
     * [deleteplans] => int //Boolean to indicate if plans must be deleted
     * 
     */
    function lmscoreCompetencyDeleteTemplate($id, $deleteplans)
    {

        $functionname = 'core_competency_delete_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'deleteplans' => $deleteplans));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The user evidence ID.
     * 
     */
    function lmscoreCompetencyDeleteUserEdevince($id)
    {

        $functionname = 'core_competency_delete_user_evidence';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userevidenceid] => int //The user evidence ID.
     * 
     * [competencyid] => int //The competency ID.
     * 
     */
    function lmscoreCompetencyDeleteUserEdevinceCompetency($userevidenceid)
    {

        $functionname = 'core_competency_delete_user_evidence_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userevidenceid' => $userevidenceid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //Data base record id for the framework
     * 
     */
    function lmscoreCompetencyDuplicateCompetencyFramework($id)
    {

        $functionname = 'core_competency_duplicate_competency_framework';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The template id
     * 
     */
    function lmscoreCompetencyDuplicateTemplate($id)
    {

        $functionname = 'core_competency_duplicate_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scaleid] => int //The scale id
     * 
     */
    function lmscoreCompetencyGetScaleValues($scaleid)
    {

        $functionname = 'core_competency_get_scale_values';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scaleid' => $scaleid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //User ID
     * 
     * [competencyid] => int //Competency ID
     * 
     * [grade] => int //New grade
     * 
     * [note] => string Varsayılan değer "null" //A note to attach to the evidence
     * 
     */
    function lmscoreCompetencyGradeCompetency($userid, $competencyid, $grade, $note = null)
    {

        $functionname = 'core_competency_grade_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid, 'grade' => $grade, 'note' => $note));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course id
     * 
     * [userid] => int //User id
     * 
     * [competencyid] => int //Competency id
     * 
     * [grade] => int //New grade
     * 
     * [note] => string Varsayılan değer "null" //A note to attach to the evidence
     * 
     */
    function lmscoreCompetencyGradeCompetencyInCourse($courseid, $userid, $competencyid, $grade, $note = null)
    {

        $functionname = 'core_competency_grade_competency_in_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid, 'competencyid' => $competencyid, 'grade' => $grade, 'note' => $note));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [planid] => int //Plan id
     * 
     * [competencyid] => int //Competency id
     * 
     * [grade] => int //New grade
     * 
     * [note] => string Varsayılan değer "null" //A note to attach to the evidence
     * 
     */
    function lmscoreCompetencyGradeCompetencyInPlan($planid, $competencyid, $grade, $note = null)
    {

        $functionname = 'core_competency_grade_competency_in_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid, 'competencyid' => $competencyid, 'grade' => $grade, 'note' => $note));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [filters] => array(
     *              'column' =>string   //Column name to filter by
     *              'value'  => string   //Value to filter by. Must be exact match
     *          )
     * 
     * [sort] => string Varsayılan değer "" //Column to sort by.
     * 
     * [order] => string Varsayılan değer "" //Sort direction. Should be either ASC or DESC
     * 
     * [skip] => int Varsayılan değer "0" //Skip this number of records before returning results
     * 
     * [limit] => int Varsayılan değer "0" //Return this number of records at most.
     * 
     */
    function lmscoreCompetencyListCompetencies($filters, $sort = "", $order = "", $skip = 0, $limit = 0)
    {

        $functionname = 'core_competency_list_competencies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('filters' => array($filters), 'sort' => $sort, 'order' => $order, 'skip' => $skip, 'limit' => $limit)); 
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The template id
     * 
     */
    function lmscoreCompetencyListCompetenciesInTemplate($id)
    {

        $functionname = 'core_competency_list_competencies_in_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //The course id
     * 
     */
    function lmscoreCompetencyListCourseCompetencies($id)
    {

        $functionname = 'core_competency_list_course_competencies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The plan ID.
     * 
     */
    function lmscoreCompetencyListPlanCompetencies($id)
    {

        $functionname = 'core_competency_list_plan_competencies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [sort] => string Varsayılan değer "" //Column to sort by.
     * 
     * [order] => string Varsayılan değer "" //Sort direction. Should be either ASC or DESC
     * 
     * [skip] => int Varsayılan değer "0" //Skip this number of records before returning results
     * 
     * [limit] => int Varsayılan değer "0" //Return this number of records at most.
     * 
     * [context] => array(
     *          'contextid'    => int  Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
                'contextlevel' => string  Varsayılan değer "" //Context level. To be used with instanceid.
                'instanceid'   =>int  Varsayılan değer "0" //Context instance ID. To be used with level
     *      )
     * 
     * [includes] => string Varsayılan değer "children" //What other contexts to fetch the templates from. (children, parents, self)
     * 
     * [onlyvisible] => int Varsayılan değer "" //If should list only visible templates
     * 
     */
    function lmscoreCompetencyListTemplates($sort = "", $order = "", $skip = 0, $limit = 0, $context, $includes = "children", $onlyvisible)
    {

        $functionname = 'core_competency_list_templates';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('sort' => $sort, 'order' => $order, 'skip' => $skip, 'limit' => $limit, 'context' => $context, 'includes' => $includes, 'onlyvisible' => $onlyvisible));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The competency id 
     * 
     */
    function lmscoreCompetencyListTemplatesUsingCompetency($id)
    {

        $functionname = 'core_competency_list_templates_using_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user id
     * 
     */
    function lmscoreCompetencyListUserPlans($userid)
    {

        $functionname = 'core_competency_list_user_plans';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The competency id
     * 
     */
    function lmscoreCompetencyMoveDownCompetency($id)
    {

        $functionname = 'core_competency_move_down_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id)); 
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The competency id
     * 
     */
    function lmscoreCompetencyMoveUpCompetency($id)
    {

        $functionname = 'core_competency_move_up_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The plan ID
     * 
     */
    function lmscoreCompetencyPlanCancelReviewRequest($id)
    {

        $functionname = 'core_competency_plan_cancel_review_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The plan ID
     * 
     */
    function lmscoreCompetencyPlanRequestReview($id)
    {

        $functionname = 'core_competency_plan_request_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The plan ID
     * 
     */
    function lmscoreCompetencyPlanStartReview($id)
    {

        $functionname = 'core_competency_plan_start_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The plan ID
     * 
     */
    function lmscoreCompetencyPlanStopReview($id)
    {

        $functionname = 'core_competency_plan_stop_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the competency
     * 
     */
    function lmscoreCompetencyReadCompetency($id)
    {

        $functionname = 'core_competency_read_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the framework
     * 
     */
    function lmscoreCompetencyReadCompetencyFramework($id)
    {

        $functionname = 'core_competency_read_competency_framework';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the plan
     * 
     */
    function lmscoreCompetencyReadPlan($id)
    {

        $functionname = 'core_competency_read_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the template
     * 
     */
    function lmscoreCompetencyReadTemplate($id)
    {

        $functionname = 'core_competency_read_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //The user evidence ID.
     * 
     */
    function lmscoreCompetencyReadUserEvidence($id)
    {

        $functionname = 'core_competency_read_user_evidence';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //The course id
     * 
     * [competencyid] => int //The competency id
     * 
     */
    function lmscoreCompetencyRemoveCompetencyFromCourse($courseid, $competencyid)
    {

        $functionname = 'core_competency_remove_competency_from_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [planid] => int //The plan id
     * 
     * [competencyid] => int //The competency id
     * 
     */
    function lmscoreCompetencyRemoveCompetencyFromPlan($planid, $competencyid)
    {

        $functionname = 'core_competency_remove_competency_from_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [templateid] => int //The template id
     * 
     * [competencyid] => int //The competency id
     * 
     */
    function lmscoreCompetencyRemoveCompetencyFromTemplate($templateid, $competencyid)
    {

        $functionname = 'core_competency_remove_competency_from_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('templateid' => $templateid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [relatedcompetencyid] => int //The related competency id
     * 
     */
    function lmscoreCompetencyRemoveRelatedCompetency($competencyid, $relatedcompetencyid)
    {

        $functionname = 'core_competency_remove_related_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'relatedcompetencyid' => $relatedcompetencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [planid] => int //The Plan id.
     * 
     */
    function lmscoreCompetencyReopenPlan($planid)
    {

        $functionname = 'core_competency_reopen_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //The course id
     * 
     * [competencyidfrom] => int //The competency id we are moving
     * 
     * [competencyidto] => int //The competency id we are moving to
     * 
     */
    function lmscoreCompetencyReorderCourseCompetency($courseid, $competencyidfrom, $competencyidto)
    {

        $functionname = 'core_competency_reorder_course_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'competencyidfrom' => $competencyidfrom, 'competencyidto' => $competencyidto));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [planid] => int //The plan id
     * 
     * [competencyidfrom] => int //The competency id we are moving
     * 
     * [competencyidto] => int //The competency id we are moving to
     * 
     */
    function lmscoreCompetencyReorderPlanCompetency($planid, $competencyidfrom, $competencyidto)
    {

        $functionname = 'core_competency_reorder_plan_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid, 'competencyidfrom' => $competencyidfrom, 'competencyidto' => $competencyidto));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [templateid] => int //The template id
     * 
     * [competencyidfrom] => int //The competency id we are moving
     * 
     * [competencyidto] => int //The competency id we are moving to
     * 
     */
    function lmscoreCompetencyReorderTemplateCompetency($templateid, $competencyidfrom, $competencyidto)
    {

        $functionname = 'core_competency_reorder_template_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('templateid' => $templateid, 'competencyidfrom' => $competencyidfrom, 'competencyidto' => $competencyidto)); 
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The user evidence ID.
     * 
     */
    function lmscoreCompetencyRequestReviewUserEvidenceLinkedCompetencies($id)
    {

        $functionname = 'core_competency_request_review_of_user_evidence_linked_competencies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [searchtext] => int //Text to search for
     * 
     * [competencyframeworkid] => int //Competency framework id
     * 
     */
    function lmscoreCompetencySearchCompetencies($searchtext, $competencyframeworkid)
    {

        $functionname = 'core_competency_search_competencies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('searchtext' => $searchtext, 'competencyframeworkid' => $competencyframeworkid)); 
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [coursecompetencyid] => int //Data base record id for the course competency
     * 
     * [ruleoutcome] => int //Ruleoutcome value
     * 
     */
    function lmscoreCompetencySetCourseCompetencyRuleoutcome($coursecompetencyid, $ruleoutcome)
    {

        $functionname = 'core_competency_set_course_competency_ruleoutcome';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('coursecompetencyid' => $coursecompetencyid, 'ruleoutcome' => $ruleoutcome));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [parentid] => int //The new competency parent id
     * 
     */
    function lmscoreCompetencySetParentCompetency($competencyid, $parentid)
    {

        $functionname = 'core_competency_set_parent_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'parentid' => $parentid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The template id
     * 
     */
    function lmscoreCompetencyTemplateHasRelatedData($id)
    {

        $functionname = 'core_competency_template_has_related_data';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Data base record id for the template
     * 
     */
    function lmscoreCompetencyTemplateViewed($id)
    {

        $functionname = 'core_competency_template_viewed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The plan ID
     * 
     */
    function lmscoreCompetencyUnapprovePlan($id)
    {

        $functionname = 'core_competency_unapprove_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [planid] => int //Data base record id for the plan
     * 
     */
    function lmscoreCompetencyUnlinkPlanFromTemplate($planid)
    {

        $functionname = 'core_competency_unlink_plan_from_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [competencyframework] => array(
     *           shortname           => string  İsteğe bağlı //shortname
     *           idnumber            => string  İsteğe bağlı //idnumber
     *           description         => string  İsteğe bağlı //description
     *           descriptionformat   => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           visible             => int  İsteğe bağlı //visible
     *           scaleid             => int  İsteğe bağlı //scaleid
     *           scaleconfiguration  => string  İsteğe bağlı //scaleconfiguration
     *           contextid           => int  İsteğe bağlı //The context id
     *           contextlevel        => string  İsteğe bağlı //The context level
     *           instanceid          => int  İsteğe bağlı //The Instance id
     *           taxonomies          => string  İsteğe bağlı //taxonomies
     *           id                  => int   //id
     *           timecreated         => int  İsteğe bağlı //timecreated
     *           timemodified        => int  İsteğe bağlı //timemodified
     *           usermodified        => int  İsteğe bağlı //usermodified
     *      
     *      )
     * 
     */
    function lmscoreCompetencyUpdateCompetencyFramework($competencyframework)
    {

        $functionname = 'core_competency_update_competency_framework';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyframework' => $competencyframework));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course id for the course to update
     * 
     * [settings] => array(
     * 
     *              'settings' => int //New value of the setting
     *          )
     * 
     */
    function lmscoreCompetencyUpdateCourseCompetencySettings($courseid, $settings)
    {

        $functionname = 'core_competency_update_course_competency_settings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'settings' => $settings));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [plan] => array(
     * 
     *       name                => string  İsteğe bağlı //name
     *       description         => string  İsteğe bağlı //description
     *       descriptionformat   => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *       userid              => int  İsteğe bağlı //userid
     *       templateid          => int  İsteğe bağlı //templateid
     *       origtemplateid      => int  İsteğe bağlı //origtemplateid
     *       status              => int  İsteğe bağlı //status
     *       duedate             => int  İsteğe bağlı //duedate
     *       reviewerid          => int  İsteğe bağlı //reviewerid
     *       id                  => int   //id
     *       timecreated         => int  İsteğe bağlı //timecreated
     *       timemodified        => int  İsteğe bağlı //timemodified
     *       usermodified        => int  İsteğe bağlı //usermodified
     * )
     * 
     */
    function lmscoreCompetencyUpdatePlan($plan)
    {

        $functionname = 'core_competency_update_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('plan' => $plan));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [template] => array(
     *           shortname          => string  İsteğe bağlı //shortname
     *           description        => string  İsteğe bağlı //description
     *           descriptionformat  => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *           duedate            => int  İsteğe bağlı //duedate
     *           visible            => int  İsteğe bağlı //visible
     *           contextid          => int  İsteğe bağlı //The context id
     *           contextlevel       => string  İsteğe bağlı //The context level
     *           instanceid         => int  İsteğe bağlı //The Instance id
     *           id                 => int   //id
     *           timecreated        => int  İsteğe bağlı //timecreated
     *           timemodified       => int  İsteğe bağlı //timemodified
     *           usermodified       => int  İsteğe bağlı //usermodified
     *          
     *      )
     * 
     */
    function lmscoreCompetencyUpdateTemplate($template)
    {

        $functionname = 'core_competency_update_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('template' => $template));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user ID
     * 
     * [competencyid] => int //The competency ID
     * 
     */
    function lmscoreCompetencyUserCompetencyCancelReviewRequest($userid, $competencyid)
    {

        $functionname = 'core_competency_user_competency_cancel_review_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [userid] => int //The user id
     * 
     * [planid] => int //The plan id
     * 
     */
    function lmscoreCompetencyUserCompetencyPlanViewed($competencyid, $userid, $planid)
    {

        $functionname = 'core_competency_user_competency_plan_viewed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'userid' => $userid, 'planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user ID
     * 
     * [competencyid] => int //The competency ID
     * 
     */
    function lmscoreCompetencyUserCompetencyRequestReview($userid, $competencyid)
    {

        $functionname = 'core_competency_user_competency_request_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [userid] => int //The user ID
     * 
     * [competencyid] => int //The competency ID
     * 
     */
    function lmscoreCompetencyUserCompetencyStartReview($userid, $competencyid)
    {

        $functionname = 'core_competency_user_competency_start_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user ID 
     * 
     * [competencyid] => int //The competency ID
     * 
     */
    function lmscoreCompetencyUserCompetencyStoptReview($userid, $competencyid)
    {

        $functionname = 'core_competency_user_competency_stop_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [usercompetencyid] => int //The user competency id
     * 
     */
    function lmscoreCompetencyUserCompetencyViewed($usercompetencyid)
    {

        $functionname = 'core_competency_user_competency_viewed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('usercompetencyid' => $usercompetencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [userid] => int //The user id
     * 
     * [courseid] => int //The course id
     * 
     */
    function lmscoreCompetencyUserCompetencyViewedInCourse($competencyid, $userid, $courseid)
    {

        $functionname = 'core_competency_user_competency_viewed_in_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'userid' => $userid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [userid] => int //The user id
     * 
     * [planid] => int //The plan id
     * 
     */
    function lmscoreCompetencyUserCompetencyViewedInPlan($competencyid, $userid, $planid)
    {

        $functionname = 'core_competency_user_competency_viewed_in_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'userid' => $userid, 'planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course ID
     * 
     * [userid] => int //User ID
     * 
     */
    function lmscoreCompletionGetActivitiesCompletionStatus($courseid, $userid)
    {

        $functionname = 'core_completion_get_activities_completion_status';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course ID
     * 
     * [userid] => int //User ID
     * 
     */
    function lmscoreCompletionGetCourseCompletionStatus($courseid, $userid)
    {

        $functionname = 'core_completion_get_course_completion_status';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course ID
     * 
     */
    function lmscoreCompletionMarkCourseSelfCompleted($courseid)
    {

        $functionname = 'core_completion_mark_course_self_completed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //user id
     * 
     * [cmid] => int //course module id
     * 
     * [newstate] => int //the new activity completion state
     * 
     */
    function lmscoreCompletionOverrideActivityCompletionStatus($userid, $cmid, $newstate)
    {

        $functionname = 'core_completion_override_activity_completion_status';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'cmid' => $cmid, 'newstate' => $newstate));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cmid] => int //course module id
     * 
     * [completed] => int //activity completed or not
     * 
     */
    function lmscoreCompletionUpdateActivityCompletionStatusManually($cmid, $completed)
    {

        $functionname = 'core_completion_update_activity_completion_status_manually';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cmid' => $cmid, 'completed' => $completed));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course id to check
     * 
     * [tocheck] => array(
     *              'contextlevel' => string   //The context level for the file location. Only module supported right now.
     *              'id'           => int   //Context instance id
     *              'since'        => int   //Check updates since this time stamp
     *          )
     * 
     * [filter] => array(
     *              [0] => string //Area name: configuration, fileareas, completion, ratings, comments, gradeitems, outcomes
     *          )
     * 
     */
    function lmscoreCourseCheckUpdates($courseid, $tocheck, $filter)
    {

        $functionname = 'core_course_check_updates';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'tocheck' => array($tocheck), 'filter' => $filter));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [categories] => array(
     *               name                => string   //new category name
     *               parent              => int  Varsayılan değer "0" //the parent category id inside which the new category will be created - set to 0 for a root category
     *               idnumber            => string  İsteğe bağlı //the new category idnumber
     *               description         => string  İsteğe bağlı //the new category description
     *               descriptionformat   => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *               theme               => string  İsteğe bağlı //the new category theme. This option must be enabled on moodle
     *          )
     * 
     */
    function lmscoreCourseCreateCategories($categories)
    {

        $functionname = 'core_course_create_categories';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('categories' => array($categories)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courses] => array(
     * 
     *               fullname            => string   //full name
     *               shortname           => string   //course short name
     *               categoryid          => int   //category id
     *               idnumber            => string  İsteğe bağlı //id number
     *               summary             => string  İsteğe bağlı //summary
     *               summaryformat       => int  Varsayılan değer "1" //summary format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *               format              => string  Varsayılan değer "topics" //course format: weeks, topics, social, site,..
     *               showgrades          => int  Varsayılan değer "1" //1 if grades are shown, otherwise 0
     *               newsitems           => int  Varsayılan değer "5" //number of recent items appearing on the course page
     *               startdate           => int  İsteğe bağlı //timestamp when the course start
     *               enddate             => int  İsteğe bağlı //timestamp when the course end
     *               numsections         => int  İsteğe bağlı //(deprecated, use courseformatoptions) number of weeks/topics
     *               maxbytes            => int  Varsayılan değer "0" //largest size of file that can be uploaded into the course
     *               showreports         => int  Varsayılan değer "0" //are activity report shown (yes = 1, no =0)
     *               visible             => int  İsteğe bağlı //1: available to student, 0:not available
     *               hiddensections      => int  İsteğe bağlı //(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students
     *               groupmode           => int  Varsayılan değer "0" //no group, separate, visible
     *               groupmodeforce      => int  Varsayılan değer "0" //1: yes, 0: no
     *               defaultgroupingid   => int  Varsayılan değer "0" //default grouping id
     *               enablecompletion    => int  İsteğe bağlı //Enabled, control via completion and activity settings. Disabled, not shown in activity settings.
     *               completionnotify    => int  İsteğe bağlı //1: yes 0: no
     *               lang                => string  İsteğe bağlı //forced course language
     *               forcetheme          => string  İsteğe bağlı //name of the force theme
     *               courseformatoptions => array(
     *                       name  => string   //course format option name
     *                       value => string   //course format option value
     *               )İsteğe bağlı //additional options for particular course format
     *          
     *      )
     * 
     */
    function lmscoreCourseCreateCourses($courses)
    {

        $functionname = 'core_course_create_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courses' => array($courses)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [categories] => array(
     *           id          => int   //category id to delete
     *           newparent   => int  İsteğe bağlı //the parent category to move the contents to, if specified
     *           recursive   => int  Varsayılan değer "0" //1: recursively delete all contents inside this category, 0 (default): move contents to newparent or current parent category (except if parent is root)
     *      )
     * 
     */
    function lmscoreCourseDeleteCategories($categories)
    {

        $functionname = 'core_course_delete_categories';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('categories' => array($categories)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => array(
     * 
     *              [0] => int //course id
     *          )
     * 
     */
    function lmscoreCourseDeleteCourses($courseids)
    {

        $functionname = 'core_course_delete_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => $courseids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cmids] => array(
     * 
     *      [0] => int //course module ID
     * )
     * 
     */
    function lmscoreCourseDeleteModules($cmids)
    {

        $functionname = 'core_course_delete_modules';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cmids' => $cmids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //course to duplicate id
     * 
     * [fullname] => string //duplicated course full name
     * 
     * [shortname] => string //duplicated course short name
     * 
     * [categoryid] => int //duplicated course category parent
     * 
     * [visible] => int Varsayılan değer "1" //duplicated course visible, default to yes
     * 
     * [options] => array(
     *          name  => string   //The backup option name:
     *                       "activities" (int) Include course activites (default to 1 that is equal to yes),
     *                       "blocks" (int) Include course blocks (default to 1 that is equal to yes),
     *                       "filters" (int) Include course filters  (default to 1 that is equal to yes),
     *                       "users" (int) Include users (default to 0 that is equal to no),
     *                       "enrolments" (int) Include enrolment methods (default to 1 - restore only with users),
     *                       "role_assignments" (int) Include role assignments  (default to 0 that is equal to no),
     *                       "comments" (int) Include user comments  (default to 0 that is equal to no),
     *                       "userscompletion" (int) Include user course completion information  (default to 0 that is equal to no),
     *                       "logs" (int) Include course logs  (default to 0 that is equal to no),
     *                       "grade_histories" (int) Include histories  (default to 0 that is equal to no)
     *          value  => string   //the value for the option 1 (yes) or 0 (no)
     * )
     * 
     */
    function lmscoreCourseDuplicateCourse($courseid, $fullname, $shortname, $categoryid, $visible, $options)
    {

        $functionname = 'core_course_duplicate_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'fullname' => $fullname, 'shortname' => $shortname, 'categoryid' => $categoryid, 'visible' => $visible, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [action] => string //action: hide, show, stealth, duplicate, delete, moveleft, moveright, group...
     * 
     * [id] => int //course module id
     * 
     * [sectionreturn] => int Varsayılan değer "null" //section to return to
     * 
     */
    function lmscoreCourseEditModule($action, $id, $sectionreturn = null)
    {

        $functionname = 'core_course_edit_module';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('action' => $action, 'id' => $id, 'sectionreturn' => $sectionreturn)); 
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [action] => string //action: hide, show, stealth, setmarker, removemarker
     * 
     * [id] => int //course section id
     * 
     * [sectionreturn] => int Varsayılan değer "null" //section to return to
     * 
     */
    function lmscoreCourseEditSection($action, $id, $sectionreturn = null)
    {

        $functionname = 'core_course_edit_section';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('action' => $action, 'id' => $id, 'sectionreturn' => $sectionreturn));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => array(
     *      [0] => int //course id.
     * )
     * 
     */
    function lmscoreCourseGetActivitiesOverview($courseids)
    {

        $functionname = 'core_course_get_activities_overview';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => $courseids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [criteria] => array(
     *      key  => string   //The category column to search, expected keys (value format) are:
     *                      "id" (int) the category id,
     *                      "ids" (string) category ids separated by commas,
     *                      "name" (string) the category name,
     *                      "parent" (int) the parent category id,
     *                      "idnumber" (string) category idnumber - user must have 'moodle/category:manage' to search on idnumber,
     *                      "visible" (int) whether the returned categories must be visible or hidden. If the key is not passed,
     *                      then the function lmsreturn all categories that the user can see. - user must have 'moodle/category:manage' or 'moodle/category:
     *                      viewhiddencategories' to search on visible,"theme" (string) only return the categories having this theme - user must have 'moodle/category:manage' to search on theme
     *      value => string   //the value to match
     * )
     * 
     * [addsubcategories] => int Varsayılan değer "1" //return the sub categories infos. (1 - default) otherwise only the category info (0)
     * 
     */
    function lmscoreCourseGetCategories($criteria, $addsubcategories = 1)
    {

        $functionname = 'core_course_get_categories';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('criteria' => array($criteria), 'addsubcategories' => $addsubcategories));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //course id
     * 
     * [options] => array(
     *          name  => string   //The expected keys (value format) are:
     *                           excludemodules (bool) Do not return modules, return only the sections structure
     *                           excludecontents (bool) Do not return module contents (i.e: files inside a resource)
     *                           includestealthmodules (bool) Return stealth modules for students in a special
     *                           section (with id -1)
     *                           sectionid (int) Return only this section
     *                           sectionnumber (int) Return only this section with number (order)
     *                           cmid (int) Return only this module information (among the whole sections structure)
     *                           modname (string) Return only modules with this name "label, forum, etc..."
     *                           modid (int) Return only the module with this id (to be used with modname
     *          value => string   //the value of the option, this param is personaly validated in the external function.
     * 
     *      )
     * 
     */
    function lmscoreCourseGetContents($courseid)
    {

        $functionname = 'core_course_get_contents';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cmid] => int //The course module id
     * 
     */
    function lmscoreCourseGetCourseModule($cmid)
    {

        $functionname = 'core_course_get_course_module';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cmid' => $cmid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [module] => string //The module name
     * 
     * [instance] => int //The module instance id
     * 
     */
    function lmscoreCourseGetCourseModuleInstance($module, $instance)
    {

        $functionname = 'core_course_get_course_module_by_instance';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('module' => $module, 'instance' => $instance));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [options] => array(
     *          isteğe bağlı //List of course id. If empty return all courses except front page course.
     *          'ids' =>  array(
     *                  [0] =>  course id 
     *              )
     *      
     *          )
     * 
     */
    function lmscoreCourseGetCourses($options)
    {

        $functionname = 'core_course_get_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [field] => string Varsayılan değer "" //The field to search can be left empty for all courses or:
     *                                           id: course id
     *                                           ids: comma separated course ids
     *                                           shortname: course short name
     *                                           idnumber: course id number
     *                                           category: category id the course belongs to
     * 
     * [value] => string Varsayılan değer "" //The value to match
     * 
     */
    function lmscoreCourseGetCoursesField($field, $value)
    {

        $functionname = 'core_course_get_courses_by_field';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('field' => $field, 'value' => $value));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [classification] => string //future, inprogress, or past
     * 
     * [limit] => int Varsayılan değer "0" //Result set limit
     * 
     * [offset] => int Varsayılan değer "0" //Result set offset
     * 
     * [sort] => string Varsayılan değer "null" //Sort string
     * 
     */
    function lmscoreCourseGetEnrolledCoursesTimelineClassification($classification, $limit = 0, $offset = 0, $sort = null)
    {

        $functionname = 'core_course_get_enrolled_courses_by_timeline_classification';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('classification' => $classification, 'limit' => $limit, 'offset' => $offset, 'sort' => $sort));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //course module id
     * 
     * [sectionreturn] => int Varsayılan değer "null" //section to return to
     * 
     */
    function lmscoreCourseGetModule($id, $sectionreturn = null)
    {

        $functionname = 'core_course_get_module';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'sectionreturn' => $sectionreturn));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, default to current user
     * 
     * [limit] => int Varsayılan değer "0" //result set limit
     * 
     * [offset] => int Varsayılan değer "0" //Result set offset
     * 
     * [sort] => string Varsayılan değer "null" //Sort string
     * 
     */
    function lmscoreCourseGetRecentCourses($userid = 0, $limit = 0, $offset = 0, $sort = null)
    {

        $functionname = 'core_course_get_recent_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'limit' => $limit, 'offset' => $offset, 'sort' => $sort));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course id to check
     * 
     * [since] => int //Check updates since this time stamp
     * 
     * [filter] => array(
     *              [0] => string //Area name: configuration, fileareas, completion, ratings, comments, gradeitems, outcomes
     *          )
     * 
     */
    function lmscoreCourseGetUpdatesSince($courseid, $since, $filter)
    {

        $functionname = 'core_course_get_updates_since';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'since' => $since, 'filter' => $filter));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => array(
     *                  [0] => int //course id.
     *              )
     * 
     */
    function lmscoreCourseGetUserAdministrationOptions($courseids)
    {

        $functionname = 'core_course_get_user_administration_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => $courseids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => array(
     *                  [0] => int //course id.
     *              )
     * 
     */
    function lmscoreCourseGetUserNavigationOptions($courseids)
    {

        $functionname = 'core_course_get_user_navigation_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => $courseids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [importfrom] => int //the id of the course we are importing from
     * 
     * [importto] => int //the id of the course we are importing to
     * 
     * [deletecontent] => int Varsayılan değer "0" //whether to delete the course content where we are importing to (default to 0 = No)
     * 
     * [options] => array(
     *              name  => string   //The backup option name:
     *                              "activities" (int) Include course activites (default to 1 that is equal to yes),
     *                              "blocks" (int) Include course blocks (default to 1 that is equal to yes),
     *                              "filters" (int) Include course filters  (default to 1 that is equal to yes)
     *              value => string   //the value for the option 1 (yes) or 0 (no)
     *          )
     * 
     */
    function lmscoreCourseImportCourse($importfrom, $importto, $deletecontent = 0, $options)
    {

        $functionname = 'core_course_import_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('importfrom' => $importfrom, 'importto' => $importto, 'deletecontent' => $deletecontent, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [criterianame] => string //criteria name (search, modulelist (only admins), blocklist (only admins), tagid)
     * 
     * [criteriavalue] => string //criteria value
     * 
     * [page] => int Varsayılan değer "0" //page number (0 based)
     * 
     * [perpage] => int Varsayılan değer "0" //items per page
     * 
     * [requiredcapabilities] => //Optional list of required capabilities (used to filter the list) 
     *                           array(
     *                            [0] => string //Capability string used to filter courses by permission
     *                           )
     * 
     * [limittoenrolled] => int Varsayılan değer "0" //limit to enrolled courses
     * 
     */
    function lmscoreCourseSearchCourse($criterianame, $criteriavalue, $page = 0, $perpage = 0, $requiredcapabilities, $limittoenrolled = 0)
    {

        $functionname = 'core_course_search_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('criterianame' => $criterianame, 'criteriavalue' => $criteriavalue, 'page' => $page, 'perpage' => $perpage, 'requiredcapabilities' => $requiredcapabilities, 'limittoenrolled' => $limittoenrolled));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courses] => array(
     *                  id        => int   //course ID
     *                  favourite => int   //favourite status    
     *              )
     * 
     */
    function lmscoreCourseSetFavouriteCourses($courses)
    {

        $functionname = 'core_course_set_favourite_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courses' => array($courses)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [categories] => array(
     *               id                 => int   //course id
     *               name               => string  İsteğe bağlı //category name
     *               idnumber           => string  İsteğe bağlı //category id number
     *               parent             => int  İsteğe bağlı //parent category id
     *               description        => string  İsteğe bağlı //category description
     *               descriptionformat  => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *               theme              => string  İsteğe bağlı //the category theme. This option must be enabled on moodle
     *      
     *      )
     * 
     */
    function lmscoreCourseUpdateCategories($categories)
    {

        $functionname = 'core_course_update_categories';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('categories' => array($categories)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courses] => array(
     * 
     *               fullname            => string   //full name
     *               shortname           => string   //course short name
     *               categoryid          => int   //category id
     *               idnumber            => string  İsteğe bağlı //id number
     *               summary             => string  İsteğe bağlı //summary
     *               summaryformat       => int  Varsayılan değer "1" //summary format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *               format              => string  Varsayılan değer "topics" //course format: weeks, topics, social, site,..
     *               showgrades          => int  Varsayılan değer "1" //1 if grades are shown, otherwise 0
     *               newsitems           => int  Varsayılan değer "5" //number of recent items appearing on the course page
     *               startdate           => int  İsteğe bağlı //timestamp when the course start
     *               enddate             => int  İsteğe bağlı //timestamp when the course end
     *               numsections         => int  İsteğe bağlı //(deprecated, use courseformatoptions) number of weeks/topics
     *               maxbytes            => int  Varsayılan değer "0" //largest size of file that can be uploaded into the course
     *               showreports         => int  Varsayılan değer "0" //are activity report shown (yes = 1, no =0)
     *               visible             => int  İsteğe bağlı //1: available to student, 0:not available
     *               hiddensections      => int  İsteğe bağlı //(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students
     *               groupmode           => int  Varsayılan değer "0" //no group, separate, visible
     *               groupmodeforce      => int  Varsayılan değer "0" //1: yes, 0: no
     *               defaultgroupingid   => int  Varsayılan değer "0" //default grouping id
     *               enablecompletion    => int  İsteğe bağlı //Enabled, control via completion and activity settings. Disabled, not shown in activity settings.
     *               completionnotify    => int  İsteğe bağlı //1: yes 0: no
     *               lang                => string  İsteğe bağlı //forced course language
     *               forcetheme          => string  İsteğe bağlı //name of the force theme
     *               courseformatoptions => array(
     *                       name  => string   //course format option name
     *                       value => string   //course format option value
     *               )İsteğe bağlı //additional options for particular course format
     *          
     *      )
     * 
     */
    function lmscoreCourseUpdateCourses($courses)
    {

        $functionname = 'core_course_update_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courses' => array($courses)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //id of the course
     * 
     * [sectionnumber] => int Varsayılan değer "0" //section number
     * 
     */
    function lmscoreCourseViewCourse($courseid, $sectionnumber = 0)
    {

        $functionname = 'core_course_view_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'sectionnumber' => $sectionnumber));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //User enrolment ID
     * 
     * [ueid] => int //User enrolment ID
     * 
     * [status] => int //Enrolment status
     * 
     * [timestart] => int Varsayılan değer "0" //Enrolment start timestamp
     * 
     * [timeend] => int Varsayılan değer "0" //Enrolment end timestamp
     * 
     */
    function lmscoreEnrolEditUserEnrolment($courseid, $ueid, $status, $timestart, $timeend)
    {

        $functionname = 'core_enrol_edit_user_enrolment';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'ueid' => $ueid, 'status' => $status, 'timestart' => $timestart, 'timeend' => $timeend));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int courseid
     * 
     */
    function lmscoreEnrolGetCourseEnrolmentMethods($courseid)
    {

        $functionname = 'core_enrol_get_course_enrolment_methods';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => 
     * 
     * [options] => //Option names:
     *                       * withcapability (string) return only users with this capability. This option requires 'moodle/role:review' on the course context.
     *                       * groupid (integer) return only users in this group id. If the course has groups enabled and this param
     *                                           isn't defined, returns all the viewable users.
     *                                           This option requires 'moodle/site:accessallgroups' on the course context if the
     *                                           user doesn't belong to the group.
     *                       * onlyactive (integer) return only users with active enrolments and matching time restrictions. This option requires 'moodle/course:enrolreview' on the course context.
     *                       * userfields ('string, string, ...') return only the values of these user fields.
     *                       * limitfrom (integer) sql limit from.
     *                       * limitnumber (integer) maximum number of returned users.
     *                       * sortby (string) sort by id, firstname or lastname. For ordering like the site does, use siteorder.
     *                       * sortdirection (string) ASC or DESC 
     *          array(
     *              name  => string   //option name
     *              value => string   //option value
     *          )
     * 
     */
    function lmscoreEnrolGetEnrolledUsers($courseid, $options)
    {

        $functionname = 'core_enrol_get_enrolled_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [coursecapabilities] => array(
     *                          courseid int   //Course ID number in the Moodle course table
     *                          capabilities => array(
     *                                          [0] => string   //Capability name, such as mod/forum:viewdiscussion    
     *                                      )
     *                      )
     * 
     * [options] => //Option names:
     *                       * groupid (integer) return only users in this group id. Requires 'moodle/site:accessallgroups' .
     *                       * onlyactive (integer) only users with active enrolments. Requires 'moodle/course:enrolreview' .
     *                       * userfields ('string, string, ...') return only the values of these user fields.
     *                       * limitfrom (integer) sql limit from.
     *                       * limitnumber (integer) max number of users per course and capability.
     *              array(
     *                  name  => string   //option name
     *                  value => string   //option value
     *              )
     * 
     */
    function lmscoreEnrolGetEnrolledUsersWithCapability($coursecapabilities, $options)
    {

        $functionname = 'core_enrol_get_enrolled_users_with_capability';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('coursecapabilities' => array($coursecapabilities), 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //course id
     * 
     * [enrolid] => int //enrolment id
     * 
     * [search] => string //query
     * 
     * [searchanywhere] => int //find a match anywhere, or only at the beginning
     * 
     * [page] => int //Page number
     * 
     * [perpage] => int //Number per page
     * 
     */
    function lmscoreEnrolGetPotentialUsers($courseid, $enrolid, $search, $searchanywhere, $page, $perpage)
    {

        $functionname = 'core_enrol_get_potential_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'enrolid' => $enrolid, 'search' => $search, 'searchanywhere' => $searchanywhere, 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //user id
     * 
     */
    function lmscoreEnrolGetUsersCourses($userid)
    {

        $functionname = 'core_enrol_get_users_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [ueid] => int //User enrolment ID
     * 
     */
    function lmscoreEnrolUnenrolUserEnrolment($ueid)
    {

        $functionname = 'core_enrol_unenrol_user_enrolment';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('ueid' => $ueid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextid] => int //Context ID
     * 
     */
    function lmscoreFetchNotifications($contextid)
    {

        $functionname = 'core_fetch_notifications';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextid' => $contextid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextid] => int //context id Set to -1 to use contextlevel and instanceid.
     * 
     * [component] => string //component
     * 
     * [filearea] => string //file area
     * 
     * [itemid] => int //associated id
     * 
     * [filepath] => string //file path
     * 
     * [filename] => string //file name
     * 
     * [modified] => int Varsayılan değer "null" //timestamp to return files changed after this time.
     * 
     * [contextlevel] => string Varsayılan değer "null" //The context level for the file location.
     * 
     * [instanceid] => int Varsayılan değer "null" //The instance id for where the file is located.
     * 
     */
    function lmscoreFilesGetFiles($contextid, $component, $filearea, $itemid, $filepath, $filename, $modified = null, $contextlevel = null, $instanceid = null)
    {

        $functionname = 'core_files_get_files';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextid' => $contextid, 'component' => $component, 'filearea' => $filearea, 'itemid' => $itemid, 'filepath' => $filepath, 'filename' => $filename, 'modified' => $modified, 'contextlevel' => $contextlevel, 'instanceid' => $instanceid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextid] => int Varsayılan değer "null" //context id
     * 
     * [component] => string //component
     * 
     * [filearea] => string //file area
     * 
     * [itemid] => int //associated id
     * 
     * [filepath] => string //file path
     * 
     * [filename] => string //file name
     * 
     * [filecontent] => string //file content
     * 
     * [contextlevel] => string Varsayılan değer "null" //The context level to put the file in, (block, course, coursecat, system, user, module)
     * 
     * [instanceid] => int Varsayılan değer "null" //The Instance id of item associated with the context level
     * 
     */
    function lmscoreFilesUpload($contextid, $component, $filearea, $itemid, $filepath, $filename, $filecontent, $contextlevel = null, $instanceid = null)
    {

        $functionname = 'core_files_upload';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextid' => $contextid, 'component' => $component, 'filearea' => $filearea, 'itemid' => $itemid, 'filepath' => $filepath, 'filename' => $filename, 'filecontent' => $filecontent, 'contextlevel' => $contextlevel, 'instanceid' => $instanceid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contexts] => array(
     *              contextlevel => string   //The context level where the filters are: (coursecat, course, module)
     *              instanceid   => int   //The instance id of item associated with the context.
     *          )
     * 
     */
    function lmscoreFiltersGetAvailableInContext($contexts)
    {

        $functionname = 'core_filters_get_available_in_context';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contexts' => array($contexts)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [onlytypes] => string Varsayılan değer "" //Limit the browser to the given groups and extensions 
     * 
     * [allowall] => int Varsayılan değer "1" //Allows to select All file types, does not apply with onlytypes are set.
     * 
     * [current] => string Varsayılan değer "" //Current types that should be selected.
     * 
     */
    function lmscoreFormGetFiletypesBrowserData($onlytypes = "", $allowall = 1, $current = "")
    {

        $functionname = 'core_form_get_filetypes_browser_data';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('onlytypes' => $onlytypes, 'allowall' => $allowall, 'current' => $current));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [component] => string //component
     * 
     * [lang] => string Varsayılan değer "null" //lang
     * 
     */
    function lmscoreGetComponentStrings($component, $lang = null)
    {

        $functionname = 'core_get_component_strings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'lang' => $lang));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [component] => string //Component for the callback e.g. mod_assign
     * 
     * [callback] => string //Name of the callback to execute
     * 
     * [contextid] => int //Context ID that the fragment is from
     * 
     * [args] => İsteğe bağlı //args for the callback are optional
     *           array(
     *              name  => string   //param name
     *              value => string   //param value
     *          )
     * 
     */
    function lmscoreGetFragment($component, $callback, $contextid, $args)
    {

        $functionname = 'core_get_fragment';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'callback' => $callback, 'contextid' => $contextid, 'args' => array($args)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [stringid] => string //string identifier
     * 
     * [component] => string Varsayılan değer "moodle" //component
     * 
     * [lang] => string Varsayılan değer "null" //lang
     * 
     * [stringparams] => //the definition of a string param (i.e. {$a->name})
     *              array(
     *                  name  => string  İsteğe bağlı //param name - if the string expect only one $a parameter then don't send this field, just send the value.
     *                  value => string   //param value
     *              )
     * 
     */
    function lmscoreGetString($stringid, $component = "moodle", $lang = null, $stringparams)
    {

        $functionname = 'core_get_string';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('stringid' => $stringid, 'component' => $component, 'lang' => $lang, 'stringparams' => array($stringparams)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [strings] => array(
     *              stringid     => string   //string identifier
     *              component    => string  Varsayılan değer "moodle" //component
     *              lang         => string  Varsayılan değer "null" //lang
     *              stringparams => //the definition of a string param (i.e. {$a->name}) Varsayılan değer "Array": 
     *                      array(
     *                          name  => string  İsteğe bağlı //param name - if the string expect only one $a parameter then don't send this field, just send the value.
     *                          value => string   //param value
     *                      )
     *          
     *          )
     * 
     */
    function lmscoreGetStrings($strings)
    {

        $functionname = 'core_get_strings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('strings' => array($strings)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextid] => int Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     * 
     * [contextlevel] => string Varsayılan değer "" //Context level. To be used with instanceid.
     * 
     * [instanceid] => int Varsayılan değer "0" //Context instance ID. To be used with level
     * 
     * [timestamps] => array(
     *                  timestamp => int   //unix timestamp
     *                  format    => string   //format string
     *              )
     * 
     */
    function lmscoreGetUserDates($contextid = 0, $contextlevel = "", $instanceid = 0, $timestamps)
    {

        $functionname = 'core_get_user_dates';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextid' => $contextid, 'contextlevel' => $contextlevel, 'instanceid' => $instanceid, 'timestamps' => array($timestamps)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //id of course
     * 
     * [component] => string Varsayılan değer "" //A component, for example mod_forum or mod_quiz
     * 
     * [activityid] => int Varsayılan değer "null" //The activity ID
     * 
     * [userids] => Varsayılan değer "Array
     *              array(
     *                  [0] => int // user id
     *              )
     * 
     */
    function lmscoreGradesGetGrades($courseid, $component, $activityid, $userids)
    {

        $functionname = 'core_grades_get_grades';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'component' => $component, 'activityid' => $activityid, 'userids' => $userids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [source] => string //The source of the grade update
     * 
     * [courseid] => int //id of course
     * 
     * [component] => string //A component, for example mod_forum or mod_quiz
     * 
     * [activityid] => int //The activity ID
     * 
     * [itemnumber] => int //grade item ID number for modules that have multiple grades. Typically this is 0.
     * 
     * [grades] => //Any student grades to alter
     *              array(
     *                  studentid    => int   //Student ID
     *                  grade        => double   //Student grade
     *                  str_feedback => string  İsteğe bağlı //A string representation of the feedback from the grader
     *              )
     * 
     */
    function lmscoreGradesUpdateGrades($source, $courseid, $component, $activityid, $itemnumber, $grades)
    {

        $functionname = 'core_grades_update_grades';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('source' => $source, 'courseid' => $courseid, 'component' => $component, 'activityid' => $activityid, 'itemnumber' => $itemnumber, 'grades' => array($grades)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cmids] => //1 or more course module ids
     *              array(
     *                  [0] => int //course module id
     *              )
     * 
     * [areaname] => string //area name
     * 
     * [activeonly] => int Varsayılan değer "0" //Only the active method
     * 
     */
    function lmscoreGradingGetDefinitions($cmids, $areaname, $activeonly = 0)
    {

        $functionname = 'core_grading_get_definitions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cmids' => $cmids, 'areaname' => $areaname, 'activeonly' => $activeonly));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [definitionid] => int //definition id
     * 
     * [since] => int Varsayılan değer "0" //submitted since
     * 
     */
    function lmscoreGradingGetGradingformInstances($definitionid, $since = 0)
    {

        $functionname = 'core_grading_get_gradingform_instances';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('definitionid' => $definitionid, 'since' => $since));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    function lmscoreGradingSaveDefinitions($areas)
    {

        $functionname = 'core_grading_save_definitions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('areas' => array($areas)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [members] => array(
     *              groupid  => int   //group record id
     *              userid   => int   //user id
     *          )
     * 
     */
    function lmscoreGroupAddGroupMembers($members)
    {

        $functionname = 'core_group_add_group_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('members' => array($members)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [assignments] => array(
     *                  groupingid => int   //grouping record id
     *                  groupid    => int   //group record id
     *          )
     * 
     */
    function lmscoreGroupAssignGrouping($assignments)
    {

        $functionname = 'core_group_assign_grouping';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignments' => array($assignments)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupings] => array(
     *              courseid          => int   //id of course
     *              name              => string   //multilang compatible name, course unique
     *              description       => string   //grouping description text
     *              descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *              idnumber          => string  İsteğe bağlı //id number
     *          )
     * 
     */
    function lmscoreGroupCreateGroupings($groupings)
    {

        $functionname = 'core_group_create_groupings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupings' => array($groupings)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groups] => array(
     *              courseid            => int   //id of course
     *              name                => string   //multilang compatible name, course unique
     *              description         => string   //group description text
     *              descriptionformat   => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *              enrolmentkey        => string  İsteğe bağlı //group enrol secret phrase
     *              idnumber            => string  İsteğe bağlı //id number
     *          )
     * 
     */
    function lmscoreGroupCreateGroups($groups)
    {

        $functionname = 'core_group_create_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groups' => array($groups)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [members] => array(
     *                  groupid => int   //group record id
     *                  userid  => int   //user id
     *              )
     * 
     */
    function lmscoreGroupDeleteGroupMembers($members)
    {

        $functionname = 'core_group_delete_group_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('members' => array($members)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupingids] => array(
     *                  [0] => int //grouping ID
     *              )
     * 
     */
    function lmscoreGroupDeleteGroupings($groupingids)
    {

        $functionname = 'core_group_delete_groupings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupingids' => $groupingids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupids] => array(
     *              [0] => int //Group ID
     *          )
     * 
     */
    function lmscoreGroupDeleteGroups($groupids)
    {

        $functionname = 'core_group_delete_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupids' => $groupids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cmid] => int //course module id
     * 
     * [userid] => int Varsayılan değer "0" //id of user, empty for current user
     * 
     */
    function lmscoreGroupGetActivityAllowedGroups($cmid, $userid = 0)
    {

        $functionname = 'core_group_get_activity_allowed_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cmid' => $cmid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cmid] => int //course module id
     * 
     */
    function lmscoreGroupGetActivityGroupmode($cmid)
    {

        $functionname = 'core_group_get_activity_groupmode';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cmid' => $cmid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //id of course
     * 
     */
    function lmscoreGroupGetCourseGroupings($courseid)
    {

        $functionname = 'core_group_get_course_groupings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //id of course
     * 
     */
    function lmscoreGroupGetCourseGroups($courseid)
    {

        $functionname = 'core_group_get_course_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int Varsayılan değer "0" //Id of course (empty or 0 for all the courses where the user is enrolled).
     * 
     * [userid] => int Varsayılan değer "0" //Id of user (empty or 0 for current user).
     * 
     * [groupingid] => int Varsayılan değer "0" //returns only groups in the specified grouping
     * 
     */
    function lmscoreGroupGetCourseUserGroups($courseid, $userid, $groupingid)
    {

        $functionname = 'core_group_get_course_user_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid, 'groupingid' => $groupingid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupids] => array(
     *          [0] => int //group id
     *      )
     * 
     */
    function lmscoreGroupGetGroupMembers($groupids)
    {

        $functionname = 'core_group_get_group_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupids' => $groupids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupingids] =>  //List of grouping id. A grouping id is an integer.
     *                  array(
     *                      [0] => int //grouping ID
     *                  )
     * 
     * [returngroups] => int Varsayılan değer "0" //return associated groups
     * 
     */
    function lmscoreGroupGetGroupings($groupingids, $returngroups)
    {

        $functionname = 'core_group_get_groupings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupingids' => $groupingids, 'returngroups' => $returngroups));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupids] => //List of group id. A group id is an integer.
     *          array(
     *              [0] => int // group id.
     *          )
     * 
     */
    function lmscoreGroupGetGroups($groupids)
    {

        $functionname = 'core_group_get_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupids' => $groupids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [unassignments] => array(
     *                  groupingid => int   //grouping record id
     *                  groupid    => int   //group record id
     *              )
     * 
     */
    function lmscoreGroupUnassignGrouping($unassignments)
    {

        $functionname = 'core_group_unassign_grouping';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('unassignments' => array($unassignments)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groupings] => //List of grouping object. A grouping has a courseid, a name and a description. 
     *          array(
     *               id                => int   //id of grouping
     *               name              => string   //multilang compatible name, course unique
     *               description       => string   //grouping description text
     *               descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *               idnumber          => string  İsteğe bağlı //id number
     *          )
     * 
     */
    function lmscoreGroupUpdateGroupings($groupings)
    {

        $functionname = 'core_group_update_groupings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groupings' => array($groupings)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [groups] => //List of group objects. A group is found by the id, then all other details provided will be updated.
     *              array(
     *                   id                => int   //ID of the group
     *                   name              => string   //multilang compatible name, course unique
     *                   description       => string  İsteğe bağlı //group description text
     *                   descriptionformat => int  Varsayılan değer "1" //description format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *                   enrolmentkey      => string  İsteğe bağlı //group enrol secret phrase
     *                   idnumber          => string  İsteğe bağlı //id number
     *              )
     * 
     */
    function lmscoreGroupUpdateGroups($groups)
    {

        $functionname = 'core_group_update_groups';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('groups' => array($groups)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userids] => //List of user IDs
     *              array(
     *                  [0] => int //user id.
     *              )
     * 
     * [userid] => int Varsayılan değer "0" //The id of the user we are blocking the contacts for, 0 for the current user
     * 
     */
    function lmscoreMessageBlockContacts($userids, $userid = 0)
    {

        $functionname = 'core_message_block_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userids' => $userids, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who is blocking
     * 
     * [blockeduserid] => int //The id of the user being blocked
     * 
     */
    function lmscoreMessageBlockUser($userid, $blockeduserid)
    {

        $functionname = 'core_message_block_user';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'blockeduserid' => $blockeduserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user making the request
     * 
     * [requesteduserid] => int //The id of the user being requested
     * 
     */
    function lmscoreMessageConfirmContactRequest($userid, $requesteduserid)
    {

        $functionname = 'core_message_confirm_contact_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'requesteduserid' => $requesteduserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user making the request
     * 
     * [requesteduserid] => int //The id of the user being requested
     * 
     */
    function lmscoreMessageCreateContactRequest($userid, $requesteduserid)
    {

        $functionname = 'core_message_create_contact_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'requesteduserid' => $requesteduserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userids] => array(
     *              [0] => int //user id.
     *          )
     * 
     * [userid] => int Varsayılan değer "0" //The id of the user we are creating the contacts for, 0 for the current user
     * 
     */
    function lmscoreMessageCreateContacts($userids, $userid)
    {

        $functionname = 'core_message_create_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userids' => $userids, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who we are viewing conversations for
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageDataForMessageAreaContacts($userid, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_data_for_messagearea_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int  //The id of the user who we are viewing conversations for
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageDataForMessageAreaConversations($userid, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_data_for_messagearea_conversations';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [currentuserid] => int //The current user's id
     * 
     * [otheruserid] => int //The other user's id
     * 
     */
    function lmscoreMessageDataForMessageAreaGetMostRecentMessage($currentuserid, $otheruserid)
    {

        $functionname = 'core_message_data_for_messagearea_get_most_recent_message';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('currentuserid' => $currentuserid, 'otheruserid' => $otheruserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [currentuserid] => int //The current user's id 
     * 
     * [otheruserid] => int //The id of the user whose profile we want to view
     * 
     */
    function lmscoreMessageDataForMessageAreaGetProfile($currentuserid, $otheruserid)
    {

        $functionname = 'core_message_data_for_messagearea_get_profile';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('currentuserid' => $currentuserid, 'otheruserid' => $otheruserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [currentuserid] => int //The current user's id
     * 
     * [otheruserid] => int //The other user's id
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     * [newest] => int Varsayılan değer "" //Newest first?
     * 
     * [timefrom] => int Varsayılan değer "0" //The timestamp from which the messages were created
     * 
     */
    function lmscoreMessageDataForMessageAreaMessages($currentuserid, $otheruserid, $limitfrom = 0, $limitnum = 0, $newest, $timefrom = 0)
    {

        $functionname = 'core_message_data_for_messagearea_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('currentuserid' => $currentuserid, 'otheruserid' => $otheruserid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum, 'newest' => $newest, 'timefrom' => $timefrom));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who is performing the search
     * 
     * [search] => string //The string being searched
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageDataForMessageAreaSearchMessages($userid, $search, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_data_for_messagearea_search_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'search' => $search, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who is performing the search
     * 
     * [search] => string //The string being searched
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageDataForMessageAreaSearchUsers($userid, $search, $limitnum = 0)
    {

        $functionname = 'core_message_data_for_messagearea_search_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'search' => $search, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who is performing the search
     * 
     * [courseid] => int //The id of the course
     * 
     * [search] => string //The string being searched
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageDataForMessageAreaSearchUsersInCourse($userid, $courseid, $search, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_data_for_messagearea_search_users_in_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'courseid' => $courseid, 'search' => $search, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user making the request
     * 
     * [requesteduserid] => int //The id of the user being requested
     * 
     */
    function lmscoreMessageDeclineContactRequest($userid, $requesteduserid)
    {

        $functionname = 'core_message_decline_contact_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'requesteduserid' => $requesteduserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userids] => array(
     *              [0] => user id.
     *          )
     * 
     * [userid] => int Varsayılan değer "0" //The id of the user we are deleting the contacts for, 0 for the current user.
     * 
     */
    function lmscoreMessageDeleteContacts($userids, $userid)
    {

        $functionname = 'core_message_delete_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userids' => $userids, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user id of who we want to delete the conversation for
     * 
     * [otheruserid] => int //The user id of the other user in the conversation
     * 
     */
    function lmscoreMessageDeleteConversation($userid, $otheruserid)
    {

        $functionname = 'core_message_delete_conversation';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'otheruserid' => $otheruserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user id of who we want to delete the conversation for
     * 
     * [conversationids] => array(
     *                  [0] => int //The id of the conversation
     *              )
     * 
     */
    function lmscoreMessageDeleteConversationsId($userid, $conversationids)
    {

        $functionname = 'core_message_delete_conversations_by_id';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'conversationids' => $conversationids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [messageid] => int //The message id
     * 
     * [userid] => int //The user id of who we want to delete the message for
     * 
     * [read] => int Varsayılan değer "1" //If is a message read
     * 
     */
    function lmscoreMessageDeleteMessage($messageid, $userid, $read = 1)
    {

        $functionname = 'core_message_delete_message';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('messageid' => $messageid, 'userid' => $userid, 'read' => $read));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //the user whose blocked users we want to retrieve
     * 
     */
    function lmscoreMessageGetBlockedUsers($userid)
    {

        $functionname = 'core_message_get_blocked_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user we want the requests for
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageGetContactRequests($userid, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_get_contact_requests';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /*
    function lmscoreMessageGetContacts()
    {

        $functionname = 'core_message_get_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array());
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    */
    
    /**
     * [userid] => int //The id of the user who we are viewing conversations for
     * 
     * [conversationid] => int //The id of the conversation to fetch
     * 
     * [includecontactrequests] => int //Include contact requests in the members
     * 
     * [includeprivacyinfo] => int //Include privacy info in the members
     * 
     * [memberlimit] => int Varsayılan değer "0" //Limit for number of members
     * 
     * [memberoffset] => int Varsayılan değer "0" //Offset for member list
     * 
     * [messagelimit] => int Varsayılan değer "100" //Limit for number of messages
     * 
     * [messageoffset] => int Varsayılan değer "0" //Offset for messages list
     * 
     * [newestmessagesfirst] => int Varsayılan değer "1" //Order messages by newest first
     * 
     */
    function lmscoreMessageGetConversation($userid, $conversationid, $includecontactrequests, $includeprivacyinfo, $memberlimit = 0, $memberoffset = 0, $messagelimit = 100, $messageoffset = 0, $newestmessagesfirst = 1)
    {

        $functionname = 'core_message_get_conversation';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'conversationid' => $conversationid, 'includecontactrequests' => $includecontactrequests, 'includeprivacyinfo' => $includeprivacyinfo, 'memberlimit' => $memberlimit, 'memberoffset' => $memberoffset, 'messagelimit' => $messagelimit, 'messageoffset' => $messageoffset, 'newestmessagesfirst' => $newestmessagesfirst));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who we are viewing conversations for
     * 
     * [otheruserid] => int //The other user id
     * 
     * [includecontactrequests] => int //Include contact requests in the members
     * 
     * [includeprivacyinfo] => int //Include privacy info in the members
     * 
     * [memberlimit] => int Varsayılan değer "0" //Limit for number of members
     * 
     * [memberoffset] => int Varsayılan değer "0" //Offset for member list
     * 
     * [messagelimit] => int Varsayılan değer "100" //Limit for number of messages
     * 
     * [messageoffset] => int Varsayılan değer "0" //Offset for messages list
     * 
     * [newestmessagesfirst] => int Varsayılan değer "1" //Order messages by newest first
     * 
     */
    function lmscoreMessageGetConversationBetweenUsers($userid, $otheruserid, $includecontactrequests, $includeprivacyinfo, $memberlimit = 0, $memberoffset = 0, $messagelimit = 100, $messageoffset = 0, $newestmessagesfirst = 1)
    {

        $functionname = 'core_message_get_conversation_between_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'otheruserid' => $otheruserid, 'includecontactrequests' => $includecontactrequests, 'includeprivacyinfo' => $includeprivacyinfo, 'memberlimit' => $memberlimit, 'memberoffset' => $memberoffset, 'messagelimit' => $messagelimit, 'messageoffset' => $messageoffset, 'newestmessagesfirst' => $newestmessagesfirst));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, 0 for current user
     * 
     */
    function lmscoreMessageGetConversationCounts($userid = 0)
    {

        $functionname = 'core_message_get_conversation_counts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user we are performing this action on behalf of
     * 
     * [conversationid] => int //The id of the conversation
     * 
     * [includecontactrequests] => int Varsayılan değer "" //Do we want to include contact requests?
     * 
     * [includeprivacyinfo] => int Varsayılan değer "" //Do we want to include privacy info?
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageGetConversationMembers($userid, $conversationid, $includecontactrequests, $includeprivacyinfo, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_get_conversation_members';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'conversationid' => $conversationid, 'includecontactrequests' => $includecontactrequests, 'includeprivacyinfo' => $includeprivacyinfo, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [currentuserid] => int //The current user's id
     * 
     * [convid] => int //The conversation id
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     * [newest] => int Varsayılan değer "" //Newest first?
     * 
     * [timefrom] => int Varsayılan değer "0" //The timestamp from which the messages were created
     * 
     */
    function lmscoreMessageGetConversationMessages($currentuserid, $convid, $limitfrom = 0, $limitnum = 0, $newest, $timefrom = 0)
    {

        $functionname = 'core_message_get_conversation_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('currentuserid' => $currentuserid, 'convid' => $convid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum, 'newest' => $newest, 'timefrom' => $timefrom));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who we are viewing conversations for
     * 
     * [limitfrom] => int Varsayılan değer "0" //The offset to start at
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number of conversations to this
     * 
     * [type] => int Varsayılan değer "null" //Filter by type
     * 
     * [favourites] => int Varsayılan değer "null" //Whether to restrict the results to contain NO favourite conversations (false), ONLY favourite conversation (true), or ignore any restriction altogether (null)
     * 
     */
    function lmscoreMessageGetConversations($userid, $limitfrom = 0, $limitnum = 0, $type, $favourites = null)
    {

        $functionname = 'core_message_get_conversations';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum, 'type' => $type, 'favourites' => $favourites));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [referenceuserid] => int //id of the user
     * 
     * [userids] => array(
     *                  [0] => int //id of members to get
     *              )
     * 
     * [includecontactrequests] => int Varsayılan değer "" //include contact requests in response
     * 
     * [includeprivacyinfo] => int Varsayılan değer "" //include privacy info in response
     * 
     */
    function lmscoreMessageGetMemberInfo($referenceuserid, $userids, $includecontactrequests, $includeprivacyinfo)
    {

        $functionname = 'core_message_get_member_info';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('referenceuserid' => $referenceuserid, 'userids' => $userids, 'includecontactrequests' => $includecontactrequests, 'includeprivacyinfo' => $includeprivacyinfo));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //id of the user, 0 for current user
     * 
     * [name] => string //The name of the message processor
     * 
     */
    function lmscoreMessageGetMessageProcessor($userid, $name)
    {

        $functionname = 'core_message_get_message_processor';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'name' => $name));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [useridto] => int //the user id who received the message, 0 for any user
     * 
     * [useridfrom] => int Varsayılan değer "0" //the user id who send the message, 0 for any user. -10 or -20 for no-reply or support user
     * 
     * [type] => string Varsayılan değer "both" //type of message to return, expected values are: notifications, conversations and both
     * 
     * [read] => int Varsayılan değer "1" //true for getting read messages, false for unread
     * 
     * [newestfirst] => int Varsayılan değer "1" //true for ordering by newest first, false for oldest first
     * 
     * [limitfrom] => int Varsayılan değer "0" //limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //limit number
     * 
     */
    function lmscoreMessageGetMessages($useridto, $useridfrom = 0, $type = "both", $read = 1, $newestfirst = 1, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_get_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('useridto' => $useridto, 'useridfrom' => $useridfrom, 'type' => $type, 'read' => $read, 'newestfirst' => $newestfirst, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user we want to return the number of received contact requests for
     * 
     */
    function lmscoreMessageGetReceivedContactRequestsCount($userid)
    {

        $functionname = 'core_message_get_received_contact_requests_count';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, 0 for current user
     * 
     */
    function lmscoreMessageGetUnreadConversationCounts($userid = 0)
    {

        $functionname = 'core_message_get_unread_conversation_counts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [useridto] => int //the user id who received the message, 0 for any user
     * 
     */
    function lmscoreMessageGetUnreadConversationsCount($useridto)
    {

        $functionname = 'core_message_get_unread_conversations_count';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('useridto' => $useridto));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who we retrieving the contacts for
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageGetUserContacts($userid, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_get_user_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, 0 for current user
     * 
     */
    function lmscoreMessageGetUserMessagePreferences($userid = 0)
    {

        $functionname = 'core_message_get_user_message_preferences';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, 0 for current user
     * 
     */
    function lmscoreMessageGetUserNotificationPreferences($userid = 0)
    {

        $functionname = 'core_message_get_user_notification_preferences';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user id who who we are marking the messages as read for
     * 
     * [conversationid] => int //The conversation id who who we are marking the messages as read for
     * 
     */
    function lmscoreMessageMarkAllConversationMessagesAsRead($userid, $conversationid)
    {

        $functionname = 'core_message_mark_all_conversation_messages_as_read';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'conversationid' => $conversationid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [useridto] => int //the user id who received the message, 0 for any user
     * 
     * [useridfrom] => int Varsayılan değer "0" //the user id who send the message, 0 for any user. -10 or -20 for no-reply or support user
     * 
     */
    function lmscoreMessageMarkAllMessagesAsRead($useridto, $useridfrom = 0)
    {

        $functionname = 'core_message_mark_all_messages_as_read';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('useridto' => $useridto, 'useridfrom' => $useridfrom));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [useridto] => int //the user id who received the message, 0 for any user
     * 
     * [useridfrom] => int Varsayılan değer "0" //the user id who send the message, 0 for any user. -10 or -20 for no-reply or support user
     * 
     */
    function lmscoreMessageMarkAllNotificationsAsRead($useridto, $useridfrom = 0)
    {

        $functionname = 'core_message_mark_all_notifications_as_read';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('useridto' => $useridto, 'useridfrom' => $useridfrom));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [messageid] => int //id of the message in the messages table
     * 
     * [timeread] => int Varsayılan değer "0" //timestamp for when the message should be marked read
     * 
     */
    function lmscoreMessageMarkMessageRead($messageid, $timeread = 0)
    {

        $functionname = 'core_message_mark_message_read';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('messageid' => $messageid, 'timeread' => $timeread));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [notificationid] => int //id of the notification
     * 
     * [timeread] => int Varsayılan değer "0" //timestamp for when the notification should be marked read
     * 
     */
    function lmscoreMessageMarkNotificationRead($notificationid, $timeread = 0)
    {

        $functionname = 'core_message_mark_notification_read';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('notificationid' => $notificationid, 'timeread' => $timeread));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //id of the user, 0 for current user
     * 
     * [name] => string //The name of the message processor
     * 
     * [formvalues] => array(
     *                  name   => string   //name of the form element
     *                  value  => string   //value of the form element
     *              )
     * 
     */
    function lmscoreMessageProcessorConfigForm($userid, $name, $formvalues)
    {

        $functionname = 'core_message_message_processor_config_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'name' => $name, 'formvalues' => array($formvalues)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who is performing the search
     * 
     * [search] => string //The string being searched
     * 
     * [limitfrom] => int Varsayılan değer "0" //Limit from
     * 
     * [limitnum] => int Varsayılan değer "0" //Limit number
     * 
     */
    function lmscoreMessageSearchUsers($userid, $search, $limitfrom = 0, $limitnum = 0)
    {

        $functionname = 'core_message_message_search_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'search' => $search, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [searchtext] => string //String the user's fullname has to match to be found
     * 
     * [onlymycourses] => int Varsayılan değer "" //Limit search to the user's courses
     * 
     */
    function lmscoreMessageSearchContacts($searchtext, $onlymycourses)
    {

        $functionname = 'core_message_search_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('searchtext' => $searchtext, 'onlymycourses' => $onlymycourses));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [messages] => array(
     *              touserid    => int   //id of the user to send the private message
     *              text        => string   //the text of the message
     *              textformat  => int  Varsayılan değer "0" //text format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *              clientmsgid => string  İsteğe bağlı //your own client id for the message. If this id is provided, the fail message id will be returned to you
     *          )
     * 
     */
    function lmscoreMessageSendInstantMessages($messages)
    {

        $functionname = 'core_message_send_instant_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('messages' => array($messages)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [conversationid] => int //id of the conversation
     * 
     * [messages] => array(
     *              text        => string   //the text of the message
     *              textformat  => int  Varsayılan değer "0" //text format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *          )
     * 
     */
    function lmscoreMessageSendMessagesToConversation($conversationid, $messages)
    {

        $functionname = 'core_message_send_messages_to_conversation';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('conversationid' => $conversationid, 'messages' => array($messages)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, 0 for current user
     * 
     * [conversations] => array(
     *                      [0] => int Varsayılan değer "0" //id of the conversation
     *                  )
     * 
     */
    function lmscoreMessageSetFavouriteConversations($userid = 0, $conversations)
    {

        $functionname = 'core_message_set_favourite_conversations';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'conversations' => $conversations));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userids] => array(
     *              [0] => user Id.
     *          )
     * 
     * [userid] => int Varsayılan değer "0" //The id of the user we are unblocking the contacts for, 0 for the current user
     * 
     */
    function lmscoreMessageUnblockContacts($userids, $userid = 0)
    {

        $functionname = 'core_message_unblock_contacts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userids' => $userids, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The id of the user who is unblocking
     * 
     * [unblockeduserid] => int //The id of the user being unblocked
     * 
     */
    function lmscoreMessageUnblockUser($userid, $unblockeduserid)
    {

        $functionname = 'core_message_unblock_user';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'unblockeduserid' => $unblockeduserid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int Varsayılan değer "0" //id of the user, 0 for current user
     * 
     * [conversations] => array(
     *                  [0] => int Varsayılan değer "0" //id of the conversation
     *              )
     * 
     */
    function lmscoreMessageUnsetFavouriteConversations($userid = 0, $conversations)
    {

        $functionname = 'core_message_unset_favourite_conversations';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'conversations' => $conversations));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [notes] => array(
     *               userid       => int   //id of the user the note is about
     *               publishstate => string   //'personal', 'course' or 'site'
     *               courseid     => int   //course id of the note (in Moodle a note can only be created into a course, even for site and personal notes)
     *               text         => string   //the text of the message - text or HTML
     *               format       => int  Varsayılan değer "1" //text format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *               clientnoteid => string  İsteğe bağlı //your own client id for the note. If this id is provided, the fail message id will be returned to you
     *      )
     * 
     */
    function lmscoreNotesCreateNotes($notes)
    {

        $functionname = 'core_notes_create_notes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('notes' => array($notes)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [notes] => array(
     * 
     *              [0] => int //ID of the note to be deleted
     *          )
     * 
     */
    function lmscoreNotesDeleteNotes($notes)
    {

        $functionname = 'core_notes_delete_notes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('notes' => $notes));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //course id, 0 for SITE
     * 
     * [userid] => int Varsayılan değer "0" //user id
     * 
     */
    function lmscoreNotesGetCourseNotes($courseid, $userid = 0)
    {

        $functionname = 'core_notes_get_course_notes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [notes] => array(
     *              [0] => int //ID of the note to be retrieved
     *          )
     * 
     */
    function lmscoreNotesGetNotes($notes)
    {

        $functionname = 'core_notes_get_notes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('notes' => $notes));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [notes] => array(
     *               id           => int   //id of the note
     *               publishstate => string   //'personal', 'course' or 'site'
     *               text         => string   //the text of the message - text or HTML
     *               format       => int  Varsayılan değer "1" //text format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     *          )
     * 
     */
    function lmscoreNotesUpdateNotes($notes)
    {

        $functionname = 'core_notes_update_notes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('notes' => array($notes)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //course id, 0 for notes at system level
     * 
     * [userid] => int Varsayılan değer "0" //user id, 0 means view all the user notes
     * 
     */
    function lmscoreNotesViewNotes($courseid, $userid = 0)
    {

        $functionname = 'core_notes_view_notes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /*
    function lmscoreOutputLoadFontawesomeIconMap()
    {

        $functionname = 'core_output_load_fontawesome_icon_map';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array());
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    */
    
    
    /**
     * [component] => string //component containing the template
     * 
     * [template] => string //name of the template
     * 
     * [themename] => string //The current theme.
     * 
     * [includecomments] => int Varsayılan değer "" //Include comments or not
     * 
     */
    function lmscoreOutputLoadTemplate($component, $template, $themename, $includecomments)
    {

        $functionname = 'core_output_load_template';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'template' => $template, 'themename' => $themename, 'includecomments' => $includecomments));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [categoryid] => int //Category id to find random questions
     * 
     * [includesubcategories] => int //Include the subcategories in the search
     * 
     * [tagids] => array(
     *              [0] => int //Tag id
     *          )
     * 
     * [contextid] => int //Context id that the questions will be rendered in (used for exporting)
     * 
     * [limit] => int Varsayılan değer "0" //Maximum number of results to return
     * 
     * [offset] => int Varsayılan değer "0" //Number of items to skip from the begging of the result set
     * 
     */
    function lmscoreQuestionGetRandomQuestionSummaries($categoryid, $includesubcategories, $tagids, $contextid, $limit = 0, $offset = 0)
    {

        $functionname = 'core_question_get_random_question_summaries';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('categoryid' => $categoryid, 'includesubcategories' => $includesubcategories, 'tagids' => $tagids, 'contextid' => $contextid, 'limit' => $limit, 'offset' => $offset));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [questionid] => int //The question id
     * 
     * [contextid] => int //The editing context id
     * 
     * [formdata] => string //The data from the tag form
     * 
     */
    function lmscoreQuestionSubmitTagsForm($questionid, $contextid, $formdata)
    {

        $functionname = 'core_question_submit_tags_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('questionid' => $questionid, 'contextid' => $contextid, 'formdata' => $formdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [qubaid] => int //the question usage id.
     * 
     * [questionid] => int //the question id
     * 
     * [qaid] => int //the question_attempt id
     * 
     * [slot] => int //the slot number within the usage
     * 
     * [checksum] => string //computed checksum with the last three arguments and the users username
     * 
     * [newstate] => int //the new state of the flag. true = flagged
     * 
     */
    function lmscoreQuestionUpdateFlag($qubaid, $questionid, $qaid, $slot, $checksum, $newstate)
    {

        $functionname = 'core_question_update_flag';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('qubaid' => $qubaid, 'questionid' => $questionid, 'qaid' => $qaid, 'slot' => $slot, 'checksum' => $checksum, 'newstate' => $newstate));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextlevel] => string //context level: course, module, user, etc...
     * 
     * [instanceid] => int //the instance id of item associated with the context level
     * 
     * [component] => string //component
     * 
     * [ratingarea] => string //rating area
     * 
     * [itemid] => int //associated id
     * 
     * [scaleid] => int //scale id
     * 
     * [rating] => int //user rating
     * 
     * [rateduserid] => int //rated user id
     * 
     * [aggregation] => int Varsayılan değer "0" //agreggation method
     * 
     */
    function lmscoreRatingAddRating($contextlevel, $instanceid, $component, $ratingarea, $itemid, $scaleid, $rating, $rateduserid, $aggregation = 0)
    {

        $functionname = 'core_rating_add_rating';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextlevel' => $contextlevel, 'instanceid' => $instanceid, 'component' => $component, 'ratingarea' => $ratingarea, 'itemid' => $itemid, 'scaleid' => $scaleid, 'rating' => $rating, 'rateduserid' => $rateduserid, 'aggregation' => $aggregation));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextlevel] => string //context level: course, module, user, etc...
     * 
     * [instanceid] => int //the instance id of item associated with the context level
     * 
     * [component] => string  //component
     * 
     * [ratingarea] => string //rating area
     * 
     * [itemid] => int //associated id
     * 
     * [scaleid] => int //scale id
     * 
     * [sort] => string //sort order (firstname, rating or timemodified)
     * 
     */
    function lmscoreRatingGetItemRatings($contextlevel, $instanceid, $component, $ratingarea, $itemid, $scaleid, $sort)
    {

        $functionname = 'core_rating_get_item_ratings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextlevel' => $contextlevel, 'instanceid' => $instanceid, 'component' => $component, 'ratingarea' => $ratingarea, 'itemid' => $itemid, 'scaleid' => $scaleid, 'sort' => $sort));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [assignments] => array(
     *               roleid       => int   //Role to assign to the user
     *               userid       => int   //The user that is going to be assigned
     *               contextid    => int  İsteğe bağlı //The context to assign the user role in
     *               contextlevel => string  İsteğe bağlı //The context level to assign the user role in (block, course, coursecat, system, user, module)
     *               instanceid   => int  İsteğe bağlı //The Instance id of item where the role needs to be assigned
     *          )
     * 
     */
    function lmscoreRoleAssignRoles($assignments)
    {

        $functionname = 'core_role_assign_roles';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignments' => array($assignments)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [unassignments] => array(
     *               roleid        => int   //Role to assign to the user
     *               userid        => int   //The user that is going to be assigned
     *               contextid     => int  İsteğe bağlı //The context to unassign the user role from
     *               contextlevel  => string  İsteğe bağlı //The context level to unassign the user role in + (block, course, coursecat, system, user, module)
     *               instanceid    => int  İsteğe bağlı //The Instance id of item where the role needs to be unassigned
     *          )
     * 
     */
    function lmscoreRoleUnassignRoles($unassignments)
    {

        $functionname = 'core_role_unassign_roles';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('unassignments' => array($unassignments)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [query] => string //Query string (full or partial user full name or other details)
     * 
     * [courseid] => int //Course id (0 if none)
     * 
     */
    function lmscoreSearchGetRelevantUsers($query, $courseid)
    {

        $functionname = 'core_search_get_relevant_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('query' => $query, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tagindex] => array(
     *               tag   => string   //tag name
     *               tc    => int   //tag collection id
     *               ta    => int   //tag area id
     *               excl  => int  İsteğe bağlı //exlusive mode for this tag area
     *               from  => int  İsteğe bağlı //context id where the link was displayed
     *               ctx   => int  İsteğe bağlı //context id where to search for items
     *               rec   => int  İsteğe bağlı //search in the context recursive
     *               page  => int  İsteğe bağlı //page number (0-based)
     * 
     *          )
     * 
     */
    function lmscoreTagGetTagindex($tagindex)
    {

        $functionname = 'core_tag_get_tagindex';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tagindex' => $tagindex));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tags] => array(
     *              id  => int   //tag id
     *          )
     * 
     */
    function lmscoreTagGetTags($tags)
    {

        $functionname = 'core_tag_get_tags';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tags' => array($tags)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tags] => array(
     *       id                => int   //tag id
     *       rawname           => string  İsteğe bağlı //tag raw name (may contain capital letters)
     *       description       => string  İsteğe bağlı //tag description
     *       descriptionformat => int  İsteğe bağlı //tag description format
     *       flag              => int  İsteğe bağlı //flag
     *       official          => int  İsteğe bağlı //(deprecated, use isstandard) whether this flag is standard
     *       isstandard        => int  İsteğe bağlı //whether this flag is standard
     *  )
     * 
     */
    function lmscoreTagUpdateTags($tags)
    {

        $functionname = 'core_tag_update_tags';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tags' => array($tags)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [component] => string //component responsible for the update
     * 
     * [itemtype] => string //type of the updated item inside the component
     * 
     * [itemid] => string //identifier of the updated item
     * 
     * [value] => string //new value
     * 
     */
    function lmscoreUpdateInplaceEditable($component, $itemtype, $itemid, $value)
    {

        $functionname = 'core_update_inplace_editable';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'itemtype' => $itemtype, 'itemid' => $itemid, 'value' => $value));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [appid] => string //the app id, usually something like com.moodle.moodlemobile
     * 
     * [name] => string //the device name, 'occam' or 'iPhone' etc.
     * 
     * [model] => string //the device model 'Nexus4' or 'iPad1,1' etc.
     * 
     * [platform] => string //the device platform 'iOS' or 'Android' etc.
     * 
     * [version] => string //the device version '6.1.2' or '4.2.2' etc.
     * 
     * [pushid] => string //the device PUSH token/key/identifier/registration id
     * 
     * [uuid] => string //the device UUID
     * 
     */
    function lmscoreUserAddUserDevice($appid, $name, $model, $platform, $version, $pushid, $uuid)
    {
        
        $functionname = 'core_user_add_user_device';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('appid' => $appid, 'name' => $name, 'model' => $model, 'platform' => $platform, 'version' => $version, 'pushid' => $pushid, 'uuid' => $uuid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    function lmscoreUserAddPrivateFiles($draftId)
    {

        $functionname = 'core_user_add_user_private_files';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('draftid' => $draftId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    function lmscoreUserCreateUsers($users)
    {

        $functionname = 'core_user_create_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('users' => array($users)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    function lmscoreUserDeleteUsers($userIds)
    {

        $functionname = 'core_user_delete_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userids' => $userIds));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    function lmscoreUserGetCourseUserProfiles($userList)
    {

        $functionname = 'core_user_get_course_user_profiles';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userlist' => $userList));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
    * 
    * $userId = array('userid' => 15);
    * 
    * coreUserGetPrivateFilesInfo($userId);
    */
    function lmscoreUserGetPrivateFilesInfo($userId)
    {

        $functionname = 'core_user_get_private_files_info';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, $userId);
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [name] => string //preference name, empty for all
     * 
     * [userid] => int
     */
    function lmscoreUserGetUserPreferences($name = "", $userId)
    {

        $functionname = 'core_user_get_user_preferences';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('name' => $name, 'userid' => $userId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * $criteria = array(
     *      'key'   => 'id',
     *      'value' => 1
     * );
     * 
     * $user = coreUserGetUsers($criteria);
     */
    function lmscoreUserGetUsers($criteria)
    {

        $functionname = 'core_user_get_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('criteria' => array($criteria)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [fields] //the search field can be 'id' or 'idnumber' or 'username' or 'email'
     * 
     * [values] //string
     * 
     * $fields = 'username';
     * 
     * $values = array('example');
     */
    function lmscoreUserGetUsersByField($fields, $values)
    {

        $functionname = 'core_user_get_users_by_field';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('fields' => $fields, 'values' => $values));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [uuid] => string
     * 
     * [appid] => string 
     * 
     */
    function lmscoreUserRemoveUserDevice($uuId, $appId)
    {

        $functionname = 'core_user_remove_user_device';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('uuid' => $uuId, 'appid' => $appId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * $preferences = array(
     *      'name'   => string, 
     *      'value'  => string,
     *      'userid' => int
     * ); 
     * 
     */
    function lmscoreUserSetUserPreferences($preferences)
    {

        $functionname = 'core_user_set_user_preferences';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('preferences' => array($preferences)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [draftitemid] => int
     * 
     * [delete] => int Varsayılan değer "" //If we should delete the user picture
     * 
     * [userid] => int Varsayılan değer "0" //Id of the user, 0 for current user
     * 
     */
    function lmscoreUserUpdatePicture($draftitemid, $delete = "", $userId)
    {

        $functionname = 'core_user_update_picture';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('draftitemid' => $draftitemid, 'delete' => $delete, 'userid' => $userId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [userid] => int
     * 
     * [emailstop] => int Varsayılan değer "null" //Enable or disable notifications for this user
     * 
     * [preferences] => array()
     * 
     * $preferences = array(
     *      'name'   => string, 
     *      'value'  => string,
     *      'userid' => int
     * ); 
     * 
     */
    function lmscoreUserUpdateUserPreferences($userId, $emailStop = null, $preferences)
    {

        $functionname = 'core_user_update_user_preferences';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userId, 'emailstop' => $emailStop, 'preferences' => array($preferences)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * $users = array(
     *      'id'  => int //ID of the user
     *      
     * );
     * 
     */
    function lmscoreUserUpdateUsers($users)
    {

        $functionname = 'core_user_update_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('users' => array($users)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [courseid] => int //id of the course, 0 for site
     * 
     */
    function lmscoreUserViewUserList($courseId)
    {

        $functionname = 'core_user_view_user_list';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //id of the user, 0 for current user
     * 
     * [courseid] => int Varsayılan değer "0" //id of the course, default site course
     * 
     */
    function lmscoreUserViewUserProfile($userId, $courseId)
    {

        $functionname = 'core_user_view_user_profile';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userId, 'courseid' => $courseId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [serviceshortnames] => string  //service shortname
     * 
     * 
     */
    function lmscoreWebserviceGetSiteInfo($servicesShortNames)
    {

        $functionname = 'core_webservice_get_site_info';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('serviceshortnames' => array($servicesShortNames)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [instanceid] => int  //Instance id of guest enrolment plugin.
     * 
     * 
     */
    function lmsenrolGuestGetInstanceInfo($instanceId)
    {

        $functionname = 'enrol_guest_get_instance_info';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('instanceid' => $instanceId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * $enrolments = array(
     * 
     *       roleid     => int   //Role to assign to the user
     *       userid     => int   //The user that is going to be enrolled
     *       courseid   => int   //The course to enrol the user role in
     *       timestart  => int  İsteğe bağlı //Timestamp when the enrolment start
     *       timeend    => int  İsteğe bağlı //Timestamp when the enrolment end
     *       suspend    => int  İsteğe bağlı //set to 1 to suspend the enrolment
     * 
     * );
     * 
     * 
     */
    function lmsenrolManuelEnrolUsers($enrolments)
    {

        $functionname = 'enrol_manual_enrol_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('enrolments' => array($enrolments)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * $enrolments = array(
     * 
     *       roleid     => int   İsteğe bağlı //The user role
     *       userid     => int   //The user that is going to be unenrolled
     *       courseid   => int   //The course to unenrol the user from
     * 
     * );
     * 
     * 
     */
    function lmsenrolManuelUnenrolUsers($enrolments)
    {

        $functionname = 'enrol_manual_unenrol_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('enrolments' => array($enrolments)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseid] => int //Id of the course
     * 
     * [password] => string  Varsayılan değer "" //Enrolment key
     * 
     * [instanceid] => int  Varsayılan değer "0" //Instance id of self enrolment plugin.
     * 
     */
    function lmsenrolSelfEnrolUser($courseId, $password = "", $instanceId = 0)
    {

        $functionname = 'enrol_self_enrol_user';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseId, 'password' => $password, 'instanceid' => $instanceId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [instanceid] => int //instance id of self enrolment plugin.
     * 
     */
    function lmsenrolSelfGetInstanceInfo($instanceId)
    {

        $functionname = 'enrol_self_get_instance_info';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('instanceid' => $instanceId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [userid] => int  Varsayılan değer "0" //Get grades for this user (optional, default current)
     * 
     */
    function lmsgradeReportGetCourseGrades($userId)
    {

        $functionname = 'gradereport_overview_get_course_grades';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //id of the course
     * 
     * [userid] => int Varsayılan değer "0" //id of the user, 0 means current user
     * 
     */
    function lmsgradeReportViewGradeReport($courseId, $userId = 0)
    {

        $functionname = 'gradereport_overview_view_grade_report';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseId, 'userid' => $userId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course Id
     * 
     * [userid]   => int Varsayılan değer "0" //Return grades only for this user (optional)
     * 
     * [groupid]  => int Varsayılan değer "0" //Get users from this group only
     * 
     */
    function lmsgradeReportUserGetGradeItems($courseId, $userId = 0, $groupId = 0)
    {

        $functionname = 'gradereport_user_get_grade_items';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseId, 'userid' => $userId, 'groupid' => $groupId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //Course Id
     * 
     * [userid]   => int Varsayılan değer "0" //Return grades only for this user (optional)
     * 
     * [groupid]  => int Varsayılan değer "0" //Get users from this group only
     * 
     */
    function lmsgradeReportUserGetGradesTable($courseId, $userId = 0, $groupId = 0)
    {

        $functionname = 'gradereport_user_get_grades_table';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseId, 'userid' => $userId, 'groupid' => $groupId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //id of the course
     * 
     * [userid]   => int Varsayılan değer "0" //id of the user, 0 means current user
     * 
     */
    function lmsgradeReportUserViewGradeReport($courseId, $userId = 0)
    {

        $functionname = 'gradereport_user_view_grade_report';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseId, 'userid' => $userId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [userids]   => int //user ID
     * 
     * $userIds = array(1);
     * 
     */
    function lmsnotificationPreferencesConfigured($userIds)
    {

        $functionname = 'message_airnotifier_are_notification_preferences_configured';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userids' => $userIds));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [deviceid]   => int //The device id
     * 
     * [enable]     => int //True for enable the device, false otherwise
     * 
     */
    function lmsmessageAirnotifierEnableDevice($deviceId, $enable)
    {

        $functionname = 'message_airnotifier_enable_device';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('deviceid' => $deviceId, 'enable' => $enable));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [appid]   => string //App unique id (usually a reversed domain)
     * 
     * [userid]  => int Varsayılan değer "0" //User id, 0 for current user
     * 
     */
    function lmsmessageAirnotifierGetUserDevices($appId, $userId = 0)
    {

        $functionname = 'message_airnotifier_get_user_devices';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('appid' => $appId, 'userid' => $userId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [useridto]   => int //the user id who received the message, 0 for current user
     * 
     * [newestfirst]  => int Varsayılan değer "1" //true for ordering by newest first, false for oldest first
     * 
     * [limit] => int Varsayılan değer "0" //the number of results to return
     * 
     * [offset] => int Varsayılan değer "0" //offset the result set by a given amount
     * 
     */
    function lmsmessageGetPopupNotifications($userIdTo, $newestFirst = 1, $limit = 0, $offset = 0)
    {

        $functionname = 'message_popup_get_popup_notifications';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('useridto' => $userIdTo, 'newestfirst' => $newestFirst, 'limit' => $limit, 'offset' => $offset));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [useridto]   => int //the user id who received the message, 0 for any user
     *
     */
    function lmsmessageGetUnreadPopupNotificationsCount($userIdTo)
    {

        $functionname = 'message_popup_get_unread_popup_notification_count';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('useridto' => $userIdTo));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     *
     */
    function lmsmodAssignCopyPreviosAttempt($assignmentId)
    {

        $functionname = 'mod_assign_copy_previous_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentId));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [courseids]   => int //course id, empty for retrieving all the courses where the user is enroled in
     * 
     * [capabilities] => string //list of capabilities used to filter courses
     * 
     * [includenotenrolledcourses] => int Varsayılan değer "" //whether to return courses that the user can see even if is not enroled in. This requires the parameter courseids to not be empty.
     *
     */
    function lmsmodAssignGetAssignments($courseIds, $capabilities, $includenotenrolledcourses = "")
    {

        $functionname = 'mod_assign_get_assignments';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseIds), 'capabilities' => array($capabilities), 'includenotenrolledcourses' => $includenotenrolledcourses));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [courseids]   => int //1 or more assignment ids
     * 
     * [since] => int Varsayılan değer "0" //timestamp, only return records where timemodified >= since
     *
     */
    function lmsmodAssignGetGrades($assignmentids, $since = 0)
    {

        $functionname = 'mod_assign_get_grades';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentids' => array($assignmentids), 'since' => $since));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignid]   => int //assign instance id
     * 
     * [userid] => int //user id
     * 
     * [embeduser] => int Varsayılan değer "" //user id
     *
     */
    function lmsmodAssignGetParticipant($assignid, $userid, $embeduser = "")
    {

        $functionname = 'mod_assign_get_participant';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignid' => $assignid, 'userid' => $userid, 'embeduser' => $embeduser));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [assignid]   => int //assignment instance id
     * 
     * [userid] => int Varsayılan değer "0" //user id (empty for current user)
     * 
     * [groupid] => int Varsayılan değer "0" //filter by users in group (used for generating the grading summary). Empty or 0 for all groups information.
     *
     */
    function lmsmodAssignGetSubmissionStatus($assignid, $userid = 0, $groupid = 0)
    {

        $functionname = 'mod_assign_get_submission_status';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignid' => $assignid, 'userid' => $userid, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentids]   => int //1 or more assignment ids
     * 
     * [status] => string  Varsayılan değer "" //status
     * 
     * [since] => int  Varsayılan değer "0" //submitted since
     * 
     * [before] => int  Varsayılan değer "0" //submitted before
     */
    function lmsmodAssignGetSubmissions($assignmentids, $status = "", $since = 0, $before = 0)
    {

        $functionname = 'mod_assign_get_submissions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentids' => array($assignmentids), 'status' => $status, 'since' => $since, 'before' => $before));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [assignmentids]   => int //1 or more assignment ids
     * 
     */
    function lmsmodAssignGetUserFlags($assignmentids)
    {

        $functionname = 'mod_assign_get_user_flags';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentids' => array($assignmentids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentids]   => int //1 or more assignment ids
     * 
     */
    function lmsmodAssignGetUserMappings($assignmentids)
    {

        $functionname = 'mod_assign_get_user_mappings';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentids' => array($assignmentids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [assignid] => int //assign instance id
     * 
     * [groupid] => int //group id
     * 
     * [filter] => string //search string to filter the results
     * 
     * [skip] => int Varsayılan değer "0" //number of records to skip
     * 
     * [limit] => int Varsayılan değer "0" //maximum number of records to return
     * 
     * [onlyids] => int Varsayılan değer "" //Do not return all user fields
     * 
     * [includeenrolments] => int Varsayılan değer "1" //Do return courses where the user is enrolled
     * 
     */
    function lmsmodAssignListParticipants($assignid, $groupid, $filter, $skip = 0, $limit = 0, $onlyids, $includeenrolments = 1)    
    {

        $functionname = 'mod_assign_list_participants';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignid' => $assignid, 'groupid' => $groupid, 'filter' => $filter, 'skip' => $skip, 'limit' => $limit, 'onlyids' => $onlyids, 'includeenrolments' => $includeenrolments));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
     
     
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [userids] => int //1 or more user ids
     * 
     */
    function lmsmodAssignLockSubmissions($assignmentid, $userids)
    {

        $functionname = 'mod_assign_lock_submissions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'userids' => array($userids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * 
     */
    function lmsmodAssignRevealIdentities($assignmentid)
    {

        $functionname = 'mod_assign_reveal_identities';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [userids] => int //1 or more user ids
     * 
     */
    function lmsmodAssignRevertSubmissionToDraft($assignmentid, $userids)
    {

        $functionname = 'mod_assign_revert_submissions_to_draft';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'userids' => array($userids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [userids] => int //1 or more user ids
     * 
     * [dates] => int //1 or more extension dates (timestamp)
     * 
     */
    function lmsmodAssignSaveUserExtensions($assignmentid, $userids, $dates)
    {

        $functionname = 'mod_assign_save_user_extensions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'userids' => array($userids), 'dates' => array($dates)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [userflags] => array(
     * 
     *       userid             => int   //student id
     *       locked             => int  İsteğe bağlı //locked
     *       mailed             => int  İsteğe bağlı //mailed
     *       extensionduedate   => int  İsteğe bağlı //extension due date
     *       workflowstate      => string  İsteğe bağlı //marking workflow state
     *       allocatedmarker    => int  İsteğe bağlı //allocated marker
     * 
     * )
     * 
     */
    function lmsmodAssignSetUserFlags($assignmentid, $userFlags)
    {

        $functionname = 'mod_assign_set_user_flags';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'userflags' => array($userFlags)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [acceptsubmissionstatement] => int //Accept the assignment submission statement
     * 
     */
    function lmsmodAssignSubmitForGrading($assignmentid, $acceptsubmissionstatement)
    {

        $functionname = 'mod_assign_submit_for_grading';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'acceptsubmissionstatement' => $acceptsubmissionstatement));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [userid] => int //The user id the submission belongs to
     * 
     * [jsonformdata] => string //The data from the grading form, encoded as a json array
     * 
     */
    function lmsmodAssignSubmitForGradingForm($assignmentid, $userid, $jsonformdata)
    {

        $functionname = 'mod_assign_submit_grading_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'userid' => $userid, 'jsonformdata' => $jsonformdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignmentid]   => int //The assignment id to operate on
     * 
     * [userids] => int //1 or more user ids
     * 
     * 
     */
    function lmsmodAssignUnlockSubmissions($assignmentid, $userids)
    {

        $functionname = 'mod_assign_unlock_submissions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignmentid' => $assignmentid, 'userids' => array($userids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignid]   => int //assign instance id
     *
     */
    function lmsmodAssignViewAssign($assignid)
    {

        $functionname = 'mod_assign_view_assign';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignid' => $assignid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignid]   => int //assign instance id
     *
     */
    function lmsmodAssignViewGradingTable($assignid)
    {

        $functionname = 'mod_assign_view_grading_table';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignid' => $assignid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [assignid]   => int //assign instance id
     *
     */
    function lmsmodAssignViewSubmissionStatus($assignid)
    {

        $functionname = 'mod_assign_view_submission_status';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assignid' => $assignid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [courseids]   => int //course id
     *
     */
    function lmsmodBookGetBookCourses($courseids)
    {

        $functionname = 'mod_book_get_books_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * 
     * [bookid]   => int //book instance id
     * 
     * [chapterid] => int Varsayılan değer "0" //chapter id
     *
     */
    function lmsmodBookViewBook($bookid, $chapterid = 0)
    {

        $functionname = 'mod_book_view_book';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('bookid' => $bookid, 'chapterid' => $chapterid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [chatsid]   => string //chat session id (obtained via mod_chat_login_user)
     * 
     * [chatlasttime] => int Varsayılan değer "0" //last time messages were retrieved (epoch time)
     *
     */
    function lmsmodChatGetChatLatestMessages($chatsid, $chatlasttime = 0)
    {

        $functionname = 'mod_chat_get_chat_latest_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatsid' => $chatsid, 'chatlasttime' => $chatlasttime));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [chatsid]   => string //chat session id (obtained via mod_chat_login_user)
     *
     */
    function lmsmodChatGetChatUsers($chatsid)
    {

        $functionname = 'mod_chat_get_chat_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatsid' => $chatsid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * 
     * [courseids]   => int //Array of course ids
     *
     */
    function lmsmodChatGetChatCourses($courseids)
    {

        $functionname = 'mod_chat_get_chats_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [chatid] => int //Chat instance id.
     * 
     * [sessionstart] => int //The session start time (timestamp).
     * 
     * [sessionend] => int //The session end time (timestamp).
     * 
     * [groupid] => int Varsayılan değer "0" //Get messages from users in this group. (0 means that the function lmswill determine the user group)
     *
     */
    function lmsmodChatGetSessionMessages($chatid, $sessionstart, $sessionend, $groupid = 0)
    {

        $functionname = 'mod_chat_get_session_messages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatid' => $chatid, 'sessionstart' => $sessionstart, 'sessionend' => $sessionend, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [chatid] => int //Chat instance id.
     * 
     * [groupid] => int Varsayılan değer "0" //Get messages from users in this group. (0 means that the function lmswill determine the user group)
     * 
     * [showall] => int Varsayılan değer "" //Whether to show completed sessions or not.
     *
     */
    function lmsmodChatGetSession($chatid, $groupid = 0, $showall)
    {

        $functionname = 'mod_chat_get_sessions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatid' => $chatid, 'groupid' => $groupid, 'showall' => $showall));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [chatid] => int //Chat instance id.
     * 
     * [groupid] => int Varsayılan değer "0" //group id, 0 means that the function lmswill determine the user group
     *
     */
    function lmsmodChatLoginUser($chatid, $groupid = 0)
    {

        $functionname = 'mod_chat_login_user';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatid' => $chatid, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [chatsid] => string //chat session id (obtained via mod_chat_login_user)
     * 
     * [messagetext] => string //the message text
     * 
     * [beepid] => string Varsayılan değer "" //the beep id
     */
    function lmsmodChatSendChatMessage($chatsid, $messagetext, $beepid = "")
    {

        $functionname = 'mod_chat_send_chat_message';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatsid' => $chatsid, 'messagetext' => $messagetext, 'beepid' => $beepid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [chatid] => int //chat instance id
     * 
     */
    function lmsmodChatViewChat($chatid)
    {

        $functionname = 'mod_chat_view_chat';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('chatid' => $chatid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [choiceid] => int //choice instance id
     * 
     * [responses] => int //Array of response ids, empty for deleting all the current user responses.
     */
    function lmsmodChoiceDeleteChoiceResponses($choiceid, $responses)
    {

        $functionname = 'mod_choice_delete_choice_responses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('choiceid' => $choiceid, 'responses' => array($responses)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [choiceid] => int //choice instance id
     * 
     */
    function lmsmodChoiceGetChoiceOptions($choiceid)
    {

        $functionname = 'mod_choice_get_choice_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('choiceid' => $choiceid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [choiceid] => int //choice instance id
     * 
     */
    function lmsmodChoiceGetChoiceResults($choiceid)
    {

        $functionname = 'mod_choice_get_choice_results';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('choiceid' => $choiceid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //Array of course ids
     * 
     */
    function lmsmodChoiceGetChoicesCourses($courseids)
    {

        $functionname = 'mod_choice_get_choices_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [choiceid] => int //choice instance id
     * 
     * [responses] => int //Array of response ids
     */
    function lmsmodChoiceSubmitChoiceResponse($choiceid, $responses)
    {

        $functionname = 'mod_choice_submit_choice_response';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('choiceid' => $choiceid, 'responses' => array($responses)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [choiceid] => int //choice instance id
     * 
     */
    function lmsmodChoiceViewChoice($choiceid)
    {

        $functionname = 'mod_choice_view_choice';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('choiceid' => $choiceid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [databaseid] => int //data instance id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group
     * 
     * [data] => array(
     * 
     *      'fieldid'  => int 
     *      'subfield' => string
     *      'value'    => string   
     * )
     * 
     */
    function lmsmodDataAddEntry($databaseid, $groupid = 0, $data)
    {

        $functionname = 'mod_data_add_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('databaseid' => $databaseid, 'groupid' => $groupid, 'data' => array($data)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [entryid] => int //Record entry id.
     * 
     * [approve] => int Varsayılan değer "1" //Whether to approve (true) or unapprove the entry.
     * 
     */
    function lmsmodDataApproveEntry($entryid, $approve = 1)
    {

        $functionname = 'mod_data_approve_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('entryid' => $entryid, 'approve' => $approve));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [entryid] => int //Record entry id.
     * 
     */
    function lmsmodDataDeleteEntry($entryid)
    {

        $functionname = 'mod_data_delete_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('entryid' => $entryid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [databaseid] => int //Database instance id.
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group.
     * 
     */
    function lmsmodDataGetDataAccessInformation($databaseid, $groupid = 0)
    {

        $functionname = 'mod_data_get_data_access_information';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('databaseid' => $databaseid, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseids] => int //Array of course ids
     * 
     */
    function lmsmodDataGetDatabaseCourses($courseids)
    {

        $functionname = 'mod_data_get_databases_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [databaseid] => int //data instance id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group
     * 
     * [returncontents] => int Varsayılan değer "" //Whether to return contents or not. This will return each entry raw contents and the complete list view (using the template).
     * 
     * [sort] => int Varsayılan değer "null" //Sort the records by this field id, reserved ids are:
     *                                       
     *                                       0: timeadded
     *                                      -1: firstname
     *                                      -2: lastname
     *                                      -3: approved
     *                                      -4: timemodified
     *                                      Empty for using the default database setting.
     * 
     * [order] => string Varsayılan değer "null" //The direction of the sorting: 'ASC' or 'DESC'. Empty for using the default database setting.
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int Varsayılan değer "0" //The number of records to return per page
     * 
     */
    function lmsmodDataGetEnteries($databaseid, $groupid = 0, $returncontents, $sort = null, $order = "", $page = 0, $perpage = 0)
    {

        $functionname = 'mod_data_get_entries';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('databaseid' => $databaseid, 'groupid' => $groupid, 'returncontents' => $returncontents, 'sort' => $sort, 'order' => $order, 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [entryid] => int //record entry id
     * 
     * [returncontents] => int Varsayılan değer "" //Whether to return contents or not.
     * 
     */
    function lmsmodDataGetEntry($entryid, $returncontents)
    {

        $functionname = 'mod_data_get_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('entryid' => $entryid, 'returncontents' => $returncontents));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [databaseid] => int //Database instance id
     * 
     */
    function lmsmodDataGetFields($databaseid)
    {

        $functionname = 'mod_data_get_fields';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('databaseid' => $databaseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [databaseid] => int //data instance id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group
     * 
     * [returncontents] => int Varsayılan değer "" //Whether to return contents or not. This will return each entry raw contents and the complete list view (using the template).
     * 
     * [search] => string Varsayılan değer "" //search string (empty when using advanced).
     * 
     * [advsearch] => //Advanced search array(
     * 
     *                                      [name]  => string
     *                                      [value] => string
     *                                  );
     * 
     * [sort] => int Varsayılan değer "null" //Sort the records by this field id, reserved ids are:
     *                                       
     *                                       0: timeadded
     *                                      -1: firstname
     *                                      -2: lastname
     *                                      -3: approved
     *                                      -4: timemodified
     *                                      Empty for using the default database setting.
     * 
     * [order] => string Varsayılan değer "null" //The direction of the sorting: 'ASC' or 'DESC'. Empty for using the default database setting.
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int Varsayılan değer "0" //The number of records to return per page
     * 
     */
    function lmsmodDataSearchEntries($databaseid, $groupid = 0, $returncontents, $search, $advsearch, $sort = null, $order = "", $page = 0, $perpage = 0)
    {

        $functionname = 'mod_data_search_entries';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('databaseid' => $databaseid, 'groupid' => $groupid, 'returncontents' => $returncontents, 'search' => $search, 'advsearch' => array($advsearch), 'sort' => $sort, 'order' => $order, 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [entryid] => int //The entry record id.
     * 
     * [data] => array(
     *              
     *              [fieldid]  => int
     *              [subfield] => string
     *              [value]    => string
     *          )
     * 
     */
    function lmsmodDataUpdateEntry($entryid, $data)
    {

        $functionname = 'mod_data_update_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('entryid' => $entryid, 'data' => array(array($data))));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [databaseid] => int //data instance id
     * 
     * 
     */
    function lmsmodDataViewDatabase($databaseid)
    {

        $functionname = 'mod_data_view_database';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('databaseid' => $databaseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [databaseid] => int //Feedback instance id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetAnalysis($feedbackid, $groupid = 0, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_analysis';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'groupid' => $groupid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetCurrentCompletedTmp($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_current_completed_tmp';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetFeedbackAccessInformation($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_feedback_access_information';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //Course id
     * 
     */
    function lmsmodFeedbackGetFeedbacksCourses($courseids)
    {

        $functionname = 'mod_feedback_get_feedbacks_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetFinishedResponses($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_finished_responses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetItems($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_items';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetLastCompleted($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_last_completed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group.
     * 
     * [sort] => string Varsayılan değer "lastaccess" //Sort param, must be firstname, lastname or lastaccess (default).
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int  Varsayılan değer "0" //The number of records to return per page.
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetNonRespondents($feedbackid, $groupid = 0, $sort = "lastaccess", $page = 0, $perpage = 0, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_non_respondents';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'groupid' => $groupid, 'sort' => $sort, 'page' => $page, 'perpage' => $perpage, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [page] => int //The page to get starting by 0
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetPageItems($feedbackid, $page, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_page_items';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'page' => $page, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group.
     * 
     * [sort] => string Varsayılan değer "lastaccess" //Sort param, must be firstname, lastname or lastaccess (default).
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int  Varsayılan değer "0" //The number of records to return per page.
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetResponsesAnalysis($feedbackid, $groupid = 0, $page = 0, $perpage = 0, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_responses_analysis';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'groupid' => $groupid, 'page' => $page, 'perpage' => $perpage, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackGetUnfinishedResponses($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_get_unfinished_responses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackLaunchFeedback($feedbackid, $courseid = 0)
    {

        $functionname = 'mod_feedback_launch_feedback';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [page] => int //The page being processed.
     * 
     * [responses] => array(
     *                  
     *                  [name]  => string
     *                  [value] => string
     *                  
     *              );
     * 
     * [goprevios] => int Varsayılan değer "" //Whether we want to jump to previous page.
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackProcessPage($feedbackid, $page, $responses, $goprevious, $courseid = 0)
    {

        $functionname = 'mod_feedback_process_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'page' => $page, 'responses' => array($responses), 'goprevious' => $goprevious, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [feedbackid] => int //Feedback instance id
     * 
     * [moduleviewed] => int Varsayılan değer "" //If we need to mark the module as viewed for completion
     * 
     * [courseid] => int Varsayılan değer "0" //Course where user completes the feedback (for site feedbacks only).
     */
    function lmsmodFeedbackViewFeedback($feedbackid, $moduleviewed, $courseid = 0)
    {

        $functionname = 'mod_feedback_view_feedback';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('feedbackid' => $feedbackid, 'moduleviewed' => $moduleviewed, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseids] => int //Course id
     * 
     */
    function lmsmodFolderGetFoldersCourses($courseids)
    {

        $functionname = 'mod_folder_get_folders_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [folderid] => int //folder instance id
     * 
     */
    function lmsmodFolderViewFolder($folderid)
    {

        $functionname = 'mod_folder_view_folder';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('folderid' => $folderid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [forumid] => int //Forum instance ID
     * 
     * [subject] => string //New Discussion subject
     * 
     * [message] => string //New Discussion message (only html format allowed)
     * 
     * [groupid] => Varsayılan değer "0" //The group, default to 0
     * 
     * [options] => array(
     *                  
     *              'name'  => string //The allowed keys (value format) are:
     *                                  discussionsubscribe (bool); subscribe to the discussion?, default to true
     *                                  discussionpinned    (bool); is the discussion pinned, default to false
     *                                  inlineattachmentsid (int); the draft file area id for inline attachments
     *                                  attachmentsid       (int); the draft file area id for attachments
     * 
     *              'value' => string //The value of the option, This param is validated in the external function.
     *      
     *      );
     * 
     */
    function lmsmodForumAddDiscussion($forumid, $subject, $message, $groupid = 0, $options)
    {

        $functionname = 'mod_forum_add_discussion';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('forumid' => $forumid, 'subject' => $subject, 'message' => $message, 'groupid' => $groupid, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [postid] => int //the post id we are going to reply to (can be the initial discussion post)
     * 
     * [subject] => string //New Discussion subject
     * 
     * [message] => string //New Discussion message (only html format allowed)
     * 
     * [options] => array(
     *                  
     *              'name'  => string //The allowed keys (value format) are:
     *                                  discussionsubscribe (bool); subscribe to the discussion?, default to true
     *                                  discussionpinned    (bool); is the discussion pinned, default to false
     *                                  inlineattachmentsid (int); the draft file area id for inline attachments
     *                                  attachmentsid       (int); the draft file area id for attachments
     * 
     *              'value' => string //The value of the option, This param is validated in the external function.
     *      
     *      );
     * 
     */
    function lmsmodForumAddDiscussionPost($postid, $subject, $message, $options)
    {

        $functionname = 'mod_forum_add_discussion_post';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('postid' => $postid, 'subject' => $subject, 'message' => $message, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [forumid] => int //Forum instance ID
     * 
     * [groupid] => int Varsayılan değer "null" //The group to check, default to active group. Use -1 to check if the user can post in all the groups.
     */
    function lmsmodForumCanAddDiscussion($forumid, $groupid)
    {

        $functionname = 'mod_forum_can_add_discussion';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('forumid' => $forumid, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [discussionid] => int //discussion ID
     * 
     * [sortby] => string Varsayılan değer "created" //sort by this element: id, created or modified
     * 
     * [sortdirection] => string Varsayılan değer "DESC" //sort direction: ASC or DESC
     * 
     */
    function lmsmodForumGetDiscussionPosts($discussionid, $sortby = "created", $sortdirection = "DESC")
    {

        $functionname = 'mod_forum_get_forum_discussion_posts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('discussionid' => $discussionid, 'sortby' => $sortby, 'sortdirection' => $sortdirection));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [forumid] => int //forum instance id
     * 
     * [sortby] => Varsayılan değer "timemodified" //sort by this element: id, timemodified, timestart or timeend
     * 
     * [sortdirection] => string Varsayılan değer "DESC" //sort direction: ASC or DESC
     * 
     * [page] => int Varsayılan değer "-1" //current page
     * 
     * [perpage] => int Varsayılan değer "0" //items per page
     * 
     */
    function lmsmodForumGetDiscussionsPaginated($forumid, $sortby = "timemodified", $sortdirection = "DESC", $page = -1, $perpage = 0)
    {

        $functionname = 'mod_forum_get_forum_discussions_paginated';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('forumid' => $forumid, 'sortby' => $sortby, 'sortdirection' => $sortdirection, 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course ID
     * 
     */
    function lmsmodForumGetForumCourses($courseids)
    {

        $functionname = 'mod_forum_get_forums_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [forumid] => int //forum instance id
     * 
     */
    function lmsmodForumViewForum($forumid)
    {

        $functionname = 'mod_forum_view_forum';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('forumid' => $forumid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [discussionid] => int //discussion id
     * 
     */
    function lmsmodForumViewForumDiscussion($discussionid)
    {

        $functionname = 'mod_forum_view_forum_discussion';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('discussionid' => $discussionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [glossaryid] => int //Glossary id
     * 
     * [concept] => string //Glossary concept
     * 
     * [definition] => string //Glossary concept definition
     * 
     * [definitionformat] => int //definition format (1 = HTML, 0 = MOODLE, 2 = PLAIN or 4 = MARKDOWN)
     * 
     * [options] => array(
     *      
     *      'name' => string //The allowed keys (value format) are:
     * 
     *                          inlineattachmentsid (int); the draft file area id for inline attachments
     *                          attachmentsid (int); the draft file area id for attachments
     *                          categories (comma separated int); comma separated category ids
     *                          aliases (comma separated str); comma separated aliases
     *                          usedynalink (bool); whether the entry should be automatically linked.
     *                          casesensitive (bool); whether the entry is case sensitive.
     *                          fullmatch (bool); whether to match whole words only.
     * 
     *      'value' => string //the value of the option (validated inside the function)
     *  )
     * 
     */
    function lmsmodGlossaryAddEntry($glossaryid, $concept, $definition, $definitionformat, $options)
    {

        $functionname = 'mod_glossary_add_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('glossaryid' => $glossaryid, 'concept' => $concept, 'definition' => $definition, 'definitionformat' => $definitionformat, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan de��er "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes self even if all of their entries require approval. When true, also includes authors only having entries pending approval. 
     *  )
     * 
     */
    function lmsmodGlossaryGetAuthors($id, $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_authors';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The glossary ID
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes self even if all of their entries require approval. When true, also includes authors only having entries pending approval. 
     *  )
     * 
     */
    function lmsmodGlossaryGetCategories($id, $from = 0, $limit = 20)
    {

        $functionname = 'mod_glossary_get_categories';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'from' => $from, 'limit' => $limit));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [letter] => string //First letter of firstname or lastname, or either keywords: 'ALL' or 'SPECIAL'.
     * 
     * [field] => string Varsayılan değer "LASTNAME" //Search and order using: 'FIRSTNAME' or 'LASTNAME'
     * 
     * [sort] => string Varsayılan değer "ASC" //The direction of the order: 'ASC' or 'DESC'
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes self even if all of their entries require approval. When true, also includes authors only having entries pending approval. 
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesAuthor($id, $letter, $field = "LASTNAME", $sort = "ASC", $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_author';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'letter' => $letter, 'field' => $field, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [authorid] => int //The author ID
     * 
     * [order] => string Varsayılan değer "CONCEPT" //Order by: 'CONCEPT', 'CREATION' or 'UPDATE'
     * 
     * [sort] => string Varsayılan değer "ASC" //The direction of the order: 'ASC' or 'DESC'
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes self even if all of their entries require approval. When true, also includes authors only having entries pending approval. 
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesAuthorId($id, $authorid, $order = "CONCEPT", $sort = "ASC", $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_author_id';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'authorid' => $authorid, 'order' => $order, 'sort' => $sort, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [categoryid] => int //The category ID. Use '0' for all categories, or '-1' for uncategorised entries.
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes self even if all of their entries require approval. When true, also includes authors only having entries pending approval. 
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesCategory($id, $categoryid, $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_category';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'categoryid' => $categoryid, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [order] => string Varsayılan değer "UPDATE" //Order the records by: 'CREATION' or 'UPDATE'.
     * 
     * [sort] => string Varsayılan değer "ASC" //The direction of the order: 'ASC' or 'DESC'
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes self even if all of their entries require approval. When true, also includes authors only having entries pending approval. 
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesDate($id, $order = "UPDATE", $sort = "DESC", $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_date';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'order' => $order, 'sort' => $sort, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [letter] => string //A letter, or either keywords: 'ALL' or 'SPECIAL'.
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes the non-approved entries created by the user. When true, also includes the ones that the user has the permission to approve.
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesLetter($id, $letter, $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_letter';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'letter' => $letter, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [query] => string //The query string
     * 
     * [fullsearch] => int Varsayılan değer "1" //The query
     * 
     * [order] => string Varsayılan değer "CONCEPT" //Order by: 'CONCEPT', 'CREATION' or 'UPDATE'
     * 
     * [sort] => string Varsayılan değer "ASC" //The direction of the order: 'ASC' or 'DESC'
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes the non-approved entries created by the user. When true, also includes the ones that the user has the permission to approve.
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesSearch($id, $query, $fullsearch = 1, $order = "CONCEPT", $sort = "ASC", $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_search';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'query' => $query, 'fullsearch' => $fullsearch, 'order' => $order, 'sort' => $sort, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [term] => string //The entry concept, or alias
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array(
     *      
     *      'includenotapproved' => int Varsayılan değer "0" //When false, includes the non-approved entries created by the user. When true, also includes the ones that the user has the permission to approve.
     *  )
     * 
     */
    function lmsmodGlossaryGetEnteriesTerm($id, $term, $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_by_term';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'term' => $term, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [letter] => string //A letter, or either keywords: 'ALL' or 'SPECIAL'.
     * 
     * [order] => string Varsayılan değer "CONCEPT" //Order by: 'CONCEPT', 'CREATION' or 'UPDATE'
     * 
     * [sort] => Varsayılan değer "ASC" //The direction of the order: 'ASC' or 'DESC'
     * 
     * [from] => int Varsayılan değer "0" //Start returning records from here
     * 
     * [limit] => int  Varsayılan değer "20" //Number of records to return
     * 
     * [options] => array() Varsayılan değer "Array" 
     * 
     */
    function lmsmodGlossaryGetEnteriesToApprove($id, $letter, $order = "CONCEPT", $sort = "ASC", $from = 0, $limit = 20, $options)
    {

        $functionname = 'mod_glossary_get_entries_to_approve';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'letter' => $letter, 'order' => $order, 'sort' => $sort, 'from' => $from, 'limit' => $limit, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     */
    function lmsmodGlossaryGetEntryId($id)
    {

        $functionname = 'mod_glossary_get_entry_by_id';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //Course id
     * 
     */
    function lmsmodGlossaryGetGlossariesCourses($courseids)
    {

        $functionname = 'mod_glossary_get_glossaries_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     */
    function lmsmodGlossaryViewEntry($id)
    {

        $functionname = 'mod_glossary_view_entry';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Glossary entry ID
     * 
     * [mode] => //The mode in which the glossary is viewed
     * 
     */
    function lmsmodGlossaryViewGlossary($id, $mode)
    {

        $functionname = 'mod_glossary_view_glossary';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'mode' => $mode));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseids] => int //Course id
     * 
     */
    function lmsmodImscpGetImscpCourses($courseids)
    {

        $functionname = 'mod_imscp_get_imscps_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [imscpid] => int //imscp instance id
     * 
     */
    function lmsmodImscpViewImscp($imscpid)
    {

        $functionname = 'mod_imscp_view_imscp';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('imscpid' => $imscpid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //Course id
     * 
     */
    function lmsmodLabelGetLabelsCourses($courseids)
    {

        $functionname = 'mod_label_get_labels_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //Lesson instance id.
     * 
     * [password] => string Varsayılan değer "" //Optional password (the lesson may be protected).
     * 
     * [outoftime] => int Varsayılan değer "" //If the user run out of time.
     * 
     * [review] => int Varsayılan değer "" //If we want to review just after finishing (1 hour margin).
     * 
     */
    function lmsmodLessonFinishAttempt($lessonid, $password, $outoftime, $review)
    {

        $functionname = 'mod_lesson_finish_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'password' => $password, 'outoftime' => $outoftime, 'review' => $review));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //Lesson instance id.
     * 
     * [groupid] => int Varsayılan değer "0" //group id, 0 means that the function lmswill determine the user group
     * 
     */
    function lmsmodLessonGetAttemptsOverview($lessonid, $groupid = 0)
    {

        $functionname = 'mod_lesson_get_attempts_overview';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //Lesson instance id.
     * 
     * [lessonattempt] => int //lesson attempt number
     * 
     * [userid] => int Varsayılan değer "null" //the user id (empty for current user)
     */
    function lmsmodLessonGetContentPagesViewed($lessonid, $lessonattempt, $userid)
    {

        $functionname = 'mod_lesson_get_content_pages_viewed';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'lessonattempt' => $lessonattempt, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [lessonid] => int //Lesson instance id.
     * 
     * [password] => string Varsayılan değer "" //lesson password
     * 
     */
    function lmsmodLessonGetLesson($lessonid, $password)
    {

        $functionname = 'mod_lesson_get_lesson';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'password' => $password));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [lessonid] => int //Lesson instance id.
     * 
     */
    function lmsmodLessonGetLessonAccessInformation($lessonid)
    {

        $functionname = 'mod_lesson_get_lesson_access_information';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodLessonGetLessonsCourses($courseids)
    {

        $functionname = 'mod_lesson_get_lessons_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [pageid] => int //the page id
     * 
     * [password] => string Varsayılan değer "" //optional password (the lesson may be protected)
     * 
     * [review] => int Varsayılan değer "" //if we want to review just after finishing (1 hour margin)
     * 
     * [returncontents] => int Varsayılan değer "" //if we must return the complete page contents once rendered
     * 
     */
    function lmsmodLessonGetPageData($lessonid, $pageid, $password, $review, $returncontents)
    {

        $functionname = 'mod_lesson_get_page_data';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'pageid' => $pageid, 'password' => $password, 'review' => $review, 'returncontents' => $returncontents));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [password] => string Varsayılan değer "" //optional password (the lesson may be protected)
     * 
     */
    function lmsmodLessonGetPages($lessonid, $password)
    {

        $functionname = 'mod_lesson_get_pages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'password' => $password));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     */
    function lmsmodLessonGetPagesPossibleJumps($lessonid)
    {

        $functionname = 'mod_lesson_get_pages_possible_jumps';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [attempt] => int //lesson attempt number
     * 
     * [correct] => int Varsayılan değer "" //only fetch correct attempts
     * 
     * [pageid] => int Varsayılan değer "null" //only fetch attempts at the given page
     * 
     * [userid] => int Varsayılan değer "null" //only fetch attempts of the given user
     * 
     */
    function lmsmodLessonGetQuestionsAttemps($lessonid, $attempt, $correct, $pageid, $userid)
    {

        $functionname = 'mod_lesson_get_questions_attempts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'attempt' => $attempt, 'correct' => $correct, 'pageid' => $pageid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [userid] => int //The user id. 0 for current user.
     * 
     * [lessonattempt] => int //The attempt number.
     * 
     */
    function lmsmodLessonGetUserAttemp($lessonid, $userid, $lessonattempt)
    {

        $functionname = 'mod_lesson_get_user_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'userid' => $userid, 'lessonattempt' => $lessonattempt));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [lessonid] => int //lesson instance id
     *
     * [lessonattempt] => int //The attempt number.
     * 
     * [userid] => int Varsayılan değer "null" //the user id (empty for current user)
     * 
     */
    function lmsmodLessonGetUserAttempGrade($lessonid, $lessonattempt, $userid)
    {

        $functionname = 'mod_lesson_get_user_attempt_grade';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'lessonattempt' => $lessonattempt, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     *
     * [userid] => int Varsayılan değer "null" //the user id (empty for current user)
     * 
     */
    function lmsmodLessonGetUserGrade($lessonid, $userid)
    {

        $functionname = 'mod_lesson_get_user_grade';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     *
     * [userid] => int Varsayılan değer "null" //the user id (empty for current user)
     * 
     */
    function lmsmodLessonGetUserTimers($lessonid, $userid)
    {

        $functionname = 'mod_lesson_get_user_timers';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [password] => string Varsayılan değer "" //optional password (the lesson may be protected)
     * 
     * [pageid] => int Varsayılan değer "0" //page id to continue from (only when continuing an attempt)
     * 
     * [review] => int Varsayılan değer "" //if we want to review just after finishing
     *
     */
    function lmsmodLessonLaunchAttempt($lessonid, $password, $pageid = 0, $review)
    {

        $functionname = 'mod_lesson_launch_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'password' => $password, 'pageid' => $pageid, 'review' => $review));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [pageid] => int //the page id
     * 
     * [data] => array(
     *          'name'  => string //data name
     *          'value' => string //data value
     *      )
     * 
     * [password] => string Varsayılan değer "" //optional password (the lesson may be protected)
     * 
     * [review] => int Varsayılan değer "" //if we want to review just after finishing (1 hour margin)
     */
    function lmsmodLessonProcessPage($lessonid, $pageid, $data, $password, $review)
    {

        $functionname = 'mod_lesson_process_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'pageid' => $pageid, 'data' => array($data), 'password' => $password, 'review' => $review));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [lessonid] => int //lesson instance id
     * 
     * [password] => string Varsayılan değer "" //lesson password
     * 
     */
    function lmsmodLessonViewLesson($lessonid, $password)
    {

        $functionname = 'mod_lesson_view_lesson';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('lessonid' => $lessonid, 'password' => $password));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [name] => string Varsayılan değer "" //Tool proxy name
     * 
     * [regurl] => string //Tool proxy registration URL
     * 
     * [capabilityoffered] => array(
     *                          [0] => string   //Tool proxy capabilities offered
     *                      )
     * 
     * [serviceoffered] => array(
     *                      [0] => string //Tool proxy services offered
     *                  )
     * 
     */
    function lmsmodLtiCreateToolProxy($name = "", $regurl, $capabilityoffered, $serviceoffered)
    {

        $functionname = 'mod_lti_create_tool_proxy';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('name' => $name, 'regurl' => $regurl, 'capabilityoffered' => array($capabilityoffered), 'serviceoffered' => array($serviceoffered)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [cartridgeurl] => string Varsayılan değer "" //URL to cardridge to load tool information
     * 
     * [key] => string Varsayılan değer "" //Consumer key
     * 
     * [secret] => string Varsayılan değer "" //Shared secret
     * 
     */
    function lmsmodLtiCreateToolType($cartridgeurl = "", $key = "", $secret = "")
    {

        $functionname = 'mod_lti_create_tool_type';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('cartridgeurl' => $cartridgeurl, 'key' => $key, 'secret' => $secret));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Tool proxy id
     * 
     */
    function lmsmodLtiDeleteToolProxy($id)
    {

        $functionname = 'mod_lti_delete_tool_proxy';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Tool type id
     * 
     */
    function lmsmodLtiDeleteToolType($id)
    {

        $functionname = 'mod_lti_delete_tool_type';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodLtiGetLtisCourses($courseids)
    {

        $functionname = 'mod_lti_get_ltis_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [toolid] => int //external tool instance id
     * 
     */
    function lmsmodLtiGetToolLaunchData($toolid)
    {

        $functionname = 'mod_lti_get_tool_launch_data';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('toolid' => $toolid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [orphanedonly] => int Varsayılan değer "0" //Orphaned tool types only
     * 
     */
    function lmsmodLtiGetToolProxies($orphanedonly = 0)
    {

        $functionname = 'mod_lti_get_tool_proxies';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('orphanedonly' => $orphanedonly));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Tool proxy id
     * 
     */
    function lmsmodLtiGetToolProxyRegistrationRequest($id)
    {

        $functionname = 'mod_lti_get_tool_proxy_registration_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [toolproxyid] => int Varsayılan değer "0" //Tool proxy id
     * 
     */
    function lmsmodLtiGetToolTypes($toolproxyid = 0)
    {

        $functionname = 'mod_lti_get_tool_types';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('toolproxyid' => $toolproxyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [url] => string //Tool url
     * 
     */
    function lmsmodLtiIsCartridge($url)
    {

        $functionname = 'mod_lti_is_cartridge';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('url' => $url));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //Tool type id
     * 
     * [name] => string Varsayılan değer "null" //Tool type name
     * 
     * [description] string Varsayılan değer "null" //Tool type description
     * 
     * [state] => int Varsayılan değer "null" //Tool type state
     * 
     */
    function lmsmodLtiUpdateToolType($id, $name = null, $description = null, $state = null)
    {

        $functionname = 'mod_lti_update_tool_type';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id, 'name' => $name, 'description' => $description, 'state' => $state));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [ltiid] => int //lti instance id
     * 
     */
    function lmsmodLtiViewLti($ltiid)
    {

        $functionname = 'mod_lti_view_lti';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('ltiid' => $ltiid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodPageGetPagesCourses($courseids)
    {

        $functionname = 'mod_page_get_pages_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [pageid] => int //page instance id
     * 
     */
    function lmsmodPageViewPage($pageid)
    {

        $functionname = 'mod_page_view_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pageid' => $pageid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [quizid] => int //quiz instance id
     * 
     * [attemptid] => int Varsayılan değer "0" //attempt id, 0 for the user last attempt if exists
     * 
     */
    function lmsmodQuizGetAttempAccessInformation($quizid, $attemptid = 0)
    {

        $functionname = 'mod_quiz_get_attempt_access_information';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid, 'attemptid' => $attemptid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [page] => int //page number
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'value' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     */
    function lmsmodQuizGetAttempData($attemptid, $page, $preflightdata)
    {

        $functionname = 'mod_quiz_get_attempt_data';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'page' => $page, 'preflightdata' => array($preflightdata)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [page] => int Varsayılan değer "-1" //page number, empty for all the questions in all the pages
     * 
     */
    function lmsmodQuizGetAttempReview($attemptid, $page = -1)
    {

        $functionname = 'mod_quiz_get_attempt_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'page' => $page));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'value' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     */
    function lmsmodQuizGetAttempSummary($attemptid, $preflightdata)
    {

        $functionname = 'mod_quiz_get_attempt_summary';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'preflightdata' => array($preflightdata)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [quizid] => int //quiz instance id
     * 
     * [userid] => int Varsayılan değer "0" //user id (empty for current user)
     */
    function lmsmodQuizGetCombinedReviewOptions($quizid, $userid = 0)
    {

        $functionname = 'mod_quiz_get_combined_review_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [quizid] => int //quiz instance id
     * 
     */
    function lmsmodQuizGetQuizAccessInformation($quizid)
    {

        $functionname = 'mod_quiz_get_quiz_access_information';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [quizid] => int //quiz instance id
     * 
     * [grade] => double //the grade to check
     * 
     */
    function lmsmodQuizGetQuizFeedbackForGrade($quizid, $grade)
    {

        $functionname = 'mod_quiz_get_quiz_feedback_for_grade';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid, 'grade' => $grade));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [quizid] => int //quiz instance id
     * 
     */
    function lmsmodQuizGetQuizRequiredQtypes($quizid)
    {

        $functionname = 'mod_quiz_get_quiz_required_qtypes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodQuizGetQuizzesCourses($courseids)
    {

        $functionname = 'mod_quiz_get_quizzes_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [quizid] => int //quiz instance id
     * 
     * [userid] => int Varsayılan değer "0" //user id, empty for current user
     * 
     * [status] => string Varsayılan değer "finished" //quiz status: all, finished or unfinished
     * 
     * [includepreviews] => int Varsayılan değer "" //whether to include previews or not
     * 
     */
    function lmsmodQuizGetUserAttempts($quizid, $userid = 0, $status = "finished", $includepreviews)
    {

        $functionname = 'mod_quiz_get_user_attempts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid, 'userid' => $userid, 'status' => $status, 'includepreviews' => $includepreviews));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [quizid] => int //quiz instance id
     * 
     * [userid] => int Varsayılan değer "0" //user id
     * 
     */
    function lmsmodQuizGetUserBestGrade($quizid, $userid = 0)
    {

        $functionname = 'mod_quiz_get_user_best_grade';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [data] => array(
     *              'name'  => string //data name
     *              'value' => string //data value
     *          )
     *          //the data to be saved
     * 
     * [finishattempt] => int Varsayılan değer "" //whether to finish or not the attempt
     * 
     * [timeup] => int Varsayılan değer "" //whether the WS was called by a timer when the time is up
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'valur' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     */
    function lmsmodQuizProcessAttempt($attemptid, $data, $finishattempt, $timeup, $preflightdata)
    {

        $functionname = 'mod_quiz_process_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'data' => array($data), 'finishattempt' => $finishattempt, 'preflightdata' => array($preflightdata)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [data] => array(
     *              'name'  => string //data name
     *              'value' => string //data value
     *          )
     *          //the data to be saved
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'valur' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     */
    function lmsmodQuizSaveAttempt($attemptid, $data, $preflightdata)
    {

        $functionname = 'mod_quiz_save_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'data' => array($data), 'preflightdata' => array($preflightdata)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //quiz instance id
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'valur' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     * [forcenew] => int Varsayılan değer "" //Whether to force a new attempt or not.
     * 
     */
    function lmsmodQuizStartAttempt($quizid, $preflightdata, $forcenew)
    {

        $functionname = 'mod_quiz_start_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid, 'preflightdata' => array($preflightdata), 'forcenew' => $forcenew));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [page] => int //page number
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'valur' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     */
    function lmsmodQuizViewAttempt($attemptid, $page, $preflightdata)
    {

        $functionname = 'mod_quiz_view_attempt';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'page' => $page, 'preflightdata' => array($preflightdata)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int /attempt id
     * 
     */
    function lmsmodQuizViewAttemptReview($attemptid)
    {

        $functionname = 'mod_quiz_view_attempt_review';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [attemptid] => int //attempt id
     * 
     * [preflightdata] => array(
     *                      'name'  => string //data name
     *                      'valur' => string //data value
     *                  )
     *                  //Preflight required data (like passwords)
     * 
     */
    function lmsmodQuizViewAttemptSummary($attemptid, $preflightdata)
    {

        $functionname = 'mod_quiz_view_attempt_summary';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('attemptid' => $attemptid, 'preflightdata' => array($preflightdata)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [quizid] => int //quiz instance id
     * 
     */
    function lmsmodQuizViewQuiz($quizid)
    {

        $functionname = 'mod_quiz_view_quiz';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('quizid' => $quizid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseids] => int //Course id
     * 
     */
    function lmsmodResourceGetResourceCourses($courseids)
    {

        $functionname = 'mod_resource_get_resources_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [resourceid] => int //resource instance id
     * 
     */
    function lmsmodResourceViewResource($resourceid)
    {

        $functionname = 'mod_resource_view_resource';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('resourceid' => $resourceid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scormid] => int //SCORM instance id
     * 
     * [userid] => int //user id
     * 
     * [ignoremissingcompletion] => int Varsayılan değer "" //Ignores attempts that haven't reported a grade/completion
     * 
     */
    function lmsmodScormGetScormAttempCount($scormid, $userid, $ignoremissingcompletion)
    {

        $functionname = 'mod_scorm_get_scorm_attempt_count';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scormid' => $scormid, 'userid' => $userid, 'ignoremissingcompletion' => $ignoremissingcompletion));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scoid] => int //sco id
     * 
     * [userid] => int //user id
     * 
     * [attempt] => int Varsayılan değer "0" //attempt number (0 for last attempt)
     * 
     */
    function lmsmodScormGetScormScoTracks($scoid, $userid, $attempt = 0)
    {

        $functionname = 'mod_scorm_get_scorm_sco_tracks';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scoid' => $scoid, 'userid' => $userid, 'attempt' => $attempt));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scoid] => int //scorm instance id
     * 
     * [organization] => string Varsayılan değer "" //organization id
     * 
     */
    function lmsmodScormGetScormScoes($scormid, $organization = "")
    {

        $functionname = 'mod_scorm_get_scorm_scoes';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scormid' => $scormid, 'organization' => $organization));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scoid] => int //scorm instance id
     * 
     * [attempt] => int //attempt number
     * 
     */
    function lmsmodScormGetScormUserData($scormid, $attempt)
    {

        $functionname = 'mod_scorm_get_scorm_user_data';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scormid' => $scormid, 'attempt' => $attempt));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodScormGetScormsCourses($courseids)
    {

        $functionname = 'mod_scorm_get_scorms_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scoid] => int //sco id
     * 
     * [attempt] => int //attempt number
     * 
     * [tracks] => array(
     *              'element'  => string //element name
     *              'value'    => string //element value
     *          )
     * 
     */
    function lmsmodScormInsertScormTracks($scoid, $attempt, $tracks)
    {

        $functionname = 'mod_scorm_insert_scorm_tracks';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scoid' => $scoid, 'attempt' => $attempt, 'tracks' => array($tracks)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scormid] => int //SCORM instance id
     * 
     * [scoid] => int Varsayılan değer "0" //SCO id (empty for launching the first SCO)
     * 
     */
    function lmsmodScormLaunchSco($scormid, $scoid = 0)
    {

        $functionname = 'mod_scorm_launch_sco';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scormid' => $scormid, 'scoid' => $scoid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [scormid] => int //SCORM instance id
     * 
     */
    function lmsmodScormViewScorm($scormid)
    {

        $functionname = 'mod_scorm_view_scorm';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('scormid' => $scormid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [surveyid] => int //survey instance id
     * 
     */
    function lmsmodSurveyGetQuestions($surveyid)
    {

        $functionname = 'mod_survey_get_questions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('surveyid' => $surveyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodSurveyGetSurveysCourses($courseids)
    {

        $functionname = 'mod_survey_get_surveys_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [surveyid] => int //survey id
     * 
     * [answers] => array(
     *                  'key'   => string //answer key
     *                  'value' => string //answer value
     *              )   
     * 
     */
    function lmsmodSurveySubmitAnswers($surveyid, $answers)
    {

        $functionname = 'mod_survey_submit_answers';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('surveyid' => $surveyid, 'answers' => array($answers)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [surveyid] => int //survey id
     * 
     */
    function lmsmodSurveyViewSurvey($surveyid)
    {

        $functionname = 'mod_survey_view_survey';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('surveyid' => $surveyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodUrlGetUrlsCourses($courseids)
    {

        $functionname = 'mod_url_get_urls_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [urlid] => int //url instance id
     * 
     */
    function lmsmodUrlViewUrl($urlid)
    {

        $functionname = 'mod_url_view_url';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('urlid' => $urlid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [pageid] => int //page id
     * 
     * [content] => string //Page contents.
     * 
     * [section] => string Varsayılan değer "null" //Section page title.
     * 
     */
    function lmsmodWikiEditPage($pageid, $content, $section = null)
    {

        $functionname = 'mod_wiki_edit_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pageid' => $pageid, 'content' => $content, 'section' => $section));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [pageid] => int //page id
     * 
     */
    function lmsmodWikiGetPageContents($pageid)
    {

        $functionname = 'mod_wiki_get_page_contents';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pageid' => $pageid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [pageid] => int //page id
     * 
     * [section] => string Varsayılan değer "null" //Section page title.
     * 
     * [lockonly] => int Varsayılan değer "" //Just renew lock and not return content.
     * 
     */
    function lmsmodWikiGetPageForEditing($pageid, $section = null, $lockonly)
    {

        $functionname = 'mod_wiki_get_page_for_editing';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pageid' => $pageid, 'section' => $section, 'lockonly' => $lockonly));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [wikiid] => int //Wiki instance ID
     * 
     * [groupid] => int Varsayılan değer "-1" //Subwiki's group ID, -1 means current group. It will be ignored if the wiki doesn't use groups.
     * 
     * [userid] => int Varsayılan değer "0" //Subwiki's user ID, 0 means current user. It will be ignored in collaborative wikis.
     * 
     */
    function lmsmodWikiGetSubwikiFiles($wikiid, $groupid = -1, $userid = 0)
    {

        $functionname = 'mod_wiki_get_subwiki_files';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('wikiid' => $wikiid, 'groupid' => $groupid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [wikiid] => int //Wiki instance ID
     * 
     * [groupid] => int Varsayılan değer "-1" //Subwiki's group ID, -1 means current group. It will be ignored if the wiki doesn't use groups.
     * 
     * [userid] => int Varsayılan değer "0" //Subwiki's user ID, 0 means current user. It will be ignored in collaborative wikis.
     * 
     * [options] => array(
     *          
     *              'sortby'         => string Varsayılan değer "title" //Field to sort by (id, title, ...).
     *              'sortdirection'  => string Varsayılan değer "ASC" //Sort direction: ASC or DESC.
     *              'includecontent' => int Varsayılan değer "1" //Include each page contents or just the contents size.
     *          )
     * 
     */
    function lmsmodWikiGetSubwikiPages($wikiid, $groupid = -1, $userid = 0, $options)
    {

        $functionname = 'mod_wiki_get_subwiki_pages';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('wikiid' => $wikiid, 'groupid' => $groupid, 'userid' => $userid, 'options' => array($options)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [wikiid] => int //Wiki instance ID
     * 
     */
    function lmsmodWikiGetSubwikis($wikiid)
    {

        $functionname = 'mod_wiki_get_subwikis';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('wikiid' => $wikiid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodWikiGetWikisCourses($courseids)
    {

        $functionname = 'mod_wiki_get_wikis_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [title] => string //New page title.
     * 
     * [content] => string //Page contents.
     * 
     * [contentformat] => string Varsayılan değer "null" //Page contents format. If an invalid format is provided, default wiki format is used.
     * 
     * [subwikiid] => int Varsayılan değer "null" //Page's subwiki ID.
     *  
     * [wikiid] => int Varsayılan değer "null" //Page's wiki ID. Used if subwiki does not exists.
     * 
     * [userid] => int Varsayılan değer "null" //Subwiki's user ID. Used if subwiki does not exists.
     * 
     * [groupid] => int Varsayılan değer "null" //Subwiki's group ID. Used if subwiki does not exists.
     * 
     */
    function lmsmodWikiNewPage($title, $content, $contentformat = null, $subwikiid = null, $wikiid = null, $userid = null, $groupid = null)
    {

        $functionname = 'mod_wiki_new_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('title' => $title, 'content' => $content, 'contentformat' => $contentformat, 'subwikiid' => $subwikiid, 'wikiid' => $wikiid, 'userid' => $userid, 'groupid' => $groupid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [pageid] => int //page id
     * 
     */
    function lmsmodWikiViewPage($pageid)
    {

        $functionname = 'mod_wiki_view_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pageid' => $pageid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [wikiid] => int //wiki id
     * 
     */
    function lmsmodWikiViewWiki($wikiid)
    {

        $functionname = 'mod_wiki_view_wiki';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('wikiid' => $wikiid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //Workshop id
     * 
     * [title] => string //Submission title
     * 
     * [content] => string Varsayılan değer "" //Submission text content
     * 
     * [contentformat] => int Varsayılan değer "0" //The format used for the content
     * 
     * [inlineattachmentsid] => int Varsayılan değer "0" //The draft file area id for inline attachments in the content
     * 
     * [attachmentsid] => int Varsayılan değer "0" //The draft file area id for attachments
     * 
     */
    function lmsmodWorkshopAddSubmission($workshopid, $title, $content = "", $contentformat = 0, $inlineattachmentsid = 0, $attachmentsid = 0)
    {

        $functionname = 'mod_workshop_add_submission';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid, 'title' => $title, 'content' => $content, 'contentformat' => $contentformat, 'inlineattachmentsid' => $inlineattachmentsid, 'attachmentsid' => $attachmentsid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [submissionid] => int //submission id
     * 
     */
    function lmsmodWorkshopDeleteSubmission($submissionid)
    {

        $functionname = 'mod_workshop_delete_submission';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('submissionid' => $submissionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [assessmentid] => int //Assessment id
     * 
     * [feedbacktext] => string Varsayılan değer "" //The feedback for the reviewer.
     * 
     * [feedbackformat] => int Varsayılan değer "0" //The feedback format for text.
     * 
     * [weight] => int Varsayılan değer "1" //The new weight for the assessment.
     * 
     * [gradinggradeover] => string Varsayılan değer "" //The new grading grade.
     * 
     */
    function lmsmodWorkshopEvaluateAssessment($assessmentid, $feedbacktext = "", $feedbackformat = 0, $weight = 1, $gradinggradeover = "")
    {

        $functionname = 'mod_workshop_evaluate_assessment';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assessmentid' => $assessmentid, 'feedbacktext' => $feedbacktext, 'feedbackformat' => $feedbackformat, 'weight' => $weight, 'gradinggradeover' => $gradinggradeover));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [submissionid] => int //submission id
     * 
     * [feedbacktext] => string Varsayılan değer "" //The feedback for the author.
     * 
     * [feedbackformat] => int Varsayılan değer "0" //The feedback format for text.
     * 
     * [published] => int Varsayılan değer "" //Publish the submission for others?.
     * 
     * [gradeover] => string Varsayılan değer "" //The new submission grade.
     * 
     */
    function lmsmodWorkshopEvaluateSubmission($submissionid, $feedbacktext = "", $feedbackformat = 0, $published, $gradeover = "")
    {

        $functionname = 'mod_workshop_evaluate_submission';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('submissionid' => $submissionid, 'feedbacktext' => $feedbacktext, 'feedbackformat' => $feedbackformat, 'published' => $published, 'gradeover' => $gradeover));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [assessmentid] => int //Assessment id
     * 
     */
    function lmsmodWorkshopGetAssessment($assessmentid)
    {

        $functionname = 'mod_workshop_get_assessment';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assessmentid' => $assessmentid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [assessmentid] => int //Assessment id
     * 
     * [mode] => string Varsayılan değer "assessment" //The form mode (assessment or preview)
     * 
     */
    function lmsmodWorkshopGetAssessmentFormDefinition($assessmentid, $mode = "assessment")
    {

        $functionname = 'mod_workshop_get_assessment_form_definition';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assessmentid' => $assessmentid, 'mode' => $mode));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //workshop id
     * 
     * [userid] => int Varsayılan değer "0" //User id (empty or 0 for current user).
     * 
     */
    function lmsmodWorkshopGetGrades($workshopid, $userid = 0)
    {

        $functionname = 'mod_workshop_get_grades';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [workshopid] => int //workshop id
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group.
     * 
     * [sortby] => string Varsayılan değer "lastname" //sort by this element: lastname, firstname, submissiontitle, submissionmodified, submissiongrade, gradinggrade.
     * 
     * [sortdirection] => string Varsayılan değer "ASC" //sort direction: ASC or DESC
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int Varsayılan değer "0" //The number of records to return per page.
     * 
     */
    function lmsmodWorkshopGetGradesReport($workshopid, $groupid = 0, $sortby = "lastname", $sortdirection = "ASC", $page = 0, $perpage = 0)
    {

        $functionname = 'mod_workshop_get_grades_report';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid, 'groupid' => $groupid, 'sortby' => $sortby, 'sortdirection' => $sortdirection, 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //workshop id
     * 
     * [userid] => int Varsayılan değer "0" //User id who did the assessment review (empty or 0 for current user).
     * 
     */
    function lmsmodWorkshopGetReviewerAssessments($workshopid, $userid = 0)
    {

        $functionname = 'mod_workshop_get_reviewer_assessments';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [submissionid] => int //submission id
     * 
     */
    function lmsmodWorkshopGetSubmission($submissionid)
    {

        $functionname = 'mod_workshop_get_submission';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('submissionid' => $submissionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [submissionid] => int //submission id
     * 
     */
    function lmsmodWorkshopGetSubmissionAssessment($submissionid)
    {

        $functionname = 'mod_workshop_get_submission_assessments';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('submissionid' => $submissionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //workshop id
     * 
     * [userid] => int Varsayılan değer "0" //Get submissions done by this user. Use 0 or empty for the current user
     * 
     * [groupid] => int Varsayılan değer "0" //Group id, 0 means that the function lmswill determine the user group. It will return submissions done by users in the given group.
     * 
     * [page] => int Varsayılan değer "0" //The page of records to return.
     * 
     * [perpage] => int Varsayılan değer "0" //The number of records to return per page.
     * 
     */
    function lmsmodWorkshopGetSubmissions($workshopid, $userid = 0, $groupid = 0, $page = 0, $perpage = 0)
    {

        $functionname = 'mod_workshop_get_submissions';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid, 'userid' => $userid, 'groupid' => $groupid, 'page' => $page, 'perpage' => $perpage));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //workshop id
     * 
     * [userid] => int Varsayılan değer "0" //User id (empty or 0 for current user).
     * 
     */
    function lmsmodWorkshopGetUserPlan($workshopid, $userid = 0)
    {

        $functionname = 'mod_workshop_get_user_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //workshop id
     * 
     */
    function lmsmodWorkshopGetWorkshopAccessInformation($workshopid)
    {

        $functionname = 'mod_workshop_get_workshop_access_information';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseids] => int //course id
     * 
     */
    function lmsmodWorkshopGetWorkshopsCourses($courseids)
    {

        $functionname = 'mod_workshop_get_workshops_by_courses';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseids' => array($courseids)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [assessmentid] => int //assessment id
     * 
     * [data] => array(
     * 
     *          'name'  => string //The assessment data (use WS get_assessment_form_definition for obtaining the data to sent).
     *                              Apart from that data, you can optionally send:
     *                              feedbackauthor (str); the feedback for the submission author
     *                              feedbackauthorformat (int); the format of the feedbackauthor
     *                              feedbackauthorinlineattachmentsid (int); the draft file area for the editor attachments
     *                              feedbackauthorattachmentsid (int); the draft file area id for the feedback attachments
     * 
     *          'value' => string //The value of the option.
     *  )
     * 
     */
    function lmsmodWorkshopUpdateAssessment($assessmentid, $data)
    {

        $functionname = 'mod_workshop_update_assessment';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('assessmentid' => $assessmentid, 'data' => array($data)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [submissionid] => int //Submission id
     * 
     * [title] => string //Submission title
     * 
     * [content] => string Varsayılan değer "" //Submission text content
     * 
     * [contentformat] => int Varsayılan değer "0" //The format used for the content
     * 
     * [inlineattachmentsid] => int Varsayılan değer "0" //The draft file area id for inline attachments in the content
     * 
     * [attachmentsid] => int Varsayılan değer "0" //The draft file area id for attachments
     * 
     */
    function lmsmodWorkshopUpdateSubmission($submissionid, $title, $content = "", $contentformat = 0, $inlineattachmentsid = 0, $attachmentsid = 0)
    {

        $functionname = 'mod_workshop_update_submission';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('submissionid' => $submissionid, 'title' => $title, 'content' => $content, 'contentformat' => $contentformat, 'inlineattachmentsid' => $inlineattachmentsid, 'attachmentsid' => $attachmentsid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [submissionid] => int //Submission id
     * 
     */
    function lmsmodWorkshopViewSubmission($submissionid)
    {

        $functionname = 'mod_workshop_view_submission';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('submissionid' => $submissionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [workshopid] => int //workshop id
     * 
     */
    function lmsmodWorkshopViewWorkshop($workshopid)
    {

        $functionname = 'mod_workshop_view_workshop';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('workshopid' => $workshopid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [courseid] => int //The course id
     * 
     * [userid] => int //The user id
     * 
     */
    function lmsreportCompetencyDataForReport($courseid, $userid)
    {

        $functionname = 'report_competency_data_for_report';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid, 'userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [predictionid] => int //The prediction id
     * 
     */
    function lmsreportInsightsSetFixedPrediction($predictionid)
    {

        $functionname = 'report_insights_set_fixed_prediction';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('predictionid' => $predictionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [predictionid] => int //The prediction id
     * 
     */
    function lmsreportInsightsSetNotusefulPrediction($predictionid)
    {

        $functionname = 'report_insights_set_notuseful_prediction';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('predictionid' => $predictionid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [requestid] => int //The request id
     * 
     */
    function lmstoolDataprivacyApproveDataRequest($requestid)
    {

        $functionname = 'tool_dataprivacy_approve_data_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestid' => $requestid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [requestids] => array(
     *          [0] => int //The request id
     *      )
     * 
     */
    function lmstoolDataprivacyBulkApproveDataRequests($requestids)
    {

        $functionname = 'tool_dataprivacy_bulk_approve_data_requests';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestids' => $requestids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [requestids] => array(
     * 
     *          [0] => int //The request id
     * 
     *      )
     * 
     */
    function lmstoolDataprivacyBulkDenyDataRequests($requestids)
    {

        $functionname = 'tool_dataprivacy_bulk_deny_data_requests';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestids' => $requestids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [requestid] => int //The request id
     * 
     */
    function lmstoolDataprivacyCancelDataRequest($requestid)
    {

        $functionname = 'tool_dataprivacy_cancel_data_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestid' => $requestid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [ids] => array(
     *          [0] => int //Expired context record ID
     *          
     *      )
     * 
     */
    function lmstoolDataprivacyConfirmContextsForDeletion($ids)
    {

        $functionname = 'tool_dataprivacy_confirm_contexts_for_deletion';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('ids' => $ids));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [message] => string //The user's message to the Data Protection Officer(s)
     */
    function lmstoolDataprivacyContactDpo($message)
    {

        $functionname = 'tool_dataprivacy_contact_dpo';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('message' => $message));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [jsonformdata] => string //The data to create the category, encoded as a json array
     */
    function lmstoolDataprivacyCreateCategoryForm($jsonformdata)
    {

        $functionname = 'tool_dataprivacy_create_category_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('jsonformdata' => $jsonformdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [jsonformdata] => string //The data to create the purpose, encoded as a json array
     */
    function lmstoolDataprivacyCreatePurposeForm($jsonformdata)
    {

        $functionname = 'tool_dataprivacy_create_purpose_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('jsonformdata' => $jsonformdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The category ID
     */
    function lmstoolDataprivacyDeleteCategory($id)
    {
        
        $functionname = 'tool_dataprivacy_delete_category';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The purpose ID
     */
    function lmstoolDataprivacyDeletePurpose($id)
    {

        $functionname = 'tool_dataprivacy_delete_purpose';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [requestid] => int //The request ID
     */
    function lmstoolDataprivacyDenyDataRequest($requestid)
    {

        $functionname = 'tool_dataprivacy_deny_data_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestid' => $requestid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [nodefaults] => int Varsayılan değer "" //Whether to fetch all activities or only those without defaults
     * 
     */
    function lmstoolDataprivacyGetActivityOptions($nodefaults)
    {

        $functionname = 'tool_dataprivacy_get_activity_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('nodefaults' => $nodefaults));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [includeinherit] => int Varsayılan değer "1" //Include option "Inherit"
     * 
     * [includenotset] => int Varsayılan değer "" //Include option "Not set"
     * 
     */
    function lmstoolDataprivacyGetCategoryOptions($includeinherit = 1, $includenotset)
    {

        $functionname = 'tool_dataprivacy_get_category_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('includeinherit' => $includeinherit, 'includenotset' => $includenotset));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [requestid] => int //The request ID
     * 
     */
    function lmstoolDataprivacyGetDataRequest($requestid)
    {

        $functionname = 'tool_dataprivacy_get_data_request';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestid' => $requestid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [includeinherit] => int Varsayılan değer "1" //Include option "Inherit"
     * 
     * [includenotset] => int Varsayılan değer "" //Include option "Not set"
     * 
     */
    function lmstoolDataprivacyGetPurposeOptions($includeinherit = 1, $includenotset)
    {

        $functionname = 'tool_dataprivacy_get_purpose_options';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('includeinherit' => $includeinherit, 'includenotset' => $includenotset));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [query] => string //The search query
     * 
     */
    function lmstoolDataprivacyGetUsers($query)
    {

        $functionname = 'tool_dataprivacy_get_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('query' => $query));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [requestid] => int //The request ID
     * 
     */
    function lmstoolDataprivacyMarkComplete($requestid)
    {

        $functionname = 'tool_dataprivacy_mark_complete';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('requestid' => $requestid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextlevel] => int //The context level
     * 
     * [category] => int //The default category for the given context level
     * 
     * [purpose] => int //The default purpose for the given context level
     * 
     * [activity] => string Varsayılan değer "null" //The plugin name of the activity
     * 
     * [override] => int Varsayılan değer "" //Whether to override existing instances with the defaults
     * 
     */
    function lmstoolDataprivacySetContextDefaults($contextlevel, $category, $purpose, $activity = null, $override)
    {

        $functionname = 'tool_dataprivacy_set_context_defaults';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextlevel' => $contextlevel, 'category' => $category, 'purpose' => $purpose, 'activity' => $activity, 'override' => $override));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [jsonformdata] => string //The context level data, encoded as a json array
     * 
     */
    function lmstoolDataprivacySetContextForm($jsonformdata)
    {

        $functionname = 'tool_dataprivacy_set_context_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('jsonformdata' => $jsonformdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [jsonformdata] => string //The context level data, encoded as a json array
     * 
     */
    function lmstoolDataprivacySetContextlevelForm($jsonformdata)
    {

        $functionname = 'tool_dataprivacy_set_contextlevel_form';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('jsonformdata' => $jsonformdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [contextid] => int //The context id to expand 
     * 
     * [element] => string //The element we are interested on
     * 
     */
    function lmstoolDataprivacyTreeExtraBranches($contextid, $element)
    {

        $functionname = 'tool_dataprivacy_tree_extra_branches';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('contextid' => $contextid, 'element' => $element));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [competencyframeworkid] => int //The competency framework id
     * 
     * [search] => string Varsayılan değer "" //A search string
     * 
     */
    function lmstoolLpDataForCompetenciesManagePage($competencyframeworkid, $search = "")
    {

        $functionname = 'tool_lp_data_for_competencies_manage_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyframeworkid' => $competencyframeworkid, 'search' => $search));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [pagecontext] => array(
     *              'contextid'    => int Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *              'contextlevel' => string Varsayılan değer "" //Context level. To be used with instanceid.
     *              'instanceid'   => int Varsayılan değer "0" //Context instance ID. To be used with level
     *          )
     */
    function lmstoolLpDataForCompetencyFrameworksManagePage($pagecontext)
    {

        $functionname = 'tool_lp_data_for_competency_frameworks_manage_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pagecontext' => $pagecontext));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     * [includerelated] => int Varsayılan değer "" //Include or not related competencies
     * 
     * [includecourses] => int Varsayılan değer "" //Include or not competency courses
     */
    function lmstoolLpDataForCompetencySummary($competencyid, $includerelated, $includecourses)
    {

        $functionname = 'tool_lp_data_for_competency_summary';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'includerelated' => $includerelated, 'includecourses' => $includecourses));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    
    /**
     * [courseid] => int //The course id
     * 
     */
    function lmstoolLpDataForCourseCompetenciesPage($courseid)
    {

        $functionname = 'tool_lp_data_for_course_competencies_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [planid] => int //The plan id
     * 
     */
    function lmstoolLpDataForPlanPage($planid)
    {

        $functionname = 'tool_lp_data_for_plan_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user id
     * 
     */
    function lmstoolLpDataForPlansPage($userid)
    {

        $functionname = 'tool_lp_data_for_plans_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //The competency id
     * 
     */
    function lmstoolLpDataForRelatedCompetenciesSection($competencyid)
    {

        $functionname = 'tool_lp_data_for_related_competencies_section';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [templateid]  => int //The template id
     * 
     * [pagecontext] => array(
     *              'contextid'    => int Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *              'contextlevel' => string Varsayılan değer "" //Context level. To be used with instanceid.
     *              'instanceid'   => int Varsayılan değer "0" //Context instance ID. To be used with level
     *          )
     */
    function lmstoolLpDataForTemplateCompetenciesPage($templateid, $pagecontext)
    {

        $functionname = 'tool_lp_data_for_template_competencies_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('templateid' => $templateid, 'pagecontext' => $pagecontext));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [pagecontext] => array(
     *              'contextid'    => int Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *              'contextlevel' => string Varsayılan değer "" //Context level. To be used with instanceid.
     *              'instanceid'   => int Varsayılan değer "0" //Context instance ID. To be used with level
     *          )
     */
    function lmstoolLpDataForTemplateManagePage($pagecontext)
    {

        $functionname = 'tool_lp_data_for_templates_manage_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('pagecontext' => $pagecontext));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //Data base record id for the user
     * 
     * [competencyid] => int //Data base record id for the competency
     * 
     */
    function lmstoolLpDataForUserCompetencySummary($userid, $competencyid)
    {

        $functionname = 'tool_lp_data_for_user_competency_summary';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //Data base record id for the user
     * 
     * [competencyid] => int //Data base record id for the competency
     * 
     * [courseid] => int //Data base record id for the course
     * 
     */
    function lmstoolLpDataForUserCompetencySummaryInCourse($userid, $competencyid, $courseid)
    {

        $functionname = 'tool_lp_data_for_user_competency_summary_in_course';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid, 'competencyid' => $competencyid, 'courseid' => $courseid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [competencyid] => int //Data base record id for the competency
     * 
     * [planid] => int //Data base record id for the plan
     * 
     */
    function lmstoolLpDataForUserCompetencySummaryInPlan($competencyid, $planid)
    {

        $functionname = 'tool_lp_data_for_user_competency_summary_in_plan';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('competencyid' => $competencyid, 'planid' => $planid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [userid] => int //The user id.
     * 
     */
    function lmstoolLpDataForUserEvidenceListPage($userid)
    {

        $functionname = 'tool_lp_data_for_user_evidence_list_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('userid' => $userid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The user evidence ID
     * 
     */
    function lmstoolLpDataForUserEvidencePage($id)
    {

        $functionname = 'tool_lp_data_for_user_evidence_page';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [id] => int //The competency id
     * 
     */
    function lmstoolLpListCoursesUsingCompetency($id)
    {

        $functionname = 'tool_lp_list_courses_using_competency';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('id' => $id));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [query] => string //Query string
     * 
     * [context] => array(
     *              'contextid'    => int Varsayılan değer "0" //Context ID. Either use this value, or level and instanceid.
     *              'contextlevel' => string Varsayılan değer "" //Context level. To be used with instanceid.
     *              'instanceid'   => int Varsayılan değer "0" //Context instance ID. To be used with level
     *          )
     * 
     * [includes] => string Varsayılan değer "parents" //What other contexts to fetch the frameworks from. (all, parents, self)
     * 
     * [limitfrom] => int Varsayılan değer "0" //limitfrom we are fetching the records from
     * 
     * [limitnum] => int Varsayılan değer "25" //Number of records to fetch
     * 
     */
    function lmstoolLpSearchCohorts($query, $context, $includes = "parents", $limitfrom = 0, $limitnum = 25)
    {

        $functionname = 'tool_lp_search_cohorts';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('query' => $query, 'context' => $context, 'includes' => $includes, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [query] => string //Query string
     * 
     * [capability] => string //Required capability
     * 
     * [limitfrom] => int Varsayılan değer "0" //Number of records to skip
     * 
     * [limitnum] => string Varsayılan değer "100" //Number of records to fetch
     * 
     */
    function lmstoolLpSearchUsers($query, $capability, $limitfrom = 0, $limitnum = "100")
    {

        $functionname = 'tool_lp_search_users';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('query' => $query, 'capability' => $capability, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [privatetoken] => string //Private token, usually generated by login/token.php
     * 
     */
    function lmstoolMobileGetAutologinKey($privatetoken)
    {

        $functionname = 'tool_mobile_get_autologin_key';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('privatetoken' => $privatetoken));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [section] => string Varsayılan değer "" //Setting section name.
     * 
     */
    function lmstoolMobileGetConfig($section)
    {

        $functionname = 'tool_mobile_get_config';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('section' => $section));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [component] => string //Component where the class is e.g. mod_assign.
     * 
     * [method] => string //Method to execute in class \$component\output\mobile.
     * 
     * [args] => İsteğe bağlı //Args for the method are optional. 
     *      
     *      array(
     *          'name'  => Param name
     *          'value' => Param value
     *      )
     * 
     */
    function lmstoolMobileGetContent($component, $method, $args)
    {

        $functionname = 'tool_mobile_get_content';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'method' => $method, 'args' => array($args)));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [versionid] => int //The policy version ID
     * 
     * [behalfid] => int Varsayılan değer "0" //The id of user on whose behalf the user is viewing the policy
     * 
     */
    function lmstoolPolicyGetPolicyVersion($versionid, $behalfid = 0)
    {

        $functionname = 'tool_policy_get_policy_version';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('versionid' => $versionid, 'behalfid' => $behalfid));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [jsonformdata] => string //The data from the create group form, encoded as a json array
     * 
     */
    function lmstoolPolicySubmitAcceptOnBehalf($jsonformdata)
    {

        $functionname = 'tool_policy_submit_accept_on_behalf ';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('jsonformdata' => $jsonformdata));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [component] => string Varsayılan değer "" //The component to search
     * 
     * [search] => string Varsayılan değer "" //The search string
     * 
     * [themename] => string Varsayılan değer "" //The current theme
     * 
     */
    function lmstoolTemplatelibraryListTemplates($component = "", $search = "", $themename = "")
    {

        $functionname = 'tool_templatelibrary_list_templates ';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'search' => $search, 'themename' => $themename));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [component] => string //component containing the template
     * 
     * [template] => string //name of the template
     * 
     */
    function lmstoolTemplatelibraryLoadCanonicalTemplate($component, $template)
    {

        $functionname = 'tool_templatelibrary_load_canonical_template ';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('component' => $component, 'template' => $template));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tourid] => int //Tour ID
     * 
     * [context] => int //Context ID
     * 
     * [pageurl] => string //Page URL
     * 
     * [stepid] => int //Step ID
     * 
     * [stepindex] => int //Step Number
     * 
     */
    function lmstoolUsertoursCompleteTour($tourid, $context, $pageurl, $stepid, $stepindex)
    {

        $functionname = 'tool_usertours_complete_tour ';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tourid' => $tourid, 'context' => $context, 'pageurl' => $pageurl, 'stepid' => $stepid, 'stepindex' => $stepindex));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tourid] => int //Tour ID
     * 
     * [context] => int //Context ID
     * 
     * [pageurl] => string //Page URL
     * 
     */
    function lmstoolUsertoursFetchAndStartTour($tourid, $context, $pageurl)
    {

        $functionname = 'tool_usertours_fetch_and_start_tour ';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tourid' => $tourid, 'context' => $context, 'pageurl' => $pageurl));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tourid] => int //Tour ID
     * 
     * [context] => int //Context ID
     * 
     * [pageurl] => string //Current page location
     * 
     */
    function lmstoolUsertoursResetTour($tourid, $context, $pageurl)
    {
        $curl = new curl;
        
        $functionname = 'tool_usertours_reset_tour ';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tourid' => $tourid, 'context' => $context, 'pageurl' => $pageurl));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [tourid] => int //Tour ID
     * 
     * [context] => int //Context ID
     * 
     * [pageurl] => string //Page URL
     * 
     * [stepid] => int //Step ID
     * 
     * [stepindex] => int //Step Number
     * 
     */
    function lmstoolUsertoursStepShown($tourid, $context, $pageurl, $stepid, $stepindex)
    {

        $functionname = 'tool_usertours_step_shown';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('tourid' => $tourid, 'context' => $context, 'pageurl' => $pageurl, 'stepid' => $stepid, 'stepindex' => $stepindex));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    /**
     * [action] => string //Action
     * 
     * [dir] => string //Plugin that is being edited
     * 
     * [table] => string //Table name
     * 
     * [field] => string Varsayılan değer "" //Field name
     * 
     * [key] => string Varsayılan değer "" //Key name
     * 
     * [index] => string Varsayılan değer "" //Index name
     * 
     * [position] => int //How many positions to move by (negative - up, positive - down)
     * 
     */
    function lmstoolXmldbInvokeMoveAction($action, $dir, $table, $field = "", $key = "", $index = "", $position)
    {

        $functionname = 'tool_xmldb_invoke_move_action';
        
        $serverurl = $this->setServerAddress . '/webservice/rest/server.php?wstoken=' . $this->setToken . '&wsfunction='.$functionname;
        
        $resp = $this->postViaCurl($serverurl . $this->restformat, array('action' => $action, 'dir' => $dir, 'table' => $table, 'field' => $field, 'key' => $key, 'index' => $index, 'position' => $position));
        
        $resp = json_decode($resp);
        
        return $resp;
    }
    
    private function objectToArray($data) {

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map('self::objectToArray', $data);
        }
        else {
            return $data;
        }
    }
    
    private function postViaCurl($url, $data) {
		$curl = curl_init();
		if (!$curl) die("Curl oturumu acilamadı, curl kütüphanesini kontrol ediniz.");
		
		$dataString = http_build_query($data);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);
		curl_setopt($curl, CURLE_OPERATION_TIMEOUTED, 300);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
		curl_setopt($curl, CURLOPT_URL, $url);
		
		$result = curl_exec ( $curl );
		curl_close ( $curl );
		return $result;
	}
}
?>