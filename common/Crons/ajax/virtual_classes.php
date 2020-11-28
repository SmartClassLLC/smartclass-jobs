<?

//check if SMARTCLASS defined
if(!defined("SMARTCLASS")) { header ("Location: index.php"); die("SmartClass Undefined!"); }

//autoload
require_once "vendor/autoload.php";

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\GetRecordingsParameters;

echo PHP_EOL . "Virtual Classes Replay URL Update Run time: ". date("Y-m-d H:i:s") . PHP_EOL;

//get all instances in the server
$instances = $dbi->get("smartclass_common.instances");
foreach($instances as $instance) {
    $i = 0;
	$insTitle = $instance["title"];
	$insPrefix = $instance["prefix"];
	$mainDB = $insPrefix . "_main";
	
	echo "Working on the instance: " . $insTitle . PHP_EOL;

	//get default season db
	$dbi->where("ontanimli", "on");
	$dbi->where("aktif", "on");
	$seasonDB = $dbi->getValue($mainDB . ".seasons", "veritabani");
	
	echo "Checking the season: " . $seasonDB . PHP_EOL;
	
	//include tables' file
    include __DIR__ . "/../../../settings/tables_cli.php";

    //get schools
    $dbi->where("aktif", "1");
    $dbi->orderBy("subeid", "asc");
    $schools = $dbi->get($schoolsTable, null, "subeid, countryCode");

    foreach($schools as $school) {
        $schoolId = $school["subeid"];

        echo "Working on the school: " . $schoolId . PHP_EOL;

        // Get BigBlueButton config
        $dbi->where("schoolId", array("0", $schoolId), "IN");
        $dbi->orderBy("schoolId", "desc");
        $bbbConfig = $dbi->getOne($mainDB.".bbb_config");

        $bbbServerUrl = $bbbConfig["serverUrl"];
        $bbbSecuritySalt = $bbbConfig["securitySalt"];

        putenv("BBB_SECRET=$bbbSecuritySalt");
        putenv("BBB_SERVER_BASE_URL=$bbbServerUrl");
        // Create bbb object
        $bbb = new BigBlueButton($bbbSecuritySalt, $bbbServerUrl);

        $dbi->where("replayUrl='' OR replayUrl IS NULL");
        $getVirtualClasses = $dbi->get($virtualClassesTable);

        foreach ($getVirtualClasses as $virtualClass) {
            $recordingParams = new GetRecordingsParameters();
            $recordingParams->setMeetingId($virtualClass["virtualClassId"]);
            $recording = $bbb->getRecordings($recordingParams);

            if ($recording->getReturnCode() == 'SUCCESS' && $recording->getMessageKey() != 'noRecordings') {
                $recordingUrl = $recording->getRecords()[0]->getPlaybackUrl();

                $updateReplayUrl = $dbi->where("Id", $virtualClass["Id"])->update($virtualClassesTable, array('replayUrl' => $recordingUrl));

                if ($updateReplayUrl) $i++;
            }
        }
    }
	
	echo "Finished replay URL update. " .$i. " row has been updated."  . PHP_EOL;
}

echo "Done." . PHP_EOL;
