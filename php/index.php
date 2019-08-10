<?php
/*
  index.php
  
  Autor: im-pro
*/

//Settings
require_once ('../Settings.php');

//Module Laden
require_once ('Database.php');
require_once ('SQL.php');
require_once ('JSON.php');
require_once ('Functions.php');

$sql=null;

set_error_handler("myErrorHandler");

if(array_key_exists("event",$_GET))
  $_POST=$_GET;

//bei falschen aufruf 'amp;' ausbessern:
$_post_keys=array_keys($_POST);
foreach ($_post_keys as $_Key)
{
  if (strpos($_Key, 'amp;') !== false)
  {
    $_POST[str_replace('amp;','',$_Key)]=$_POST[$_Key];
    unset($_POST[$_Key]);
  }
}

//Mit Datenbank verbinden
$sql = new SQL(server_sql, benutzer_sql, passwort_sql, dbname_sql,"error_event_schreiben");

//check tables:
if(is_null($sql->query("SELECT id FROM user LIMIT 1",0)))
{
  //initilise database:
  $sql_querys=$Database_Tabels.$Database_Admin_Entry;
  $sql_querys=explode(";",$sql_querys);
  foreach($sql_querys as $sql_query)
  {
    $sql->query($sql_query);    
  }
  error_event_schreiben("Database","Database was initialized! \nUsername:admin \nPassword:admin");
}

if (isset($_POST['event']))
{
  $event=$_POST['event'];
}  
else
{
  //No event
  error("no event");
}

print_debug("CALL GET",print_r($_GET,true));
print_debug("CALL POST",print_r($_POST,true));

//User managment:
$passwordhash= hash('sha256', "_USER_".strtolower($_POST["name"]).$_POST["password"] );
$user=false;
if (isset($_COOKIE["cookie"]) and ctype_alnum($_COOKIE["cookie"]))
{
  //check cookie:
  $sqlresult=$sql->query("SELECT * FROM `user` WHERE `cookie` = '".$sql->escape($_COOKIE["cookie"])."'");
  if($sqlresult->num_rows()==1)
  {
    $userdata=$sqlresult->fetch();
    if($event!="logout")
    {
      $user=true;
      //update time:
      $sql->query("UPDATE `user` SET `lastaktive` = '".time()."' WHERE `cookie` = '".$sql->escape($_COOKIE["cookie"])."'");
      setcookie("cookie",$_COOKIE["cookie"],time()+60*60*24*356); //One Year!
    }
  }
}
if($user==false){
  $userdata=array("id"=>0,"name"=>"guest");
  setcookie("cookie","",time()-3600); //clear cookie!
}


