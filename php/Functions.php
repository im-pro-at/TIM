<?php
/*
  Functions.php
  
  Autor: im-pro
*/

$GLOBALS['debug']="";
function print_debug($system,$message)
{
  if(enable_debug)
  {
    $GLOBALS['debug'].="--------".$system."----------\n".$message."\n\n";    
  }
} 

function texttypo($text)
{
  $output=array();
  $text=strtolower($text);
  
  //create splitts:
  $splits=array();
  for($i=0;$i<strlen($text);$i++)
  {
    array_push($splits,array(substr($text,0,$i),substr($text,-strlen($text)+$i)));      
  }
  array_push($splits,array($text,""));        
  
  //remove 1 Letter
  foreach($splits as $split)
  {
    if(strlen($split[1])>1)
    {
      $string=$split[0].substr($split[1],1); 
      if(array_search($string,$output)===False)
          array_push($output,$string);    
    }
  }
  
  //tow letters mixed
  foreach($splits as $split)
  {
    if(strlen($split[1])>=2)
    {
      $string=$split[0].substr($split[1],1,1).substr($split[1],0,1).substr($split[1],2); 
      if(array_search($string,$output)===False)
          array_push($output,$string);    
    }
  }
  
  //replace 1 letter
  foreach($splits as $split)
  {
    if(strlen($split[1])>=1)
    {
      for($i=0;$i<26;$i++)
      {
        $string=$split[0].chr(97+$i).substr($split[1],1); 
        if(array_search($string,$output)===False)
            array_push($output,$string);    
      }
    }
  }
  
  //add 1 letter
  foreach($splits as $split)
  {
    for($i=0;$i<26;$i++)
    {
      $string=$split[0].chr(97+$i).$split[1]; 
      if(array_search($string,$output)===False)
          array_push($output,$string);    
    }
  }  
  
  return $output;
}


$ErrorMailsend=false;
//Bei Fehlern in der Datenbank oder bei anderen Sachen hier Posten (Es wird auch eine E-Mail an den Autor gesendet)
function error_event_schreiben($system,$meldung)  //user=-1 bei systemänderung
{  
  global $ErrorMailsend;
  
  if ($ErrorMailsend==false and enable_debug!=true)
  {
    //E-Mail an den Administrator:
    //Mail zusammenstellen
    $_IP=$_SERVER['REMOTE_ADDR'];
    $mail="<p><font size=\"5\"><b>Nachricht von</b> ".htmlentities("").",</font></p>";
    $mail=$mail."\n";
    $mail=$mail."<p>IP: ".$_IP."!</p>";
    $mail=$mail."\n";
    $mail=$mail."<p>Nachricht: ".$system."</p>";
    $mail=$mail."\n";
    $mail=$mail."<p></p>";
    $mail=$mail."\n";
    $mail=$mail."<p>".nl2br(htmlentities($meldung))."</p>";
    $mail=$mail."\n";
    $mail=$mail."<p></p>";
    $mail=$mail."\n";
    $mail=$mail."<p>ENDE</p>";
    $betreff="Error ".$_SERVER['HTTP_HOST']."!";
    $header="";
    $headers .= "From:Admin<admin@".$_SERVER['HTTP_HOST'].">\n";
    $headers .= "Reply-To:Admin<admin@".$_SERVER['HTTP_HOST'].">\n";
    $headers .= "X-Mailer: PHP/" . phpversion(). "\n";
    $headers .= "X-Sender-IP: ".$_SERVER['REMOTE_ADDR']."\n";
    $headers .= "Content-type: text/html\n";
    if(error_event_email!="")
      mail(error_event_email, $betreff, $mail , $headers);
    $ErrorMailsend=true;
  }

  error($meldung);

}

//Errorhaendling:
function myErrorHandler($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile, $errcontext )
{
  //only send errors 
  switch ($fehlercode) {
    case E_ERROR:
    case E_CORE_ERROR:
    case E_COMPILE_ERROR:
        break;
    case E_WARNING:
    case E_CORE_WARNING:
    case E_COMPILE_WARNING:
    case E_RECOVERABLE_ERROR:
    case E_USER_NOTICE:
        print_debug("WARING","[$fehlercode] $fehlertext, in line $fehlerzeile of $fehlerdatei");
        return true;
    default:
        print_debug("NOTE","[$fehlercode] $fehlertext, in line $fehlerzeile of $fehlerdatei");
        return true;
    }
  
    $Email_Text="";
    $Email_Text.="ERROR: [$fehlercode] $fehlertext, in line $fehlerzeile of $fehlerdatei\n";

    $Email_Text.="_POST     ".print_r($_POST,true);
    $Email_Text.="_GET     ".print_r($_GET,true);
    
    @error_event_schreiben('Error N.r.: '.$fehlercode,$Email_Text);
    
    return $Errorhandling;
}

function error($message)
{
  global $sql;
  $output["error"]=$message;
  senddata($output);
  //Vor dem Beenden durchführen:
  if(!is_null($sql))
    $sql->close();
  die();
}

function senddata($data)
{
  $json = new Services_JSON();
  if(enable_debug)
  {
    $data["debug"]=$GLOBALS['debug'];
  }
  $outputjson=$json->encode($data);
  header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
  header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
  header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
  header( 'Cache-Control: post-check=0, pre-check=0', false ); 
  header( 'Content-type: application/jsonp; charset=utf-8');
  header( 'Pragma: no-cache; Cache-control: no-cache, no-store');
  echo $_GET['callback']."(".$outputjson.")";
}


?>