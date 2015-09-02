<?php

#	--- About ----------------------
#
#	Phinstagram - Instagram scrape/API/proxy 
#	Author: Johan Sandén
#	@Sounden
#	www.sounden.com
#	copywrong 2015
#	
#	--------------------------------

if($_GET['username'])
		$username = $_GET['username'];
	else
		$username = "sounden";

if($_GET['limit'])
		$limit = $_GET['limit'];
	else
		$limit = 0;

# define variable for local environment
define("TMP_DIR", "/tmp");
define("CACHE_FILE_NAME",$username.".json");

# define cache time 5 minutes default //
define("LOCAL_CACHE_IN_SECONDS", 300);
// Create DOM from URL or file

if (file_exists(TMP_DIR."/".CACHE_FILE_NAME) && (filemtime(TMP_DIR."/".CACHE_FILE_NAME) > (time() - LOCAL_CACHE_IN_SECONDS ))) 
{
		// fetch from caceh //
		//echo "fetch from cache";
		$phinstagram_json_object = json_decode(file_get_contents(TMP_DIR."/".CACHE_FILE_NAME));
}
else
{
		// get a fresh download //
		//echo "fetch from cache";
		$html = file_get_contents('https://instagram.com/'.$username);

		// Create a new DOMDocument to parse the result in
		$doc = new DOMDocument();

		//supress the HTML5 tags warning //
		libxml_use_internal_errors(true);

		// Load content from Instagram //
		$doc->loadHTML($html);

		// set errors back to normal state //
		libxml_use_internal_errors(false);

		// Create a new Xpath from domDoc //
		$xpath = new DOMXPath($doc);

		//query all the script tags // 
		$tags = $xpath->query('//script');

		//loop through each tag to find the magic content of JSON //
		foreach ($tags as $tag) {

				// lets find a match for window._jscalls //
				if(strlen($tag->nodeValue) > 10000)
				{
				
				$arr = explode("\n", $tag->nodeValue);
					
				// check each rows for userprofile string
				foreach($arr as $k => $value) 
				{
		                	if(preg_match('/window._sharedData/i',$value)) 
		                	{
		                		$useIndex = $k;
		                	}
		        	}

		        // set the index //
        		$useIndex?$useIndex:'';

        		$json_string = $arr[$useIndex];
				$json_string = trim($json_string);
				
				$json_string = str_replace("window._sharedData = ",'',$json_string);
				$json_string = substr_replace($json_string ,"",-1,1);

				// decode the string to an object //
			    	$phinstagram_json_object = json_decode($json_string);

							if($phinstagram_json_object == NULL) 
							{
									   
									// return last working json string if it exists //
									if (file_exists(TMP_DIR."/".CACHE_FILE_NAME))
									{
										$phinstagram_json_object = json_decode(file_get_contents(TMP_DIR."/".CACHE_FILE_NAME));
									}
									else
									{
										//oh nooo .. we didnt have a stored old json string on disk.. nor parsing instagram.com site succeeded!!! FAIL, DIE!
										$phinstagram_json_object = array("error" => json_last_error());
									}


							}
							else
							{
									// save to local cache if the new one works //
									file_put_contents(TMP_DIR."/".CACHE_FILE_NAME, $json_string);
							}
				}

		} #end tag loop #

} #end if statment time cache #
header('Content-type: application/json');
echo json_encode($phinstagram_json_object);
?>
