<?php

class frwdYext {
    protected $database;
    protected $blyxt  = __fryext__dbtable__;

    function getModestate(){
        global $wpdb;
        $db = $wpdb->prefix . $this->blyxt;
        $sql = "SELECT livemode FROM $db Where id=1";
        $res = $wpdb->get_var($sql);
        return $res;
    }

    function getAccountbyState(){
        $mode = $this->getModestate()==1 ? 'live' : 'sandbox';
        global $wpdb;
        $state = array();
        $db = $wpdb->prefix . $this->blyxt;
        $sql = "SELECT {$mode}_apiKey, {$mode}_accountID FROM $db Limit 1";
        $res = $wpdb->get_results($sql);
        if(!empty($res)) {
            $stateData = json_decode(json_encode($res), true);
            $x = 0;
            foreach ($stateData[0] as $sd => $sVal) {
                $state[$x] = $sVal;
                $x++;
            }
        }
        return $state;
    }

    function getblAccount(){
        global $wpdb;
        $db = $wpdb->prefix . $this->blyxt;
        $sql = "SELECT * FROM $db Limit 1";
        $res = $wpdb->get_results($sql);
        return $res;
    }

    function updateDB($post){
        global $wpdb;
        $db = $wpdb->prefix . $this->blyxt;
        $livemode = $post['livemode'] ?? 0;
        $sandbox_apiKey = $post['sandbox_apiKey'];
        $sandbox_accountID = $post['sandbox_accountID'];
        $live_apiKey = $post['live_apiKey'];
        $live_accountID = $post['live_accountID'];
        $sql ="UPDATE $db SET livemode = '$livemode', sandbox_apiKey = '$sandbox_apiKey', sandbox_accountID = '$sandbox_accountID', live_apiKey = '$live_apiKey', live_accountID ='$live_accountID' WHERE $db.id =1";
        $res = $wpdb->query($sql);
        return $res;
    }

    function getLocation($locationFilter="", $nextPage=""){
        global $configBLYdata;
        $today = date('Ymd');
        $state = $this->getAccountbyState();
        if(empty($state)){
            echo '<div role="alert" class="col-12 alert alert-danger"><b>'.__('Please fill in YEXT API and account-id first.','blyxt').'</b></div>';
            return false;
        }
        $accountID = $state[1];
        $apiKey    = $state[0];
        $getnextPage = $nextPage ? '&pageToken='.$nextPage : '';
        $locationFilter = $locationFilter ? '&filter='.urlencode('{"websiteUrl.url":{"$contains":"'.$locationFilter.'"}}') : '';
        $apiPath = 'https://api.yext.com/v2/accounts/'.$accountID.'/entities?api_key='.$apiKey.'&v='.$today.'&limit=50'.$getnextPage.$locationFilter;
        if(__devMode__){
            echo '<div class="mt-3 alert alert-warning" role="alert"><b>'.__('Sandbox mode active','blyxt').'</b>';
            echo '<p class="d-none">'.$apiPath.'</p></div>';
        }
        $apiData = curl_get_contents($apiPath) or die("Error: Cannot create object");
        return json_decode($apiData, true);
    }

    function getNextpage(){
        $rawData = $this->getLocation();
        return $rawData['response']['pageToken'];
    }

    function getLocationbyID($locationID){
        global $configBLYdata;
        $today = date('Ymd');
        $state = $this->getAccountbyState();
        if(empty($state)){
            echo '<div role="alert" class="col-12 alert alert-danger"><b>'.__('Please fill in YEXT API and account-id first.','blyxt').'</b></div>';
            return false;
        }
        $accountID = $state[1];
        $apiKey    = $state[0];
        $locationFilter = $locationID ? '&filter='.urlencode('{"meta.id":{"$eq":"'.$locationID.'"}}') : '';
        $apiPath = 'https://api.yext.com/v2/accounts/'.$accountID.'/entities?api_key='.$apiKey.'&v='.$today.$locationFilter;
        if(__devMode__){
            echo '<div class="col-12 alert alert-warning" role="alert"><b>'.__('Sandbox mode active','blyxt').'</b><br/>';
            echo '<p class="d-none">'.$apiPath.'</p></div>';
        }
        $apiData = curl_get_contents($apiPath) or die("Error: Cannot create object");
        return json_decode($apiData, true);
    }

