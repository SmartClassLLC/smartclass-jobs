<?

function time_duration($seconds, $use = null, $zeros = false)
{
    // Define time periods
    $periods = array (
        _YIL	=> 31556926,
        _AY		=> 2629743,
        _HAFTA	=> 604800,
        _GUN	=> 86400,
        _SA		=> 3600,
        _DK		=> 60,
        _SN		=> 1
	);

    // Break into periods
    $seconds = (float) $seconds;
    $segments = array();
    foreach ($periods as $period => $value) {
        if ($use && strpos($use, $period[0]) === false) {
            continue;
        }
        $count = floor($seconds / $value);
        if ($count == 0 && !$zeros) {
            continue;
        }
        $segments[strtolower($period)] = $count;
        $seconds = $seconds % $value;
    }

    // Build the string
    $string = array();
    foreach ($segments as $key => $value) {
        //$segment_name = substr($key, 0, -1);
        $segment_name = $key;
        $segment = $value . ' ' . $segment_name;
        $string[] = $segment;
    }

    return implode(', ', $string);
}

?>
