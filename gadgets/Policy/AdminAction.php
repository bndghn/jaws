<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_AdminAction extends Jaws_Gadget_Action
{
    /**
     * Calls default admin action (IPBlocking)
     *
     * @access  public
     * @return  string  Template content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('IPBlocking')) {
            $gadgetHTML = $this->gadget->loadAdminAction('IP');
            return $gadgetHTML->IPBlocking();
        } elseif ($this->gadget->GetPermission('AgentBlocking')) {
            $gadgetHTML = $this->gadget->loadAdminAction('Agent');
            return $gadgetHTML->AgentBlocking();
        } elseif ($this->gadget->GetPermission('Encryption')) {
            $gadgetHTML = $this->gadget->loadAdminAction('Encryption');
            return $gadgetHTML->Encryption();
        } elseif ($this->gadget->GetPermission('AntiSpam')) {
            $gadgetHTML = $this->gadget->loadAdminAction('AntiSpam');
            return $gadgetHTML->AntiSpam();
        }

        $this->gadget->CheckPermission('AdvancedPolicies');
        $gadgetHTML = $this->gadget->loadAdminAction('AdvancedPolicies');
        return $gadgetHTML->AdvancedPolicies();
    }

    /**
     * Display the sidebar
     *
     * @access  public
     * @param   string  $action Selected Action
     * @return  XHTML template content
     */
    function SideBar($action)
    {
        $actions = array('IPBlocking', 'AgentBlocking', 'Encryption', 'AntiSpam',
                         'AdvancedPolicies');
        if (!in_array($action, $actions)) {
            $action = 'IPBlocking';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Sidebar.php';
        $sidebar = new Jaws_Widgets_Sidebar('policy');

        if ($this->gadget->GetPermission('IPBlocking')) {
            $sidebar->AddOption('IPBlocking', _t('POLICY_IP_BLOCKING'), 
                                BASE_SCRIPT . '?gadget=Policy&amp;action=IPBlocking',
                                'images/stock/stop.png');
        }
        if ($this->gadget->GetPermission('AgentBlocking')) {
            $sidebar->AddOption('AgentBlocking', _t('POLICY_AGENT_BLOCKING'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=AgentBlocking',
                                'images/stock/stop.png');
        }
        if ($this->gadget->GetPermission('Encryption')) {
            $sidebar->AddOption('Encryption', _t('POLICY_ENCRYPTION'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=Encryption',
                                'gadgets/Policy/Resources/images/encryption.png');
        }
        if ($this->gadget->GetPermission('AntiSpam')) {
            $sidebar->AddOption('AntiSpam', _t('POLICY_ANTISPAM'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=AntiSpam',
                                'gadgets/Policy/Resources/images/antispam.png');
        }
        if ($this->gadget->GetPermission('AdvancedPolicies')) {
            $sidebar->AddOption('AdvancedPolicies', _t('POLICY_ADVANCED_POLICIES'),
                                BASE_SCRIPT . '?gadget=Policy&amp;action=AdvancedPolicies',
                                'gadgets/Policy/Resources/images/policies.png');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }
}