<?php
// This file is part of Moodle - http://moodle.org/
//
// Signinsheet is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Signinsheet is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
 
/**
 *
 * @package    block_signinsheet
 * @copyright  2013 Kyle Goslin, Daniel McSweeney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * rendersigninsheet.php
 * This file is used for rendering the signin sheet HTML. From this, both full class and
 * group based signinsheets can be generated.
 */

global $CFG, $DB;
require_login();



/*
 * 
 * Retrieve and print the logo for the top of the
 * sign in sheet.
 * 
 * */
function printHeaderLogo(){
	
	global $DB;
	
 	$imageurl =  $DB->get_field('block_signinsheet', 'field_value', array('id'=>1), $strictness=IGNORE_MISSING);
	echo '<img src="'.$imageurl.'"/><br><div style="height:30px"></div>';
	
}


/*
 * 
 * 
 *
 * 
 * */ 
function renderGroup($extra){
		
	global $DB, $cid, $CFG;
	$outputhtml = '';
	
	$cid = required_param('cid', PARAM_INT);
	$selectedgroupid = optional_param('selectgroupsec', '', PARAM_INT);
	$extra = optional_param('extra', '', PARAM_INT);
	
	$appendorder = '';
	$orderby = optional_param('orderby', '', PARAM_TEXT);
	
	
		
		if($orderby == 'byid'){
			$appendorder = ' order by userid';
		}
		else if($orderby == 'firstname'){
			$appendorder = ' order by  (select firstname from '.$CFG->prefix.'user usr where userid = usr.id)';
		}
		else if($orderby == 'lastname'){
			$appendorder = ' order by  (select lastname from '.$CFG->prefix.'user usr where userid = usr.id)';
		}
		 else {
			$appendorder = ' order by userid';
		}
	
	

	
			// Check if we need to include a custom field
	$addfieldenabled = get_config('block_signinsheet', 'includecustomfield');
	
	
	
	$groupname = $DB->get_record('groups', array('id'=>$selectedgroupid), $fields='*', $strictness=IGNORE_MISSING); 
	
	$query = 'select * from '.$CFG->prefix.'groups_members where groupid = ?' . $appendorder;
	
	
	
	$result = $DB->get_records_sql($query,array($selectedgroupid));
	$date = date('d-m-y');

    $teacherName = "";
    foreach($result as $face){
        $cContext = context_course::instance($cid); // global $COURSE
        $isTeacher = current(get_user_roles($cContext, $face->userid))->shortname=='editingteacher'? true : false;
        if ($isTeacher){
            $single = $DB->get_record('user', array('id' => $face->userid), $fields = '*', $strictness = IGNORE_MISSING);
            $teacherName = $single->lastname." ".$single->firstname;
            break;
        }
    }
	
	$courseName = $DB->get_record('course', array('id'=>$cid), 'fullname', $strictness=IGNORE_MISSING); 
	$courseSName = $DB->get_record('course', array('id'=>$cid), 'shortname', $strictness=IGNORE_MISSING);
    $outputhtml .= '<div style="text-align: center"> <span style="font-size:25px"> <b>'. "Attendance list".'</span></div><p></p>';

    $outputhtml .= '<div style="text-align: center"><span style="font-size:20px"> <b>'. get_string('course', 'block_signinsheet').': </b>' .$courseSName->shortname.' - ' .$courseName->fullname.'</span></div><p></p>';

    $reportdate = date('l jS \of F Y');
    $outputhtml .= '<div style="text-align: center"><span style="font-size:18px"> <b>'. get_string('date', 'block_signinsheet').':</b> '.$reportdate.'</span></div><p></p>';

    $outputhtml .= '<div style="text-align: center"><span style="font-size:18px"> <b>'."Teacher".':</b> '.$teacherName.'</b> </span></div><p></p>&nbsp;<p></p>&nbsp;';

    if(isset($groupname)){
		$outputhtml .= '<span style="font-size:18px">'. $groupname->name . '</span><p></p>';
	}

	$outputhtml .= '<table style="border-style: solid; border-collapse: collapse;" width="600px"  border="1px"><tr>
					<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="200"><b>'. get_string('personname', 'block_signinsheet').'</b></td>
				';
	

	
if($addfieldenabled){
		$fieldid = get_config('block_signinsheet', 'customfieldselect');
		$fieldname = $DB->get_field('user_info_field', 'name', array('id'=>$fieldid), $strictness=IGNORE_MISSING);
		$outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"><b>'.$fieldname.'</b></td>';
}

//Add custom field text if enabled
$addtextfield = get_config('block_signinsheet', 'includecustomtextfield');
if($addtextfield){
		$fielddata = get_config('block_signinsheet', 'customtext');
		$outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"><b>'.$fielddata.'</b></td>';

}	
// Id number field enabled
$addidfield = get_config('block_signinsheet', 'includeidfield');
if($addidfield){
		
		$outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"><b>'. get_string('idnumber', 'block_signinsheet').' </b></td>';

}	

// Add additional mdl_user field if enabled
$addUserField = get_config('block_signinsheet', 'includedefaultfield');
if($addUserField){
	$mdlUserFieldName = get_config('block_signinsheet', 'defaultfieldselection');
	$outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"><b>'. $mdlUserFieldName.' </b></td>';
}
	
	$outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" border="1px"><b>'. get_string('signature', 'block_signinsheet').'</b></td></tr>';
	
	$colCounter = 0;
	$totalrows = 0;
	
	foreach($result as $face){
		$outputhtml .=  printSingleFace($face->userid, $cid);
	}
	
	
	//do we need to print additional lines
	for ($x = 1; $x <= $extra; $x++) {
    	$outputhtml .=  printblank();
	} 

	
	$outputhtml .= '</tr></table>';
	
	return $outputhtml;
	
	
}


