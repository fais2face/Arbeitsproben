<?php
function fryxtTableHeader() {
    $data = array(
        0 => 'edit',
        1 => 'street',
        2 => 'city',
        3 => 'zip',
        4 => 'phone',
        5 => 'fax',
        6 => 'email',
        7 => 'monday start',
        8 => 'monday end',
        9 => 'tuesday start',
        10 => 'tuesday end',
        11 => 'wednesday start',
        12 => 'wednesday end',
        13 => 'thursday start',
        14 => 'thursday end',
        15 => 'friday start',
        16 => 'friday end',
        17 => 'saturday start',
        18 => 'saturday end',
        19 => 'sunday start',
        20 => 'sunday end',
        21 => 'additionalHoursText',
    );
    return $data;
}

function fryxtDays(){
    $data = array( 0 => 'monday', 1 => 'tuesday', 2 => 'wednesday', 3 => 'thursday', 4 => 'friday', 5 => 'saturday', 6 => 'sunday');
    return $data;
}
