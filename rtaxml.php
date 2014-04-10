<?php
include_once "func2.0.php";
header('Content-type: text/xml');
header("Cache-Control: no-cache, must-revalidate");
$id_tmp=$_GET['id'];
$source=$_GET['source'];
$id_type=substr($id_tmp,0,1);
$id_type2=substr($id_tmp,0,2);
$vas= array();
	if(id_type=="m"&&$source!="georgemason") $source="georgemason"; 
	
	if($source=='georgemason'&&$id_type!='m') $id_tmp='m'.$id_tmp;
	if($id_type2=='bb') $id_tmp=substr($id_tmp,1,strlen($id_tmp)-1);
	$info=request_status($id_tmp,'bibid');
	if($info[0] == 200){
		$vas=getstatus_rta($info[1],$id_tmp);
	}


	if(empty($vas)&&$source=='georgemason'){
		$vas=get_status_gmz39($id_tmp);
	}



if(empty($vas)){
	array_push($vas,array($id_tmp=>array('id'=>$id_tmp,"availability"=>'false',"availability_message"=>"Status not available/BibID:".$_GET['id'],"location"=>" ","LocationList"=>'false','reserve'=>'false',"reserve_message"=>' ',"callno"=>" ")));
}


$rtaXML = new SimpleXMLElement("<records></records>");

foreach($vas as $key=>$val){
  $rtaBib[$key]=$rtaXML->addChild('bibrecord');
  $rtaBib[$key]->addAttribute('id',$key);

	foreach($val as $copy=>$item){
	$bu=$rtaBib[$key]->addChild('item');
	$bu->addAttribute('copy',$copy);
	$bu->addChild('status',$item['availability_message'] );
	$bu->addChild('location', $item['location'] );
	$bu->addChild('callnumber', $item['callno'] );
	//$bu->addChild('reserve_message', html_entity_decode ($item['reserve_message']));
	}

}
echo $rtaXML->asXML();

?>
