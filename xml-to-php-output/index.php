<?php

ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);


function curl_get_contents($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function sanitizeString($string){
    $replace = array('ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue');
    $search = array('ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü');
    $stringRaw = str_replace($search, $replace, $string);
    return str_replace(' ', '_', strtolower($stringRaw));
}

// xml file path
$path = "https://client.jobs.personio.de/xml";

$xmlUrl = curl_get_contents($path) or die("Error: Cannot create object");

$xml   = simplexml_load_string($xmlUrl);
$json  = json_encode($xml);
$array = json_decode($json,TRUE);


$html = '';
$translations = [
    "full-time" =>  [
        "de" => "Vollzeit",
        "en" => "Full-time"
    ],
    "part-time" =>  [
        "de" => "Teilzeit",
        "en" => "Part-time"
    ],
    "permanent" =>  [
        "de" => "Festanstellung",
        "en" => "Permanent Employment"
    ],
    "intern" =>  [
        "de" => "Praktikum",
        "en" => "Internship"
    ],
    "trainee" =>  [
        "de" => "Trainee Stelle",
        "en" => "Trainee Stelle"
    ],
    "freelance" =>  [
        "de" => "Freelance Position",
        "en" => "Freelance Position"
    ],
];
$lang = 'de';
$i = 1;
$html .='<div id="kd_joblist" data-lang="'.$lang.'"><ul>';
foreach($array as $jobs) {
    foreach($jobs as $job){
        $kd_company = isset($job['subcompany']) ? sanitizeString($job['subcompany']) : 'corporate';
        $kd_office = isset($job['office']) ? $job['office'] : '';
        $kd_employmentType = isset($job['employmentType']) ? $translations[$job['employmentType']][$lang].', ' : '';
        $kd_schedule = isset($job['schedule']) ? $translations[$job['schedule']][$lang].', ': '';
        $kd_jobID = $job['id'];
        $html .= '<li class="kd_job_offer kd_job_offer-'.$i.'" data-jobid="'.$kd_jobID.'" data-company="'.$kd_company.'">';
        $html .= '<h3>'.$job['name'].'</h3>';
        $html .= '<p>'.$kd_employmentType.$kd_schedule.$kd_office.'</p>';
        $html .= '<a href="?jobid='.$kd_jobID.'">Zur Stellenbeschreibung</a>';
        $html .= '</li>';
        $i++;
    }
}
$html .= '</ul></div>'; // END Wrapper
echo $html;
