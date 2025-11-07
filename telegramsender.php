<?php

require_once(INCLUDE_DIR.'class.signal.php');
require_once(INCLUDE_DIR.'class.plugin.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.osticket.php');
require_once(INCLUDE_DIR.'class.config.php');
require_once(INCLUDE_DIR.'class.format.php');
require_once(INCLUDE_DIR.'class.dynamic_forms.php');
require_once(INCLUDE_DIR.'ost-config.php');
require_once('config.php');

class TelegramPlugin extends Plugin {
    var $config_class = 'TelegramPluginConfig';
//Aleks private $tgurl = "https://api.telegram.org/bot6654298086:AAHubLB3j_trBYOflW_vFtqFHGlSyC3MRKM/sendMessage";
private $chatid;
private $count = 1;

    function bootstrap() {
global $ib;
global $tgurl;
global $chatid;
global $debug;
    	Signal::connect('ticket.created', array($this, 'onTicketCreated'));
	Signal::connect('object.edited', array($this, 'onTicketClosed'));
//	Signal::connect('object.edited', function($object)
//{
//    // Check if the object is a ticket and its status is now closed
//    if ($object instanceof Ticket && $object->isClosed()) {
//        // Perform actions when a ticket is closed
//        // For example, log the event, send a custom notification, etc.
//    //    error_log('Ticket ' . $object->getNumber() . ' was closed.');
//$this->onTicketClosed($object);
//    }
//});

$config = $this->getConfig();

$tgurl = $config->get('tgURL');
$chatid = "-". $config->get('tgChatId');
$debug = $config->get('tgDebug');
$ib = $config->get('tgIncludeBody');

}

	function onTicketCreated(Ticket $ticket)
	{
		global $ost; 
global $cfg;
global $ib;
global $chatid;

		$ticketLink = $ost->getConfig()->getUrl().'scp/tickets.php?id='.$ticket->getId();
		$ticketId = $ticket->getNumber();
$user = $ticket->getName();
$userPhone = $ticket->getOwner()->getPhoneNumber();
//$dynamicData = $ticket->getDynamicData();
//$form = DynamicFormEntry::lookupForTicket($ticket->getId());
//if ($form) {
//        $customFieldValue = $form->get('client');
//    }
$answers = $ticket->loadDynamicData();
//$myfile = fopen("/var/www/html/osticket/include/plugins/ales.txt", "w") or die("Unable to open file");
//fwrite($myfile, implode(" 777 ",array_keys($answers)));
foreach( array_keys($answers) as $name7) {
if (strpos($name7, "ol7") ===0 && mb_strlen($ticket->getAnswer($name7),'UTF-8')>0) {
$client = $ticket->getAnswer($name7);
}
}
$telephone = $ticket->getAnswer('telephone');
//fclose($myfile);
//$client = $ticket->getAnswer('energo');
        $title = $ticket->getSubject() ?: 'No subject';
		$createdBy = $ticket->getName()." (".$ticket->getEmail().")";
        $assignee = $ticket->getAssignee() ?: 'No assignee';
$HelpTopic = $ticket->getHelpTopic();
//		$chatid = $this->getConfig()->get('telegram-chat-id');
		$chatid = is_numeric($chatid)?"-".$chatid:"@".$chatid;
        if ($ib) {
            $body = $ticket->getLastMessage()->getMessage() ?: 'No content';
			$body = str_replace('<p>', '', $body);
			$body = str_replace('</p>', '<br />' , $body);
			$breaks = array("<br />","<br>","<br/>");
			$body = str_ireplace($breaks, "\n", $body);
			$body = preg_replace('/\v(?:[\v\h]+)/', '', $body);
            $body = strip_tags($body);
        }
//$myclient = $ticket->getCustomFieldValue('client');
		$this->sendToTelegram(
			array(
//				"method" => "sendMessage",
				"chat_id" => $chatid,
				"text" => "&#x2757 <b>Нова заявка:</b> <a href=\"".$ticketLink."\">#".$ticketId."</a>\n<b>".$HelpTopic."\nКлієнт : </b> ".$client."\n<b>Телефон: </b> ".$telephone."\n<b>Здав: </b> ".$user.". Тел. ".$userPhone."\n<b>Для :</b> ".$assignee."\n<b>Тема:</b> ".$title.($body?"\n<b>Детально: </b>".$body:''),
				"parse_mode" => "HTML",
				"disable_web_page_preview" => true,
			)
		);
	}