//log functions
function getentrydata($id)
{
  global $sql;
	$sql_ergebniss=$sql->query('
		SELECT * 
		FROM 
			eintrag a 
		WHERE
			a.id = \''.$sql->escape($id).'\' 
	');
	$output=$sql_ergebniss->fetch();
	
	$output["tags"]=array();
	$sql_ergebniss=$sql->query('
		SELECT a.id, a.name, a.beschreibung 
		FROM tag a  INNER JOIN link b 
		ON a.id=b.tagid 
		WHERE b.eintragid = \''.$sql->escape($id).'\' 
		ORDER BY `name` ASC
  ');  
	while ($akt_user=$sql_ergebniss->fetch())
	{
		$output["tags"][]=$akt_user;    
	}
	return $output;
}
function addlog($eintragid, $changetype, $oldvalues, $newvalues)
{
	global $sql,$userdata;
	$json = new Services_JSON();
	$sql->query('INSERT INTO `log` (`eintragid`, `userid`, `changetype`, `oldvalues`, `newvalues`, `datum`) 
					VALUES (\''.$sql->escape($eintragid).'\', \''.$sql->escape($userdata["id"]).'\', \''.$sql->escape($changetype).'\', \''.$sql->escape($json->encode($oldvalues)).'\', \''.$sql->escape($json->encode($newvalues)).'\', \''.time().'\')');
}

$output=null;

if($event=="login")
{
  if($user==true)
  {
    //Schon angemeldet!
    error("already logged in!"); 
  }
  else
  {
    print_debug("Login","Try to login ".$_POST["name"]." hash ".$passwordhash);
    $sqlresult=$sql->query("SELECT * FROM `user` WHERE `name` = '".$sql->escape(strtolower($_POST["name"]))."' and `password` = '".$sql->escape($passwordhash)."'");
    if($sqlresult->num_rows()!=1){
      //User password not found
      error("user or password not found!"); 
    }
    else
    {
      $userdata=$sqlresult->fetch();
      $_COOKIE["cookie"]= hash('sha256', microtime()."_GUEST_".rand().$userdata["name"]);
      //update time:
      $sql->query("UPDATE `user` SET `lastaktive` = '".time()."' , `cookie` = '".$sql->escape($_COOKIE["cookie"])."' WHERE `id` = '".$sql->escape($userdata['id'])."'");
      setcookie("cookie",$_COOKIE["cookie"],time()+60*60*24*356); //One Year!
      $output="OK";
    }
  }
}
elseif($event=="changepassword")
{
  if($userdata["name"]!=$_POST['name'])
    error("WUPS....");
  if($_POST['password']=="")
    error("Enter a new password!");

  $sql->query("UPDATE `user` SET  `password` =  '".$sql->escape($passwordhash)."' WHERE `id` = '".$sql->escape($userdata['id'])."'");  
  $output="OK"; 
}
elseif($event=="logout")
{
  $output="OK";  //Done above
}
elseif($event=="entry")
{
  $output=getentrydata($_POST["id"]);
	//Load log data:
	$sql_ergebniss=$sql->query('SELECT l.changetype type, l.datum datum, u.name username,  l.oldvalues old
					FROM log l INNER JOIN user u ON l.userid = u.id 
					WHERE l.eintragid=\''.$sql->escape($_POST["id"]).'\' 
					ORDER BY l.datum DESC');
	$output["log"]=array();
	while ($akt_log=$sql_ergebniss->fetch())
	{
		$output["log"][]=$akt_log;    
	}
}
elseif($event=="newentry")
{
	if($user!=true) error("You must be loged in!");
	$id=$_POST['id'];
	$location=$_POST['location'];
  $title=$_POST['title'];
  $des=$_POST['des'];
  $link1=$_POST['link1'];
  $link2=$_POST['link2'];
  $link3=$_POST['link3'];
  $count=$_POST['count'];
  $price=$_POST['price'];
  $tags=$_POST['tags'];
  //check title
	if(strlen($location)>=100)
		error("The location is to long!");
	if(strlen($location)<=0)
		error("Pleas enter a location!");
	//Does name exists?
  $sql_location=$sql->query('SELECT * FROM `eintrag` WHERE id != \''.$sql->escape($id).'\' AND `location` LIKE \''.$sql->escape($location).'\'');
	if($sql_location->num_rows()>=1){
    $location_data=$sql_location->fetch();
		error("The location is allready used by: ".$location_data["title"]);    
  }  
	if(strlen($title)>=100)
		error("The tile fo the entry is to long!");
	if(strlen($title)<=0)
		error("Pleas enter a title!");
  //Check Description      
 if($link1!="" and filter_var($link1, FILTER_VALIDATE_URL)==false)
    error("LINK 1 is not valid!");
  if($link2!="" and filter_var($link2, FILTER_VALIDATE_URL)==false)
    error("LINK 2 is not valid!");
  if($link3!="" and filter_var($link3, FILTER_VALIDATE_URL)==false)
    error("LINK 3 is not valid!");
  if($count!="" and is_numeric($count)==false)
    error("COUNT is not valid!");
  if($price!="" and is_numeric($price)==false)
    error("PRICE is not valid!");
  //check tags 
  if(is_array($tags)==FALSE or count($tags)<3)
    error("Add at least 3 Tags! If necessary create a new one.");
  foreach($tags as $tag)
  {
    $sql_ergebniss=$sql->query('SELECT `id` FROM `tag` WHERE `id` = \''.$sql->escape($tag).'\' ');
    $temp=$sql_ergebniss->fetch();
    if($sql_ergebniss->num_rows()!=1 or $temp["id"]!=$tag)
      error("Tag not recognized!");
  }

	$olddata=getentrydata($id);
	
  if($count=="")
    $count="NULL";
  else
    $count='\''.$sql->escape($count).'\'';
  if($price=="")
    $price="NULL";
  else
    $price='\''.$sql->escape($price).'\'';
  
	if($id!="") 
	{
		//Update entry
		$sql->query('UPDATE `eintrag` SET 
              `location` = \''.$sql->escape($location).'\',
              `title` = \''.$sql->escape($title).'\',
              `beschreibung` = \''.$sql->escape($des).'\',
              `link1` = \''.$sql->escape($link1).'\',
              `link2` = \''.$sql->escape($link2).'\',
              `link3` = \''.$sql->escape($link3).'\',
              `count` = '.$count.',
              `price` = '.$price.'  
            WHERE `eintrag`.`id` = \''.$sql->escape($id).'\'');
		//delete old tags
		$sql->query('DELETE FROM `link` WHERE `link`.`eintragid` = \''.$sql->escape($id).'\' ');		
		$entryid=$id;
	}
	else
	{
		//ADD entry
		$sql_ergebniss=$sql->query('INSERT INTO `eintrag` (`location`,          `title`,                `beschreibung`,             `link1`,                         `link2`,                        `link3`,         `count`,   `price`) 
						VALUES (\''.$sql->escape($location).'\',\''.$sql->escape($title).'\', \''.$sql->escape($des).'\', \''.$sql->escape($link1).'\', \''.$sql->escape($link2).'\', \''.$sql->escape($link3).'\', '.$count.', '.$price.')');
		$entryid=$sql_ergebniss->insert_id();
	}
	//ADD tags
	foreach($tags as $tag)
	{
		$sql->query('INSERT INTO `link` (`eintragid`, `tagid`) VALUES (\''.$sql->escape($entryid).'\', \''.$sql->escape($tag).'\')');
	}
	$newdata=getentrydata($entryid);
	if($olddata==$newdata)
		error("No changes made!");
	
	addlog($entryid, ($id!="")?"changed":"added", $olddata, $newdata);
  $output=$entryid;
} 
elseif($event=="deleteentry")
{
	if($user!=true) error("You must be loged in!");
	addlog($_POST['id'], "deleted", getentrydata($_POST['id']), getentrydata(""));
	$sql->query('DELETE FROM `eintrag` WHERE `eintrag`.`id` = \''.$sql->escape($_POST['id']).'\' ');
	$sql->query('DELETE FROM `link` WHERE `link`.`eintragid` = \''.$sql->escape($_POST['id']).'\' ');
  $output="OK";
}
elseif($event=="newtag")
{
	if($user!=true) error("You must be loged in!");
	$name=$_POST['name'];
	$des=$_POST['beschreibung'];
	//Does name is ok?
	if(strlen($name)>=30)
		error("The name of the hashtag is to long!");
	if(strlen($name)<=2)
		error("The name of the hashtag is to short!");
	if(ctype_alpha($name)==FALSE)
		error("The name of the hashtag should just contain a-z and A-Z");
	//Does name exists?
	$sql_ergebniss=$sql->query('SELECT * FROM `tag` WHERE `name` LIKE \''.$sql->escape($name).'\'');
	if($sql_ergebniss->num_rows()>=1)
		error("The name of the hashtag allready exists");
	//Does name is ok?
	if(strlen($des)<=10)
		error("The Description is to short!");
  //Insert
  $sql->query('INSERT INTO `tag` (`name`, `beschreibung`, `typo`) 
            VALUES (\''.$sql->escape($name).'\', \''.$sql->escape($des).'\', \''.$sql->escape(join(" ",texttypo($name))).'\')');
	$output="OK";
}
elseif($event=="taginfo")
{
  $search=strtolower($_POST['name']);
  preg_replace("/[^A-Za-z0-9 ]/", '', $search);
  
  $output=array();

  $searches=array(  
                    //equals
                    'SELECT `id`, `name`, `beschreibung` FROM `tag` WHERE `name` LIKE \''.$search.'\' LIMIT 1 ',
                    //direct:
                    'SELECT `id`, `name`, `beschreibung` FROM `tag` WHERE `name` LIKE \''.$search.'%\' LIMIT 1 ',
                    //middel
                    'SELECT `id`, `name`, `beschreibung` FROM `tag` WHERE `name` LIKE \'%'.$search.'%\' LIMIT 1 ',
                    //Typo driecht
                    'SELECT `id`, `name`, `beschreibung` FROM `tag` WHERE MATCH (typo) AGAINST (\''.$search.'*\' IN BOOLEAN MODE) LIMIT 1 ',
                  );

  $output['found']=false;
  foreach($searches as $sql_query)
  {
    $sql_ergebniss=$sql->query($sql_query);
    if ($data=$sql_ergebniss->fetch())
    {
      $output['found']=true;
      $output['data']=$data;
      break;
    } 
  }
}
elseif($event=="hashsearch")
{
  $exlude=false;
  $search=strtolower($_POST['q']);
  if(substr($search,0,1)=="-")
  {
    $search= substr($search,1);
    $exlude=true;
  }  
  preg_replace("/[^A-Za-z0-9 ]/", '', $search);
  

  $output=array();
  $maxtreffer=10;  //insgesamt 10 treffer

  $searches=array(  
                    //direct:
                    'SELECT `name` FROM `tag` WHERE `name` LIKE \''.$search.'%\' LIMIT 0, 10 ',
                    //middel
                    'SELECT `name` FROM `tag` WHERE `name` LIKE \'%'.$search.'%\' LIMIT 0, 10 ',
                    //Typo driecht
                    'SELECT `name` FROM `tag` WHERE MATCH (typo) AGAINST (\''.$search.'*\' IN BOOLEAN MODE) LIMIT 0, 10 ',
                  );

  foreach($searches as $sql_query)
  {
    if (count($output)<$maxtreffer) 
    {
      $sql_ergebniss=$sql->query($sql_query);
      while ($akt_user=$sql_ergebniss->fetch())
      {
        if($exlude)
          $akt_user['name']="-".$akt_user['name'];  
        if(array_search($akt_user['name'],$output)===False)
          array_push($output,$akt_user['name']);
        if (count($output)>=$maxtreffer) 
          break;
      }  
    } 
  }
  
}
elseif($event=="alltags")
{
  $output=array();
  $sql_ergebniss=$sql->query('SELECT `id`, `name`, `beschreibung` FROM `tag` ORDER BY `name` ASC');
  while ($akt_user=$sql_ergebniss->fetch())
  {
    $output[]=$akt_user;    
  }
}
elseif($event=="search")
{
  //Master SQL:
  //All Ids from entry who have all the maches
  //  SELECT `eintragid`FROM (SELECT `eintragid`, count(*) as c FROM `link` WHERE `tagid` = 1 OR `tagid` = 3  GROUP BY `eintragid`) x WHERE c=2
  //All ids from entry who have antymaches   
  //  SELECT `eintragid` FROM `link` WHERE `tagid` = 2 or  `tagid` = 2
  //left outer join 
  //  SELECT in.* FROM in_table in LEFT JOIN out_tabel out ON in.ID = out.ID WHERE out.ID IS NULL   
  //inner join
  //  SELECT a.* FROM a_table a INNER JOIN b_table b on a.ID = b.ID   
  //SELECT a.* FROM
  //eintrag a
  //INNER JOIN
  //(SELECT i.eintragid FROM 
  //    (SELECT `eintragid` FROM (SELECT `eintragid`, count(*) as c FROM `link` WHERE `tagid` = 1 OR `tagid` = 3  GROUP BY `eintragid`) x WHERE c=2) i 
  //    LEFT JOIN 
  //    (SELECT `eintragid` FROM `link` WHERE `tagid` = 2 or  `tagid` = 2) o 
  //    ON i.eintragid = o.eintragid WHERE o.eintragid IS NULL) b  
  //on a.id = b.eintragid
  
  //build query
  $sqlinclude="SELECT `eintragid`FROM `link` GROUP BY `eintragid` ";
  if(is_array($_POST["include"]))
  {
    $include='FALSE'; 
    foreach($_POST["include"] as $tag)
    {
      $include=$include.' OR `tagid` = \''.$sql->escape($tag).'\''; 
    }
    $sqlinclude='SELECT `eintragid` FROM (SELECT `eintragid`, count(*) as c FROM `link` WHERE '.$include.' GROUP BY `eintragid`) x WHERE c= '.count($_POST["include"]);
  }
  $sqlexclude="SELECT `eintragid` FROM `link` WHERE `tagid` = '0' ";
  if(is_array($_POST["exclude"]))
  {
    $exclude='FALSE'; 
    foreach($_POST["exclude"] as $tag)
    {
      $exclude=$exclude.' OR `tagid` = \''.$sql->escape($tag).'\''; 
    }
    $sqlexclude='SELECT `eintragid` FROM `link` WHERE '.$exclude;
  }
  
  $sql_ergebniss=$sql->query('
    SELECT a.id, a.title, a.beschreibung, a.location 
    FROM 
      eintrag a 
    INNER JOIN 
      (
        SELECT i.eintragid FROM 
          (
            '.$sqlinclude.'
          ) i 
        LEFT JOIN 
          (
            '.$sqlexclude.'
          ) o 
        ON i.eintragid = o.eintragid 
        WHERE o.eintragid IS NULL
      ) b
    ON a.id = b.eintragid
    ORDER BY a.title ASC
    LIMIT 0 , 101
  ');
  $output=array();
  while ($akt_element=$sql_ergebniss->fetch())
  {
    $output[]=$akt_element;    
  }    
}
elseif($event=="textsearch")
{
  $sql_ergebniss=$sql->query('
    SELECT id, title, beschreibung, location
    FROM 
      eintrag
    WHERE 
      MATCH (title,location,beschreibung)
      AGAINST (\''.$sql->escape($_POST['text']).'\' IN BOOLEAN MODE)
      OR
      location LIKE \''.$sql->escape($_POST['text']).'\'
    ORDER BY title ASC
    LIMIT 0 , 101
  ');
  $output=array();
  while ($akt_element=$sql_ergebniss->fetch())
  {
    $output[]=$akt_element;    
  }
}
elseif($event=="admin")
{
	$aevent=$_POST["aevent"];

	if($userdata["id"]!=1 and (backup_key=="" or $aevent!="dump" or $_POST["backup_key"]!=backup_key))
		error("Not an administrator!");
	
	if($aevent=="adduser")
	{    
    if($_POST["name"]=="" or !ctype_alnum($_POST["name"]))
      error("Name not alphanumeric or empty!");
    //Does name exists?
    $sql_ergebniss=$sql->query('SELECT * FROM `user` WHERE `name` LIKE \''.$sql->escape($_POST["name"]).'\'');
    if($sql_ergebniss->num_rows()>=1)
      error("The name of the user allready exists");
    if($_POST["password"]=="")
      error("Use a passowrd!");

    //Insert
    $sql->query('INSERT INTO `user` (`name`, `password`) 
              VALUES (\''.$sql->escape($_POST["name"]).'\', \''.$sql->escape($passwordhash).'\' )');
    $output="OK";    
    
	}
	elseif($aevent=="userlist")
	{    
    $sql_ergebniss=$sql->query('SELECT `name` FROM `user` ');
    $output=$sql_ergebniss->fetch_all();
  }
	elseif($aevent=="remuser")
	{ 
    if($_POST["name"]=="admin")
      error("admin cannot be removed!");

    //get user id:
    $id=$sql->query('SELECT id FROM user WHERE name= \''.$sql->escape($_POST["name"]).'\'');
    if($id->num_rows()!=1)
      error("User not found!");
    $id=$id->fetch();
    $id=$id['id'];
    
    $sql->query('DELETE FROM `user` WHERE `id` = \''.$sql->escape($id).'\'');
    
    $sql->query('UPDATE `log` SET  `userid` =  \'0\' WHERE `userid` =\''.$sql->escape($id).'\'');

    $output="OK";
  }
	elseif($aevent=="log")
	{
		//Load log data:
		$sql_ergebniss=$sql->query('SELECT l.id id, l.eintragid eintragid, l.changetype changetype, u.name username, l.datum datum, l.oldvalues old, l.newvalues new FROM log l JOIN user u ON l.userid = u.id ORDER BY l.datum DESC LIMIT 0,1000');
		$output=array();
		while ($akt_log=$sql_ergebniss->fetch())
		{
			$output[]=$akt_log;    
		}	
	}
  elseif($aevent=="revert")
  {
    $json = new Services_JSON();
    $data=$json->decode($_POST["data"]);
    
    $olddata=getentrydata($data->id);
    //Delete entry;
    $sql->query('DELETE FROM eintrag WHERE id = \''.$sql->escape($data->id).'\' ');		
    //Delete links;
    $sql->query('DELETE FROM link WHERE eintragid = \''.$sql->escape($data->id).'\' ');		
    //ADD entry
		$sql->query('INSERT INTO `eintrag` (`id`,              `location`,                                  `title`,                       `beschreibung`,                          `link1`,                            `link2`,                        `link3`,                   `count`,                                                                           `price`) 
						VALUES (\''.$sql->escape($data->id).'\', \''.$sql->escape($data->location).'\',\''.$sql->escape($data->title).'\', \''.$sql->escape($data->beschreibung).'\', \''.$sql->escape($data->link1).'\', \''.$sql->escape($data->link2).'\', \''.$sql->escape($data->link3).'\', '.($data->count==""?"null":' \''.$sql->escape($data->count).'\'').', '.($data->price==""?"null":' \''.$sql->escape($data->price).'\'').')');
    //ADD links
    foreach($data->tags as $tag)
    {
      $sql->query('INSERT INTO `link` (`eintragid`,                             `tagid`) 
              VALUES (\''.$sql->escape($data->id).'\', \''.$sql->escape($tag->id).'\')');
    }
    addlog($data->id,"reverted", $olddata, getentrydata($data->id));
    $output="OK";
  }
  elseif($aevent=="tag")
  {
    $sql->query("UPDATE `tag` SET `name` = '".$sql->escape($_POST["name"])."', `beschreibung` = '".$sql->escape($_POST["des"])."' WHERE `id` = '".$sql->escape($_POST["id"])."'");
    $output="OK";
  }
  elseif($aevent=="dump")
  {
    ini_set('max_execution_time', '0');
    $return ="";
    //cycle through
    foreach($Database_TableNames_Backup as $table)
    {
      //load column names:
      $colums=array();      

      $colums_sql = $sql->query(' 
        SELECT `COLUMN_NAME` 
          FROM `INFORMATION_SCHEMA`.`COLUMNS` 
          WHERE `TABLE_SCHEMA`=\''.dbname_sql.'\' AND
                `TABLE_NAME`=\''.$table.'\'
      ');
      while($row = $colums_sql->fetch())
      {
        array_push($colums,$row['COLUMN_NAME']);
      }
            
      $return.= 'INSERT INTO `'.$table.'` (';
      
      foreach($colums as $i => $colum)
      {
        $return.= '`'.$colum.'`';
        if ($i+1 != count($colums))
        {
          $return.=', ';
        }          
      }
                    
      $return.= ') VALUES '."\n";

      $result = $sql->query('SELECT * FROM '.$table);
      $num_rows = $result->num_rows();
      $counter=1;
      //Over tables
      while($row = $result->fetch())
      {   
          $return.= '(';

          //Over fields
          foreach($colums as $i => $colum)
          {
            if(is_null($row[$colum]))
            {
              $return.= 'NULL';                            
            }
            else
            {
              $return.= '\''.str_replace("\n","\\n",$sql->escape($row[$colum])).'\'';              
            }
            if ($i+1 != count($colums))
            {
              $return.=', ';
            }          
          }

          if($num_rows == $counter){
              $return.= ");\n";
          } else{
              $return.= "),\n";
          }
          $counter++;
      }
      
      $colums_sql = $sql->query(' 
        SELECT `AUTO_INCREMENT` 
          FROM `INFORMATION_SCHEMA`.`TABLES` 
          WHERE `TABLE_SCHEMA`=\''.dbname_sql.'\' AND
                `TABLE_NAME`=\''.$table.'\'
      ');
      $autoincement = $colums_sql->fetch();
      $autoincement=$autoincement['AUTO_INCREMENT'];
      $return.='ALTER TABLE `'.$table.'` AUTO_INCREMENT='.$autoincement.';'."\n";
    }

    header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
    header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
    header( 'Cache-Control: post-check=0, pre-check=0', false ); 
    header( 'Content-type: text/plain ; charset=utf-8');
    header( 'Content-Disposition:attachment; filename='.preg_replace('/[^A-Za-z0-9]/', '_', $_SERVER['SERVER_NAME']).'_backup_'.date(DATE_ATOM).'.tim');
    header( 'Pragma: no-cache; Cache-control: no-cache, no-store');
    die($return);
  }
  elseif($aevent=="restore")  
  {  
    ini_set('max_execution_time', '0');
    
    //rename old tables
    foreach($Database_TableNames_Backup as $table)
    {
      $sql->query('RENAME TABLE `'.$table.'` TO `'.$table.'_old`;  ');      
    }    
    
    $error=0;    
    //initilise emty tables:
    $sql_querys=$Database_Tabels;
    $sql_querys=explode(";",$sql_querys);
    foreach($sql_querys as $sql_query)
    {
      if(trim($sql_query)=="")
        continue;
      $sql->query($sql_query);
    }
    
    //restor backup
    $sql_commands=file_get_contents($_FILES['backup']['tmp_name']);
    $sql_commands = preg_split("/\s*;[\n\r]\s*/", $sql_commands);
    foreach($sql_commands as $sql_command)
    {
      if(trim($sql_command)=="")
        continue;
      if($sql->query($sql_command,0)==null)
      {
        $error=1;
        break;
      }
    }    
    
    if($error==1)
    {
      //undo!
      //remove new tables
      foreach($Database_TableNames_Backup as $table)
      {
        $sql->query('DROP TABLE `'.$table.'`;  ');      
      }    
      //rename old tables
      foreach($Database_TableNames_Backup as $table)
      {
        $sql->query('RENAME TABLE `'.$table.'_old` TO `'.$table.'`;  ');      
      }   
      error("Could not restore backup!");
    }
    else
    {
      //remove old tables
      foreach($Database_TableNames_Backup as $table)
      {
        $sql->query('DROP TABLE `'.$table.'_old`;  ');      
      }  
    }        
    $output="OK";      
  }
  else
  {
    error("Admin Event unknown!");      
  }
}
else
{
  error("Event unknown!");  
}

//Vor dem Beenden durchfuehren:
$sql->close();

//generate Output:
$data=array();
$data["data"]=$output;
$data["user"]=$userdata["name"];
senddata($data);

?>