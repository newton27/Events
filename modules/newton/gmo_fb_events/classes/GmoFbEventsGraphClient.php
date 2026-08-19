<?php defined('BX_DOL') or die('hack attempt');

class GmoFbEventsGraphClient
{
    private $sToken;
    private $sVersion;

    public function __construct($sToken, $sVersion)
    {
        $this->sToken = trim((string)$sToken);
        $this->sVersion = preg_replace('/[^0-9.]/', '', (string)$sVersion);
    }

    public function getEvent($sEventId)
    {
        if (!preg_match('/^\d{5,32}$/', $sEventId))
            throw new InvalidArgumentException('Invalid Facebook event ID.');

        if ($this->sToken === '')
            throw new RuntimeException('Add a Facebook Page access token in the module settings.');
        if (!preg_match('/^\\d+\\.\\d+$/', $this->sVersion))
            throw new RuntimeException('The Meta Graph API version is invalid.');

        $sFields = 'id,name,description,start_time,end_time,place,timezone,cover,event_times';
        $sUrl = 'https://graph.facebook.com/v' . $this->sVersion . '/' . rawurlencode($sEventId)
            . '?' . http_build_query(array('fields' => $sFields), '', '&', PHP_QUERY_RFC3986);

        $oCurl = curl_init($sUrl);
        curl_setopt_array($oCurl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer ' . $this->sToken,
            ),
        ));
        $sBody = curl_exec($oCurl);
        $iStatus = (int)curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        $sCurlError = curl_error($oCurl);
        curl_close($oCurl);

        if ($sBody === false)
            throw new RuntimeException('Meta request failed: ' . $sCurlError);

        $aData = json_decode($sBody, true);
        if (!is_array($aData))
            throw new RuntimeException('Meta returned invalid JSON.');
        if ($iStatus < 200 || $iStatus >= 300 || isset($aData['error'])) {
            $sMessage = isset($aData['error']['message']) ? $aData['error']['message'] : 'HTTP ' . $iStatus;
            throw new RuntimeException('Meta Graph API: ' . $sMessage);
        }
        return $aData;
    }
}

