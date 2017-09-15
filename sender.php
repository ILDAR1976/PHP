<?php

class Sender {

	private $smtp_host = 'smtp.mail.ru';
	private $smtp_port = '465';
	private $user      = "";
	private $pass      = "";
        private $path_cert = "./cacert.pem";
	private $domain    = "www.mail.ru";

function setTransport($smtp_host_in, $smtp_port_in, $user_in, $pass_in){
	$this->smtp_host = $smtp_host_in;
	$this->smtp_port = $smtp_port_in;
	$this->user      = $user_in;
	$this->pass      = $pass_in;
}

function setCertificate($path_cert_in, $domain_in){
	$this->path_cert = $path_cert_in;
	$this->domain    = $domain;	
}

function smtp_mail($from, $to, $subject, $message, $headers = '')
{
    $recipients = explode(',', $to);
 
    $email = $to;
    
    $context = stream_context_create(
     array(
      'ssl' => array(
        'verify_peer' => false,
        'cafile' => $this->path_cert,
        'peer_name' => $this->domain,
        'ciphers' => 'HIGH:!SSLv2:!SSLv3',
        'disable_compression' => true,
       )
     )
    );
    
    if (!($socket = stream_socket_client('ssl://' . $this->smtp_host . ':' . $this->smtp_port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $context)))
    {
      echo 'Error connecting to ' . $this->smtp_host . 'error number: ' . $errno . ' content: ' . $errstr;
    }

    $this->server_parse($socket, '220');
 
    fwrite($socket, 'EHLO '.$this->smtp_host."\r\n");
    $this->server_parse($socket, '250');
 
    fwrite($socket, 'AUTH LOGIN'."\r\n");
    $this->server_parse($socket, '334');
 
    fwrite($socket, base64_encode($this->user)."\r\n");
    $this->server_parse($socket, '334');
 
    fwrite($socket, base64_encode($this->pass)."\r\n");
    $this->server_parse($socket, '235');
 
    fwrite($socket, 'MAIL FROM: <'.$from.'>'."\r\n");
    $this->server_parse($socket, '250');
 
    foreach ($recipients as $email)
    {
        fwrite($socket, 'RCPT TO: <'.$email.'>'."\r\n");
        $this->server_parse($socket, '250');
    }
 
    fwrite($socket, 'DATA'."\r\n");
    $this->server_parse($socket, '354');
 
    fwrite($socket, 'Subject: '
      .$subject."\r\n".'To: <'.implode('>, <', $recipients).'>'
      ."\r\n".$headers."\r\n\r\n".$message."\r\n");
 
    fwrite($socket, '.'."\r\n");
    $this->server_parse($socket, '250');
 
    fwrite($socket, 'QUIT'."\r\n");
    fclose($socket);
 
    return true;
}

function server_parse($socket, $expected_response)
{
    $server_response = '';
    while (substr($server_response, 3, 1) != ' ')
    {
        if (!($server_response = fgets($socket, 256)))
        {
          echo 'Error while fetching server response codes.', __FILE__, __LINE__;
        }            
    }
 
    if (!(substr($server_response, 0, 3) == $expected_response))
    {
      echo 'Unable to send e-mail."'.$server_response.'"', __FILE__, __LINE__;
    }
}

function test_send() {
 if($this->smtp_mail('','', 'Test Mail Subject', 'Test email subject.'))
  {
    echo "Email Sent Successfully.";
  }
    
  else
  {
    echo "Oops! Error Sending Email.";
  }
}
}
 ?>