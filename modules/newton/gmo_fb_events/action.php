<?php
require_once('../../../inc/header.inc.php');

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

if (empty($_SESSION['gmo_fb_events_csrf']))
    $_SESSION['gmo_fb_events_csrf'] = bin2hex(random_bytes(32));

$sStudioUrl = BX_DOL_URL_STUDIO;
$sSettingsUrl = BX_DOL_URL_STUDIO . 'module.php?name=gmo_fb_events';
$bTokenConfigured = trim((string)getParam('gmo_fb_events_page_token')) !== '';
$bAuthorConfigured = (int)getParam('gmo_fb_events_author_profile_id') > 0;
$bCategoryConfigured = (int)getParam('gmo_fb_events_category_id') > 0;
$bConfigured = $bTokenConfigured && $bAuthorConfigured && $bCategoryConfigured;

$aResults = array();
$sUrls = isset($_POST['urls']) ? trim((string)$_POST['urls']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sToken = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
    if (!hash_equals($_SESSION['gmo_fb_events_csrf'], $sToken)) {
        http_response_code(400);
        $aResults[] = array('url' => '', 'ok' => false, 'message' => 'The form expired. Reload and try again.');
    } else {
        $bImport = isset($_POST['mode']) && $_POST['mode'] === 'import';
        $aUrls = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', $sUrls)))));
        if (count($aUrls) > 25)
            $aUrls = array_slice($aUrls, 0, 25);
        foreach ($aUrls as $sUrl) {
            try {
                $aData = $bImport ? $oModule->import($sUrl) : array('event' => $oModule->preview($sUrl), 'message' => 'Preview ready.');
                $aResults[] = array('url' => $sUrl, 'ok' => true, 'message' => $aData['message'], 'data' => $aData);
            } catch (Throwable $e) {
                $aResults[] = array('url' => $sUrl, 'ok' => false, 'message' => $e->getMessage());
            }
        }
    }
}

function gmo_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Facebook Events Importer</title>
<style>:root{color-scheme:light}body{margin:0;background:#f5f7fb;color:#202124;font:16px/1.45 system-ui,-apple-system,"Segoe UI",sans-serif}.topbar{display:flex;gap:10px;align-items:center;padding:14px 22px;background:#fff;border-bottom:1px solid #dfe3eb;box-shadow:0 1px 4px #102a4312}.wrap{max-width:900px;margin:32px auto;padding:0 20px}.card{background:#fff;border:1px solid #dfe3eb;border-radius:12px;padding:20px;margin:16px 0;box-shadow:0 4px 16px #102a430d}.btn,button{display:inline-block;border:0;border-radius:7px;padding:10px 16px;background:#1769e0;color:#fff;text-decoration:none;font:600 14px system-ui;cursor:pointer}.btn.secondary{background:#e8eef8;color:#17345f}.status{border-left:5px solid #188038}.status.bad{border-left-color:#d93025}textarea{width:100%;min-height:180px;padding:12px;border:1px solid #b9c1ce;border-radius:8px;box-sizing:border-box;font:15px/1.4 ui-monospace,monospace}button{margin:12px 8px 0 0}.result{border:1px solid #ddd;border-radius:8px;padding:14px;margin:12px 0}.ok{border-left:5px solid #188038}.bad{border-left:5px solid #d93025}.muted{color:#666;font-size:14px}h1{margin-bottom:6px}</style></head><body>
<nav class="topbar"><a class="btn secondary" href="<?=gmo_h($sStudioUrl)?>">← Back to Studio</a><a class="btn" href="<?=gmo_h($sSettingsUrl)?>">Configure API settings</a></nav><main class="wrap">
<h1>Facebook Events Importer</h1>
<p>Paste one Facebook event URL per line. Preview first, then import. Maximum 25 links per request.</p>
<section class="card status <?=$bConfigured?'':'bad'?>"><strong><?=$bConfigured?'Ready to import':'Setup required'?></strong>
<p><?=$bConfigured?'The Page token, UNA author profile, and event category are configured.':'Open API settings and complete the Page access token, UNA author profile ID, and UNA event category ID.'?></p>
<div class="muted">Page token: <?=$bTokenConfigured?'configured':'missing'?> · Author profile: <?=$bAuthorConfigured?'configured':'missing'?> · Event category: <?=$bCategoryConfigured?'configured':'missing'?></div></section>
<form class="card" method="post"><input type="hidden" name="csrf" value="<?=gmo_h($_SESSION['gmo_fb_events_csrf'])?>">
<textarea name="urls" required placeholder="https://www.facebook.com/events/123456789012345/"><?=gmo_h($sUrls)?></textarea><br>
<button name="mode" value="preview">Preview</button><button name="mode" value="import">Import into GayMen.Online</button></form>
<?php foreach ($aResults as $aResult): ?><section class="result <?=$aResult['ok']?'ok':'bad'?>">
<strong><?=gmo_h($aResult['ok']?'Success':'Error')?></strong> — <?=gmo_h($aResult['message'])?><div class="muted"><?=gmo_h($aResult['url'])?></div>
<?php if (!empty($aResult['data']['event'])): $e=$aResult['data']['event']; ?><h2><?=gmo_h($e['name'])?></h2><p><?=nl2br(gmo_h($e['description']))?></p><p><b>Starts:</b> <?=gmo_h(date('c',$e['date_start']))?> · <b>Timezone:</b> <?=gmo_h($e['timezone'])?></p><?php endif; ?>
</section><?php endforeach; ?></main></body></html>
