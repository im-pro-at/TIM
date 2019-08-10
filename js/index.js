/*
  index.js
  
  Autor: im-pro
*/

function anotinb(a,b,compaire){
  if(!compaire)
    compaire=function(a,b){return a==b;}
  out=[];
  for(var i=0;i<a.length;i++){
    inb=false
    for(var j=0;j<b.length;j++){
      if(compaire(a[i],b[j])){
        inb=true;
      }
    }
    if(!inb){
      out.push(a[i]);
    }
  }
  return out;
}
              
function custom_alert(output_msg, title_msg)
{
	if (!title_msg)
			title_msg = 'Error';

	if (!output_msg)
			output_msg = 'No Message to Display.';

	$("<div></div>").html(output_msg).dialog({
			title: title_msg,
			resizable: false,
			modal: true,
			buttons: {
					"Ok": function() 
					{
							$( this ).dialog( "close" );
							$( this ).dialog( "destroy" );
					}
			}
	});
}

function custom_dialog(output_msg, title_msg, OK_button, CANCEL_button, OK_event, CANCEL_event,CLOSE_event)
{
	if (!title_msg)	title_msg='Error';
	if (!output_msg) output_msg='No Message to Display.';

	var div=$( "<div />" ).append(output_msg);
	div.find("input").keyup(function(e){
		if(e.keyCode==13) 
		{
      if(OK_event()) div.dialog( "close" );						
		}
	});

	if (!OK_event) OK_event=function(){return true;}; 
	if (!CANCEL_event) CANCEL_event=function(){return true;}; 
	if (!CLOSE_event) CLOSE_event=function(){}; 

	var buttons={};
	if(OK_button)
		buttons[OK_button] = function() {
						if(OK_event()) div.dialog( "close" );						
					};
	if (CANCEL_button) 
		buttons[CANCEL_button] = function() {
						if(CANCEL_event()) div.dialog( "close" );
					}			
	div.dialog({
			title: title_msg,								
			resizable: false,
			modal: true,
			buttons: buttons,
			close: function() {
        CLOSE_event();
				div.dialog( "destroy" );
			}
		});	
	return div;
}

function setLoadState(state){
	if(state)
		$(".fountainG").css("display", "block");
	else
		$(".fountainG").css("display", "none");
}

var logedin=false;

function addTag(containter,data, exclude )
{
	var tag=$( "<span/>" ).addClass("tag")
								.attr( "exclude", exclude?"":null )
								.text(data["name"])
								.attr("hashid", data["id"])
								.attr("title", data["beschreibung"])
  							.attr("title-copy", data["beschreibung"]) 
								.appendTo( containter );
	$( "<span/>" ).text(" ").appendTo( containter );     
  return tag;
}

function compairtags(o,n,tagconatiner)
{
  var compaire=function(a,b){return a["id"]==b["id"];};
  var tremoved=anotinb(o,n,compaire);
  var tadded=anotinb(n,o,compaire);
  var tags=[].concat(tremoved,anotinb(n,tadded,compaire),tadded);
  for(var i=0;i<tags.length;i++){
    addTag(tagconatiner,tags[i]).attr(anotinb([tags[i]],tremoved,compaire).length==0?"removed":(anotinb([tags[i]],tadded,compaire).length==0?"added":"stayed"),"");
  }
}

function slideentryview(openClose,finisched) {
		var animation1={ right: "-52%"}
		if(openClose){
			animation1={right: 0};
    }
		$( "#entry_conteiner,#entry_table_th"      ).stop().animate(animation1,1000);
}


function selectentry(id) {
	if(id!="" && id)
	{
		$("#selectable .selected").removeClass("selected");
		$("#selectable [entryid=\""+id+"\"]").addClass("selected");
	}
	else
	{
		if($(this).attr("entryid")==-1)
			return;
		var selected=$(this).is(".selected");
		$("#selectable .selected").removeClass("selected");
		if(selected==false)
		{
			$(this).addClass("selected");
		}
	}
	eventHandler("entry",$("#selectable .selected").attr("entryid")); 
}

