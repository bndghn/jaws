<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Notepad_Actions_View extends Jaws_Gadget_HTML
{
    /**
     * Displays a single note
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ViewNote($id = null)
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Notepad/resources/site_style.css');
        $tpl = $this->gadget->loadTemplate('View.html');
        $tpl->SetBlock('note');

        if ($id === null) {
            $id = (int)jaws()->request->fetch('id', 'get');
        }
        $model = $GLOBALS['app']->LoadGadget('Notepad', 'Model', 'Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $note = $model->GetNote($id, $user);
        if (Jaws_Error::IsError($note) || empty($note)) {
            $tpl->SetVariable('text', _t('NOTEPAD_ERROR_RETRIEVING_DATA'));
            $tpl->SetVariable('type', 'response_error');
        }

        $tpl->SetVariable('title', $note['title']);
        $tpl->SetVariable('content', $this->gadget->ParseText($note['content'], 'Notepad'));

        $tpl->ParseBlock('note');
        return $tpl->Get();
    }
}