    function checkKey($arrKey, $array){
        return array_key_exists($arrKey,$array) ?  $array[$arrKey] : '';
    }

    function getSingleData($location){
        $convData = array();
        $addressData = $this->getLocation($location);
        if(empty($addressData)){
            echo '<!--'.__('Please fill in YEXT API and account-id first.','blyxt').'-->';
            return false;
        }
        foreach($addressData['response']['entities'] as $locationData) {
            $convData['address'][] = $locationData['address']['line1'];
            $convData['address'][] = $locationData['address']['city'];
            $convData['address'][] = $locationData['address']['postalCode'];

            $convData['email'] = $locationData['emails'][0];
            $convData['fax']   = $this->checkKey('fax',$locationData);
            $convData['phone'] = $this->checkKey('mainPhone',$locationData);
            foreach ($locationData['hours'] as $days => $day){
                if (array_key_exists("openIntervals",$day)){
                    $convData['openingTime'][$days]['start'] = $day['openIntervals'][0]['start'];
                    $convData['openingTime'][$days]['end'] = $day['openIntervals'][0]['end'];
                } else {
                    $convData['openingTime'][$days] = $day;
                }
            }
            $convData['additionalHoursText'] = $this->checkKey('additionalHoursText',$locationData);
            $convData['entityID'] = $locationData['meta']['id'];
        }
        return $convData;
    }

    function getAllLocationIDs(){
        $convData = array();
        $addressData = $this->getLocation();

        foreach($addressData['response']['entities'] as $locationData) {
            $convData['entityID'][] = $locationData['meta']['id'];
        }
        if(array_key_exists('pageToken', $addressData['response'])){
            $nextPageData = $this->getLocation('', $addressData['response']['pageToken']);
            foreach($nextPageData['response']['entities'] as $nextPageLocData) {
                array_push($convData['entityID'], $nextPageLocData['meta']['id']);
            }
        }
        return $convData;
    }

    function rearrangeOpeningtime($timeArr){
        $x    = 0;
        $days = array( 0 => 'monday', 1 => 'tuesday', 2 => 'wednesday', 3 => 'thursday', 4 => 'friday', 5 => 'saturday', 6 => 'sunday');
        $timeData = $newTimedata = array();
        foreach($timeArr as $tkey => $tvalue ){
            $startTime = $timeArr[$tkey]['start'] ?? '';
            $endTime   = isset($timeArr[$tkey]['end']) ? ' - '.$timeArr[$tkey]['end'].' Uhr' : '';
            $closed    = isset($timeArr[$tkey]['isClosed']) && $timeArr[$tkey]['isClosed'] == 1 ? 'geschlossen' : '';
            $timeData[$x]['day']  = '<span class="bl_tday">'.ucfirst(__( $tkey, 'blyxt')).'</span>';
            $timeData[$x]['time'] = '<span class="bl_totime">'.$startTime.$endTime.$closed.' </span>';
            $x++;
        }
        $y = $z = 0;
        foreach($timeData as $timeCompare){
            if($timeData[0]['time'] == $timeCompare['time']){
                $z++;
            }
            $y++;
        }
        $newTimedata[0] = '<span class="bl_tday">'.ucfirst(__( $days[0], 'blyxt')).' bis '.ucfirst(__( $days[$z-1], 'blyxt')).'</span>'.$timeData[0]['time'];
        for($f = $z; $f <= 6; $f++) {
            $newTimedata[$f] = $timeData[$f]['day'].$timeData[$f]['time'];
        }
        return $newTimedata;
    }

    function prettyNumber($locNumber, $location){
        $vorwahl = convVorwahl($location);
        return $vorwahl ? str_replace($vorwahl['before'],$vorwahl['after'], $locNumber) : $locNumber;
    }

