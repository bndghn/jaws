<?php
/**
 * Comments Core Gadget
 *
 * @category    Gadget
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Comments extends Comments_Actions_Admin_Default
{
    /**
     * Show comments list
     *
     * @access  public
     * @param   string $gadget     Gadget name
     * @param   string $url        Gadget manage comments URL
     * @return  string XHTML template content
     */
    function Comments($gadget='', $url='')
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Comments.html');
        $tpl->SetBlock('Comments');

        //Menu bar
        if(!empty($url)) {
        $tpl->SetVariable('menubar', $url);
        } else {
            $tpl->SetVariable('menubar', $this->MenuBar('Comments'));
        }

        //load other gadget translations
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        if (empty($gadget)) {
            $tpl->SetBlock('Comments/gadgets_filter');
            //Gadgets filter
            $gadgetsCombo =& Piwi::CreateWidget('Combo', 'gadgets_filter');
            $gadgetsCombo->SetID('gadgets_filter');
            $gadgetsCombo->setStyle('width: 100px;');
            $gadgetsCombo->AddEvent(ON_CHANGE, "searchComment()");
            $gadgetsCombo->AddOption('', '');
            // TODO: Get List Of Gadget Which Use Comments
            $gadgetsCombo->AddOption(_t('COMMENTS_TITLE'), 'Comments');
            $gadgetsCombo->AddOption(_t('BLOG_TITLE'), 'Blog');
            $gadgetsCombo->AddOption(_t('PHOO_TITLE'), 'Phoo');
            $gadgetsCombo->AddOption(_t('SHOUTBOX_TITLE'), 'Shoutbox');
            $gadgetsCombo->SetDefault('');
            $tpl->SetVariable('lbl_gadgets_filter', _t('COMMENTS_GADGETS'));
            $tpl->SetVariable('gadgets_filter', $gadgetsCombo->Get());
            $tpl->ParseBlock('Comments/gadgets_filter');
        } else {
            $gadgets_filter =& Piwi::CreateWidget('HiddenEntry', 'gadgets_filter', $gadget);
            $gadgets_filter->SetID('gadgets_filter');
            $tpl->SetVariable('gadgets_filter', $gadgets_filter->Get());
        }

        //Status
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->AddOption('&nbsp;',0);
        $status->AddOption(_t('COMMENTS_STATUS_APPROVED'), Comments_Info::COMMENTS_STATUS_APPROVED);
        $status->AddOption(_t('COMMENTS_STATUS_WAITING'), Comments_Info::COMMENTS_STATUS_WAITING);
        $status->AddOption(_t('COMMENTS_STATUS_SPAM'), Comments_Info::COMMENTS_STATUS_SPAM);
        $status->AddOption(_t('COMMENTS_STATUS_PRIVATE'), Comments_Info::COMMENTS_STATUS_PRIVATE);
        $status->SetDefault(0);
        $status->AddEvent(ON_CHANGE, 'searchComment();');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $status->Get());

        // filter
        $filterData = jaws()->request->fetch('filter', 'get');
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', is_null($filterData)? '' : $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('COMMENTS_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript: searchComment();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        //DataGrid
        $tpl->SetVariable('grid', $this->Get('', true));

        //CommentUI
        $tpl->SetVariable('comment_ui', $this->CommentUI());

        if ($this->gadget->GetPermission('ManageComments')) {
            $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $btnCancel->AddEvent(ON_CLICK, 'stopCommentAction();');
            $btnCancel->SetStyle('display: none;');
            $tpl->SetVariable('btn_cancel', $btnCancel->Get());

            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "updateComment(false);");
            $btnSave->SetStyle('display: none;');
            $tpl->SetVariable('btn_save', $btnSave->Get());

            $btnReply =& Piwi::CreateWidget('Button', 'btn_reply', _t('COMMENTS_SAVE_AND_REPLY'), 
                                                        'gadgets/Contact/Resources/images/contact_mini.png');
            $btnReply->AddEvent(ON_CLICK, "updateComment(true);");
            $btnReply->SetStyle('display: none;');
            $tpl->SetVariable('btn_reply', $btnReply->Get());
        }

        $tpl->SetVariable('incompleteCommentsFields', _t('COMMENTS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmCommentDelete',    _t('COMMENTS_CONFIRM_DELETE'));
        $tpl->SetVariable('legend_title',            _t('COMMENTS_EDIT_MESSAGE_DETAILS'));
        $tpl->SetVariable('messageDetail_title',     _t('COMMENTS_EDIT_MESSAGE_DETAILS'));

        $tpl->ParseBlock('Comments');
        return $tpl->Get();
    }

    /**
     * Show a form to show/edit a given comments
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function CommentUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Comments.html');
        $tpl->SetBlock('CommentUI');

        //IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));

        //name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        //email
        $nameEntry =& Piwi::CreateWidget('Entry', 'email', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $nameEntry->Get());

        //url
        $nameEntry =& Piwi::CreateWidget('Entry', 'url', '');
        $nameEntry->setStyle('width: 270px;');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $nameEntry->Get());

        //Status
        $status =& Piwi::CreateWidget('Combo', 'comment_status');
        $status->AddOption('&nbsp;','various');
        $status->AddOption(_t('COMMENTS_STATUS_APPROVED'), Comments_Info::COMMENTS_STATUS_APPROVED);
        $status->AddOption(_t('COMMENTS_STATUS_WAITING'), Comments_Info::COMMENTS_STATUS_WAITING);
        $status->AddOption(_t('COMMENTS_STATUS_SPAM'), Comments_Info::COMMENTS_STATUS_SPAM);
        $status->AddOption(_t('COMMENTS_STATUS_PRIVATE'), Comments_Info::COMMENTS_STATUS_PRIVATE);
        $status->SetDefault('various');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $status->Get());

        //message
        $messageText =& Piwi::CreateWidget('TextArea', 'message','');
        $messageText->SetStyle('width: 270px;');
        $messageText->SetRows(8);
        $tpl->SetVariable('lbl_message', _t('COMMENTS_MESSAGE'));
        $tpl->SetVariable('message', $messageText->Get());


        //reply
        $replyText =& Piwi::CreateWidget('TextArea', 'reply','');
        $replyText->SetStyle('width: 270px;');
        $replyText->SetRows(8);
        if(!$this->gadget->GetPermission('ReplyComments')) {
            $replyText->SetEnabled(false);
        }
        $tpl->SetVariable('lbl_reply', _t('COMMENTS_REPLY'));
        $tpl->SetVariable('reply', $replyText->Get());

        $tpl->ParseBlock('CommentUI');
        return $tpl->Get();
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $editAction Edit action
     * @param   string  $term       Search term
     * @param   int     $status     Spam status (approved=1, waiting=2, spam=3)
     * @param   mixed   $offset     Data offset (numeric/boolean)
     * @param   bool    $gadgetColumn   Display gadget column?
     * @return  array   Filtered Comments
     */
    function GetDataAsArray($gadget, $editAction, $term, $status, $offset, $gadgetColumn=false)
    {
        $cModel = $this->gadget->model->load('Comments');
        $comments = $cModel->GetComments($gadget, '', '', $term, $status, 15, $offset, 0, true);
        if (Jaws_Error::IsError($comments)) {
            return array();
        }

        if ($gadgetColumn) {
            //load other gadget translations
            $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
            $objTranslate = Jaws_Translate::getInstance();
            $objTranslate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
            $objTranslate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
            $objTranslate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);
        }

        $date = Jaws_Date::getInstance();
        $data = array();
        foreach ($comments as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];

            if($gadgetColumn) {
                $newRow['gadget']  = _t(strtoupper($row['gadget']).'_TITLE');
            }

            $newRow['name']    = $row['name'];

            if (!empty($editAction)) {
                $edit_url = str_replace('{id}', $row['id'], $editAction);
            }

            $newRow['created'] = $date->Format($row['createtime']);
            if ($row['status'] == Comments_Info::COMMENTS_STATUS_APPROVED) {
                $status = _t('COMMENTS_STATUS_APPROVED');
            } elseif ($row['status'] == Comments_Info::COMMENTS_STATUS_WAITING) {
                $status = _t('COMMENTS_STATUS_WAITING');
            } elseif ($row['status'] == Comments_Info::COMMENTS_STATUS_SPAM) {
                $status = _t('COMMENTS_STATUS_SPAM');
            } elseif ($row['status'] == Comments_Info::COMMENTS_STATUS_PRIVATE) {
                $status = _t('COMMENTS_STATUS_PRIVATE');
            }
            $newRow['status']  = $status;

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'), $edit_url, STOCK_EDIT);
            $actions= $link->Get().'&nbsp;';

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                        "javascript: commentDelete('".$row['id']."');",
                                        STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';
            $newRow['actions'] = $actions;

            $data[] = $newRow;
        }
        return $data;
    }

    /**
     * Builds and returns the UI
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   bool    $gadgetColumn   Display gadget column?
     * @return  string  UI XHTML
     */
    function Get($gadget, $gadgetColumn = false)
    {
        $cModel = $this->gadget->model->load('Comments');
        $total  = $cModel->GetCommentsCount($gadget, '', '', '', array(), false);

        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('comments_box');
        $gridBox->SetStyle('width: 100%;');

        //Datagrid
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('comments_datagrid');
        $grid->SetStyle('width: 100%;');
        $grid->TotalRows($total);
        $grid->useMultipleSelection();
        if($gadgetColumn) {
            $grid->AddColumn(Piwi::CreateWidget('Column', _t('COMMENTS_GADGETS')));
        }
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_CREATED')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('comments_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');

        $actions =& Piwi::CreateWidget('Combo', 'comments_actions');
        $actions->SetID('comments_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('COMMENTS_MARK_AS_APPROVED'), Comments_Info::COMMENTS_STATUS_APPROVED);
        $actions->AddOption(_t('COMMENTS_MARK_AS_WAITING'), Comments_Info::COMMENTS_STATUS_WAITING);
        $actions->AddOption(_t('COMMENTS_MARK_AS_SPAM'), Comments_Info::COMMENTS_STATUS_SPAM);
        $actions->AddOption(_t('COMMENTS_MARK_AS_PRIVATE'), Comments_Info::COMMENTS_STATUS_PRIVATE);

        $execute =& Piwi::CreateWidget('Button', 'executeCommentAction', '',
                                       STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript: commentDGAction(document.getElementById('comments_actions_combo'));");

        $gridFormBox->Add($actions);
        $gridFormBox->Add($execute);
        $gridForm->Add($gridFormBox);

        //Pack everything
        $gridBox->Add($grid);
        $gridBox->Add($gridForm);

        return $gridBox->Get();
    }

}