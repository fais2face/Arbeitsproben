<?php
require_once (__fryext__pluginPATH__ . __fryext__pluginName__ .'/libs/frwd_yext.class.php');
include (__fryext__pluginPATH__ . __fryext__pluginName__ .'/libs/frwd_yext.data.php');

$frYextBE = new frwdYext();

$tableHeader = fryxtTableHeader();
$days = fryxtDays();

if($_POST){
    $frYextBE->updateDB($_POST);
}

$getData = json_decode(json_encode($frYextBE->getblAccount()), true);
$livemode = $getData[0]['livemode'] ?? '';
$sandbox_accountID = $getData[0]['sandbox_accountID'] ?? '';
$sandbox_apiKey = $getData[0]['sandbox_apiKey'] ?? '';
$live_accountID = $getData[0]['live_accountID'] ?? '';
$live_apiKey    = $getData[0]['live_apiKey'] ?? '';


?>
<div id="blyxt_settings" class="pt-5 blyxt_wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Bauen+Leben Yxt</h1>
            </div>
            <div class="col-12 col-md-6">
                <h2>Einstellungen</h2>
                <?php
                if($frYextBE->getModestate()==0) {
                    echo '<div class="col-12 alert alert-warning" role="alert"><b>' . __('Sandbox mode active', 'blyxt') . '</b></div>';
                }
                ?>
                <?php if($_POST){ ?>
                    <div class="alert alert-success" role="alert">
                        Einstellungen gespeichert.
                    </div>
                <?php } ?>
                <form action="admin.php?page=blyxtdata-settings" method="post">
                    <div class="form-check">
                        <div class="switch">
                            <input class="form-check-input mt-1" type="checkbox" value="1" id="livemode" name="livemode" <?=$livemode=='1' ? 'checked="chcked"':''?>>
                            <span class="slider round"></span>
                        </div>
                        <label class="form-check-label" for="livemode">
                            Livemodus
                        </label>
                        <p>Bei deaktiviertem Livemodus wird automatisch der Sandbox Modus aktiv.</p>
                    </div>
                    <hr/>
                    <h3 class="mt-3">Sandbox-Daten</h3>
                    <div class="form-group">
                        <label for="sandbox_accountID">Account-ID</label>
                        <input type="text" class="form-control" id="sandbox_accountID" placeholder="Sandbox Account ID eingeben" name="sandbox_accountID" value="<?=$sandbox_accountID?>">
                    </div>
                    <div class="form-group">
                        <label for="sandbox_apiKey">API-Key</label>
                        <input type="text" class="form-control" id="sandbox_apiKey" name="sandbox_apiKey" placeholder="Sandboy API Key eingeben" value="<?=$sandbox_apiKey?>">
                    </div>
                    <hr/>
                    <h3 class="mt-3">Live-Daten</h3>
                    <div class="form-group">
                        <label for="live_accountID">Account-ID</label>
                        <input type="text" class="form-control" id="live_accountID" placeholder="Live Account ID eingeben" name="live_accountID" value="<?=$live_accountID?>">
                    </div>
                    <div class="form-group">
                        <label for="live_apiKey">API-Key</label>
                        <input type="text" class="form-control" id="live_apiKey" placeholder="Live API Key eingeben" name="live_apiKey" value="<?=$live_apiKey?>">
                    </div>
                    <button type="submit" class="btn btn-success float-end mt-2">Einstellungen speichern</button>
                </form>
            </div>
            <div class="col-12 col-md-6 col-xl-4 offset-xl-1">
                <div class="jumbotron">
                    <h2>Shortcode</h2>
                    <pre>[bl_location]</pre>
                    <p>Optional: Standortangabe</p>
                    <pre>[bl_location location="bonn"]</pre>
                </div>
            </div>
        </div>
    </div>
</div>