function updatelist(data,id)
{
  $( "#selectable" ).empty();
  for(i=0;i<data.length;i++)
  {
    if(i==99)
    {
      //to much elements:
      $( "<li/>" )
        .text("To much results!")
        .attr("entryid",-1)
        .appendTo("#selectable");
      break;
    }
    var entry=data[i];
    $( "<li/>" ).append($("<span>").text(entry["location"]+":   "))
      .append($("<b>").text(entry["title"]))
      .attr("entryid",entry["id"])
      .attr("title",entry["beschreibung"].length>100? entry["beschreibung"].substring(0,97)+"...":entry["beschreibung"])
      .appendTo("#selectable");
  }
  if(data.length==0)
  {
      //no elements:
      $( "<li/>" )
        .text("No entries found!")
        .attr("entryid",-1)
        .appendTo("#selectable");        
  }	
  selectentry(id); 
}

var backend= "php/";
var busy=false;
function loader(request,respondfunction)
{
	busy=true;
	setLoadState(true);
	console.log(request);
  var processData=true;
  var contentType='application/x-www-form-urlencoded; charset=UTF-8';
  if(FormData.prototype.isPrototypeOf(request)){
    //Send FormData:
    processData=false;
    contentType=false;
  }  
	$.ajax({
		url: backend,
		dataType: "jsonp",
		method: "POST",
		data: request,
    processData: processData,
    contentType: contentType,
		success: function( data ) {
			console.log(data);
			setLoadState(false);
			busy=false;
			if(data==null)
			{
				custom_alert("Communication error!");
				return;
			}
			if(data["error"])
			{
				custom_alert($("<pre/>").text(data["error"]),"Error Message");            
			}
			if(data["user"])
			{
				logedin=(data["user"]!="guest");
        $("#user").attr("title",data["user"]);
        if(logedin)
        {
          $("#user").addClass("active");       
        }
        else{
          $("#user").removeClass("active");                 
        }
			}
      if(data["debug"])
      {
        console.log(data["debug"]);
      }
			if(data["data"])
			{
				respondfunction(data["data"]);
			}
		},
		error: function( x, serror )
		{
			setLoadState(false);
			busy=false;
			custom_alert("Communication error. Server not reachable! Check your internet connection.");
      $.ajax({
          url: backend,
          dataType: "text",
          method: "POST",
          processData: processData,
          contentType: contentType,
          data: request,
          success: function( data ) {
            console.log(data);
          }
      });
		}
	});  
}

