<?php defined('BX_DOL') or die('hack attempt');

class GmoFbEventsDb extends BxDolModuleDb
{
    public function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }

    public function findByFacebookId($sFacebookId)
    {
        return $this->getRow('SELECT * FROM `gmo_fb_events_imports` WHERE `facebook_event_id` = :id LIMIT 1', array('id' => $sFacebookId));
    }

    public function saveResult($sFacebookId, $sUrl, $iUnaEventId, $sHash, $sStatus, $sMessage)
    {
        $iNow = time();
        return $this->query(
            'INSERT INTO `gmo_fb_events_imports` (`facebook_event_id`,`source_url`,`una_event_id`,`payload_hash`,`status`,`message`,`created_at`,`updated_at`)
             VALUES (:facebook_id,:url,:una_id,:hash,:status,:message,:created,:updated)
             ON DUPLICATE KEY UPDATE `source_url`=:url2,`una_event_id`=:una_id2,`payload_hash`=:hash2,`status`=:status2,`message`=:message2,`updated_at`=:updated2',
            array(
                'facebook_id' => $sFacebookId, 'url' => $sUrl, 'una_id' => $iUnaEventId, 'hash' => $sHash,
                'status' => $sStatus, 'message' => $sMessage, 'created' => $iNow, 'updated' => $iNow,
                'url2' => $sUrl, 'una_id2' => $iUnaEventId, 'hash2' => $sHash, 'status2' => $sStatus,
                'message2' => $sMessage, 'updated2' => $iNow,
            )
        );
    }
}

