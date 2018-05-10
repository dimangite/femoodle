<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once($CFG->dirroot.'/grade/export/lib.php');

class grade_export_fexls extends grade_export {

    public $plugin = 'fexls';

    /**
     * Constructor should set up all the private variables ready to be pulled
     * @param object $course
     * @param int $groupid id of selected group, 0 means all
     * @param stdClass $formdata The validated data from the grade export form.
     */

    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);

        // Overrides.
        $this->usercustomfields = true;
    }

    /**
     * To be implemented by child classes
     */
    public function print_grades() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        // Calculate file name
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades.xlsx");
        // Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers
        $workbook->send($downloadfilename);
        // Adding the worksheet
        $myxls = $workbook->add_worksheet($strgrades);

        // add Header of grading

        // add format of cell
        $header_str = ["ព្រះរាជាណាចក្រកម្ពុជា", "ជាតិ        សាសនា         ព្រះមហាក្សត្រ","ក្រសួងអប់រំ យុវជន និងកីឡា" , "សាកលវិទ្យាល័យភូមិន្ទភ្នំពេញ", "តារាងសម្រង់វត្តមាន និង​វាយតំលៃបញ្ចប់ឆមាសទី២ របស់និស្សិត​ឆ្នាំទី៣( ឆ្នាំសិក្សា ២០១៧ - ២០១៨)", "មហាវិទ្យាល៍យវិស្វកម្មឯកទេស   វិស្វកម្មបច្ចេវិទ្យាពត៍មាន (អាហារូបករណ៍)   Group A  មុខវិជ្ជា:", ""];
        $formatCenter = $workbook->add_format();
        $formatCenter->set_h_align('center');
        $formatCenter->set_v_align('center');
        $formatCenter->set_bottom(4);
        $num = 0;
        foreach($header_str as $item){
            $myxls->set_row($num, 17, $formatCenter);
            $myxls->merge_cells($num, 0, $num, 24);
            $myxls->write_string($num, 0, $item);
            $num++;
        }

        $startRow = 7;
        // Print names of all the fields
//        $profilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);

        $profilefields = grade_helper::get_user_profile_fields($this->course->id, true);
//        foreach ($profilefields as $id => $field) {
//            $myxls->write_string($startRow, $id, $field->fullname);
//        }


        $myxls->merge_cells(7, 0, 9, 0);
        $myxls->merge_cells(7, 1, 9, 1);
        $myxls->merge_cells(7, 2, 9, 2);
        $myxls->merge_cells(7, 3, 9, 3);
        $myxls->merge_cells(7, 4, 8, 19);
        $myxls->merge_cells(7, 19, 7, 21);


        $header1_str = ["ល.រ", "គោត្តនាម", "នាម", "ភេទ", "វត្តមាន និងអវត្តមាន", "វាយតម្លៃក្នុងថ្នាក់​ ៤០%=៤/១០", "ពិន្ទុកប្រឡងឆមាស", "ពិន្ទុកសរុបរួម", "ផ្សេងៗ"];

        $myxls->set_column(0, 0, 4);
        $myxls->write_string($startRow, 0, $header1_str[0], $formatCenter);
        $myxls->set_column(1, 1, 12);
        $myxls->write_string($startRow, 1, $header1_str[1], $formatCenter);
        $myxls->set_column(2, 2, 10);
        $myxls->write_string($startRow, 2, $header1_str[2], $formatCenter);
        $myxls->set_column(3, 3, 5);
        $myxls->write_string($startRow, 3, $header1_str[3], $formatCenter);

        $myxls->write_string($startRow, 4, $header1_str[4], $formatCenter);
        $myxls->set_column(4, 18, 3);
        for($i=1 ; $i<16; $i++){
            $myxls->write_string($startRow+2, 3+$i, $i , $formatCenter);
        }
        $myxls->write_string($startRow, 19, $header1_str[5], $formatCenter);

        $myxls->set_column(19, 19, 10);
        $myxls->write_string($startRow+1, 19, "វត្តមាន", $formatCenter);
        $myxls->set_column(20, 20, 14);
        $myxls->write_string($startRow+1, 20, "កិច្ចការស្រាវជ្រាវ", $formatCenter);
        $myxls->set_column(21, 21, 21);
        $myxls->write_string($startRow+1, 21, "សំនួរត្រួតពិនិត្យ", $formatCenter);

        $myxls->write_string($startRow+2, 19, "10%", $formatCenter);
        $myxls->write_string($startRow+2, 20, "10%", $formatCenter);
        $myxls->write_string($startRow+2, 21, "ពាក់កណ្តាលឆមាស 20%", $formatCenter);

        $myxls->set_column(22, 22, 15);
        $myxls->write_string($startRow, 22, $header1_str[6], $formatCenter);
        $myxls->write_string($startRow+2, 22, "60%", $formatCenter);

        $myxls->set_column(23, 23, 11);
        $myxls->write_string($startRow, 23, $header1_str[7], $formatCenter);
        $myxls->write_string($startRow+2, 23, "100%", $formatCenter);

