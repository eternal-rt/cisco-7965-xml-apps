<?php
error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set("America/Chicago");

header("Content-Type: text/xml");

$server = "http://YOUR_SERVER_IP:8096";
$api_key = "JELLYFIN_API_KEY";

function trimLine($str, $len = 22) {
    return strlen($str) > $len ? substr($str, 0, $len - 3) . "..." : $str;
}

function xmlSafe($string) {
    return preg_replace('/[^\x09\x0A\x0D\x20-\x7F]/', '', $string);
}

function formatTime($ticks) {
    if (!$ticks || $ticks <= 0) return "00m";
    $seconds = $ticks / 10000000;
    $minutes = floor($seconds / 60);
    return str_pad($minutes, 2, "0", STR_PAD_LEFT) . "m";
}

function getEndTime($pos, $run) {
    if (!$pos || !$run) return "";

    $remaining = $run - $pos;
    if ($remaining <= 0) return "";

    $remainingSeconds = $remaining / 10000000;
    $end = strtotime("+$remainingSeconds seconds");

    return strtolower(date("g:ia", $end));
}

$response_raw = @file_get_contents("$server/Sessions?api_key=$api_key");
$response = $response_raw ? json_decode($response_raw, true) : null;

$text = "Idle\n(No active media)";

if ($response === null) {
    $text = "Jellyfin offline";
} else {
    foreach ($response as $session) {
        if (
            isset($session['NowPlayingItem']) &&
            isset($session['PlayState']) &&
            !$session['PlayState']['IsPaused']
        ) {
            $item = $session['NowPlayingItem'];
            $type = $item['Type'] ?? "";

            $title = trimLine($item['Name'] ?? "Unknown");
            $user = trimLine($session['UserName'] ?? "Unknown");

            $progress = "";
            $endTime = "";

            if (isset($session['PlayState']['PositionTicks']) && isset($item['RunTimeTicks'])) {
                $pos = $session['PlayState']['PositionTicks'];
                $run = $item['RunTimeTicks'];

                $progress = formatTime($pos) . "/" . formatTime($run);
                $endTime = getEndTime($pos, $run);
            }

            if ($type === "Movie") {
                $year = $item['ProductionYear'] ?? "N/A";

                $text = "Movie: $title";
                $text .= "\nYear: $year";
            } elseif ($type === "Episode") {
                $series = trimLine($item['SeriesName'] ?? "Unknown");
                $season = $item['ParentIndexNumber'] ?? "?";
                $episode = $item['IndexNumber'] ?? "?";

                $text = "Show: $series";
                $text .= "\nEpisode: S{$season}E{$episode}";
                $text .= "\nTitle: $title";
            } elseif ($type === "Audio") {
                $artist = trimLine($item['Artists'][0] ?? "Unknown");

                $text = "Song: $title";
                $text .= "\nArtist: $artist";
            } else {
                $text = "Playing: $title";
            }

            if ($progress) {
                $text .= "\nNow Playing - $progress";
            }

            if ($endTime) {
                $text .= "\nEnds at: $endTime";
            }

            $text .= "\nName: $user";

            break;
        }
    }
}

$text = xmlSafe($text);
$text = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<CiscoIPPhoneText>
  <Title>Jellyfin</Title>
  <Text><?php echo $text; ?></Text>

  <SoftKeyItem>
    <Name>Refresh</Name>
    <URL>http://YOUR_SERVER_IP/xmlservices/jellyfin.php</URL>
    <Position>1</Position>
  </SoftKeyItem>

  <SoftKeyItem>
    <Name>Exit</Name>
    <URL>SoftKey:Exit</URL>
    <Position>3</Position>
  </SoftKeyItem>
</CiscoIPPhoneText>
