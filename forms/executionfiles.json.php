<?php
/**
 * @version		$Id: edit_process.php,v 1.8 2013-04-16 17:45:40 juanca Exp $
 * @package		VPL. process submission edit
 * @copyright	2012 Juan Carlos Rodríguez-del-Pino
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author		Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

define('AJAX_SCRIPT', true);
$outcome = new stdClass();
$outcome->success = true;
$outcome->response = new stdClass();
$outcome->error = '';
try{
	require_once dirname(__FILE__).'/../../../config.php';
	require_once dirname(__FILE__).'/../locallib.php';
	require_once dirname(__FILE__).'/../vpl.class.php';
	if(!isloggedin()){
		throw new Exception(get_string('loggedinnot'));
	}
	
	$id      = required_param('id', PARAM_INT); // course id
	$action  = required_param('action', PARAM_ALPHANUMEXT);
	$vpl = new mod_vpl($id);
	//TODO use or not sesskey 
	//require_sesskey();
	require_login($vpl->get_course(),false);
	$vpl->require_capability(VPL_MANAGE_CAPABILITY);
	$PAGE->set_url(new moodle_url('/mod/vpl/forms/executionfiles.json.php', array('id'=>$id, 'action'=>$action)));
	echo $OUTPUT->header(); // Send headers.
	$data=json_decode(file_get_contents('php://input'));
    switch ($action) {
	case 'save':
		$postfiles=(array)$data;
		foreach($postfiles as $name => $data){
			$files[]=array('name' => $name, 'data' => $data);
		}
		$fgm = $vpl->get_execution_fgm();
		$filelist =$fgm->getFileList();
		//TODO Make new file_group operation to do it better
		for($i=count($filelist)-1; $i>=0; $i--){
			$fgm->deleteFile($i);
		}
    	foreach($postfiles as $name => $data){
			$fgm->addFile($name, $data);
		}
	break;
    default:
			throw new Exception('ajax action error');
  }
}catch(Exception $e){
	$outcome->success =false;
	$outcome->error = $e->getMessage();
}
echo json_encode($outcome);
die();
