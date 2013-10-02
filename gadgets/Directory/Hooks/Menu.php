<?php
/**
 * Directory - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url' => $GLOBALS['app']->Map->GetURLFor('Directory', 'Directory'),
                        'title' => _t('DIRECTORY_NAME'));
        return $urls;
    }
}