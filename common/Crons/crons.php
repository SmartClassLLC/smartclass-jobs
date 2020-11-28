<?php

//check if SMARTCLASS defined
if (!defined("SMARTCLASS")) {
    header("Location: index.php");
    die("SmartClass Undefined!");
}

$cron = $argv[1];

if (!empty($cron)) {
    include __DIR__ . "/ajax/" . $cron . ".php";
} else {
    $everyOtherHour = ["0000", "0200", "0400", "0600", "0800", "1000", "1200", "1400", "1600", "1800", "2000", "2200"];

    // Runtime
    $runTime = date("Hi");

    include __DIR__ . "/ajax/emails.php";
    include __DIR__ . "/ajax/announcements.php";
    include __DIR__ . "/ajax/homeworks.php";
    include __DIR__ . "/ajax/exams.php";
    include __DIR__ . "/ajax/exams_stats.php";

    //include __DIR__  . "/ajax/data/menu.php";
    if ($runTime == "2250" || $runTime == "2251") {
        include __DIR__ . "/ajax/attendance.php";
    }
    if ($runTime == "0045" || $runTime == "0046") {
        include __DIR__ . "/ajax/usage.php";
    }
    if ($runTime == "0100" || $runTime == "0101") {
        include __DIR__ . "/ajax/stats.php";
    }
    if ($runTime == "0110" || $runTime == "0111") {
        include __DIR__ . "/ajax/lms.php";
    }
    if ($runTime == "0120" || $runTime == "0121") {
        include __DIR__ . "/ajax/logs.php";
    }
    if ($runTime == "0130" || $runTime == "0131") {
        include __DIR__ . "/ajax/locale.php";
    }
    if ($runTime == "0140" || $runTime == "0141") {
        include __DIR__ . "/ajax/menu.php";
    }
    if ($runTime == "0150" || $runTime == "0151") {
        include __DIR__ . "/ajax/update.php";
    }
    if ($runTime == "0200" || $runTime == "0201") {
        include __DIR__ . "/ajax/user_type.php";
    }
    if ($runTime == "0700" || $runTime == "0701") {
        include __DIR__ . "/ajax/translation.php";
    }
    if (in_array($runTime, $everyOtherHour)) {
        include __DIR__ . "/ajax/virtual_classes.php";
    }
}
