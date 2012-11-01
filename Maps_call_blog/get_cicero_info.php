<?php
// Some external info we want to use
include('include.php');

// Function to do our API calls (returns object made from JSON)
function get_response($url, $postfields=''){
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, True);
    if($postfields !== '')://if there is post data, POST it, else, just do a GET
        curl_setopt ($ch, CURLOPT_POST, True);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postfields);
    endif;
    $json = curl_exec($ch);
    curl_close($ch);
    return json_decode($json);//take the JSON response we just got and format it for PHP
}

// @return array of $token_info
function get_cicero_token($username, $password){
    //urlencode data before sending it to Cicero
    $username = urlencode($username);
    $password = urlencode($password);

    // Obtain a token:
    $response = get_response('http://cicero.azavea.com/v3.1/token/new.json', "username=$username_enc&password=$password_enc");

    // Check to see if the token was obtained okay:
    if($response->success != True):
        exit('Could not obtain token.');
    endif;

    // The token and user obtained are used for other API calls:
    $token_info = array("token" => urlencode($response->token), "user" => urlencode($response->user));
    return $token_info;
}

// @return array of $map_info
function get_map_info_by_location_and_district_type($token_info, $search_loc, $district_type='STATE_LOWER'){
    //urlencode our data
    $search_loc = urlencode($search_loc);
    $district_type = urlencode($district_type);

    $query_string = "search_loc=$search_loc_enc&token=${token_info['token']}&user=${token_info['user']}&district_type=$district_type_enc&format=json";
    $official_response = get_response("http://cicero.azavea.com/v3.1/official?$query_string");

    if(count($official_response->response->results->candidates) == 0):
        die('No location found for the given address.');
    endif;

    //we will center the map on the search location
    $lat_long_of_search_loc = array("lat_y" => $official_response->response->results->candidates[0]->y,
                                    "long_x" => $official_response->response->results->candidates[0]->x);

    //the easy way. Another way would be to get State, country, and district_id
    $unique_district_id = $official_response->response->results->candidates[0]->officials[0]->office->district->id;

    $map_info = array("lat_long" => $lat_long_of_search_loc, "unique_district_id" => $unique_district_id);

    return $map_info;

}

// @return JSON encoded array
function get_district_map($token_info, $map_info){
    $query_string = "${map_info['unique_district_id']}?token=${token_info['token']}&user=${token_info['user']}&include_image_data&srs=4326&width=500&height=500&format=json";
    $map_url = "http://cicero.azavea.com/v3.1/map/$query_string";
    //echo $map_url;
    $map_response = get_response($map_url);
    $map_list = $map_response->response->results->maps[0];
    //echo $map_list;
    if(count($map_list) > 0):
        $district_map_image_data = $map_response->response->results->maps[0];
    else:
        die('No map found for the given unique_district_id.');
        //$district_map_image_data = json_encode(array("url" => "No ")
    endif;

    return json_encode(array("center_lat_long" => $map_info['lat_long'], "district_map_image_data" => $district_map_image_data));
}

//Go get 'em!
$token_info = get_cicero_token($username, $password);
$unique_district_id = get_map_info_by_location_and_district_type($token_info, $search_loc);
$map_data_for_javascript = get_district_map($token_info, $unique_district_id);  

?>