<?
$SERVER_NAME = $_SERVER['HTTP_HOST'];
if (mail("wanderer.kh@gmail.com", "the subject", "message2",
 "From: webmaster@$SERVER_NAME", "-fwebmaster@$SERVER_NAME"))
echo "sent "."From: webmaster@$SERVER_NAME";
?>