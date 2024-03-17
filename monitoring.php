<!--  
title : monitoring system using php 
date : 10/3/2024	
author: Boukhalfa Khaled Islam
-->

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require 'vendor/autoload.php';


	$operating_system = PHP_OS_FAMILY;

	if ($operating_system === 'Linux') {
		// Linux CPU
		$load = sys_getloadavg();
		$cpuload = $load[0];
		$numOfcors = shell_exec('nproc');
		// Linux MEM
		$free = shell_exec('free');
		$free = (string)trim($free);
		$free_arr = explode("\n", $free);
		$mem = explode(" ", $free_arr[1]);
		$mem = array_filter($mem, function($value) { return ($value !== null && $value !== false && $value !== ''); }); 
		$mem = array_merge($mem); 
		$memtotal = round($mem[1] / 1000000,2);
		$memused = round($mem[2] / 1000000,2);
		$memfree = round($mem[3] / 1000000,2);
		$memshared = round($mem[4] / 1000000,2);
		$memcached = round($mem[5] / 1000000,2);
		$memavailable = round($mem[6] / 1000000,2);
		// Connections
		$connections = `netstat -ntu | grep -E ':80 |443 ' | grep ESTABLISHED | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`; 
		$totalconnections = `netstat -ntu | grep -E ':80 |443 ' | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`; 
	} else {
    echo "monitoring works on linux only at the moment, it will be on windows soon!";
	}

	$memusage = round(($memused/$memtotal)*100);		


	$phpload = round(memory_get_usage() / 1000000,2);

	// disk status
	$diskfree = round(disk_free_space(".") / 1000000000);
	$disktotal = round(disk_total_space(".") / 1000000000);
	$diskused = round($disktotal - $diskfree);
	$diskusage = round($diskused/$disktotal*100);

	 if ($memusage > 85 || $cpuload > 85 || $diskusage > 85) {
		$mail = new PHPMailer(true);

			try {
				//Server settings
				$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
				$mail->isSMTP();                                            //Send using SMTP
				$mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
				$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
				$mail->Username   = 'user@example.com';                     //SMTP username
				$mail->Password   = 'secret';                               //SMTP password
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
				$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				
				//Attachments
				// $mail->addAttachment('/var/tmp/file.tar.gz');
				// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');
		
				//Recipients
				$mail->setFrom('from@example.com', 'Mailer');
				$mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
				$mail->addAddress('ellen@example.com');               //Name is optional
				$mail->addReplyTo('info@example.com', 'Information');
				$mail->addCC('cc@example.com');
				$mail->addBCC('bcc@example.com');
		

					$bodyMail= "You may have a RAM or CPU problem  <br/> CPU : {$cpuload}% <br/> RAM :{$memusage}%";
					//Content
					$mail->isHTML(false);
					$mail->Subject = 'server Alert';
					$mail->Body    = $bodyMail;
					$mail->AltBody = 'From Monitoring script message alert about ram and cpu';

					$mail->send();
			} catch (Exception $e) {
					echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			}

	 }


	// use servercheck.php?json=1 [api]
	if (isset($_GET['json'])) {
		echo '{ "disktotal":'.$disktotal.',"diskUsed":'.$diskused.', "diskFree":'.$diskfree.', "memAvailable":'.$memavailable.' ,"memUsed":'.$memused.' ,"memTotal":'.$memtotal.', "numberOfCores":'.$numOfcors.', "ram":'.$memusage.',"cpu":'.$cpuload.',"disk":'.$diskusage.',"connections":'.$totalconnections.'}';
		exit;
	}
?>