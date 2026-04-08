<?php
ob_start();

$answers = [
    "It is certain.",
    "Without a doubt.",
    "Yes, definitely.",
    "Signs point to yes.",
    "Outlook good.",
    "Ask again later.",
    "Better not tell you now.",
    "Cannot predict now.",
    "My sources say no.",
    "Very doubtful."
];

$answer = $answers[array_rand($answers)];

$answer = strip_tags($answer);
$answer = htmlspecialchars($answer, ENT_QUOTES | ENT_XML1, 'UTF-8');

ob_end_clean();

header("Content-Type: text/xml; charset=UTF-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<CiscoIPPhoneText>
  <Title>Magic 8-Ball</Title>
  <Prompt>Shake it!</Prompt>
  <Text><?php echo $answer; ?></Text>

  <SoftKeyItem>
    <Name>Roll Again</Name>
    <URL>http://YOUR_SERVER_IP/xmlservices/8ball.php</URL>
    <Position>1</Position>
  </SoftKeyItem>
  <SoftKeyItem>
    <Name>Exit</Name>
    <URL>SoftKey:Exit</URL>
    <Position>3</Position>
  </SoftKeyItem>
</CiscoIPPhoneText>
