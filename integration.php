<?php

$connOR = oci_connect('test','test','//93.115.136.18:4024/clouddev.world', 'AL32UTF8');
if (!$connOR) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$servername = "185.181.230.88";
$username = "unisimso_site22";
$password = "61rzRv+B36;mAW";
$dbname = "unisimso_site22unamd";
    	
//Connection
$connWP = new mysqli($servername, $username, $password, $dbname);
//Check connection
if ($connWP->connect_error) {
	die("Connection failed: " . $connWP->connect_error);
}

$meta_key = array('interview_dates_0_vacancy_date', 'interview_dates_1_vacancy_date');
$Dates = array();
array_push($Dates, $_POST['date1'], $_POST['date2']);

function insertDatesIntoOracle($connOR, $connWP, $post_ID, $meta_key, $Dates) {
    
    $sql_ispostID = "select * from VMTESTINTEGRATIONWP where POST_ID=:vm_POST_ID";
    $compiled_postID = oci_parse($connOR, $sql_ispostID);
    oci_bind_by_name($compiled_postID, ':vm_POST_ID', $post_ID);
        
    $result_postID = oci_execute($compiled_postID);
        
    $fetchedArr = oci_fetch_array($compiled_postID, OCI_ASSOC+OCI_RETURN_NULLS);
    
    for ($i = 0; $i < count($Dates); $i++) {

        if ($fetchedArr['POST_ID'] == null && $fetchedArr['META_KEY'] == null) {
            
            $sql_insert = 'INSERT INTO VMTESTINTEGRATIONWP (POST_ID, META_KEY, META_VALUE)'.
               'VALUES(:vm_POST_ID, :vm_META_KEY, :vm_META_VALUE)';
    
            $compiled_insert = oci_parse($connOR, $sql_insert);
        
            oci_bind_by_name($compiled_insert, ':vm_POST_ID', $post_ID);
            oci_bind_by_name($compiled_insert, ':vm_META_KEY', $meta_key[$i]);
            oci_bind_by_name($compiled_insert, ':vm_META_VALUE', $Dates[$i]);
            
            $result_insert = oci_execute($compiled_insert);
            
            if (!$result_insert) {
                $e = oci_error($compiled_insert);
                trigger_error(htmlentities($e['message']), E_USER_ERROR);
            }
        } else {
            $sql_update = "UPDATE VMTESTINTEGRATIONWP SET META_VALUE=:vm_META_VALUE WHERE POST_ID=:vm_POST_ID AND META_KEY=:vm_META_KEY";
    
            $compiled_update = oci_parse($connOR, $sql_update);
        
            oci_bind_by_name($compiled_update, ':vm_POST_ID', $post_ID);
            oci_bind_by_name($compiled_update, ':vm_META_KEY', $meta_key[$i]);
            oci_bind_by_name($compiled_update, ':vm_META_VALUE', $Dates[$i]);
            
            $result_update = oci_execute($compiled_update);
            
            if (!$result_update) {
                $e = oci_error($compiled_update);
                trigger_error(htmlentities($e['message']), E_USER_ERROR);
            }
        }
        
        

    	$wp_result = $connWP->query("UPDATE wp_postmeta SET meta_value='$Dates[$i]' WHERE post_id='$post_ID' AND meta_key='$meta_key[$i]'");

    	if (!$wp_result) {
    	    print_r($connWP->eror);
    	} else {
    	    echo 'OK';
    	}
    }
}
insertDatesIntoOracle($connOR, $connWP, 271, $meta_key, $Dates);
