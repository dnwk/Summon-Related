<?php
include_once "rta_f.php";
header('Content-type: application/json');
header("Cache-Control: no-cache, must-revalidate");
$id_tmp=$_GET['id'];
$itemtype=$_GET['itemtypes'];
$ids=explode( '_', $id_tmp );
$ida=explode( '-', $id_tmp );
$id_old1=substr($ids[2],0,-1);
if($ids[1]=='georgemason') {
	$id_old1="m".$id_old1;
}
$id=$id_old1;
$vas= array();
	$debug="Start";
//if get empty try trueID

	//if GW/GT
	if(substr($id,0,1)=='m'||substr($id,0,1)=='b'||$itemtype=='eBook'||$ida[1]=='LOGICAL'||substr($ida[1],0,6)=='ebrary'){
		$debug.="/In TRUE ID Mode/";
			//try true ID first
		$id2=trueid($id_tmp);
		//if gt bib contain x

			$info=request_status($id2[0],'bibid');

			$vas=getstatus_rta($info[1],$id);
		
		$debug.="/In ISBN Mode AFTER TRUE ID/";
		//if still fail, try ISBN
		if(empty($vas)){
			$info=request_status($id2[1],'isbn');
			if($info[0] == 200){
				$vas=getstatus_rta($info[1],$id);
			}
		}
		
	}else{//if alreay wrlc id*/
	$debug.="/In WRLC MODE/";
	$info=request_status($id,'bibid');
	if($info[0] == 200){
		$vas=getstatus_rta($info[1],$id);
	}
	}
   $debug=$id_old;

	if(empty($vas)&&$ids[1]=='georgemason'){
	$json3 = file_get_contents('http://wrlcapi.wrlc.org/bibstatus/gm.php?id='.$id_old);
	$data2 = simplexml_load_string($json3);
	$busa=(array)$data2->bibrecord->item;
	array_push($vas,array("library"=>"George Mason","location"=>"GM:".$busa['location'],"availability"=>$busa['status'],"callno"=>$busa['callnumber'],"link"=>" ",'bibid'=>" "));
	
	}

if($id2[3]==1){
	array_push($vas,array("library"=>"Catholic","location"=>"CU: Electronic Resources" ,"availability"=>'<span style="color:#090">Available</span> Click on item title to access.',"callno"=>"&nbsp;","link"=>"&nbsp;","debug"=>$debug));
}
if(empty($vas)&&$itemtype=='Dissertation'){
	array_push($vas,array("library"=>"Check Availability","location"=>"Dissertation Citation" ,"availability"=>"Please click on the item title for additional information","callno"=>"&nbsp;","link"=>"&nbsp;","debug"=>$debug));
}

if(empty($vas)){
	array_push($vas,array("library"=>"Check Availability","location"=>"Not Available" ,"availability"=>"Please click on the item title for additional information","callno"=>"&nbsp;","link"=>"&nbsp;","debug"=>$debug));
}
echo $_GET['callback'] . '('.json_encode($vas).')';

?>