//        $myxls->set_column(22, 22, 15);
        $myxls->write_string($startRow, 24, $header1_str[8], $formatCenter);


//
//        $pos = count($profilefields);
//        if (!$this->onlyactive) {
//            $myxls->write_string($startRow, $pos++, get_string("suspended"));
//        }
//        foreach ($this->columns as $grade_item) {
//            foreach ($this->displaytype as $gradedisplayname => $gradedisplayconst) {
//                $myxls->write_string($startRow, $pos++, $this->format_column_name($grade_item, false, $gradedisplayname));
//            }
//            // Add a column_feedback column
//            if ($this->export_feedback) {
//                $myxls->write_string($startRow, $pos++, $this->format_column_name($grade_item, true));
//            }
//        }
//        // Last downloaded column header.
//        $myxls->write_string($startRow, $pos++, get_string('timeexported', 'gradeexport_xls'));

        // Print all the lines of data.
        $i = $startRow+2;
        $index = 1;
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            $i++;
            // add index
            $myxls->write_string($i, 0, $index++, $formatCenter);

            $user = $userdata->user;

            foreach ($profilefields as $id => $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);
                if(strcmp($fieldvalue, "Male") == 0 ){
                    $myxls->write_string($i, $id+1, "ប", $formatCenter);
                }elseif (strcmp($fieldvalue, "Female") == 0){
                    $myxls->write_string($i, $id+1, "ស", $formatCenter);
                }else $myxls->write_string($i, $id+1, $fieldvalue, $formatCenter);
            }

            $j = count($profilefields) +1 + 15;
            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                $myxls->write_string($i, $j++, $issuspended, $formatCenter);
            }

            // grade print

            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }
                $tem = 0;
                foreach ($this->displaytype as $gradedisplayconst) {
                    $gradestr = $this->format_grade($grade, $gradedisplayconst);
                    if (is_numeric($gradestr)) {
                        $myxls->write_number($i, $j++, $gradestr, $formatCenter);
                    } else {
                        $myxls->write_string($i, $j++, $gradestr, $formatCenter);
                    }
                }
                // writing feedback if requested
                /*
                if ($this->export_feedback) {
                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
                }
                */
            }
            // Time exported.
//            $myxls->write_string($i, $j++, time());
        }

        $footerStr = ["កំណត់ចំណាំ:សាស្រ្តាចារ្យបង្រៀនទាំងអស់ត្រូវប្រគល់បញ្ជីវត្តមានដល់ការិយាល័យសិក្សាជារៀងរាល់ចុងខែ ។                                            រាជធានីភ្នំពេញ ថ្ងៃទី",
            "រាជធានីភ្នំពេញ ថ្ងៃទី                                                                                                      ​​​​​​​​​​​​​​​​​​​​​​​​​​                           សាស្រ្តាចារ្យ",
            "ប្រធានការិយាល័យសិក្សា",
            "វេង ឆាង"
            ];
        $row = $i+1;
        foreach ($footerStr as $item){
            $myxls->set_row($row, 17, $formatCenter);
            $myxls->merge_cells($row, 0, $row, 24);
            $myxls->write_string($row, 0, $item);
            $row++;
        }
        $gui->close();
        $geub->close();

    /// Close the workbook
        $workbook->close();

        exit;
    }
}


