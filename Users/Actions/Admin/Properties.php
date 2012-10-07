<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Properties extends UsersAdminHTML
{
    /**
     * Edit properties
     *
     * @access  public
     * @return  string HTML content
     */
    function Properties()
    {
        $this->CheckPermission('ManageProperties');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Properties.html');
        $tpl->SetBlock('Properties');

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Users'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveProperties'));

        $authmethod =& Piwi::CreateWidget('Combo', 'auth_method');
        $authmethod->SetTitle(_t('CONTROLPANEL_AUTH_METHOD'));
        foreach ($GLOBALS['app']->GetAuthMethods() as $method) {
            $authmethod->AddOption($method, $method);
        }
        $authmethod->SetDefault($GLOBALS['app']->Registry->Get('/config/auth_method'));
        $authmethod->SetEnabled($this->GetPermission('ManageAuthenticationMethod'));

        $anonRegister =& Piwi::CreateWidget('Combo', 'anon_register');
        $anonRegister->SetTitle(_t('USERS_PROPERTIES_ANON_REGISTER'));
        $anonRegister->AddOption(_t('GLOBAL_YES'), 'true');
        $anonRegister->AddOption(_t('GLOBAL_NO'), 'false');
        $anonRegister->SetDefault($GLOBALS['app']->Registry->Get('/config/anon_register'));

        $anonEmail =& Piwi::CreateWidget('Combo', 'anon_repetitive_email');
        $anonEmail->SetTitle(_t('USERS_PROPERTIES_ANON_REPETITIVE_EMAIL'));
        $anonEmail->AddOption(_t('GLOBAL_YES'), 'true');
        $anonEmail->AddOption(_t('GLOBAL_NO'), 'false');
        $anonEmail->SetDefault($GLOBALS['app']->Registry->Get('/config/anon_repetitive_email'));

        $anonactivate =& Piwi::CreateWidget('Combo', 'anon_activation');
        $anonactivate->SetTitle(_t('USERS_PROPERTIES_ANON_ACTIVATION'));
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_AUTO'), 'auto');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_BY_USER'), 'user');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_BY_ADMIN'), 'admin');
        $anonactivate->SetDefault($GLOBALS['app']->Registry->Get('/config/anon_activation'));

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();

        $anonGroup =& Piwi::CreateWidget('Combo', 'anon_group');
        $anonGroup->SetID('anon_group');
        $anonGroup->SetTitle(_t('USERS_PROPERTIES_ANON_GROUP'));
        $anonGroup->AddOption(_t('USERS_GROUPS_NOGROUP'), 0);
        $groups = $userModel->GetGroups(null, 'title');
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $anonGroup->AddOption($group['title'], $group['id']);
            }
        }
        $anonGroup->SetDefault($GLOBALS['app']->Registry->Get('/config/anon_group'));

        $passRecovery =& Piwi::CreateWidget('Combo', 'password_recovery');
        $passRecovery->SetTitle(_t('USERS_PROPERTIES_PASS_RECOVERY'));
        $passRecovery->AddOption(_t('GLOBAL_YES'), 'true');
        $passRecovery->AddOption(_t('GLOBAL_NO'), 'false');
        $passRecovery->SetDefault($GLOBALS['app']->Registry->Get('/gadgets/Users/password_recovery'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet('');
        $fieldset->SetTitle('vertical');

        $fieldset->Add($authmethod);
        $fieldset->Add($anonRegister);
        $fieldset->Add($anonEmail);
        $fieldset->Add($anonactivate);
        $fieldset->Add($anonGroup);
        $fieldset->Add($passRecovery);

        $form->Add($fieldset);

        $buttons =& Piwi::CreateWidget('HBox');
        $buttons->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: saveSettings();');

        $buttons->Add($save);
        $form->Add($buttons);

        $tpl->SetVariable('form', $form->Get());
        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $tpl->ParseBlock('Properties');

        return $tpl->Get();
    }

}