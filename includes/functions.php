<?php
// includes/functions.php
// Basic helpers if needed

function time_elapsed_string($datetime, $full = false)
{
    if ($datetime == '0000-00-00 00:00:00')
        return "Just now";

    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks manually since DateInterval doesn't have 'w' property
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    // Map for values
    $string = array(
        'y' => ['val' => $diff->y, 'unit' => 'year'],
        'm' => ['val' => $diff->m, 'unit' => 'month'],
        'w' => ['val' => $weeks, 'unit' => 'week'],
        'd' => ['val' => $days, 'unit' => 'day'],
        'h' => ['val' => $diff->h, 'unit' => 'hour'],
        'i' => ['val' => $diff->i, 'unit' => 'minute'],
        's' => ['val' => $diff->s, 'unit' => 'second'],
    );

    foreach ($string as $k => &$v) {
        if ($v['val']) {
            $v = $v['val'] . ' ' . $v['unit'] . ($v['val'] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>