lastsearch="tagsearch";
function eventHandler(event, parameter, parameter2){
	if(busy)
		return;
	console.log("Event: "+event+" ("+parameter+", "+parameter2+")");
	if(event=="login")
	{
		loader(
			{
				event: "login", 
				name: parameter["name"],
				password: parameter["password"]
			},
      parameter2
		);
	}
	if(event=="changepassword")
	{
		loader(
			{
				event: "changepassword", 
				name: parameter["name"],
				password: parameter["password"]
			},
      parameter2
		);
	}
	if(event=="logout")
	{
		loader(
			{
				event: "logout", 
			},
			function(){}
		);
	}
	else if(event=="newtag")
	{
		loader(
			{
				event: "newtag", 
				name: parameter["name"],
				beschreibung: parameter["des"]
			},
			parameter2
		);
	}
	else if(event=="newentry")
	{    
		loader(
			{
				event: "newentry", 
				id: parameter["id"],
				location: parameter["location"],
				title: parameter["title"],
				des: parameter["des"],
				link1: parameter["link1"],
				link2: parameter["link2"],
				link3: parameter["link3"],
				count: parameter["count"],
				price: parameter["price"],
				tags: parameter["tags"],
			},
			parameter2
		);
	}  
	else if(event=="addtag")
	{
		exclude=false;
		if(parameter.substring(0,1)=="-")
		{
			parameter=parameter.substring(1);
			exclude=true;
		}  
		loader(
			{
				event: "taginfo", 
				name: parameter
			},
			function(data)
			{
				if(data['found'])
				{
					//load ids
					var exitst=false;
					$( "#"+parameter2+" .tag" ).each(function() {
						if ($(this).attr("hashid") == data["data"]["id"])
							exitst=true;  
					});
					if (exitst)
					{
						custom_alert("Tag \""+data["data"]["name"]+"\" already used!", "Meldung");  
					}
					else
					{
						//add Tag
            addTag("#"+parameter2, data["data"], exclude);
					}
				}
				else
				{
					custom_alert("Tag \""+parameter+"\" not found!", "Meldung");  
				}
			});    
	}
	else if(event=="tagsearch")
	{
		lastsearch="tagsearch";
		var include=[];
		var exclude=[];
		$( "#hashcontainer .tag" ).each(function() {
			if ($(this).attr("exclude")==null)
				include.push($(this).attr("hashid"));  
			else
				exclude.push($(this).attr("hashid"));  
		});
		
		loader(
			{
				event: "search", 
				include: include,
				exclude: exclude 
			},
			function(data)
			{
        updatelist(data,$( "#entry_id" ).val());
        //Some changes?
     		var tinclude=[];
    		var texclude=[];
    		$( "#hashcontainer .tag" ).each(function() {
    			if ($(this).attr("exclude")==null)
    				tinclude.push($(this).attr("hashid"));  
    			else
    				texclude.push($(this).attr("hashid"));  
    		});
        if(JSON.stringify(tinclude)!= JSON.stringify(include) || JSON.stringify(texclude)!= JSON.stringify(exclude))
          eventHandler("tagsearch");  
			}
		);  
	}
  else if (event=="textsearch")
  {
		lastsearch="textsearch";
		loader(
			{
				event: "textsearch", 
				text: $( "#input_textsearch" ).val()
			},
			function(data)
			{
        updatelist(data,$( "#entry_id" ).val());
 			}
		);    
  }
  else if (event=="entry")
  {
    $( "#entry_id" ).val("");
    $("#entry button").button({disabled: true});
		slideentryview(false,function(){
			$( "#entry .content" ).text("");			
		});
		loader(
			{
				event: "entry",
        id: parameter
			},
			function(data)
			{
				if(data["id"])
				{
					$( "#entry .content" ).text("");			
					$( "#entry_id" ).val(data["id"]);
					$( "#entry_title" ).text(data["title"]);
					$( "#entry_location" ).text(data["location"]);
					for(var i in data["tags"] )
					{
						var element=data["tags"][i];
						addTag("#entry_hashcontainer",element,false);
					}
					$( "#entry_des" ).text(data["beschreibung"]);
          $( "#entry_div_des" ).css("display",data["beschreibung"]==""?"none":"block");
					for(var i=1;i<=3;i++)
					{
            $( "#entry_link"+i ).text(data["link"+i]);
            $( "#entry_a_link"+i ).attr("href",data["link"+i]);
            $( "#entry_div_link"+i ).css("display",data["link"+i]==""?"none":"block");
          }
					$( "#entry_count" ).text(data["count"]===null?"-":data["count"]);
					$( "#entry_change_count" ).val(1);
          
					$( "#entry_price" ).text(data["price"]);
          $( "#entry_div_price" ).css("display",data["price"]===null?"none":"block");
					$("#entry button").button({disabled: false});
					for(var i in data["log"] )
					{
						var element=data["log"][i];
						$("<a />")
							.attr("href","#")
							.text(element["type"].substring(0,1).toUpperCase() + element["type"].substring(1) + " by "+element["username"]+" "+ jQuery.format.prettyDate(new Date(element["datum"]*1000)) )
							.appendTo("#entry_log")
							.click(function(data, element){
                $( "#entry_content>div" ).css("display","block");
								var n=data;
								var o=$.parseJSON(element["old"]);
								var names=[
                           ["title","#entry_title"],
                           ["location","#entry_location"],
                           ["beschreibung","#entry_des"],
                           ["link1","#entry_link1"],
                           ["link2","#entry_link2"],
                           ["link3","#entry_link3"],
                           ["count","#entry_count"],
                           ["price","#entry_price"]
                           ];
								for(var i in names)
								{
									var name=names[i][0];
									var diffContainer=names[i][1];
									$("#entry "+diffContainer).empty();
									$("#entry").prettyTextDiff({
											originalContent: (o[name] === undefined || o[name] == null || o[name] =="")?" ":o[name],
											changedContent:  (n[name] === undefined || n[name] == null || n[name] =="")?" ":n[name],
											diffContainer: diffContainer,
											cleanup: true,
									});
                  console.log([i,names[i],o[name],n[name]])
								}
								compairtags(o["tags"],n["tags"],$( "#entry_hashcontainer" ).empty());                
							}.bind(null,data,element));
						$("<br />").appendTo("#entry_log");
					}
					slideentryview(true);
				}
				else
				{
					slideentryview(false,function(){
						$( "#entry .content" ).text("");			
					});
				}
      }
     );    
  }
	else if (event=="deleteentry")
	{
		loader(
			{
				event: "deleteentry",
				id: parameter,
			},
			function(data)
			{
				eventHandler(lastsearch);
			}
		);
	}
	else if (event=="cangeentry" || event=="addsimentry")
	{
		loader(
			{
				event: "entry",
        id: parameter
			},
			function(data)
			{
        if(event=="cangeentry")
        {
          $( "#addentry_id" ).val(data["id"]);
          $( "#addentry_location" ).val(data["location"]);          
        }
        else
        {
          $( "#addentry_id" ).val("");
          $( "#addentry_location" ).val("");                    
        }
				$( "#addentry_title" ).val(data["title"]);
				$( "#addentry_des" ).val(data["beschreibung"]);
        $( "#addentry_link1" ).val(data["link1"]);
        $( "#addentry_link2" ).val(data["link2"]);
        $( "#addentry_link3" ).val(data["link3"]);
        $( "#addentry_count" ).val(data["count"]);
        $( "#addentry_price" ).val(data["price"]);
        $( "#addentry_hashcontainer .tag" ).remove();
        
        for(var i in data["tags"] )
				{
					var element=data["tags"][i];
          addTag("#addentry_hashcontainer",element,false);
        }
        if(event=="cangeentry")
          $(d_addentry).parent().find(".OKButton").button( "option", "label","Change");
				else
          $(d_addentry).parent().find(".OKButton").button( "option", "label","Create");          
        d_addentry.dialog( "open" );
			}
		);
	}
	else if (event=="cangeentrycount")
  {
		loader(
			{
				event: "entry",
        id: parameter['id']
			},
			function(data)
			{
        var count=data['count'];
        if(count===null)
          count=0;
        count=parseInt(count)
        count+=parameter['value'];
        var tags=[];
        for(var i in data["tags"] )
				{
					var element=data["tags"][i];
          tags.push(element['id']);
        }        
        eventHandler("newentry",
          {
            id: data['id'],
            location: data['location'],
            title: data['title'],
            des: data['beschreibung'],
            link1: data['link1'],
            link2: data['link2'],
            link3: data['link3'],
            count: count,
            price: data['price'],
            tags: tags,
          },
          function()
          {
            //reload entry
            eventHandler("entry",parameter['id']);
          }
        );
        
      }
    );
  }  
	else if (event=="alltags")
	{
		loader(
			{
				event: "alltags",
			},
			function(data)
			{
				var couter = $( "<div />" );
				var lastletter = ' ';
				var cinner = null;
				
				for(var i in data )
				{
					var element=data[i];
					if ( element['name'].substring(0, 1) != lastletter )
					{
						lastletter=element['name'].substring(0, 1); 
						$( "<div />" )
							.addClass("hashlist_lable")
							.text(element['name'].substring(0, 1).toUpperCase())
							.appendTo(couter); 
						cinner= $( "<div />" ).appendTo(couter);
						if(!(typeof parameter === "function") && parameter!=null)
							cinner.addClass("addable");

					}
          addTag(cinner,element, false ).attr("stayed",(typeof parameter === "function")?"":null);
				}
				couter.find(".tag").click(function(){
          if(typeof parameter === "function"){
            return parameter(this,couter);
          }
					if(parameter==null)
						return;
					var exitst=false;
					var that=this;
					$( "#"+parameter+" .tag" ).each(function() {
						if ($(this).attr("hashid") == $(that).attr("hashid"))
							exitst=true;  
					}); 
					if (exitst)
					{
						custom_alert("Tag \""+$(this).text()+"\" already used!", "Meldung");  
					}
					else
					{
						console.log(this,$(this),$(this).attr("title"));
            var celement=[];
            celement["name"]=$(this).text()
            celement["id"]=$(this).attr("hashid")
            celement["beschreibung"]=$(this).attr("title-copy")
            addTag("#"+parameter, celement, false);
					}
				});
				couter.dialog({
					title: "Hashtag list: ",
					height: 400,
					width: 350,
					modal: true,
					close: function() {
						couter.dialog( "destroy" );  
					}
				});  
					
			}
		);    
	}
}

