<?php
require_once('../../../inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');

header('Content-Type: text/html; charset=utf-8');
if (!isAdmin()) {
    http_response_code(403);
    echo 'Administrator access is required.';
    exit;
}

$oModule = BxDolModule::getInstance('gmo_fb_events');
if (!$oModule) {
    http_response_code(503);
    echo 'Facebook Events Importer is not enabled.';
    exit;
}

$sCsrfToken = BxDolForm::getCsrfToken();
$sStudioUrl = BX_DOL_URL_STUDIO;
$sSettingsUrl = BX_DOL_URL_STUDIO . 'module.php?name=gmo_fb_events';
$sStudioIcon = 'gmo_fb_events@modules/newton/gmo_fb_events/|std-icon.svg';
$oDb = BxDolDb::getInstance();
$oDb->query("UPDATE `sys_std_pages` SET `icon` = ? WHERE `name` = ?", array($sStudioIcon, 'gmo_fb_events'));
$oDb->query("UPDATE `sys_std_widgets` SET `icon` = ? WHERE `module` = ?", array($sStudioIcon, 'gmo_fb_events'));
$oDb->query("UPDATE `sys_options_types` SET `icon` = ? WHERE `name` = ?", array($sStudioIcon, 'gmo_fb_events'));
$oDb->query("DELETE FROM `sys_options` WHERE `name` IN ('gmo_fb_events_page_token', 'gmo_fb_events_graph_version')");

$bAuthorConfigured = (int)getParam('gmo_fb_events_author_profile_id') > 0;
$bCategoryConfigured = (int)getParam('gmo_fb_events_category_id') > 0;
$bConfigured = $bAuthorConfigured && $bCategoryConfigured;
$sTimezone = getParam('gmo_fb_events_timezone') ?: 'America/New_York';
$aFields = array(
    'event_url' => isset($_POST['event_url']) ? trim((string)$_POST['event_url']) : '',
    'event_name' => isset($_POST['event_name']) ? trim((string)$_POST['event_name']) : '',
    'event_start' => isset($_POST['event_start']) ? trim((string)$_POST['event_start']) : '',
    'event_end' => isset($_POST['event_end']) ? trim((string)$_POST['event_end']) : '',
    'event_location' => isset($_POST['event_location']) ? trim((string)$_POST['event_location']) : '',
    'event_description' => isset($_POST['event_description']) ? trim((string)$_POST['event_description']) : '',
);
$aResults = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sToken = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
    if (getParam('sys_security_form_token_enable') === 'on' && ($sToken === '' || !BxDolForm::isCsrfTokenValid($sToken))) {
        http_response_code(400);
        $aResults[] = array('url' => $aFields['event_url'], 'ok' => false, 'message' => 'The form expired. Reload and try again.');
    } else {
        try {
            $aData = $oModule->importSharedLink($aFields['event_url'], array(
                'name' => $aFields['event_name'],
                'start' => $aFields['event_start'],
                'end' => $aFields['event_end'],
                'location' => $aFields['event_location'],
                'description' => $aFields['event_description'],
                'timezone' => $sTimezone,
            ));
            $aResults[] = array('url' => $aFields['event_url'], 'ok' => true, 'message' => $aData['message'], 'data' => $aData);
            if ($aData['status'] === 'imported')
                $aFields = array_fill_keys(array_keys($aFields), '');
        } catch (Throwable $e) {
            $aResults[] = array('url' => $aFields['event_url'], 'ok' => false, 'message' => $e->getMessage());
        }
    }
}

