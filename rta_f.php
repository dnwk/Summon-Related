<?php
function hmacsha1($key, $data)
{
        $blocksize=64;
        $hashfunc='sha1';
        if (strlen($key)>$blocksize) {
            $key=pack('H*', $hashfunc($key));
        }
        $key=str_pad($key, $blocksize, chr(0x00));
        $ipad=str_repeat(chr(0x36), $blocksize);
        $opad=str_repeat(chr(0x5c), $blocksize);
        $hmac = pack(
            'H*', $hashfunc(
                ($key^$opad).pack(
                    'H*', $hashfunc(
                        ($key^$ipad).$data
                    )
                )
            )
		);
return base64_encode($hmac);
}

function trueid($id){

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (CUA Query agent, lina@cua.edu, Rev 3.0) Summon RTA 1.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, 156, 15000); 
	$url="http://api.summon.serialssolutions.com/2.0.0/search?s.q=ID:".$id;
	curl_setopt($ch, CURLOPT_URL,$url);
	$construct= "application/json" . "\n" . date('D, d M Y H:i:s T') . "\n" .  "api.summon.serialssolutions.com" ."\n" .  "/2.0.0/search" . "\n" . "s.q=ID:" .$id. "\n";
	 $header=array('Accept: application/json','x-summon-date: '.date('D, d M Y H:i:s T'),'Authorization: Summon cua;'.hmacsha1('[put your Summon API ID here]',$construct));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $html = curl_exec($ch);
	$info = curl_getinfo($ch);
		if($info['http_code'] == 200){
		$returns=json_decode($html,true);
		}else{
			return array($id,' ',$calno);
		}
	$compid=array();
	
	//get isbn number
	$isbn=$returns['documents'][0]['ISBN'][0];
	$fulltext=$returns['documents'][0]["hasFullText"];
	$dbids=array();
	$dbids[$returns['documents'][0]['DBID'][0]]=$returns['documents'][0]['ExternalDocumentID'][0];
	$compid[$returns['documents'][0]['DBID'][0]]=$returns['documents'][0]['ExternalDocumentID'][0];
	if(in_array(array('Z6W','W9Z','-1A'),$returns['documents'][0]['DBID'][0])){
		$prebib1=$returns['documents'][0]['ExternalDocumentID'][0];	
	}else{
		
		switch ($returns['documents'][0]['DBID'][0])
					{
					case '7RG': //geroge mason
					  $pribib3='m'.$returns['documents'][0]['ExternalDocumentID'][0];
					  break;
					case 'Z6W': //wrlc
					  $pribib2=$returns['documents'][0]['ExternalDocumentID'][0];
					  break;
					 case '-1A': //wrlc
					  $pribib2=$returns['documents'][0]['ExternalDocumentID'][0];
					  break;
					case 'W9Z': //cua
					  $pribib1=$returns['documents'][0]['ExternalDocumentID'][0];
					  break;
					case '7Q1': //gerogetown
					  $pribib3=$returns['documents'][0]['ExternalDocumentID'][0];
					  break;		 
					case '-G0': //gerogetown
					  $pribib3=$returns['documents'][0]['ExternalDocumentID'][0];
					  break;
					} 					
	}
		
		if(!empty($returns['documents'][0]['peerDocuments'])){
				foreach($returns['documents'][0]['peerDocuments'] as $pedoc){
				
					if(is_array($dbids[$pedoc['DBID'][0]])){
						array_push($dbids[$pedoc['DBID'][0]],$pedoc['ExternalDocumentID'][0]);
					}else{
						$dbids[$pedoc['DBID'][0]]=array();
						array_push($dbids[$pedoc['DBID'][0]],$pedoc['ExternalDocumentID'][0]);
					}
				
					
					switch ($pedoc['DBID'][0])
					{
					case '7RG': //geroge mason
					  $pribib3='m'.$pedoc['ExternalDocumentID'][0];
					  break;
					case 'Z6W': //wrlc
					  $pribib2=$pedoc['ExternalDocumentID'][0];
					  break;
					 case '-1A': //wrlc
					  $pribib2=$pedoc['ExternalDocumentID'][0];
					  break;
					case 'W9Z': //cua
					  $pribib1=$pedoc['ExternalDocumentID'][0];
					  break;
					case '7Q1': //gerogetown
					  $pribib3=$pedoc['ExternalDocumentID'][0];
					  break;		 
					case '-G0': //gerogetown
					  $pribib3=$pedoc['ExternalDocumentID'][0];
					  break;
					} 				
				}//foreach ends
		}
	//use WRLC bib id First
	if(isset($pribib3)) $prebib=$pribib3;
	if(isset($pribib2)) $prebib=$pribib2;
	if(isset($pribib1)) $prebib=$pribib1;
	
	
	if(!isset($prebib)&&!in_array('7RG',$returns['documents'][0]['DBID'])) $prebib=$returns['documents'][0]['ExternalDocumentID'][0];
	if(!isset($prebib)&&in_array('7RG',$returns['documents'][0]['DBID'])) $prebib='m'.$returns['documents'][0]['ExternalDocumentID'][0];
	curl_close($ch);

	return array($prebib,$isbn,$dbids,$fulltext);
	
}


