<?php
require_once (__fryext__pluginPATH__ . __fryext__pluginName__ .'/libs/frwd_yext.class.php');
include (__fryext__pluginPATH__ . __fryext__pluginName__ .'/libs/frwd_yext.data.php');

$frYextBE = new frwdYext();

$tableHeader= fryxtTableHeader();
$days = fryxtDays();
$data = $frYextBE->getAccountbyState();

if($_POST){
    $frYextBE->updateData($_POST);
}

?>
<div id="blyxt_overview" class="pt-5 blyxt_wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Bauen+Leben Yext-Data</h1>
                <h2>Standorte</h2>
                <hr/>
                <?php
                if($frYextBE->getModestate()==0) {
                    echo '<div class="col-12 alert alert-warning" role="alert"><b>' . __('Sandbox mode active', 'blyxt') . '</b></div>';
                }
                ?>
                <?php if(isset($_GET['editlocation']) && !empty($_GET['editlocation']) ){ ?>
                <?php if($_POST){ ?>
                    <div class="alert alert-success" role="alert">
                        Einstellungen gespeichert.
                    </div>
                <?php } ?>
                <h3 class="mt-1">Standort bearbeiten</h3>
                <form class="row g-3" method="post" action="">
                    <?php
                    $locationData = $frYextBE->getLocationbyID($_GET['editlocation']);

                    foreach($locationData['response']['entities'] as $locData) {
                        $relocData[0] = $locData['meta']['id'];
                        $relocData[1] = $locData['address']['line1'];
                        $relocData[2] = $locData['address']['city'];
                        $relocData[3] = $locData['address']['postalCode'];
                        $relocData[4] = $frYextBE->checkKey('mainPhone', $locData);
                        $relocData[5] = $frYextBE->checkKey('fax', $locData);
                        $relocData[6] = $locData['emails'][0] ?? '';
                    }

                    $rl = 7;
                    foreach($days as $day){
                        $relocData[$rl] = $locData['hours'][$day]['openIntervals'][0]['start'] ?? '';
                        $relocData[++$rl] = $locData['hours'][$day]['openIntervals'][0]['end'] ?? '';
                        $rl++;
                    }

                    $relocData[$rl] = $frYextBE->checkKey('additionalHoursText', $locationData['response']['entities'][0]);

                    $x = 1;
                    $renderInput = "";

                    foreach($tableHeader as $th => $thvalue){
                        if($th == 0){
                            continue;
                        }
                        $thval = $relocData[$x] ?? '';

                        if(is_array($relocData[$x])){
                            $tdDatastart = $frYextBE->checkKey('start',$relocData[$x]);
                            $tdDataend   = $frYextBE->checkKey('end',$relocData[$x]);
                            $renderInput .= '<div class="mb-3 col-auto"><label for="bl_data_'.$x.'" class="form-label">'.__( $thvalue, 'blyxt').'</label><input class="form-control" id="bl_data_'.$x.'" type="text" name="'.sanitize_title($thvalue).'" value="'.$relocData[$x]['start'].'"></div>';
                            $renderInput .= '<div class="mb-3 col-auto"><label for="bl_data_'.$x.'2" class="form-label">'.__( $thvalue, 'blyxt').'</label><input class="form-control" id="bl_data_'.$x.'2" type="text" name="'.sanitize_title($thvalue).'" value="'.$relocData[$x]['end'].'"></div>';

                        } else {
                            if($x<21){
                                $renderInput .= '<div class="mb-3 col-auto"><label for="bl_data_'.$x.'" class="form-label">'.__( $thvalue, 'blyxt').'</label><input class="form-control" id="bl_data_'.$x.'" type="text" name="'.sanitize_title($thvalue).'" value="'.$thval.'"></div>';
                            }else {
                                $renderInput .= '<div class="mb-3 col-12"><label for="bl_data_'.$x.'" class="form-label">'.__( $thvalue, 'blyxt').'</label><textarea class="form-control" id="bl_data_'.$x.'" type="text" name="'.$thvalue.'">'.$thval.'</textarea></div>';
                            }

                        }
                        $x++;
                    }
                    echo $renderInput;
                    ?>
                    <input type="hidden" name="blid" value="<?=$relocData[0]?>"/>
                    <div class="mb-3 col-12">
                        <a href="admin.php?page=blyxtdata" class="float-start">&laquo; Zurück zur Übersicht</a>
                        <button type="submit" class="btn btn-success float-end">Daten Speichern</button>
                    </div>
                </form>
            </div>
        </div>
        <?php } else { ?>

            <?php
            $nextPage = isset($_GET['nextPage']) && !empty($_GET['nextPage']) ? $_GET['nextPage'] : '';
            $locationData = isset($_GET['getlocation']) && !empty($_GET['getlocation']) ? $frYextBE->getLocation(sanitize_title(strtolower($_GET['getlocation']))) : $frYextBE->getLocation('',$nextPage);
            ?>

            <?php if(!empty($locationData)){ ?>
                <div class="row mt-3">
                    <div class="col-md-6 col-12">
                        <h3>Standortsuche</h3>
                        <form method="GET" action="admin.php?page=blyxtdata">
                            <div class="input-group mb-3">
                                <input class="form-control" id="findblloc" type="text" name="getlocation" placeholder="Standort suchen" value=""/>
                                <button type="submit" class="btn btn-search">Suchen</button>
                            </div>
                            <input type="hidden" name="page" value="blyxtdata"/>
                        </form>
                        <?php if(isset($_GET['getlocation'])){
                           echo '<a class="showall" href="admin.php?page=blyxtdata">Alle anzeigen</a>';
                        }?>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="col-12">
                    <hr/>
                    <h3>Übersicht aller Standorte</h3>
                    <div class="table-wrapper">
                        <div class="overflow-auto">
                            <?php
                            if(empty($locationData)){
                                echo __('no data available.', 'blyxt');
                            } else {
                                $renderTable  = '<table class="table table-striped table-hover">';
                                $renderTable .= '<thead><tr>';
                                foreach($tableHeader as $th => $thvalue){
                                    $renderTable .= '<th>'.__( $thvalue, 'blyxt').'</th>';
                                }
                                $renderTable .= '</tr></thead>';
                                $renderTable .= '<tbody>';
                                foreach($locationData['response']['entities'] as $locData){
                                    $renderTable .= '<tr>';
                                    $renderTable .= '<td><a href="admin.php?page=blyxtdata&editlocation='.$locData['meta']['id'].'"><img src="'.__fryext__pluginURL__. 'assets/images/edit.png"/></i></a></td>';
                                    $renderTable .= '<td>'.$locData['address']['line1'].'</td>';
                                    $renderTable .= '<td>'.$locData['address']['city'].'</td>';
                                    $renderTable .= '<td>'.$locData['address']['postalCode'].'</td>';
                                    $renderTable .= '<td>'.$frYextBE->checkKey('mainPhone',$locData).'</td>';
                                    $renderTable .= '<td>'.$frYextBE->checkKey('fax',$locData).'</td>';
                                    $email = $locData['emails'][0] ?? '';
                                    $renderTable .= '<td>'.$email.'</td>';

                                    $days = array( 0 => 'monday', 1 => 'tuesday', 2 => 'wednesday', 3 => 'thursday', 4 => 'friday', 5 => 'saturday', 6 => 'sunday');
                                    foreach($days as $day){
                                        $dayData1 = $locData['hours'][$day]['openIntervals'][0]['start'] ?? '';
                                        $dayData2 = $locData['hours'][$day]['openIntervals'][0]['end'] ?? '';
                                        $renderTable .= '<td>'.$dayData1.'</td>';
                                        $renderTable .= '<td>'.$dayData2.'</td>';
                                    }
                                    $renderTable .= '<td>'.$frYextBE->checkKey('additionalHoursText',$locData).'</td>';
                                    $renderTable .= '</tr>';

                                }
                                $renderTable .= '</tbody>';
                                $renderTable .= '</table>';
                                echo $renderTable;
                            }

                            ?>
                        </div>
                        <?php
                        if(!empty($locationData)){
                            $nextPage = $_GET['nextPage'] ?? '';
                            if(isset($locationData['response']['pageToken']) || $nextPage){ ?>
                                <nav aria-label="Page navigation example" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $_GET['nextPage'] ? '':'disabled'; ?>">
                                            <a class="page-link" href="admin.php?page=blyxtdata&nextPage=" tabindex="-1"><?= __('Previous page', 'blyxt')?></a>
                                        </li>
                                        <li class="page-item <?php echo isset($locationData['response']['pageToken']) ? '':'disabled'; ?>">
                                            <a class="page-link" href="admin.php?page=blyxtdata&nextPage=<?=$locationData['response']['pageToken'] ?? '';?>"><?= __('Next page', 'blyxt')?></a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
