<?php

namespace app\components;

use Yii;
use yii\base\Component; 

/**
 * Implementation of string helper functions for BiP! application. 
 *
 * @author Thanasis Vergoulis
 */
class BipStringHelper extends Component 
{
	/**
	 * Shorten a string (keep the prefix).
	 *
	 * @author Thanasis Vergoulis
	 */
    public function shortenString($string, $char_num)
    {
    	if( strlen($string)>$char_num)
    		return trim(substr($string,0,$char_num)." (...)");
    	else
        	return trim($string);
    } 
    
    public function is_all_upper($string)
    {
        $characters = str_split($string);
        foreach ($characters as $char)
        {
            if(ctype_alpha($char) && ctype_lower($char))
            {
                return false;
            }
        }
        return true;
    }
    
    public function lowerize($string)
    {
       if($this->is_all_upper($string))
       {
           return ucwords(strtolower($string));
       }
       else
       {
           return $string;
       }
    }
}