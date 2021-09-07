<?php

// Maria:: Migration ISPC to CISPC 08.08.2020
class Net_EDIFACT_Segment
{
    public $data=[];
    public $meta=[];
    public $tables=[];

    public $separator=["'","+",":",","];
    public $escape="?";
    public $errors=[];
    public $name="";

    public $regexp=[
        'N'=>'/^([0-9]*?)$/',
        'AN'=>'/^([\u0000-\u00ff]*)$/'  ,       //ISO/IEC 8859-1
        'ND1'=>'/^\d+(,\d{1})?$/',
        'ND2'=>'/^\d+(,\d{2})?$/',
    ];

    public function __construct($name,$data, $meta, $more) {
        $this->name=$name;
        $this->meta=$meta;
        $this->data=$data;

        if(isset($more['tables'])){
            $this->tables = $more['tables'];
        }

    }

    public function get_edifact_string($level=1){
        $output=[];
        foreach($this->meta as $element_info){
            $data=$this->data[$element_info[0]-1];
            $data=$this->get_edifact_string_r($element_info, $data, $level);

            //Fix 10.02.2021 start
            if(is_array($data)){
                $data=implode($this->separator[$level+1],$data);
            }
            //Fix 10.02.2021 end

            $output[$element_info[0]-1] = $data;
        }
        $output=implode($this->separator[$level], $output);
        $output=Net_EDIFACT_Segment::convert($output);

        return $output;
    }


    /**
     * @param $element_info
     * @param $data
     * @param $level
     * @return string
     */
    public function get_edifact_string_r($element_info, $data, $level){
        if($data===null || (strlen($data) < 1 && !is_array($data))){
            //no data
            if($element_info[4]==="M"){
                $this->add_error($element_info, "is empty but must be filled");
            }
            $data="";
        }else{
            //we have data
            switch($element_info[3]){
                case "N":
                    if(is_array($data)){
                        $this->add_error($element_info, "must not contain composited data");
                    }
                    $data=strval($data);
                    if(strlen($data)<$element_info[1]){
                        $this->add_error($element_info, "is too short");
                    }
                    if(strlen($data)>$element_info[2]){
                        $this->add_error($element_info, "is too long");
                    }
                    if(!preg_match($this->regexp['N'], $data)){
                        $this->add_error($element_info, "contains non-numeric characters");
                    }
                    break;
                case "ND1":
                    if(is_array($data)){
                        $this->add_error($element_info, "must not contain composited data");
                    }
                    $data=strval($data);
                    if(strlen($data)<$element_info[1]){
                        $this->add_error($element_info, "is too short");
                    }
                    if(strlen($data)>$element_info[2]){
                        $this->add_error($element_info, "is too long");
                    }
                    if(!preg_match($this->regexp['ND1'], $data)){
                        $this->add_error($element_info, "is not a number with one decimal");
                    }
                    break;
                case "ND2":
                    if(is_array($data)){
                        $this->add_error($element_info, "must not contain composited data");
                    }
                    $data=strval($data);
                    if(strlen($data)<$element_info[1]){
                        $this->add_error($element_info, "is too short");
                    }
                    if(strlen($data)>$element_info[2]){
                        $this->add_error($element_info, "is too long");
                    }
                    if(!preg_match($this->regexp['ND2'], $data)){
                        $this->add_error($element_info, "is not a number with two decimals");
                    }
                    break;
                case "AN":
                    if(is_array($data)){
                        $this->add_error($element_info, "must not contain composited data");
                    }
                    $data=strval($data);
                    if(strlen($data)<$element_info[1]){
                        $this->add_error($element_info, "is too short");
                    }
                    if(strlen($data)>$element_info[2]){
                        $this->add_error($element_info, "is too long");
                    }
                    if(!preg_match($this->regexp['AN'], $data) && !mb_detect_encoding ($data)=="ASCII"){
                        $this->add_error($element_info, "contains not allowed characters");
                    }
                    $data=$this->get_escaped_string($data);
                    break;
                case "CMP":
                    if(!is_array($data)||count($data)==0||strlen(implode($data))<1){
                        if($element_info[4]==="M"){
                            $this->add_error($element_info, "is empty but must be filled");
                        }else{
                            $data="";
                            break;
                        }
                    }
                    foreach ($element_info[6] as $i_info){
                        $idata=$data[$i_info[0]-1];
                        $idata=$this->get_edifact_string_r($i_info, $idata, $level+1);
                        $data[$i_info[0]-1] = $idata;
                    }
                    $data=implode($this->separator[$level+1], $data);
                    break;
            }
        }
        return $data;
    }

    public function get_escaped_string($string){
        $string=str_replace($this->escape, $this->escape . $this->escape, $string);

        foreach($this->separator as $sep){
            $string=str_replace($sep, $this->escape . $sep, $string);
        }

        return $string;
    }

    public function add_error($element_info, $message){
        $this->errors[]="".$this->name."#".$element_info[0] ." (". $element_info[5] .") ". $message ."!";
    }

    public static function convert($string){
        //convert to ISO/IEC 8859-1
        $string=utf8_decode($string);
        return $string;
    }

    /**
     * Update or set data in this segment
     * @param $field "the fieldnumber where the data is set to"
     * @param $newdata
     */
    public function set_data($field,$newdata){
        $this->data[$field-1]=$newdata;
    }

    public function get_data($field){
        return $this->data[$field-1];
    }


    public static function test_example(){

        $dia_data=['DIA', ['J45.0','G','', '20200429']];

        $dia_meta=[
            [1,	3, 3, 'AN',  'M', 'Segmentkennung', ['fix', 'DIA'] ],
            [2,	1, 1, 'CMP', 'M', 'Diagnose',
                [
                    [1, 1, 12, 'AN', 'M', 'ICD-Schlüssel (Mit Punkt. Beispiel: J45.0 oder S73.0-)'],
                    [2, 1, 1,  'AN', 'M', 'Diagnosesicherheit'      ,['table', '6.1.13'] ],
                    [3, 1, 1,  'AN', 'C', 'Seitenlokalisation'      ,['table', '6.1.14'] ],
                    [4, 8, 8,  'N',  'C', 'Diagnosedatum JJJJMMTT']
                ]
            ],
        ];


        $tabs['6.1.13']=
            [
            'name'=>'Diagnosesicherheit',
            'items'=>
                [
                'A'=>'ausgeschlossene Diagnose',
                'G'=>'gesicherte Diagnose',
                'V'=>'Verdachtsdiagnose',
                'Z'=>'symptomloser Zustand nach der betreffenden Diagnose'
                ]
            ];

        $tabs['6.1.14']=
            [
            'name'=>'Seitenlokalisation',
            'items'=>
                [
                'R'=>'rechts',
                'L'=>'links',
                'B'=>'beidseitig'
                ]
            ];

        $more=['Tables'=>$tabs];

        $obj=new Net_EDIFACT_Segment($dia_data, $dia_meta, $more);
        $str=$obj->get_edifact_string(1);
        var_dump($str);
        exit();

    }

}