/*
 * 
 * Render the entire class 
 * 
 * */
function renderAll($extra){
	
	global $DB, $cid, $OUTPUT, $CFG;
	
	
	$appendorder = '';
	$orderby = '';
	$cid = required_param('cid', PARAM_INT);
	$orderby = optional_param('orderby', '', PARAM_TEXT);
	
	

		
		if($orderby == 'byid'){
			$appendorder = ' order by userid';
		}
		else if($orderby == 'firstname'){
			$appendorder = ' order by  (select firstname from '.$CFG->prefix.'user usr where userid = usr.id)';
		} 
		else if($orderby == 'lastname'){
			$appendorder = ' order by  (select lastname from '.$CFG->prefix.'user usr where userid = usr.id)';
		} 
		
		else {
			$appendorder = ' order by  (select firstname from '.$CFG->prefix.'user usr where userid = usr.id)';
	
		}



    $query = "select userid from ".$CFG->prefix."user_enrolments en where en.enrolid IN (select e.id from ".$CFG->prefix."enrol e where courseid= ?)" . $appendorder;

    // Check if we need to include a custom field
	$addfieldenabled = get_config('block_signinsheet', 'includecustomfield');
	
	// Get the list of users for this particular course
	$result = $DB->get_records_sql($query, array($cid));

	$teacherName = "";
    foreach($result as $face){
        $cContext = context_course::instance($cid); // global $COURSE
        $isTeacher = current(get_user_roles($cContext, $face->userid))->shortname=='editingteacher'? true : false;
        if ($isTeacher){
            $single = $DB->get_record('user', array('id' => $face->userid), $fields = '*', $strictness = IGNORE_MISSING);
            $teacherName = $single->lastname." ".$single->firstname;
            break;
        }
    }

	$date = date('d-m-y');
	
	$courseName = $DB->get_record('course', array('id'=>$cid), 'fullname', $strictness=IGNORE_MISSING); 
	$courseSName = $DB->get_record('course', array('id'=>$cid), 'shortname', $strictness=IGNORE_MISSING);


	$outputhtml = '';
	$outputhtml .= '<div style="text-align: center"> <span style="font-size:25px"> <b>'. "Attendance list".'</span></div><p></p>';

	$outputhtml .= '<div style="text-align: center"><span style="font-size:20px"> <b>'. get_string('course', 'block_signinsheet').': </b>' .$courseSName->shortname.' - ' .$courseName->fullname.'</span></div><p></p>';
	
	$reportdate = date('l jS \of F Y');
	$outputhtml .= '<div style="text-align: center"><span style="font-size:18px"> <b>'. get_string('date', 'block_signinsheet').':</b> '.$reportdate.'</span></div><p></p>';
	
	$outputhtml .= '<div style="text-align: center"><span style="font-size:18px"> <b>'."Teacher".':</b> '.$teacherName.'</b> </span></div><p></p>&nbsp;<p></p>&nbsp;';
	
	
	$outputhtml .= '<table style="border-style: solid; border-collapse: collapse;" width="800px"  border="1px" align="center"><tr>
                    <td style="border-right: thin solid; border-bottom: thin solid" width="50"><b>'."No".' </b></td>
                    ';

    // Id number field enabled
    $addidfield = get_config('block_signinsheet', 'includeidfield');
    if($addidfield){
        $outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"><b>'. get_string('idnumber', 'block_signinsheet').' </b></td>';
    }

    // add Name
    $outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" width="200"><b>'. get_string('personname', 'block_signinsheet').'</b></td>';

    // add custom field
	if($addfieldenabled){
		$fieldid = get_config('block_signinsheet', 'customfieldselect');
		$fieldname = $DB->get_field('user_info_field', 'name', array('id'=>$fieldid), $strictness=IGNORE_MISSING);
		$outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="70"><b>'.$fieldname.'</b></td>';
	} else {
		//$outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"></td>';
    }
	
    //Add custom field text if enabled
    $addtextfield = get_config('block_signinsheet', 'includecustomtextfield');
    if($addtextfield){
            $fielddata = get_config('block_signinsheet', 'customtext');
            $outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="70"><b>'.$fielddata.'</b></td>';
    }

    // Add additional mdl_user field if enabled
    $addUserField = get_config('block_signinsheet', 'includedefaultfield');
    if($addUserField){
        $mdlFieldName = get_config('block_signinsheet', 'defaultfieldselection');
        $outputhtml.='<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="70"><b>'. $mdlFieldName.' </b></td>';
    }


	$outputhtml .='	<td style="border-right: thin solid; border-bottom: thin solid" border="1px"><b>'.get_string('signature', 'block_signinsheet').'</b></td>
	</tr>';
	
	$colCounter = 0;
	$totalrows = 0;
    $index = 0;
	foreach($result as $face){
	    $index++;
		$outputhtml .=  printSingleFace($face->userid, $cid, $index);
	}
	
	
	//do we need to print additional lines
	for ($x = 1; $x <= $extra; $x++) {
    	$outputhtml .=  printblank();
	} 
	
	
	$outputhtml .= '</table>';
	
	return $outputhtml;
	
}
/*
 *  Render a single profile face
 * 
 * 
 */
