<?php defined('BX_DOL') or die('hack attempt');

class GmoFbEventsStudioPage extends BxTemplStudioModule
{
    protected $_sModule;
    protected $_oModule;

    public function __construct($sModule, $mixedPageName, $sPage = '')
    {
        $this->_sModule = 'gmo_fb_events';
        $this->_oModule = BxDolModule::getInstance($this->_sModule);
        parent::__construct($sModule, $mixedPageName, $sPage);
    }

    protected function getSettings()
    {
        $oOptions = new BxTemplStudioOptions($this->sModule);
        $this->aPageCss = array_merge($this->aPageCss, $oOptions->getCss());
        $this->aPageJs = array_merge($this->aPageJs, $oOptions->getJs());

        $sImporterUrl = BX_DOL_URL_ROOT . 'modules/newton/gmo_fb_events/action.php';
        $sIntro = '<div class="bx-def-margin-bottom"><a class="bx-btn" href="' . htmlspecialchars($sImporterUrl, ENT_QUOTES, 'UTF-8') . '">Open UNA Facebook Importer</a></div>';

        return BxDolStudioTemplate::getInstance()->parseHtmlByName('module.html', array(
            'content' => $sIntro . $oOptions->getCode(),
        ));
    }
}
