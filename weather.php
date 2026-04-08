<?php
date_default_timezone_set('America/New_York');

$lat = 40.7128;
$lon = -74.0060;

$view = isset($_GET['view']) ? $_GET['view'] : 'current';

if ($view == "hourly") {
    $url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&hourly=temperature_2m&temperature_unit=fahrenheit";
} elseif ($view == "weekly") {
    $url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&daily=temperature_2m_max,temperature_2m_min&temperature_unit=fahrenheit&timezone=auto";
} else {
    $url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current_weather=true&temperature_unit=fahrenheit";
}

$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : null;

$text = "Weather Unavailable";

if ($view == "current") {
    if ($data && isset($data['current_weather'])) {
        $cw = $data['current_weather'];
        $temp = round($cw['temperature']);
        $wind = round($cw['windspeed']);
        $condition = "Unknown";

        switch ($cw['weathercode']) {
            case 0: $condition = "Clear"; break;
            case 1:
            case 2:
            case 3: $condition = "Cloudy"; break;
            case 45:
            case 48: $condition = "Fog"; break;
            case 61:
            case 63:
            case 65: $condition = "Rain"; break;
            case 71:
            case 73:
            case 75: $condition = "Snow"; break;
            case 95: $condition = "Storm"; break;
        }

        $text =
"Temp: {$temp}°F
Cond: {$condition}
Wind: {$wind} mph
Updated: " . date("H:i");
    }
} elseif ($view == "hourly") {
    if ($data && isset($data['hourly']['temperature_2m'])) {
        $text = "Next Hours:\n";
        for ($i = 0; $i < 5; $i++) {
            $time = substr($data['hourly']['time'][$i], 11, 5);
            $temp = round($data['hourly']['temperature_2m'][$i]);
            $text .= "{$time} - {$temp}°F\n";
        }
    }
} elseif ($view == "weekly") {
    if ($data && isset($data['daily']['temperature_2m_max'])) {
        $text = "7 Day Forecast:\n";
        for ($i = 0; $i < 5; $i++) {
            $day = date("D", strtotime($data['daily']['time'][$i]));
            $max = round($data['daily']['temperature_2m_max'][$i]);
            $min = round($data['daily']['temperature_2m_min'][$i]);
            $text .= "{$day}: {$max}/{$min}\n";
        }
    }
}
?>

<CiscoIPPhoneText>
  <Title>Weather</Title>
  <Prompt>New York, NY</Prompt>
  <Text><?php echo htmlspecialchars($text); ?></Text>
  <SoftKeyItem>
    <Name>Now</Name>
    <URL>http://YOUR_SERVER_IP/xmlservices/weather.php?view=current</URL>
    <Position>1</Position>
  </SoftKeyItem>
  <SoftKeyItem>
    <Name>Hourly</Name>
    <URL>http://YOUR_SERVER_IP/xmlservices/weather.php?view=hourly</URL>
    <Position>2</Position>
  </SoftKeyItem>
  <SoftKeyItem>
    <Name>Weekly</Name>
    <URL>http://YOUR_SERVER_IP/xmlservices/weather.php?view=weekly</URL>
    <Position>3</Position>
  </SoftKeyItem>
  <SoftKeyItem>
    <Name>Exit</Name>
    <URL>SoftKey:Exit</URL>
    <Position>4</Position>
  </SoftKeyItem>
</CiscoIPPhoneText>