function getstatus($jsonp,$id){
	return  getstatus_rta($jsonp,$id);
}


//pharse status
function getstatus_rta($jsonp,$id){
	$vas= array();
	$existinglib=array();
	$noneexistinglib=array();
	$noneexistinglibfull=array();
	$existinglibfull=array();
	$displaycount=0;
	$jsarray = json_decode($jsonp,true);
	if(empty($jsarray["holdings"])){
		return $vas;
	}

	foreach ($jsarray["holdings"] as $key => $value){
		$avs=false;
		unset($link_term);
		unset($link_term2);
		unset($request);
		$real_ID=$value[BIB_ID];
		$libcode=$value[LIBRARY_NAME];
		$libraryfullname=$value[LIBRARY_FULL_NAME]!=NULL ? $value[LIBRARY_FULL_NAME] : $value[LIBRARY_NAME];
		//filter
		//$donotshow=array('HI','HL','JB','HS','AL');
		if (in_array($libcode, $donotshow) && $value[LIBRARY_NAME]!='CU') continue;
		$donotshow_location=array('AU: Electronic Books','GT: INTERNET','GW: Online','OL: Open Library','GT: Qatar Stacks','GM: Electronic Resource (available through Internet/WWW)','GA: UNIV Electronic Book','INTERNET');
		if (in_array($value[LOCATION_DISPLAY_NAME], $donotshow_location)) continue;	
		//set location name
		$locationname=isset($value["LOCATION_DISPLAY_NAME"]) ? $value["LOCATION_DISPLAY_NAME"] : $value[LIBRARY_NAME]; 
		$cleanlocation=explode(":",$locationname);
		if(isset($cleanlocation[1])){
		$locationname=$libraryfullname." ".$cleanlocation[1];
		}else{
			$locationname=$libraryfullname." ".$locationname;
		}
		//set callnumber
		$callno=isset($value["ITEMS"][0]["DISPLAY_CALL_NO"]) ? $value["ITEMS"][0]["DISPLAY_CALL_NO"] : $value["DISPLAY_CALL_NO"];
	
		
		//get loc
		$availability=isset($value["ITEMS"][0]["ITEM_STATUS_DESC"]) ? $value["ITEMS"][0]["ITEM_STATUS_DESC"] :  $value["AVAILABILITY"]["ITEM_STATUS_DESC"];
		$temploc=isset($value["ITEMS"][0]["TEMPLOCATION"]) ? $value["ITEMS"][0]["TEMPLOCATION"] :  $value["AVAILABILITY"]["TEMPLOCATION"];
		$permloc=isset($value["ITEMS"][0]["PERMLOCATION"]) ? $value["ITEMS"][0]["PERMLOCATION"] :  $value["AVAILABILITY"]["PERMLOCATION"];
		
		if(preg_match('/(available)/i',$availability)) $availability="Not Charged";
		
		//Set Ava language
		$availability_term=$availability=="Not Charged" ? '<span style="color:#090">Available</span>' : $availability;
		//Set Ava language
		//$availability_term=$availability=="AVAILABLE" ? '<span style="color:#090">Available</span>' : $availability;
		
		if(($availability=="Not Charged"&&$value["ELIGIBLE"]==TRUE)||$value["LOCATION_NAME"]=='sharedebsc') $avs=TRUE;
		if($value["LOCATION_NAME"]=='E-Pub'&&$value["LIBRARY_NAME"]=="E-GovDoc") $avs=TRUE;
		if(stristr($temploc,'Reserve')||stristr($permloc,'Reserve')){
			$reservelocation= isset($temploc)? $temploc : $permloc;
			$availability_term='<span style="color:#7039FF">On&nbsp;Reserve at '.$reservelocation.'</span>';
			$avs=TRUE;
		}
		if(stristr($temploc,'WRLC')){
			$availability_term='<span style="color:#707E18">WRLC Shared Collections Facility</span>';
			$avs=TRUE;
		}
		
		if(!isset($availability_term) && $value["ELIGIBLE"]==TRUE) $availability_term="No status available";

		if($avs==FALSE&&($availability=='Renewed'||$availability=='Charged'))
		{
				$availability_term = "Checked Out. ";
		}
		
		if($avs==FALSE&&preg_match('(Lost)',$availability))
		{
				$availability_term = "Lost";
		}
		
		
		
		//end set ava
		
		
		//set request
		$request=$value["ELIGIBLE"];
		$norequest=array('CU','AL','HL','JB');
		//make sure it is eligiable for CLS for it to show up
		if($value["LIBRARY_NAME"]=='CU') $request=FALSE;
		if($value["ELIGIBLE"]==TRUE&&$value["LIBRARY_NAME"]!='CU'){	
		$link_term='<a href="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:RTA&bibid='.$real_ID.'">Request</a>';
		$link_term2="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:Detail-Page&bibid=".$real_ID;
		}
		//check if off-site
		if(stristr($temploc,'WRLC')!=false&&$value["LIBRARY_NAME"]=='CU'){	
		$link_term='<a href="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:RTA&bibid='.$id.'">Request</a>';
		$link_term2="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:Detail-Page&bibid=".$id;
		$request=TRUE;
		}
		if(!isset($request)) $request=false;
		
		//set stackmap
		if($value["LOCATION_NAME"]=='cuml stk'&&!isset($value["AVAILABILITY"]["TEMPLOCATION"])&&$avs==TRUE){
	
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term='.$callno.'", "newwindow", "width=750, height=650"); return false;\'  href="#">'.$callno.'</a><small>&lt;-Click on call number to see where it is!</small>';
			
		}
		if($value["LOCATION_NAME"]=='cuml juvf'&&!isset($temploc)&&$avs==TRUE){
			
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term=cumljuvf", "newwindow", "width=750, height=650"); return false;\' >'.$callno.'</a><small>&lt;-Click on call number to see where it is!</small>';
		}
		if($value["LOCATION_NAME"]=='cuml foli'&&!isset($temploc)&&$avs==TRUE){
		
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term=cumlfoli", "newwindow", "width=750, height=650"); return false;\' href="">'.$callno.'</a>';
		}
		if($value["LOCATION_NAME"]=='cuml per'&&!isset($temploc)&&$avs==TRUE){
		
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term=cumlper", "newwindow", "width=750, height=650"); return false;\' href="#">'.$callno.'</a>';
		}
		
		//misc
		
		if($callno==NULL) $callno="";
		if($link_term==NULL) $link_term="";
		if($link_term2==NULL) $link_term2="";
		
		
		if($libcode=='GM'&&(strlen($availability_term)<=2||preg_match("/(George Mason Holdings)/i",$locationname))){
			$debug .= $real_ID;
			$tmpgm=convert_GM($real_ID);
			if($tmpgm[0]=='Available'){
				$availability_term='<span style="color:#090">Available</span>';
				$locationname="George Mason[1]:".$tmpgm[1];
				$callno=$tmpgm[2];
				$avs=TRUE;
			}else{
				$availability_term=$tmpgm[0];
				if(strlen($tmpgm[1])>=6)$locationname="GM:".$tmpgm[1];
			}
			//$availability_term="No status available";
			
		}
		if(preg_match("/(George Mason Holdings)/i",$locationname)) $locationname="George Mason: Library";
		if(preg_match("/(Georgetown Holdings)/i",$locationname)) $locationname="Georgetown";
			if($avs==TRUE&&!in_array($libraryfullname,$existinglibfull)){$special="primary";}
			if($avs==TRUE&&in_array($libraryfullname,$existinglibfull)){$special="additional";}
			
			if($avs==FALSE&&in_array($libraryfullname,$existinglibfull)){$special="additional";}
			if($avs==FALSE&&!in_array($libraryfullname,$existinglibfull)){$special="equal";}
		
		if($avs==FALSE){
			if(in_array('GT',$existinglib)&&$libcode=='GT') continue;
			if(in_array('GM',$existinglib)&&$libcode=='GM') continue;
		}
		//keep track of existing lib
		if($avs==TRUE)	{
		 array_push($existinglib,$libcode);
		 array_push($existinglibfull, $libraryfullname);
		
		}else{
		array_push($noneexistinglib,$libcode);
		array_push($noneexistinglibfull, $libraryfullname);
		}
		
		
		if(strlen($availability_term)<2) $availability_term="No status available";
		$libraryfullname=str_replace(' ','&nbsp;',$libraryfullname);
		if($value[LIBRARY_NAME]=='CU'){
		array_unshift($vas,array("library"=>$libraryfullname,"location"=>$locationname,"availability"=>$availability_term,"callno"=>$callno,"link"=>$link_term,'reserve'=>$link_term2,'bibid'=>$real_ID,'request'=>$request,'requestbib'=>$real_ID,'debug'=>$debug));
		}else{
			array_push($vas,array("library"=>$libraryfullname,"location"=>$locationname,"availability"=>$availability_term,"callno"=>$callno,"link"=>$link_term,'reserve'=>$link_term2,'bibid'=>$real_ID,'request'=>$request,'priority'=>$special,'requestbib'=>$real_ID,'debug'=>$debug));
		
			$displaycount=$displaycount+1;
			unset($special);
		}
		//unset variables
		unset($libraryfullname);
		unset($locationname);
		unset($availability_term);
		unset($callno);
		unset($special);
		unset($link_term);
		unset($link_term2);
	}//foreach end
	

return $vas;

}


