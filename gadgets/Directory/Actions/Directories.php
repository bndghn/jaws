<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Directories extends Jaws_Gadget_HTML
{
    /**
     * Builds the directory management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function DirectoryForm()
    {
        $tpl = $this->gadget->loadTemplate('Directory.html');
        $tpl->SetBlock('directoryForm');
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_DIR_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_DIR_DESC'));
        $tpl->SetVariable('lbl_parent', _t('DIRECTORY_DIR_PARENT'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
        $tpl->ParseBlock('directoryForm');
        return $tpl->Get();
    }

    /**
     * Creates a new directory
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateDirectory()
    {
        try {
            $request =& Jaws_Request::getInstance();
            $data = $request->get(array('title', 'description', 'parent'));
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = true;
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $result = $model->InsertFile($data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_CREATE'));
            }
        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse($e->getMessage(), 'Directory', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(_t('DIRECTORY_NOTICE_DIR_CREATED'), 'Directory');
        Jaws_Header::Referrer();
    }

    /**
     * Updates directory
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateDirectory()
    {
        try {
            $request =& Jaws_Request::getInstance();
            $id = (int)$request->get('id');
            $data = $request->get(array('title', 'description', 'parent'));
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['is_dir'] = true;
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $result = $model->UpdateFile($id, $data);
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('DIRECTORY_ERROR_DIR_UPDATE'));
            }
        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse($e->getMessage(), 'Directory', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        }

        $GLOBALS['app']->Session->PushResponse(_t('DIRECTORY_NOTICE_DIR_UPDATED'), 'Directory');
        Jaws_Header::Referrer();
    }

}