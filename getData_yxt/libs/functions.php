<?php

function curl_get_contents($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function curl_post_contents($url, $data){
    $req = curl_init();
    curl_setopt_array($req, [
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => "PUT",
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
        ),
        CURLOPT_RETURNTRANSFER => true,
    ]);

    return curl_exec($req);

}

function showArray($array){
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function after($needle, $string){
    return !is_bool(strpos($string, $needle)) ? substr($string, strpos($string,$needle)+strlen($needle)) : false;
}


function before($needle, $string){
    return substr($string, 0, strpos($string, $needle));
}

function convVorwahl($location){
    require('ww-german-phone-area-codes.php');
    foreach($vorwahlDataset as $convNumbers){
        if($convNumbers['city'] == ucfirst($location)){
            $preNumber = $convNumbers['phone_area_code'];
            $numbers = array(
                $location => array(
                    'before' => '+49'.$preNumber,
                    'after' => '0'.$preNumber.'-',
                )
            );
        }
    }

    return $numbers[$location] ?? false;
}