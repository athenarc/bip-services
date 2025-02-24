<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use yii\base\Model;

/**
 * Orcid is the model used for data transfer with the orcid website
 *
 *
 */
class Orcid extends Model
{

	private static $auth_url = 'https://orcid.org/oauth/authorize';

	private static $token_url = "https://orcid.org/oauth/token";

	private static $works_url = "https://pub.orcid.org/v2.1/%orcid%/works";

	public static function authorize($orcid_code, $redirect_uri) {

		//The data you want to send via POST
        $data = [
            'client_id' => Yii::$app->params['orcid_client_id'],
            'client_secret' => Yii::$app->params['orcid_client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri,
            'code' => $orcid_code
        ];

        //url-ify the data for the POST
        $data_str = http_build_query($data);

        //open connection
        $ch = curl_init(Orcid::$token_url);

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

        //execute post
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $response;
	}

	public static function get_works($orcid, $access_token) {
		$authorization = "Authorization: Bearer " . $access_token;

		$url = str_replace("%orcid%", $orcid, Orcid::$works_url);

        $ch = curl_init($url);

	 	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $response = curl_exec($ch);
	    curl_close($ch);
	    // print_r($response);

	    $response = json_decode($response, true);
	    
	    $works = [];

	    if (!isset($response["group"]))
	    	return [];

	    foreach ($response["group"] as $work) {

	    	$paper = [];
   		
   			// check if title exists
	    	if (!isset($work["work-summary"]))
	    		continue;

    		foreach ($work["work-summary"] as $metadata) {
    			if (!isset($metadata["title"]["title"]["value"]))
					continue;
    			
				$paper["title"] = $metadata["title"]["title"]["value"];

				if (!isset($metadata["publication-date"]["year"]["value"]))
					continue;

				$paper["year"] = $metadata["publication-date"]["year"]["value"];
    		}

   			// check if external-id data exist
   			if (!isset($work["external-ids"]["external-id"])) {
     				continue;
   			}

    		foreach ($work['external-ids']['external-id'] as $external_id) {
	    		if ($external_id["external-id-type"] == "doi") {
	    			$paper["doi"] = strtolower($external_id["external-id-value"]);
	    			break;
	    		}
	    	}
	    	
			array_push($works, $paper);
	    }

	    return $works;
	}

	public static function getAuthorizationUrl($redirect_uri) {
        return Orcid::$auth_url . '?' . http_build_query([
            'client_id' => Yii::$app->params['orcid_client_id'],
            'response_type' => 'code',
            'scope' => '/authenticate',
            'redirect_uri' => $redirect_uri,
        ]);
    }
}
