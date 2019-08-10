<?php


header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
header( 'Cache-Control: post-check=0, pre-check=0', false ); 
header( 'Content-type: text/html; charset=utf-8');
header( 'Pragma: no-cache; Cache-control: no-cache, no-store');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
    <meta charset="utf-8">
		<link rel="stylesheet" href="css/jquery-ui.css">
		<link rel="stylesheet" href="css/liteaccordion.css">
		<link rel="stylesheet" href="css/index.css">
    <link rel="shortcut icon" href="images/search_hash.ico">
		<script src="js/jquery.min.js"></script>
		<script src="js/liteaccordion.jquery.min.js"></script>
		<script src="js/diff_match_patch.js"></script>
		<script src="js/jquery.pretty-text-diff.min.js"></script>
		<script src="js/jquery-dateFormat.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<script src="js/index.js"></script>

		<title>TIM (Tag based Inventory Management)</title>
	</head>
	<body>
  
    <!-- head --> 
    <div id="head">
      <table id="haedtable">
        <tr id= "haedtable_search">
          <td>
            <div id="sliderouter">				
              <div id="slider">
                <ol>
                  <li>
                    <h2  title="Hash teg search">
                      <img id="search" src="images/search_hash.png"/>
                    </h2>
                    <div id="tagsearch_div">
                      <table id="hash_table">
                        <tr>
                          <td id="hash_content_td">
                            <div  id="hash_content_div">
                              <form  autocomplete="off" action="javascript:void(0);">
                                <fieldset  id="hash_content">
                                  <label id="input_hash_lable" class="link" for="input_hash" title="Use '-Name' to exclude it from the results">
                                    Search#: 
                                  </label>
                                  <input id="input_hash" name="input_hash" value="" />
                                  <button id="b_hashclear">Clear</button>
                                </fieldset>	
                              </form>
                            </div>
                          </td>
                          <td id="hashcontainer_tr">
                            <div id="hashcontainer" class="delitable">
                            </div>
                          </td>
                        </tr>
                      </table>    
                    </div>
                  </li>
                  <li>
                    <h2  title="Full text search">
                      <img id="search" src="images/search_text.png"/>
                    </h2>
                    <div id="textsearch_div">
                      <form  autocomplete="off" action="javascript:void(0);">
                        <fieldset  id="textserach_content">
                          <label for="input_textsearch" title="Use -name to exclude a name, use +name to force a name to be included, use * as wildcard!">Text: </label>
                          <input type="text" name="input_textsearch" id="input_textsearch" value="">
                          <br />
                          <button id="b_textsearch">Search</button>
                        </fieldset>													
                      </form>
                    </div>
                  </li>
                </ol>
              </div>				
            </div>	
          </td>
          <td id="haedtable_add">
            <div id="add"> 
            </div>
          </td>
          <td id="haedtable_user">
            <div id="user">                   
            </div>
          </td>
          <td id="haedtable_logo">
            <div id="fountainG">
              <img src="images/logo.png"/>
              <div id="fountainG_1" class="fountainG"></div>
              <div id="fountainG_2" class="fountainG"></div>
              <div id="fountainG_3" class="fountainG"></div>
              <div id="fountainG_4" class="fountainG"></div>
              <div id="fountainG_5" class="fountainG"></div>
              <div id="fountainG_6" class="fountainG"></div>
            </div>
          </td>
        </tr>
      </table>	
    </div>
    
    <div id="placeholder"> 
      <!-- emulate header distance --> 
    </div>
    
    <div id="selectable_container">
      <ul id="selectable">
      </ul>
    </div>
    
    <div id="entry_conteiner">
      <div id="entry">
        <fieldset id="entry_content" class="mydialog">
          <input type="hidden" name="entry_id" id="entry_id" value="" >
          <label>Title: </label>
          <pre id="entry_title" class="content"></pre>

          <label>Location: </label>
          <pre id="entry_location" class="content"></pre>
                              
          <label>Tags:</label>
          <div id="entry_hashcontainer" class="content"></div>

          <div id="entry_div_des">
            <label>Description:</label>
            <pre id="entry_des" class="content"></pre>
          </div>

          <div id="entry_div_link1">
            <label>Link 1:</label>
            <a id="entry_a_link1" target="_blank" ><pre id="entry_link1" class="content"></pre></a>
          </div>

          <div id="entry_div_link2">
            <label>Link 2:</label>
            <a id="entry_a_link2" target="_blank" ><pre id="entry_link2" class="content"></pre></a>
          </div>

          <div id="entry_div_link3">
            <label>Link 3:</label>
            <a id="entry_a_link3" target="_blank" ><pre id="entry_link3" class="content"></pre></a>
          </div>

          <label>Count:</label>
          <pre id="entry_count" class="content"></pre>
          
          <label>Change Count: </label>
          <div class="content_offset">
            <input type="number" id="entry_change_count"  value="" >
            <button id="entry_change_count_P">+</button>
            <button id="entry_change_count_M">-</button>
          </div>
          
          
          <div id="entry_div_price">
            <label>Price:</label>
            <pre id="entry_price" class="content"></pre>
          </div>
          
          <label>Options:</label>
          <div class="content_offset">
            <button id="entry_change">Change</button>
            <button id="entry_add">Add similar</button>
            <button id="entry_delete">Delete</button>
          </div>
          
          <label>Log:</label>
          <div id="entry_log" class="content"></div>
        </fieldset>	
      </div>
    </div>
    
    
    
    <!-- Dialogs --> 
    <div id="d_addentry">
      <form  autocomplete="off" action="javascript:void(0);">
        <fieldset id="addentry_content"  class="mydialog">
          <input type="hidden" name="addentry_id" id="addentry_id" value="" >
          <label for="addentry_title" title="Use a significant title but not longer than necessary">Title: </label>
          <input type="text" name="addentry_title" id="addentry_title" value="" >      
          <label for="addentry_location">Location: </label>
          <input type="text" name="addentry_location" id="addentry_location" value="" >      
          <label id="addentry_tagtable_lable" class="link" for="addentry_tag" title="Find as many Tags as you can think of!">Tags:</label>
          <table id="addentry_tagtable">
            <tr>
              <th><input name="addentry_tag" id="addentry_tag" value="" ></th>
              <th><button type="button" id="addentry_newtag" >Create new Tag</button></th>
            </tr>
          </table>
          <div id="addentry_hashcontainer" class="delitable"></div>
          <label for="addentry_count" class="ui-widget">Count: </label>
          <input type="number" name="addentry_count" id="addentry_count"  value="" >
          <label for="addentry_des" class="ui-widget" title="Take some minutes to write a description for the Full Text search.">Description: </label>
          <textarea name="addentry_des" id="addentry_des" rows="5" cols="20"></textarea>
          <label for="addentry_link1" class="ui-widget">Link 1: </label>
          <input type="url" name="addentry_link1" id="addentry_link1"  value="" >
          <label for="addentry_link2" class="ui-widget">Link 2: </label>
          <input type="url" name="addentry_link2" id="addentry_link2"  value="" >
          <label for="addentry_link3" class="ui-widget">Link 3: </label>
          <input type="url" name="addentry_link3" id="addentry_link3"  value="" >
          <label for="addentry_price" class="ui-widget">Price: </label>
          <input type="number" name="addentry_price" id="addentry_price"  value="" >
        </fieldset>	
      </form>
    </div>

    <div id="d_addtag">
      <form  autocomplete="off" action="javascript:void(0);">
        <fieldset id="addtag_content" class="mydialog">
          <label for="addtag_name" title="Use a significant Name but not longer than necessary">Name: </label>
          <input type="text" name="addtag_name" id="addtag_name" value="" >
          <button id="addtag_listall" title="Take some time to read the list to avoid double tags for the same topic!">List of all  HashTags</button>
          <label for="addtag_des" class="ui-widget" title="Take some minutes to write a description for the HashTag.">Description: </label>
          <textarea name="addtag_des" id="addtag_des" rows="5" cols="20"></textarea>
        </fieldset>	
      </form>
    </div>	

    <div id="d_login">
      <form id="login_form" action="javascript:void(0);">
        <fieldset id="login_content" class="mydialog">
          <label for="login_name">Username: </label>
          <input type="text" name="login_name" id="login_name" value="" autocomplete="username">
          <label for="login_password">Password: </label>
          <input type="password" name="login_password" id="login_password" value="" autocomplete="current-password">
        </fieldset>	
      </form>
    </div>			
	</body>
</html>
