<?php
header("Content-Type: text/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';

$feeds = [
    "local" => "https://abc7ny.com/feed/",
    "us"    => "https://abcnews.go.com/abcnews/topstories"
];

function ciscoTitle($text, $len = 45) {
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = mb_substr($text, 0, $len, 'UTF-8');
    return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function fetchItems($url, $limit = 7) {
    $items = [];
    $resp = @file_get_contents($url);
    if ($resp !== false) {
        $xml = @simplexml_load_string($resp);
        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $items[] = [
                    "title" => (string)$item->title,
                    "desc"  => strip_tags((string)($item->description ?? $item->content ?? ''))
                ];
                if (count($items) >= $limit) break;
            }
        }
    }
    return $items;
}

$local_items = fetchItems($feeds['local'], 6);
$us_items = fetchItems($feeds['us'], 7);
?>

<CiscoIPPhoneMenu>
  <Title>News</Title>
  <Prompt>Select Headline</Prompt>

  <MenuItem><Name>--- NYC News ---</Name><URL></URL></MenuItem>
<?php
if (empty($local_items)) {
    echo '<MenuItem><Name>Local feed unavailable</Name><URL></URL></MenuItem>';
} else {
    foreach ($local_items as $i => $item) {
        $title = ciscoTitle($item['title'], 45);
        echo "<MenuItem>";
        echo "<Name>" . ($i + 1) . ". $title</Name>";
        echo "<URL>http://YOUR_SERVER_IP/xmlservices/article.php?cat=local&amp;id=$i</URL>";
        echo "</MenuItem>";
    }
}
?>

  <MenuItem><Name>--- U.S. News ---</Name><URL></URL></MenuItem>
<?php
if (empty($us_items)) {
    echo '<MenuItem><Name>US feed unavailable</Name><URL></URL></MenuItem>';
} else {
    foreach ($us_items as $i => $item) {
        $title = ciscoTitle($item['title'], 45);
        echo "<MenuItem>";
        echo "<Name>" . ($i + 1) . ". $title</Name>";
        echo "<URL>http://YOUR_SERVER_IP/xmlservices/article.php?cat=us&amp;id=$i</URL>";
        echo "</MenuItem>";
    }
}
?>

  <SoftKeyItem>
    <Name>Refresh</Name>
    <URL>SoftKey:Update</URL>
    <Position>1</Position>
  </SoftKeyItem>

  <SoftKeyItem>
    <Name>Exit</Name>
    <URL>SoftKey:Exit</URL>
    <Position>3</Position>
  </SoftKeyItem>
</CiscoIPPhoneMenu>