    function renderQuickinfo($location){
        $locData = $this->getSingleData(strtolower($location));
        if(empty($locData)){
            echo '<!--'.__('Please fill in YEXT API and account-id first.','blyxt').'-->';
            return false;
        }
        $opTime  = $addInfo ='';
        $address = wpautop($locData['address'][0].', '.$locData['address'][2].' '.$locData['address'][1]);
        $email   = !empty($locData['email']) ? '<br/> E-Mail: <a href="mailto:'.$locData['email'].'">'.$locData['email'].'</a>' : '';
        $prettyTel = $this->prettyNumber($locData['phone'], $locData['address'][1]);
        $prettyFax = !empty($locData['fax']) ? '- Fax: '.$this->prettyNumber($locData['fax'], $locData['address'][1]) : '';
        $contact  = wpautop('Tel.: <a href="tel:'.$locData['phone'].'">'.$prettyTel.'</a> '.$prettyFax.$email);
        $infoHL   = substr($locData['additionalHoursText'], 0, strpos($locData['additionalHoursText'], ' '));
        $infoText = str_replace($infoHL, '', $locData['additionalHoursText']);
        $infoDay  = before('von',$infoText);
        $infoTime = after('von', $infoText);
        if(isset($locData['additionalHoursText']) && !empty($locData['additionalHoursText'])) {
            if ($infoHL == 'Warenannahme:' || $infoHL == 'Entladezeiten:') {
                $addInfo = '<h3>' . $infoHL . '</h3>';
                if ($infoHL == 'Entladezeiten:') {
                    $addInfo .= wpautop(after('Entladezeiten:', $locData['additionalHoursText']));
                } else {
                    $addInfo .= wpautop('<span class="bl_tday">' . $infoDay . '</span><span class="bl_totime">' . str_replace('bis', '-', $infoTime) . '</span>');
                }
            } else {
                $addInfo = '<h3>Zusatzinfo</h3>' . wpautop($locData['additionalHoursText']);
            }
        }

        $openingTimeData = $this->rearrangeOpeningtime($locData['openingTime']);
        foreach($openingTimeData as $openingTime){
            $opTime .= wpautop($openingTime);
        }
        $locationURL = '<a id="bl_locationurl" href="'.__bl_locationURL__.$location.'/standort-detailinformationen">Standort-Detailinformationen</a>';
        $colsData  = '<div id="bl_location_wrapper" class="row" data-blid="'.$locData['entityID'].'">';
        $colsData .= '<div class="bl_location_col-1 col-xs-6 col-md-6 bl_contacts"><h2>Hier finden Sie uns:</h2>'.$address.$contact.$locationURL.'</div>';
        $colsData .= '<div class="bl_location_col-2 col-xs-6 col-md-6 bl_times"><h3>Öffnungszeiten:</h3>'.$opTime.$addInfo.'</div>';
        $colsData .= '</div>';
        return $colsData;
    }


    function updateData($postData){
        global $configBLYdata;
        $days = fryxtDays();
        $today = date('Ymd');
        $entityID =  $postData['blid'];
        $state = $this->getAccountbyState();
        $accountID = $state[1];
        $apiKey    = $state[0];
        $url  = 'https://api.yext.com/v2/accounts/'.$accountID.'/entities/'.$entityID.'?api_key='.$apiKey.'&v='.$today;
        $upData = array(
            "address" => array(
                "line1" => $postData['street'],
                "city"  => $postData['city'],
                "postalCode" => $postData['zip'],
            ),
            "emails" => array(
                0 => $postData['email'],
            ),
            "fax" => $postData['fax'],
            "mainPhone" => $postData['phone'],
            "additionalHoursText" => $postData['additionalHoursText'] //Warenannahme Montag bis Freitag von 09.00 bis 16.00 Uhr
        );
        function isClosed($postDayStart,$postDayEnd){
            if(!empty($postDayStart)){
                $dayData['openIntervals'][0] = array(
                    "start" => $postDayStart,
                    "end" => $postDayEnd
                );
            } else {
                $dayData['isClosed'] = true;
            }
            return $dayData;
        }
        foreach($days as $day){
            $upData['hours'][$day] = isClosed($postData[$day.'-start'],$postData[$day.'-end']);
        }

        if(__devMode__){
            echo '<div class="col-12 alert alert-warning" role="alert"><b>'.__('Sandbox mode active','blyxt').'</b><br/>';
            echo '<p class="d-none">'.$url.'</p></div>';
            echo '<p class="d-none">';
            showArray($upData);
            echo '</p></div>';
        }

        $res =  curl_post_contents($url, $upData);
        $resData = json_decode($res, true);
        $resMsg = !empty($resData['meta']['errors']) ? $resData['meta']['errors'] : 'data saved';
        return $resMsg;
    }
}