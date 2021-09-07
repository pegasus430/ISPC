<?php

// Maria:: Migration ISPC to CISPC 08.08.2020
class Net_EDIFACT_Message
{
    public $segments=[];
    public $separator=["'","+",":",","];
    public $errors=[];

    public function __construct() {
    }

    public function add_segment($segment){
        $this->segments[]=$segment;
    }

    /**
     * Trennzeichenvorgabe
     */
    public function get_una_str(){
        return "UNA:+.? '";
    }


    public function get_segment_by_name($name){
        $segments=[];
        foreach ($this->segements as $segment){
            if($segment->name == $name){
                $segments[]=$segment;
            }
        }
    }


    public function get_edifact_string($level=0){
        $output=array();
        foreach($this->segments as $segment){
            $output[]=$segment->get_edifact_string($level+1);
            foreach($segment->errors as $error){
                $this->errors[]=$error;
            }
        }
        $output=implode($this->separator[$level], $output);
        $output=Net_EDIFACT_Segment::convert($output);

        $una=$this->get_una_str();
        $output=$una . $output ."'";
        return $output;
    }


}