function get_duedate($id){
	$json = file_get_contents('http://www.lib.cua.edu/z39/ava.php?bibid='.$id);
	$data = json_decode($json, TRUE);
	$timestr=strtotime($data["holdings"]["holding"]['circulations']['circulation']['availabilityDate']);
	if($timestr!=FALSE)
	{
		return date('m/d/Y',$timestr);	
	}else{
		return false;	
	}
}


function convert_GM($wrid){
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (CUA Query agent, lina@, Rev 3.0) Navigator/9.0.0.6');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, 156, 15000); 
		curl_setopt($ch, CURLOPT_URL,'http://www.lib.cua.edu/z39/ava.php?bibid='.$wrid);
		$jsonp= curl_exec($ch);
		$info = curl_getinfo($ch);
	
	$result=json_decode($jsonp,true);
	foreach($result['bibliographicRecord']['record']['datafield'] as $key=>$val){
		
		 if($val["@attributes"]["tag"]=='035'&&is_numeric($val['subfield'])){
			$usr=$val['subfield'];
		 }

	}
	if(strlen($usr)>=5){
	$json = file_get_contents('http://wrlcapi.wrlc.org/bibstatus/gm.php?id='.$usr);
	$data = simplexml_load_string($json);
	$bus=(array)$data->bibrecord->item;
	return array($bus['status'],$bus['location'],$bus['callnumber']);
	}
	
	
	
}



//request status
function request_status($id,$tpy,$notes=''){

	$baseurl="http://findit.library.gwu.edu";

$ch = curl_init();
switch ($tpy) {
   case 'bibid':	
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (CUA Query agent, lina@, Rev 3.0) Navigator/9.0.0.6');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, 156, 15000); 
		curl_setopt($ch, CURLOPT_URL,$baseurl.'/item/'.$id.'.json');
		$jsonp= curl_exec($ch);
		$info = curl_getinfo($ch);
		break;
  case 'isbn':

  
 		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (CUA Query agent, lina@, Rev 3.0) Navigator/9.0.0.6');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch,	CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_URL,$baseurl.'/isbn/'.$id);
		$jsonp= curl_exec($ch);
		$headerinfo=curl_getinfo($ch);
		curl_close($ch); 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (CUA Query agent, lina@cua.edu, Rev 3.0) Navigator/9.0.0.6');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, 156, 15000); 
		curl_setopt($ch, CURLOPT_URL,$headerinfo['CURLINFO_EFFECTIVE_URL'].".json");
		$jsonp= curl_exec($ch);
		$info = curl_getinfo($ch);
		break;
	}
		curl_close($ch); 
	   return array($info['http_code'],$jsonp,$headerinfo);
}





?>
