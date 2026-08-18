<?php defined('BX_DOL') or die('hack attempt');

class GmoFbEventsModule extends BxDolModule
{
    public function extractEventId($sUrl)
    {
        $sUrl = trim((string)$sUrl);
        if (!filter_var($sUrl, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException('Invalid URL.');
        $aUrl = parse_url($sUrl);
        $sHost = strtolower(isset($aUrl['host']) ? $aUrl['host'] : '');
        if (!in_array($sHost, array('facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com'), true))
            throw new InvalidArgumentException('Only facebook.com event URLs are accepted.');
        $sPath = isset($aUrl['path']) ? $aUrl['path'] : '';
        if (!preg_match('~(?:^|/)events/(\d{5,32})(?:/|$)~', $sPath, $aMatch))
            throw new InvalidArgumentException('The URL does not contain a numeric Facebook event ID.');
        return $aMatch[1];
    }

    public function preview($sUrl)
    {
        $sEventId = $this->extractEventId($sUrl);
        $oClient = new GmoFbEventsGraphClient(
            getParam('gmo_fb_events_page_token'),
            getParam('gmo_fb_events_graph_version') ?: '24.0'
        );
        $aEvent = $oClient->getEvent($sEventId);
        return $this->normalize($sUrl, $aEvent);
    }

    public function import($sUrl)
    {
        $aEvent = $this->preview($sUrl);
        $aExisting = $this->_oDb->findByFacebookId($aEvent['facebook_id']);
        if ($aExisting && $aExisting['status'] === 'imported' && (int)$aExisting['una_event_id'] > 0)
            return array('status' => 'skipped', 'message' => 'Already imported.', 'una_event_id' => (int)$aExisting['una_event_id'], 'event' => $aEvent);

        $iAuthor = (int)getParam('gmo_fb_events_author_profile_id');
        $iCategory = (int)getParam('gmo_fb_events_category_id');
        if ($iAuthor < 1 || $iCategory < 1 || !getParam('gmo_fb_events_page_token'))
            throw new RuntimeException('Importer settings are incomplete.');

        $oEvents = BxDolModule::getInstance('bx_events');
        if (!$oEvents)
            throw new RuntimeException('UNA Events is not installed or enabled.');

        $aValues = array(
            'event_name' => $aEvent['name'],
            'event_desc' => $aEvent['description'],
            'event_cat' => $iCategory,
            'date_start' => $aEvent['date_start'],
            'date_end' => $aEvent['date_end'],
            'timezone' => $aEvent['timezone'],
            'allow_view_to' => 3,
            'allow_post_to' => 3,
            'join_confirmation' => 0,
        );

        $oHelper = $oEvents->getFormsHelper();
        $aResult = $oHelper->addData($iAuthor, $aValues);
        $sHash = hash('sha256', json_encode($aEvent));
        if (!isset($aResult['code']) || (int)$aResult['code'] !== 0) {
            $sMessage = isset($aResult['errors']) ? json_encode($aResult['errors']) : (isset($aResult['message']) ? $aResult['message'] : 'UNA event creation failed.');
            $this->_oDb->saveResult($aEvent['facebook_id'], $sUrl, 0, $sHash, 'failed', $sMessage);
            throw new RuntimeException($sMessage);
        }

        $iUnaId = (int)$aResult['content']['id'];
        $this->_oDb->saveResult($aEvent['facebook_id'], $sUrl, $iUnaId, $sHash, 'imported', 'Imported successfully.');
        return array('status' => 'imported', 'message' => 'Imported successfully.', 'una_event_id' => $iUnaId, 'event' => $aEvent);
    }

    private function normalize($sUrl, array $aEvent)
    {
        if (empty($aEvent['id']) || empty($aEvent['name']) || empty($aEvent['start_time']))
            throw new RuntimeException('Facebook did not return the required id, name, and start_time fields.');

        $sTimezone = !empty($aEvent['timezone']) ? $aEvent['timezone'] : (getParam('gmo_fb_events_timezone') ?: 'America/New_York');
        try { $oTimezone = new DateTimeZone($sTimezone); } catch (Exception $e) { $oTimezone = new DateTimeZone('UTC'); $sTimezone = 'UTC'; }
        $oStart = new DateTime($aEvent['start_time']);
        $oEnd = !empty($aEvent['end_time']) ? new DateTime($aEvent['end_time']) : (clone $oStart)->modify('+2 hours');
        $sVenue = '';
        if (!empty($aEvent['place']['name'])) $sVenue = $aEvent['place']['name'];
        if (!empty($aEvent['place']['location']) && is_array($aEvent['place']['location'])) {
            $aParts = array_filter(array_intersect_key($aEvent['place']['location'], array_flip(array('street','city','state','zip','country'))));
            if ($aParts) $sVenue .= ($sVenue ? "\n" : '') . implode(', ', $aParts);
        }
        $sDescription = trim((string)(isset($aEvent['description']) ? $aEvent['description'] : ''));
        if ($sVenue) $sDescription .= ($sDescription ? "\n\n" : '') . 'Location: ' . $sVenue;
        $sDescription .= ($sDescription ? "\n\n" : '') . 'Source: ' . $sUrl;

        return array(
            'facebook_id' => (string)$aEvent['id'], 'source_url' => $sUrl,
            'name' => trim($aEvent['name']), 'description' => $sDescription,
            'date_start' => $oStart->getTimestamp(), 'date_end' => $oEnd->getTimestamp(),
            'timezone' => $sTimezone, 'venue' => $sVenue,
            'cover_url' => isset($aEvent['cover']['source']) ? $aEvent['cover']['source'] : '',
        );
    }
}

