<?php
namespace GreyDogSystems\Developer;
class Debug{
    public function VariableDumper($variable){
        if ($variable === true){
            return '[BOOLEAN]=true';
        }else if ($variable === false){
            return '[BOOLEAN]=false';
        }else if ($variable === null){
            return 'null';
        }else if (is_array($variable)){
            $html = '<table border="1" cellspacing="0" cellpadding="2">'."\n";
            $html .= "<thead><tr><td><b>KEY</b></td><td><b>VALUE</b></td></tr></thead>\n";
            $html .= "<tbody>\n";
            foreach ($variable as $key => $value){
                $value = $this->VariableDumper($value);
                if ($value == ''){
                    $value = '[Empty Variable]';
                }
                $html .= "<tr><td>$key</td><td>$value</td></tr>\n";
            }
            $html .= "</tbody>\n";
            $html .= "</table>";
            return $html;
        }else if (is_object($variable)) {
        	return '[OBJECT]';
        }else{
            return '[STRING]='.strval($variable);
        }
    }
}

?>