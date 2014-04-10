<?php

//pharse status
function getstatus_rta($jsonp,$id){
	$vas= array();
	$existinglib=array();
	$noneexistinglib=array();
	$displaycount=0;
	$jsarray = json_decode($jsonp,true);
	if(empty($jsarray["holdings"])){
		return $vas;
	}

	foreach ($jsarray["holdings"] as $key => $value){
		$avs=false;
		$brdgeid="false";
		unset($link_term);
		unset($request);
		unset($availability_term);
		$real_ID=$value[BIB_ID];
		$libcode=$value[LIBRARY_NAME];
		$libraryfullname=$value[LIBRARY_FULL_NAME]!=NULL ? $value[LIBRARY_FULL_NAME] : $value[LIBRARY_NAME];
		//filter
		$donotshow=array('HI','HL','JB','HS');
		if (in_array($libcode, $donotshow) && $value[LIBRARY_NAME]!='CU') continue;
		$donotshow_location=array('AU: Electronic Books','GT: INTERNET','GW: Online','OL: Open Library','GT: Qatar Stacks','GM: Electronic Resource (available through Internet/WWW)');
		if (in_array($value[LOCATION_DISPLAY_NAME], $donotshow_location)) continue;
		
		//set location name
		$locationname=$value["LOCATION_DISPLAY_NAME"]!=NULL ? $value["LOCATION_DISPLAY_NAME"] : $value['LIBRARY_NAME']; 
		if($libcode=='GM'||$libcode=='GT') $locationname=$libcode.":".$locationname;
		//set callnumber
		$callno=$value[AVAILABILITY][DISPLAY_CALL_NO]!=NULL ? $value[AVAILABILITY][DISPLAY_CALL_NO] : $value[DISPLAY_CALL_NO];
		//get loc
		$availability=$value[ITEMS][0][ITEM_STATUS_DESC]!=NULL ? $value[ITEMS][0][ITEM_STATUS_DESC] :  $value[AVAILABILITY][ITEM_STATUS_DESC];
		$temploc=$value[ITEMS][0]["TEMPLOCATION"]!=NULL ? $value[ITEMS][0]["TEMPLOCATION"] :  $value[AVAILABILITY]["TEMPLOCATION"];
		$permloc=$value[ITEMS][0]["PERMLOCATION"]!=NULL ? $value[ITEMS][0]["PERMLOCATION"] :  $value[AVAILABILITY]["PERMLOCATION"];
		
		//Set Ava language
		$availability_term=$availability=="Not Charged" ? '<span style="color:#090">Available</span>' : $availability;
		if($availability_term=="AVAILABLE") $availability_term = '<span style="color:#090">Available</span>';
		if(($availability=="Not Charged"&&$value[ELIGIBLE]==TRUE)||$value[LOCATION_NAME]=='sharedebsc') $avs=TRUE;
		if($value[LOCATION_NAME]=='E-Pub'&&$value[LIBRARY_NAME]=="E-GovDoc") $avs=TRUE;
		if(stristr("PERMLOCATION",'Reserve')||stristr($permloc,'Reserve')){
			$reservelocation= $temploc != NULL ? $temploc : $permloc;
			$availability_term='<span style="color:#7039FF">On Reserve</span>';
			$avs=TRUE;
		}
		if(stristr($temploc,'WRLC')){
			$availability_term='<span style="color:#707E18">WRLC Shared Collections Facility</span>';
			$avs=TRUE;
		}
		
		if(!isset($availability_term) && $value[ELIGIBLE]==TRUE) $availability_term="No status available";

		if($avs==FALSE&&($availability=='Renewed'||$availability=='Charged'))
		{
				$availability_term = "Checked Out. ";
		}
		//end set ava
		
		
		//set request
		$request=$value["ELIGIBLE"];
		$norequest=array('CU','AL','HL','JB');
		//make sure it is eligiable for CLS for it to show up
		if($value[LIBRARY_NAME]=='CU') $request=FALSE;
		if($value[ELIGIBLE]==TRUE&&$value[LIBRARY_NAME]!='CU'){	
		$link_term='<a href="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:RTA&bibid='.$value[BIB_ID].'">Request</a>';
		}
		//check if off-site
		if(stristr($value[AVAILABILITY][TEMPLOCATION],'WRLC')!=false&&$value[LIBRARY_NAME]=='CU'){	
		$link_term='<a href="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:RTA&bibid='.$value[BIB_ID].'">Request</a>';
		$request=TRUE;
		}
		if(!isset($request)) $request=false;
		//set stackmap
		/*
		if($value[LOCATION_NAME]=='cuml stk'&&!isset($value[AVAILABILITY][TEMPLOCATION])&&!in_array($value[AVAILABILITY][ITEM_STATUS_DESC],array("Charged","Renewed"))){
	
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term='.$callno.'", "newwindow", "width=750, height=650"); return false;\'  href="#">'.$callno.'</a><small>&lt;-Click on call number to see where is it!</small>';
			
		}
		if($value[LOCATION_NAME]=='cuml juvf'&&!isset($value[AVAILABILITY][TEMPLOCATION])&&!in_array($value[AVAILABILITY][ITEM_STATUS_DESC],array("Charged","Renewed"))){
			
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term=cumljuvf", "newwindow", "width=750, height=650"); return false;\' >'.$callno.'</a><small>&lt;-Click on call number to see where is it!</small>';
		}
		if($value[LOCATION_NAME]=='cuml foli'&&!isset($value[AVAILABILITY][TEMPLOCATION])&&!in_array($value[AVAILABILITY][ITEM_STATUS_DESC],array("Charged","Renewed"))){
		
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term=cumlfoli", "newwindow", "width=750, height=650"); return false;\' href="">'.$callno.'</a>';
		}
		if($value[LOCATION_NAME]=='cuml per'&&!isset($value[AVAILABILITY][TEMPLOCATION])&&!in_array($value[AVAILABILITY][ITEM_STATUS_DESC],array("Charged","Renewed"))){
		
			$callno='<a class="various" onclick=\'window.open("http://www.lib.cua.edu/stackmap/popup-map.php?term=cumlper", "newwindow", "width=750, height=650"); return false;\' href="#">'.$callno.'</a>';
		}
		*/
		//misc
		
		if($callno==NULL) $callno="";
		if($link_term==NULL) $link_term="";
		
		
		if($libcode=='GM'&&strlen($availability_term)<=2){
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
			$availability_term="No status available";
			
		}
		if($locationname=='GM: Click "George Mason Holdings" for holdings and status') $locationname="George Mason: Library";
		
		if($avs==FALSE){
			$brdgeid="false";
			if(in_array('GT',$existinglib)&&$libcode=='GT') continue;
			if(in_array('GM',$existinglib)&&$libcode=='GM') continue;
		}
		if($avs==TRUE)	{
			$brdgeid="true";
		 array_push($existinglib,$libcode);
		}else{
			array_push($noneexistinglib,$libcode);
		}
		
		
		if(strlen($availability_term)<2) $availability_term="No status available";
		
		$libraryfullname=str_replace(' ','&nbsp;',$libraryfullname);
		if(is_array($vas[$real_ID])){
			$addcopy=1;
		}else{
			$vas[$real_ID]=array();
			$addcopy=0;
		}
		
		if($value[LIBRARY_NAME]=='CU'){

		array_unshift($vas[$real_ID],array('id'=>$real_ID,"availability"=>$brdgeid,"availability_message"=>$availability_term,"location"=>$locationname,"LocationList"=>false,'reserve'=>$request,"reserve_message"=>$link_term,"callno"=>$callno));
		$vas=array($real_ID=>$vas[$real_ID])+$vas;
		}else{
			
		array_push($vas[$real_ID],array('id'=>$real_ID,"availability"=>$brdgeid,"availability_message"=>$availability_term,"location"=>$locationname,"LocationList"=>false,'reserve'=>$request,"reserve_message"=>$link_term,"callno"=>$callno));
			$displaycount=$displaycount+1;
			
		}
		//unset variables
		unset($libraryfullname);
		unset($locationname);
		unset($availability_term);
		unset($callno);
		unset($link_term);
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
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (CUA Query agent, lina@cua.edu, Rev 3.0) Navigator/9.0.0.6');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, 156, 15000); 
		curl_setopt($ch, CURLOPT_URL,'http://www.lib.cua.edu/z39/ava.php?bibid='.$wrid);
		$jsonp= curl_exec($ch);
		$info = curl_getinfo($ch);
	
	$result=json_decode($jsonp,true);
	//print_r($result['bibliographicRecord']['record']['datafield']);
	foreach($result['bibliographicRecord']['record']['datafield'] as $key=>$val){
		
		//$val=(array)$val;
		 if($val["@attributes"]["tag"]=='035'&&is_numeric($val['subfield'])){
			$usr=$val['subfield'];
		 }

	}
	if(strlen($usr)>=5){
	$json = file_get_contents('http://wrlcapi.wrlc.org/bibstatus/gm.php?id='.$usr);
	$data = simplexml_load_string($json);
	$bus=(array)$data->bibrecord->item;
	$bus['callnumber']=str_replace("+"," ",$bus['callnumber']);
	return array($bus['status'],$bus['location'],$bus['callnumber']);
	}
	
	
	
}


function get_status_gmz39($id){
	$id_tmp=substr($id,1,strlen($id)-1);
	$gmxml = file_get_contents('http://www.lib.cua.edu/z39/gm.php?bibid='.$id_tmp);
	$data = simplexml_load_string($gmxml);
	$abus=(array)$data->holdings->holding;
	$dbus=json_decode(json_encode($abus),TRUE);
	if($dbus['circulations']['circulation']['availableNow']['@attributes']['value']=='1'){
		$ava='true';
		$status='<span style="color:#090">Available</span>';
	}else{
		$ava='false';
		$status='Not Available';
	}
	$location=$dbus['localLocation'];
	$callno=$dbus['callNumber'];
	
	return array($id=>array("0"=>array('id'=>$id,"availability"=>$ava,"availability_message"=>$status,"location"=>"GM:".$location,"LocationList"=>false,'reserve'=>'',"reserve_message"=>'',"callno"=>$callno)));
}


//request status
function request_status($id,$tpy){
	$baseurl="http://findit.library.gwu.edu";
	$ch = curl_init();
	if($tpy=='bibid') {
	
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 XML 2.0(CUA Query agent, lina@cua.edu, Rev 3.0) Summon RTA 2.0');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, 156, 15000); 
			curl_setopt($ch, CURLOPT_URL,$baseurl.'/item/'.$id.'.json');
			$jsonp= curl_exec($ch);
			$info = curl_getinfo($ch);
		}
		curl_close($ch); 
return array($info['http_code'],$jsonp,$headerinfo);
}


class SimpleXMLExtended extends SimpleXMLElement {
  public function addCData($cdata_text) {
    $node = dom_import_simplexml($this); 
    $no   = $node->ownerDocument; 
    $node->appendChild($no->createCDATASection($cdata_text)); 
  } 
}


?>