function printSingleFace($uid, $cid, $index){
	global $DB, $OUTPUT;


    $outputhtml = '';

    $cContext = context_course::instance($cid); // global $COURSE
    $isStudent = current(get_user_roles($cContext, $uid))->shortname=='student'? true : false;

    if ($isStudent) {


        $singlerec = $DB->get_record('user', array('id' => $uid), $fields = '*', $strictness = IGNORE_MISSING);


        $firstname = $singlerec->firstname;
        $lastname = $singlerec->lastname;

        //$user = $DB->get_record('user', array('id' => $uid));


        $picoutput = '';

        global $PAGE;


        $outputhtml .= '
				<tr height="10">
				    <td  style="border-right: thin solid;  border-bottom: thin solid" border="1px" width="50">' . $index . '</td>
					';

        // Id number field enabled
        $addidfield = get_config('block_signinsheet', 'includeidfield');
        if ($addidfield) {
            $outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"> ' . $singlerec->idnumber . ' </td>';
        }

        // add name
        $outputhtml .= '<td  style="border-right: thin solid;  border-bottom: thin solid" border="1px" width="200">' . $lastname . ' ' . $firstname . '</td>';

        // Include additional field data if enabled
        $addfieldenabled = get_config('block_signinsheet', 'includecustomfield');
        if ($addfieldenabled) {
            $fieldid = get_config('block_signinsheet', 'customfieldselect');
            $fielddata = $DB->get_field('user_info_data', 'data', array('fieldid' => $fieldid, 'userid' => $uid), $strictness = IGNORE_MISSING);
            $outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="70">' . $fielddata . '  </td>';
        } else {
            //	$outputhtml .=	'<td style="border-right: thin solid;  border-bottom: thin solid" border="1px" width="150">  </td>';
        }

        //Add custom field text if enabled
        $addtextfield = get_config('block_signinsheet', 'includecustomtextfield');
        if ($addtextfield) {
            $outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="70">  </td>';
        }

        $addUserField = get_config('block_signinsheet', 'includedefaultfield');
        if ($addUserField) {
            $mdlUserFieldName = get_config('block_signinsheet', 'defaultfieldselection');
            $outputhtml .= '<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="70"> ' . $singlerec->$mdlUserFieldName . ' </td>';
        }


        $outputhtml .= '	<td style=" border-bottom: thin solid"> </td>
				</tr>	
		';

    }

    return $outputhtml;

}


function printblank(){

		
	$outputhtml =  '<tr height="10"><td  style="border-right: thin solid;  border-bottom: thin solid" border="1px" width="200"> &nbsp;</td>';

	$addfieldenabled = get_config('block_signinsheet', 'includecustomfield');
	
	// Include additional field data if enabled
	if($addfieldenabled){
		$outputhtml .=	'<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150">&nbsp; </td>';
	} 
	
	//Add custom field text if enabled
	$addtextfield = get_config('block_signinsheet', 'includecustomtextfield');
	if($addtextfield){
			$outputhtml .=	'<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150">&nbsp;  </td>';
	}

	// Id number field enabled
	$addidfield = get_config('block_signinsheet', 'includeidfield');
	if($addidfield){
			$outputhtml .=	'<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"> &nbsp; </td>';
	}

	// add default field
	$addUserField = get_config('block_signinsheet', 'includedefaultfield');
	if($addUserField){
	    $outputhtml .=	'<td style="border-right: thin solid; border-bottom: thin solid" border="1px" width="150"> &nbsp; </td>';
	}


	$outputhtml .='	<td style=" border-bottom: thin solid"> </td>
				</tr>';

	return $outputhtml;

}
