<?php defined('BX_DOL') or die('hack attempt');

/**
 * Creates UNA Events from administrator-supplied Facebook event links and details.
 */
class GmoFbEventsModule extends BxDolModule
{
    /**
     * Extract a numeric event identifier from an approved Facebook event URL.
     *
     * @param string $sUrl Facebook event URL.
     * @return string
     * @throws InvalidArgumentException
     */
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

    /**
     * Create a UNA event from a Facebook link and administrator-entered details.
     *
     * @param string $sUrl Facebook event URL.
     * @param array $aInput Event fields submitted by the administrator.
     * @return array
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function importSharedLink($sUrl, array $aInput)
    {
        $sFacebookId = $this->extractEventId($sUrl);
        $sName = trim(isset($aInput['name']) ? (string)$aInput['name'] : '');
        $sDescription = trim(isset($aInput['description']) ? (string)$aInput['description'] : '');
        $sLocation = trim(isset($aInput['location']) ? (string)$aInput['location'] : '');
        $sStart = trim(isset($aInput['start']) ? (string)$aInput['start'] : '');
        $sEnd = trim(isset($aInput['end']) ? (string)$aInput['end'] : '');
        $sTimezone = trim(isset($aInput['timezone']) ? (string)$aInput['timezone'] : '');

        if ($sName === '' || $sStart === '')
            throw new InvalidArgumentException('Event title and start date/time are required.');
        if (mb_strlen($sName) > 255)
            throw new InvalidArgumentException('Event title is too long.');
        if (mb_strlen($sLocation) > 1000)
            throw new InvalidArgumentException('Event location is too long.');

        try {
            $oTimezone = new DateTimeZone($sTimezone !== '' ? $sTimezone : 'America/New_York');
        } catch (Exception $oException) {
            throw new InvalidArgumentException('Invalid timezone.');
        }

        $oStart = $this->parseLocalDateTime($sStart, $oTimezone, 'start');
        $oEnd = $sEnd !== ''
            ? $this->parseLocalDateTime($sEnd, $oTimezone, 'end')
            : (clone $oStart)->modify('+2 hours');

        if ($oEnd <= $oStart)
            throw new InvalidArgumentException('End date/time must be after the start.');

        $aExisting = $this->_oDb->findByFacebookId($sFacebookId);
        if ($aExisting && $aExisting['status'] === 'imported' && (int)$aExisting['una_event_id'] > 0) {
            return array(
                'status' => 'skipped',
                'message' => 'This Facebook event link was already imported.',
                'una_event_id' => (int)$aExisting['una_event_id'],
            );
        }

        $iAuthor = (int)getParam('gmo_fb_events_author_profile_id');
        $iCategory = (int)getParam('gmo_fb_events_category_id');
        if ($iAuthor < 1 || $iCategory < 1)
            throw new RuntimeException('Set the UNA author profile ID and event category ID in Studio first.');
        if (!BxDolProfile::getInstance($iAuthor))
            throw new RuntimeException('The configured UNA author profile does not exist.');

        $oEvents = BxDolModule::getInstance('bx_events');
        if (!$oEvents)
            throw new RuntimeException('UNA Events is not installed or enabled.');

        if ($sLocation !== '')
            $sDescription .= ($sDescription !== '' ? "\n\n" : '') . 'Location: ' . $sLocation;
        $sDescription .= ($sDescription !== '' ? "\n\n" : '') . 'Facebook event: ' . $sUrl;

        $aEvent = array(
            'facebook_id' => $sFacebookId,
            'source_url' => $sUrl,
            'name' => $sName,
            'description' => $sDescription,
            'date_start' => $oStart->getTimestamp(),
            'date_end' => $oEnd->getTimestamp(),
            'timezone' => $oTimezone->getName(),
            'venue' => $sLocation,
        );
        $aValues = array(
            'event_name' => $sName,
            'event_desc' => $sDescription,
            'event_cat' => $iCategory,
            'date_start' => $oStart->format('Y-m-d H:i:s P'),
            'date_end' => $oEnd->format('Y-m-d H:i:s P'),
            'timezone' => $oTimezone->getName(),
            'allow_view_to' => 3,
            'allow_post_to' => 3,
            'join_confirmation' => 0,
        );

        $aResult = $oEvents->getFormsHelper()->addData($iAuthor, $aValues);
        $sHash = hash('sha256', json_encode($aEvent));
        if (!isset($aResult['code']) || (int)$aResult['code'] !== 0) {
            $sMessage = $this->getAddErrorMessage($aResult);
            $this->_oDb->saveResult($sFacebookId, $sUrl, 0, $sHash, 'failed', $sMessage);
            throw new RuntimeException($sMessage);
        }

        $iUnaId = isset($aResult['content']['id']) ? (int)$aResult['content']['id'] : 0;
        if ($iUnaId < 1) {
            $sMessage = 'UNA created the event but did not return its content ID.';
            $this->_oDb->saveResult($sFacebookId, $sUrl, 0, $sHash, 'failed', $sMessage);
            throw new RuntimeException($sMessage);
        }

        $this->_oDb->saveResult($sFacebookId, $sUrl, $iUnaId, $sHash, 'imported', 'Imported successfully.');

        return array(
            'status' => 'imported',
            'message' => 'UNA event created from the shared Facebook link.',
            'una_event_id' => $iUnaId,
            'event' => $aEvent,
        );
    }

    /**
     * Parse an HTML datetime-local value without accepting calendar overflows.
     *
     * @param string $sValue Date/time value.
     * @param DateTimeZone $oTimezone Selected timezone.
     * @param string $sField Field name for the validation message.
     * @return DateTime
     * @throws InvalidArgumentException
     */
    private function parseLocalDateTime($sValue, DateTimeZone $oTimezone, $sField)
    {
        $oDate = DateTime::createFromFormat('!Y-m-d\\TH:i', $sValue, $oTimezone);
        $aErrors = DateTime::getLastErrors();
        $bHasErrors = is_array($aErrors) && ($aErrors['warning_count'] > 0 || $aErrors['error_count'] > 0);

        if (!$oDate || $bHasErrors || $oDate->format('Y-m-d\\TH:i') !== $sValue)
            throw new InvalidArgumentException('Invalid ' . $sField . ' date/time.');

        return $oDate;
    }

    /**
     * Convert an UNA form-helper failure into a safe administrator-facing message.
     *
     * @param mixed $aResult Form-helper response.
     * @return string
     */
    private function getAddErrorMessage($aResult)
    {
        if (is_array($aResult) && !empty($aResult['errors']) && is_array($aResult['errors'])) {
            $aMessages = array();
            foreach ($aResult['errors'] as $sField => $sError)
                $aMessages[] = $sField . ': ' . (is_scalar($sError) ? _t((string)$sError) : 'invalid value');

            if ($aMessages)
                return implode('; ', $aMessages);
        }

        if (is_array($aResult) && !empty($aResult['message']) && is_string($aResult['message']))
            return _t($aResult['message']);

        return 'UNA event creation failed.';
    }
}