	function onTicketClosed($object)
	{
		global $ost; 
global $cfg;
global $ib;
global $chatid;
global $count;

 if ($object instanceof Ticket && $object->isClosed() && $count < 1) {
 $ticket = $object;
		$ticketLink = $ost->getConfig()->getUrl().'scp/tickets.php?id='.$ticket->getId();
		$ticketId = $ticket->getNumber();
$user = $ticket->getName();
$userPhone = $ticket->getOwner()->getPhoneNumber();
$answers = $ticket->loadDynamicData();
foreach( array_keys($answers) as $name7) {
if (strpos($name7, "ol7") ===0 && mb_strlen($ticket->getAnswer($name7),'UTF-8')>0) {
$client = $ticket->getAnswer($name7);
}
}
$telephone = $ticket->getAnswer('telephone');
        $title = $ticket->getSubject() ?: 'No subject';
        $assignee = $ticket->getAssignee() ?: 'No assignee';
$HelpTopic = $ticket->getHelpTopic();
$entries = $ticket->getThread()->getEntries();
foreach ($entries as $entry) {
$body = $entry->getBody()->getClean();
			$body = str_replace('<p>', '', $body);
			$body = str_replace('</p>', '<br />' , $body);
			$breaks = array("<br />","<br>","<br/>");
			$body = str_ireplace($breaks, "\n", $body);
			$body = preg_replace('/\v(?:[\v\h]+)/', '', $body);
            $body = strip_tags($body);

$person = $entry->getPoster();
}


 error_log($ticketId." ". $count.' was closed.'.$body);
		$this->sendToTelegram(
			array(
				"chat_id" => $chatid,
				"text" => "&#x2705 <b>Заявка закрита :</b> <a href=\"".$ticketLink."\">#".$ticketId."</a>\n<b>".$HelpTopic."\nКлієнт : </b> ".$client."\n<b>Телефон: </b> ".$telephone."\n<b>Здав: </b> ".$user.". Тел. ".$userPhone."\n<b>Тема:</b> ".$title.($body?"\n<b>Результат: </b>".$body:'')."<b>Закрив: </b>".$person,
				"parse_mode" => "HTML",
				"disable_web_page_preview" => true,
			)
		);
$count = $count+1;	}
}


	function sendToTelegram($payload)
    {
        try {
global $tgurl;
            global $ost, $cfg;
global $ib;
global $debug;
            $data_string = json_encode($payload);
// $decoded_string = preg_replace_callback('/\\\\x([0-9a-fA-F]{2})/', function($matches) {
//        return hex2bin($matches[1]);
//    }, $data_string);
//$data_string = $decoded_string;

//            $url = $this->getConfig()->get('tgURL');

//Aleks       $ch = curl_init("https://api.telegram.org/bot6654298086:AAHubLB3j_trBYOflW_vFtqFHGlSyC3MRKM/sendMessage");
$ch = curl_init($tgurl."/sendMessage");
//            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string)
                )
            );
$myfile = fopen("/var/www/html/osticket/include/plugins/aleks.txt", "w") or die("Unable to open file");
$mystring = $tgurl . " url\n";
fwrite($myfile, $mystring);
$txt = $data_string . " datastring\n";
fwrite($myfile, $txt);
$txt1 = $debug. " debug";
fwrite($myfile, $txt1);
fclose($myfile);
            $result = curl_exec($ch);
            if ($result === false) {
                throw new Exception($this->$tgurl . ' - ' . curl_error($ch));
            } else {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);












                if ($statusCode != '200') {
                    throw new Exception($this->$tgurl . ' Http code: ' . $statusCode);
                }	
            }

            curl_close($ch);
		    if ($debug) {
			    error_log ($result);
		    }
        } catch(Exception $e) {
            error_log('Error posting to Telegram. '. $e->getMessage());
        }
    }

//    function escapeText($text)
//    {
//        $text = str_replace('&', '&amp;', $text);
//        $text = str_replace('<', '&lt;', $text);
//        $text = str_replace('>', '&gt;', $text);
//
//        return $text;
//    }
}
