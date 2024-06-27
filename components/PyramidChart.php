<?php

namespace app\components;

use Yii;
use yii\base\Component; 

/**
 * Implementation of pyramid chart functions. 
 *
 * @author Thanasis Vergoulis
 */
class PyramidChart extends Component 
{
	/**
	 * It returns the HTML code for the pyramid chart. 
	 *
	 * @author Thanasis Vergoulis
	 */
    public function build( $id, $class, $property_adj, $percentage)
    {
    	$chart_html = "<div id='".$id."'>";
    	
    	if( $percentage<=0.0001 )
    	{
 	  		$chart_html .= "<div class='top-box active-box'></div>";
			$chart_html .= "<div class='med-box inactive-box'></div>";
			$chart_html .= "<div class='bot-box inactive-box'></div>";
			$chart_html .= "<div class='".$class."'>";
			$chart_html .= "<span title='in the top 0.01%' style='cursor:pointer;'>Exceptional</span>";
			$chart_html .= "</div>";
    	}
    	else if( $percentage<=0.01 )
    	{
    		$chart_html .= "<div class='top-box inactive-box'></div>";
			$chart_html .= "<div class='med-box active-box'></div>";
			$chart_html .= "<div class='bot-box inactive-box'></div>";
			$chart_html .= "<div class='".$class."'>";
			$chart_html .= "<span title='in the top 1%' style='cursor:pointer;'>Substantial</span>"; 
			$chart_html .= "</div>";
    	}
    	else
    	{
    		$chart_html .= "<div class='top-box inactive-box'></div>";
			$chart_html .= "<div class='med-box inactive-box'></div>";
			$chart_html .= "<div class='bot-box active-box'></div>";
			$chart_html .= "<div class='".$class."'>";
			$chart_html .= "in the rest 99%";
			$chart_html .= "</div>";
    	}
    	$chart_html .= "</div>";
    	return $chart_html;	 
    }    
}