function gmo_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Facebook Link Event Importer</title>
<style>:root{color-scheme:light}body{margin:0;background:#f5f7fb;color:#202124;font:16px/1.45 system-ui,-apple-system,"Segoe UI",sans-serif}.topbar{display:flex;gap:10px;align-items:center;padding:14px 22px;background:#fff;border-bottom:1px solid #dfe3eb;box-shadow:0 1px 4px #102a4312}.wrap{max-width:900px;margin:32px auto;padding:0 20px}.card{background:#fff;border:1px solid #dfe3eb;border-radius:12px;padding:20px;margin:16px 0;box-shadow:0 4px 16px #102a430d}.btn,button{display:inline-block;border:0;border-radius:7px;padding:10px 16px;background:#1769e0;color:#fff;text-decoration:none;font:600 14px system-ui;cursor:pointer}.btn.secondary{background:#e8eef8;color:#17345f}.status{border-left:5px solid #188038}.status.bad,.result.bad{border-left:5px solid #d93025}.result.ok{border-left:5px solid #188038}.field{margin:14px 0}.field label{display:block;font-weight:600;margin-bottom:6px}.field input,.field textarea{width:100%;padding:11px;border:1px solid #b9c1ce;border-radius:8px;box-sizing:border-box;font:15px/1.4 system-ui}.field textarea{min-height:120px}.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}.result{border:1px solid #ddd;border-radius:8px;padding:14px;margin:12px 0}.muted{color:#666;font-size:14px}h1{margin-bottom:6px}@media(max-width:650px){.row{grid-template-columns:1fr}}</style></head><body>
<nav class="topbar"><a class="btn secondary" href="<?=gmo_h($sStudioUrl)?>">&larr; Back to Studio</a><a class="btn" href="<?=gmo_h($sSettingsUrl)?>">Importer settings</a></nav><main class="wrap">
<h1>Facebook Link Event Importer</h1>
<p>Share a Facebook event link and enter its essential details. No Facebook API token is required.</p>
<section class="card status <?=$bConfigured?'':'bad'?>"><strong><?=$bConfigured?'Ready to import':'Setup required'?></strong>
<p><?=$bConfigured?'UNA author profile and event category are configured.':'Set the UNA author profile ID and event category ID in Studio.'?></p>
<div class="muted">Author profile: <?=$bAuthorConfigured?'configured':'missing'?> &middot; Event category: <?=$bCategoryConfigured?'configured':'missing'?> &middot; Timezone: <?=gmo_h($sTimezone)?></div></section>

<form class="card" method="post">
<input type="hidden" name="csrf" value="<?=gmo_h($sCsrfToken)?>">
<div class="field"><label for="event_url">Facebook event link</label><input id="event_url" name="event_url" type="url" required placeholder="https://www.facebook.com/events/1371657733979615/" value="<?=gmo_h($aFields['event_url'])?>"></div>
<div class="field"><label for="event_name">Event title</label><input id="event_name" name="event_name" maxlength="255" required value="<?=gmo_h($aFields['event_name'])?>"></div>
<div class="row"><div class="field"><label for="event_start">Starts</label><input id="event_start" name="event_start" type="datetime-local" required value="<?=gmo_h($aFields['event_start'])?>"></div>
<div class="field"><label for="event_end">Ends</label><input id="event_end" name="event_end" type="datetime-local" value="<?=gmo_h($aFields['event_end'])?>"></div></div>
<div class="field"><label for="event_location">Location (optional)</label><input id="event_location" name="event_location" value="<?=gmo_h($aFields['event_location'])?>"></div>
<div class="field"><label for="event_description">Description (optional)</label><textarea id="event_description" name="event_description"><?=gmo_h($aFields['event_description'])?></textarea></div>
<button type="submit">Create UNA event</button>
</form>

<?php foreach ($aResults as $aResult): ?><section class="result <?=$aResult['ok']?'ok':'bad'?>">
<strong><?=gmo_h($aResult['ok']?'Success':'Error')?></strong> &mdash; <?=gmo_h($aResult['message'])?>
<?php if ($aResult['url']): ?><div class="muted"><?=gmo_h($aResult['url'])?></div><?php endif; ?>
</section><?php endforeach; ?></main></body></html>
