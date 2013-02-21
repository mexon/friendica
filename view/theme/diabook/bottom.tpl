<script type="text/javascript" src="$baseurl/view/theme/diabook/js/jquery.autogrow.textarea.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $("iframe").each(function(){
        var ifr_source = $(this).attr("src");
        var wmode = "wmode=transparent";
        if(ifr_source.indexOf("?") != -1) {
            var getQString = ifr_source.split("?");
            var oldString = getQString[1];
            var newString = getQString[0];
            $(this).attr("src",newString+"?"+wmode+"&"+oldString);
        }
        else $(this).attr("src",ifr_source+"?"+wmode);
       
    });
    
    $("div#pause").attr("style", "position: fixed;bottom: 43px;left: 5px;");
    $("div#pause").html("<img src='images/pause.gif' alt='pause' title='pause live-updates (ctrl+space)' style='border: 1px solid black;opacity: 0.2;'>");
    $(document).keydown(function(event) {
    if (!$("div#pause").html()){
    $("div#pause").html("<img src='images/pause.gif' alt='pause' title='pause live-updates (ctrl+space)' style='border: 1px solid black;opacity: 0.2;'>");
		}});  
    $(".autocomplete").attr("style", "width: 350px;color: black;border: 1px solid #D2D2D2;background: white;cursor: pointer;text-align: left;max-height: 350px;overflow: auto;");
	 
	});
	
	$(document).ready(function(){
		$("#sortable_boxes").sortable({
			update: function(event, ui) {
				var BoxOrder = $(this).sortable("toArray").toString();
				$.cookie("Boxorder", BoxOrder , { expires: 365, path: "/" });
			}
		});

    	var cookie = $.cookie("Boxorder");		
    	if (!cookie) return;
    	var SavedID = cookie.split(",");
	   for (var Sitem=0, m = SavedID.length; Sitem < m; Sitem++) {
           $("#sortable_boxes").append($("#sortable_boxes").children("#" + SavedID[Sitem]));
	       }
	     
	});
	
	
	function tautogrow(id){
		$("textarea#comment-edit-text-" +id).autogrow(); 	
 	};
 	
 	function open_boxsettings() {
		$("div#boxsettings").attr("style","display: block;height:500px;width:300px;");
		$("label").attr("style","width: 150px;");
		};
 	
	function yt_iframe() {
	$("iframe").load(function() { 
	var ifr_src = $(this).contents().find("body iframe").attr("src");
	$("iframe").contents().find("body iframe").attr("src", ifr_src+"&wmode=transparent");
    });

	};

	function scrolldown(){
			$("html, body").animate({scrollTop:$(document).height()}, "slow");
			return false;
		};
		
	function scrolltop(){
			$("html, body").animate({scrollTop:0}, "slow");
			return false;
		};
	 	
	$(window).scroll(function () { 
		
		var footer_top = $(document).height() - 30;
		$("div#footerbox").css("top", footer_top);
	
		var scrollInfo = $(window).scrollTop();      
		
		if (scrollInfo <= "900"){
      $("a#top").attr("id","down");
      $("a#down").attr("onclick","scrolldown()");
	 	$("img#scroll_top_bottom").attr("src","view/theme/diabook/icons/scroll_bottom.png");
	 	$("img#scroll_top_bottom").attr("title","Scroll to bottom");
	 	} 
	 	    
      if (scrollInfo > "900"){
      $("a#down").attr("id","top");
      $("a#top").attr("onclick","scrolltop()");
	 	$("img#scroll_top_bottom").attr("src","view/theme/diabook/icons/scroll_top.png");
	 	$("img#scroll_top_bottom").attr("title","Back to top");
	 	}
		
    });
  

	function insertFormatting(comment,BBcode,id) {
	
		var tmpStr = $("#comment-edit-text-" + id).val();
		if(tmpStr == comment) {
			tmpStr = "";
			$("#comment-edit-text-" + id).addClass("comment-edit-text-full");
			$("#comment-edit-text-" + id).removeClass("comment-edit-text-empty");
			openMenu("comment-edit-submit-wrapper-" + id);
								}

	textarea = document.getElementById("comment-edit-text-" +id);
	if (document.selection) {
		textarea.focus();
		selected = document.selection.createRange();
		if (BBcode == "url"){
			selected.text = "["+BBcode+"]" + "http://" +  selected.text + "[/"+BBcode+"]";
			} else			
		selected.text = "["+BBcode+"]" + selected.text + "[/"+BBcode+"]";
	} else if (textarea.selectionStart || textarea.selectionStart == "0") {
		var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		if (BBcode == "url"){
			textarea.value = textarea.value.substring(0, start) + "["+BBcode+"]" + "http://" + textarea.value.substring(start, end) + "[/"+BBcode+"]" + textarea.value.substring(end, textarea.value.length);
			} else
		textarea.value = textarea.value.substring(0, start) + "["+BBcode+"]" + textarea.value.substring(start, end) + "[/"+BBcode+"]" + textarea.value.substring(end, textarea.value.length);
	}
	return true;
	}

	function cmtBbOpen(id) {
	$(".comment-edit-bb-" + id).show();
	}
	function cmtBbClose(id) {
	$(".comment-edit-bb-" + id).hide();
	}
	
	/*$(document).ready(function(){
	var doctitle = document.title;
	function checkNotify() {
	if(document.getElementById("notify-update").innerHTML != "")
	document.title = "("+document.getElementById("notify-update").innerHTML+") " + doctitle;
	else
	document.title = doctitle;
	};
	setInterval(function () {checkNotify();}, 10 * 1000);
	})*/
</script>
<script>
var pagetitle = null;
$("nav").bind('nav-update',  function(e,data){
  if (pagetitle==null) pagetitle = document.title;
  var count = $(data).find('notif').attr('count');
  if (count>0) {
    document.title = "("+count+") "+pagetitle;
  } else {
    document.title = pagetitle;
  }
});
</script>
