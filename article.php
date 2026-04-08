<?php
header("Content-Type: text/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cat = $_GET['cat'] ?? 'us';

$feeds = [
    "local" => "https://abc7ny.com/feed/",
    "us"    => "https://abcnews.go.com/abcnews/topstories"
];

$rss_url = $feeds[$cat] ?? $feeds['us'];

$title = "Article";
$content = "Unavailable";

$response = @file_get_contents($rss_url);
if ($response !== false) {
    $xml = @simplexml_load_string($response);
    if ($xml && isset($xml->channel->item[$id])) {
        $item = $xml->channel->item[$id];
        $title = mb_substr((string)$item->title, 0, 30, 'UTF-8');
        $desc = (string)($item->description ?? $item->content ?? '');
        $desc = strip_tags($desc);
        $desc = preg_replace('/\s+/', ' ', $desc);
        $desc = wordwrap($desc, 32, "\n", true);
        $content = mb_substr($desc, 0, 400, 'UTF-8');
    }
}
?>

<CiscoIPPhoneText>
  <Title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8'); ?></Title>
  <Prompt><?php echo strtoupper(htmlspecialchars($cat)); ?></Prompt>
  <Text><?php echo htmlspecialchars($content, ENT_QUOTES | ENT_XML1, 'UTF-8'); ?></Text>

  <SoftKeyItem>
    <Name>Back</Name>
    <URL>http://YOUR_SERVER_IP/xmlservices/news.php</URL>
    <Position>1</Position>
  </SoftKeyItem>

  <SoftKeyItem>
    <Name>Exit</Name>
    <URL>SoftKey:Exit</URL>
    <Position>3</Position>
  </SoftKeyItem>
</CiscoIPPhoneText>