//initialisieren
$(function() {
  //Load Data
	eventHandler("tagsearch");	
	//Add tooltips
	$(document).tooltip(
  {
		content: function() {
			var element = $(this);
			
			return  $("<pre \>").text(element.attr("title"));
		},
    open: function (event, ui) {
        ui.tooltip.css("max-width", "50vw");
    }
  });
	//make reach input filds:
	$( "textarea, input:text, input:password, input[type=email], input[type=number], input[type=url]" ).addClass("text ui-widget ui-widget-content ui-corner-all");
	$( "label" ).addClass("ui-widget");
	$( "button" ).button();
	//Init Slider:
	$('#slider').liteAccordion();
	$(window).resize(function(){
		$('#slider').liteAccordion('destroy');
		$('#slider').liteAccordion({
			containerWidth : $('#sliderouter').width(),
			containerHeight : 100,                  // fixed (px)
			headerWidth: 80,                       // fixed (px)
			onTriggerSlide: function(){
					$("#slider input").prop("disabled", true);   
					$("#slider button").button({disabled: true});
					$("#input_add_type").selectmenu("disable");
					$(this).find("input").prop("disabled", false);
					$(this).find("button").button({disabled: false});
					$(this).find( "#input_add_type" ).selectmenu("enable");
					if($(this).is("#tagsearch_div"))
						eventHandler("tagsearch");
					if($(this).is("#textsearch_div"))
						eventHandler("textsearch");
				},
			onSlideAnimComplete: function(){
					$(this).find(":input").first().focus();
				},  
			theme : 'basic',                        // basic, dark, light, colorful, or stitch
			rounded : false,                        // square or rounded corners
			enumerateSlides : false,                // put numbers on slides
			linkable : false                        // link slides via hash						
		});
    $( "#entry"      ).css({height:window.innerHeight-100});
  });
	$(window).trigger( "resize" );
	$('#slider').liteAccordion('next').liteAccordion('prev');
			
	//init Buttons and Events:
	$( "#user" ).click(function(){
    console.log(logedin);
		if(logedin)
		{
  		eventHandler("logout");
		}
		else
		{
      d_login.dialog( "open" );  
		}
	});
	$('#hashcontainer').bind("DOMNodeInserted DOMNodeRemoved",function(){
		eventHandler("tagsearch");
	});
	$( ".delitable" ).on("click" , ".tag", function() {
		$(this).removeClass("tag");
    $(this).remove();
	});
	$( "#b_hashclear" ).click(function(){
    $( "#hashcontainer" ).removeClass("tag");
		$( "#hashcontainer .tag" ).remove();
		eventHandler("tagsearch");
	});
	$( "#b_textsearch" ).click(function(){
		eventHandler("textsearch");
	});
	$( "#input_textsearch" ).keyup(function(e){
		if(e.keyCode==13) 
  		eventHandler("textsearch");
	});
	$( "#add" ).click(function (){
		if(logedin)
		{
  		$(d_addentry).parent().find(".OKButton").button( "option", "label","Create");
			d_addentry.dialog( "open" );  
		}
		else
		{
      d_login.dialog( "open" );  
		}
	});
	$( "#input_hash_lable" ).click(function(){
		eventHandler("alltags","hashcontainer");  
	});    
	$( "#addentry_tagtable_lable" ).click(function(){
		eventHandler("alltags","addentry_hashcontainer");  
	});
	$( "#addtag_listall" ).click(function(){
		eventHandler("alltags");  
	});
	$( "#input_hash" ).keydown(function(event){ 
		if(event.keyCode==13 || event.keyCode==32) 
		{
			event.preventDefault();
			eventHandler("addtag", $( "#input_hash" ).val(),"hashcontainer");
			$( "#input_hash" ).val("");
			$( "#input_hash" ).autocomplete("close");
		}
	});
	$( "#addentry_tag" ).keydown(function(event){ 
		if(event.keyCode==13 || event.keyCode==32) 
		{
			event.preventDefault();
			if($( "#addentry_tag" ).val().substring(0,1)=="-")
			{
				custom_alert("Exluding elements is not allwoed!", "Meldung");	
			}
			else
			{
				eventHandler("addtag", $( "#addentry_tag" ).val(), "addentry_hashcontainer");
			}
			$( "#addentry_tag" ).val("");
			$( "#addentry_tag" ).autocomplete("close");
		}
	});
	$( "#addentry_newtag" ).click(function(){
		d_addtag.dialog( "open" );
	});
  
  
	$( "#entry_change_count_P,#entry_change_count_M" ).click(function(){
		if(logedin)
		{
      var val=parseInt($( "#entry_change_count" ).val());
      if (this.id=="entry_change_count_M")
        val=val*(-1);
			eventHandler("cangeentrycount", 	
        {
          id: $( "#entry_id" ).val(),
          value: val,
        });
		}
		else
		{
      d_login.dialog( "open" );  
		}
	});	
	$( "#entry_change" ).click(function(){
		if(logedin)
		{
			eventHandler("cangeentry", $( "#entry_id" ).val());		
		}
		else
		{
      d_login.dialog( "open" );  
		}
	});	
	$( "#entry_add" ).click(function(){
		if(logedin)
		{
			eventHandler("addsimentry", $( "#entry_id" ).val());		
		}
		else
		{
      d_login.dialog( "open" );  
		}
	});	
	$( "#entry_delete" ).click(function(){
		if(logedin)
		{
			custom_dialog("Do you really want to delete this entry?","Waring","Yes","No", function(){
				eventHandler("deleteentry", $( "#entry_id" ).val());
        return true; 
			});
		}
		else
		{
      d_login.dialog( "open" );  
		}
	});
	//init add entry dialog
	$( "#addentry_title, #addentry_location, #addentry_count, #addentry_link1, #addentry_link2, #addentry_link3, #addentry_price" ).keyup(function(e){
		if(e.keyCode==13){
 			e.preventDefault();
      d_addentry.parent().find(".OKButton").click();      
    }
	});  
	d_addentry = $( "#d_addentry" ).dialog({
		autoOpen: false,
		title: "Add new Entry",
		height: 500,
		width: 600,
		modal: true,
		buttons: [
			{
				text: "Create",
				"class": 'OKButton',
				click:function(){
					var tags=[];
					$( "#addentry_hashcontainer .tag" ).each(function() {tags.push($(this).attr("hashid"));});
					eventHandler("newentry",
						{
							id: $( "#addentry_id" ).val(),
							location: $( "#addentry_location" ).val(),
							title: $( "#addentry_title" ).val(),
							des: $( "#addentry_des" ).val(),
							link1: $( "#addentry_link1" ).val(),
							link2: $( "#addentry_link2" ).val(),
							link3: $( "#addentry_link3" ).val(),
							count: $( "#addentry_count" ).val(),
							price: $( "#addentry_price" ).val(),
							tags: tags,
						},
						function(newid)
						{
							var id=$( "#addentry_id" ).val();
              d_addentry.dialog( "close" );
              eventHandler(lastsearch);                
              if(id=="")
              {
                custom_dialog("Do you want to add a simelar element again?","Note","Yes","No", function(){           
             			eventHandler("addsimentry", newid);		
                  return true; 
                });
              }
						}
					);
					return false;
				}
			},
			{
				text: "Cancel",
				click:function() {
					d_addentry.dialog( "close" );
				}
			}
		],
		close: function() {
      $( "#addentry_id" ).val("");
      $( "#addentry_location" ).val("");
      $( "#addentry_title" ).val("");
      $( "#addentry_des" ).val("");
      $( "#addentry_link1" ).val("");
      $( "#addentry_link2" ).val("");
      $( "#addentry_link3" ).val("");
      $( "#addentry_count" ).val("");
      $( "#addentry_price" ).val("");
      $( "#addentry_tag" ).val("");
      $( "#addentry_hashcontainer .tag" ).remove();
		}
	});
	//init add tag dialog
	d_addtag = $( "#d_addtag" ).dialog({
		autoOpen: false,
		title: "Add new HashTag",
		height: 400,
		width: 600,
		modal: true,
		buttons: {
			"Create": function() {
				eventHandler("newtag",
					{
						name: $( "#addtag_name" ).val(),
						des: $( "#addtag_des" ).val()
					},
					function()
					{
						eventHandler("addtag", $( "#addtag_name" ).val(), "addentry_hashcontainer");
						d_addtag.dialog( "close" );
					}
				);
				return false;
			},
			Cancel: function() {
				d_addtag.dialog( "close" );
			}
		},
		close: function() {
			$( "#addtag_name" ).val("");
			$( "#addtag_des" ).val("");			
		}
	});	
	//init login dialog
  d_login_execute=function(cb){
    eventHandler("login",
      {
        name: $( "#login_name" ).val(),
        password: $( "#login_password" ).val()
      },
      function()
      {
        if(cb)
          cb();
        else
          d_login.dialog( "close" );
      }
    );
    return false;    
  }
	$( "#login_form input" ).keyup(function(e){
		if(e.keyCode==13) 
    {
 			e.preventDefault();
      d_login_execute();      
    }
	});
	d_login = $( "#d_login" ).dialog({
		autoOpen: false,
		title: "Login",
		modal: true,
		buttons: {
			"Change password": function(){
        d_login_execute(function(){        
          var username=$( "#login_name" ).val();
          d_login.dialog( "close" );
          var dialog=$("<div/>").css({width:"100%"});
          $("<p/>").text("New Password:").appendTo(dialog)
          var password_input=$("<input type=text >");
          $("<p/>").append(password_input).appendTo(dialog);

          var d_newpassword=custom_dialog(dialog,"New Password:","Save","Cancel",function(){
          eventHandler("changepassword",
            {
              name: username,
              password: password_input.val()
            },
            function(data){
              d_newpassword.dialog("close");
            });
          });
        })
      },
			"Login": function(){d_login_execute()},      
			Cancel: function() {
				d_login.dialog( "close" );
			}
		},
		close: function() {
			$( "#login_name" ).val("");
			$( "#login_password" ).val("");			
		}
	});	
	//init Hash imput:
	$( "#input_hash, #addentry_tag" ).autocomplete({
		source: function( request, response ) {
			loader(
				{
					event: "hashsearch",
					q: request.term
				},
				function( data ) {
					response( data );
				}
			)
		},
		minLength: 1
	}); 
	//Init selectable
	$("#selectable").on("click" , "li", function (){selectentry.bind(this)();});
	
	//Admin
	$( "#haedtable_logo img" ).click(function(){
    if($("#user").attr("title")!="admin")
    {
      //no admin ...
      return;
    }  
    var div=$("<div \>");
    $( "<a \>").attr("href","#").text("Add user").click(function(){
      var dialog=$("<div/>").css({width:"100%"});
      $("<p/>").text("Username:").appendTo(dialog)
      var username_input=$("<input type=text>");
      $("<p/>").append(username_input).appendTo(dialog)
      $("<p/>").text("Password:").appendTo(dialog)
      var password_input=$("<input type=text>");
      $("<p/>").append(password_input).appendTo(dialog)

      var d_adduser=custom_dialog(dialog,"Add a new user:","Create","Cancel",function(){
        loader(
        {
          event: "admin",
          aevent: "adduser",      
          name: username_input.val(),      
          password: password_input.val()      
        },
        function(data){
          d_adduser.dialog("close");
        });
      });
    }).appendTo(div);
    $( "<br>" ).appendTo(div);
    $( "<a \>").attr("href","#").text("Remove user").click(function(){
      loader(
      {
        event: "admin",
        aevent: "userlist"  
      },
      function(data){
        var dialog=$("<div/>").css({width:"100%"});
        $("<p/>").text("Username:").appendTo(dialog)
        var username_input=$("<select>");
        data.forEach(function(user){
          username_input.append($("<option>").val(user['name']).text(user['name']));
        });
        $("<p/>").append(username_input).appendTo(dialog);

        var d_remuser=custom_dialog(dialog,"Remove a user:","Remove","Cancel",function(){
          loader(
          {
            event: "admin",
            aevent: "remuser",      
            name: username_input.val()      
          },
          function(data){
            d_remuser.dialog("close");
          });
        });
      });
    }).appendTo(div);
    $( "<br>" ).appendTo(div);
    $( "<a \>").attr("href","#").text("Change Log").click(function(){
      loader(
      {
        event: "admin",
        aevent: "log"
      },
      function(data){
        var div=$("<div \>");
        for(var i in data )
        {
          var element=data[i];
          var o=$.parseJSON(element["old"]);
          var n=$.parseJSON(element["new"]);
          var span=$( "<span\>" ).text("Entry "+element["eintragid"]+" ("+n["title"]+")"+": "+element["changetype"].substring(0,1).toUpperCase() + element["changetype"].substring(1) + " by "+element["username"]+" "+ jQuery.format.prettyDate(new Date(element["datum"]*1000))); 
          $( "<p />" ).append(span).append(" ").appendTo(div);
          var names=["title","location","beschreibung","link1","link2","link3","count","price"];
          var css1={"font-size": "12px", "margin-left": "30px"};
          var css2={"font-size": "12px", "margin-left": "50px"};
          for(var i in names)
          {
            var name=names[i];
            if(o[name]!==n[name])
            {
              $("<pre />").css(css1).text(name+": ").appendTo(div);            
              $("<div>").append($("<pre>").css(css2)).prettyTextDiff({
                  originalContent: (o[name] === undefined || o[name] == null || o[name] == "")?" ":o[name],
                  changedContent:  (n[name] === undefined ||  n[name] == null || n[name] == "")?" ":n[name],
                  diffContainer: "*",
                  cleanup: true,
              }).appendTo(div);
            }
          }
          $("<pre />").css(css1).text("tags: ").appendTo(div);
          var tagconatiner=$("<div />").css(css2).appendTo(div);                      
          compairtags(o["tags"],n["tags"],tagconatiner)
          var revert=$("<div />").css(css1).appendTo(div)
            .append($("<span/>").text("revert: "))
            .append($("<span/>").text(" "))
          var parameters=[["before",o],["after",n]];
          for(i in parameters){
            var d=parameters[i][1];
            $("<button>")
              .text(parameters[i][0])
              .button({disabled: !("id" in d)})
              .click(function(d){
                loader(
                {
                  event: "admin",
                  aevent: "revert",
                  data: JSON.stringify(d)
                },
                function(data){
                  changelog.dialog( "close" );
                });
              }.bind(null,d))
              .appendTo(revert);
          }
          
        }
        var changelog=custom_dialog(div,"Changelog:").dialog("option", "width", 800).dialog("option", "height", 600);
      });
    }).appendTo(div);
    $( "<br>" ).appendTo(div);
    $( "<a \>").attr("href","#").text("Change Tags").click(function(){
      eventHandler("alltags",function(tag,tagdialog){
        tagdialog.dialog("close");
        $(document).tooltip('disable');
        $(document).tooltip('enable');
        var tagedite=$("<div/>").css({width:"100%"});
        $("<p/>").text("Just use this to correct typos! Don't change the meaning of the Tag!").appendTo(tagedite);
        $("<p/>").text("Name:").appendTo(tagedite)
        var name=$("<input/>").val($(tag).text()).appendTo(tagedite).css({width:"100%"});;  
        $("<p/>").text("Description:").appendTo(tagedite);
        var des=$("<textarea/>").val($(tag).attr("title-copy")).appendTo(tagedite).attr({rows:"5", cols:"20"}).css({width:"100%"});  
        var d_tagedite=custom_dialog(tagedite,"Edit Tag:","Edit","Cancel",function(){
          loader(
          {
            event: "admin",
            aevent: "tag",
            id: $(tag).attr("hashid"),
            name: name.val(),
            des: des.val(),
          },
          function(data){
            d_tagedite.dialog("close");
          });
        }).dialog("option", "width", 600).dialog("option", "height", 500)
      });  
    }).appendTo(div);
    $( "<br>" ).appendTo(div);
    $( "<a \>").attr("href",backend+"?"+"event=admin&aevent=dump").text("Create Backup").appendTo(div);;
    $( "<br>" ).appendTo(div);
    $( "<a \>").attr("href","#").text("Restore Backup").click(function(){
      var dialog=$("<div/>").css({width:"100%"});
      $("<p/>").html("Warning: <b>All Data will be overridden!!!</b>").appendTo(dialog)
      $("<p/>").text("BackupFile:").appendTo(dialog)
      var file_input=$("<input type=file accept='.tim'>");
      $("<p/>").append(file_input).appendTo(dialog)

      var d_restore=custom_dialog(dialog,"Restore Backup:","Restore","Cancel",function(){
        var send = new FormData();
        send.append('event','admin');
        send.append('aevent','restore');
        send.append('backup',file_input[0].files[0]);
        send.append('','');
        loader(
        send,
        function(data){
          custom_dialog($("<div>Successfully resort!</div>"),"Restore Backup:","OK");
          d_restore.dialog("close");
        });
      }).dialog("option", "width", 600).dialog("option", "height", 500);            
    }).appendTo(div);
    custom_dialog(div,"Admin Console:");
  });
	
});
