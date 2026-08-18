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
<style>body{font:16px/1.45 system-ui,sans-serif;max-width:900px;margin:40px auto;padding:0 20px;color:#202124}textarea{width:100%;min-height:180px;padding:12px;box-sizing:border-box}button{padding:10px 18px;margin:12px 8px 12px 0}.result{border:1px solid #ddd;border-radius:8px;padding:14px;margin:12px 0}.ok{border-left:5px solid #188038}.bad{border-left:5px solid #d93025}.muted{color:#666;font-size:14px}</style></head><body>
<h1>Facebook Events Importer</h1>
<p>Paste one Facebook event URL per line. Preview first, then import. Maximum 25 links per request.</p>
<form method="post"><input type="hidden" name="csrf" value="<?=gmo_h($_SESSION['gmo_fb_events_csrf'])?>">
<textarea name="urls" required placeholder="https://www.facebook.com/events/123456789012345/"><?=gmo_h($sUrls)?></textarea><br>
<button name="mode" value="preview">Preview</button><button name="mode" value="import">Import into GayMen.Online</button></form>
<?php foreach ($aResults as $aResult): ?><section class="result <?=$aResult['ok']?'ok':'bad'?>">
<strong><?=gmo_h($aResult['ok']?'Success':'Error')?></strong> — <?=gmo_h($aResult['message'])?><div class="muted"><?=gmo_h($aResult['url'])?></div>
<?php if (!empty($aResult['data']['event'])): $e=$aResult['data']['event']; ?><h2><?=gmo_h($e['name'])?></h2><p><?=nl2br(gmo_h($e['description']))?></p><p><b>Starts:</b> <?=gmo_h(date('c',$e['date_start']))?> · <b>Timezone:</b> <?=gmo_h($e['timezone'])?></p><?php endif; ?>
</section><?php endforeach; ?></body></html>

