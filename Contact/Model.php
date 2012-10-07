<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ContactModel extends Jaws_Model
{
    /**
     * Get information of a Contact
     *
     * @access  public
     * @param   int Contact ID
     * @return  array Array of Contact Information or Jaws_Error on failure
     */
    function GetContact($id)
    {
        $sql = '
            SELECT
                [id], [ip], [name], [email], [company], [url], [tel], [fax], [mobile], [address],
                [recipient], [subject], [msg_txt], [attachment], [createtime], [updatetime]
            FROM [[contacts]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get information of one Recipient
     *
     * @access  public
     * @param   string ID of the Recipient
     * @return  array  Array with the information of a Recipient or Jaws_Error on failure
     */
    function GetRecipient($id)
    {
        $sql = '
            SELECT
                [id], [name], [email], [tel], [fax], [mobile], [inform_type], [visible]
            FROM [[contacts_recipients]]
            WHERE [id] = {id}';

        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'), _t('CONTACT_NAME'));
    }

    /**
     * Get a list of the available Recipients
     *
     * @access  public
     * @param   boolean
     * @param   boolean
     * @param   boolean
     * @return  array Array of Recipients or Jaws_Error on failure
     */
    function GetRecipients($onlyVisible = false, $limit = false, $offset = null)
    {
        if (is_numeric($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        if ($onlyVisible) {
            $sql = '
                SELECT
                    [id], [name], [email], [tel], [fax], [mobile]
                FROM [[contacts_recipients]]
                WHERE [visible] = {visible}
                ORDER BY [id] ASC';
        } else {
            $sql = '
                SELECT
                    [id], [name], [email], [tel], [fax], [mobile], [visible]
                FROM [[contacts_recipients]]
                ORDER BY [id] ASC';
        }

        $params = array();
        $params['visible'] = 1;

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Sends email to user
     *
     * @access  public
     * @param   string  $name       Name
     * @param   string  $email      Email address
     * @param   string  $$company
     * @param   string  $url
     * @param   string  $tel
     * @param   string  $fax
     * @param   string  $mobile
     * @param   string  $address
     * @param   string  $rcipient   Rcipient ID
     * @param   string  $subject    Subject of message
     * @param   string  $attachment Attachment filename
     * @param   string  $message    Message content
     * @return  boolean Success/Failure
     */
    function InsertContact($name, $email, $company, $url, $tel, $fax, $mobile,
                           $address, $rcipient, $subject, $attachment, $message)
    {
        $sql = "
            INSERT INTO [[contacts]]
                ([user], [ip], [name], [email], [company], [url], [tel], [fax], [mobile], [address], [recipient],
                 [subject], [attachment], [msg_txt], [reply], [reply_sent], [createtime], [updatetime])
            VALUES
                ({user}, {ip}, {name}, {email}, {company}, {url}, {tel}, {fax}, {mobile}, {address}, {rcipient},
                 {subject}, {attachment}, {message}, {reply}, {reply_sent}, {now}, {now})";

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['user']       = $GLOBALS['app']->Session->GetAttribute('user');
        $params['ip']         = $_SERVER['REMOTE_ADDR'];
        $params['name']       = $xss->filter($name);
        $params['email']      = $xss->filter($email);
        $params['company']    = $xss->filter($company);
        $params['url']        = $xss->filter($url);
        $params['tel']        = $xss->filter($tel);
        $params['fax']        = $xss->filter($fax);
        $params['mobile']     = $xss->filter($mobile);
        $params['address']    = $xss->filter($address);
        $params['rcipient']   = (int)$rcipient;
        $params['subject']    = $xss->filter($subject);
        $params['attachment'] = $xss->filter($attachment);
        $params['message']    = $xss->filter($message);
        $params['reply']      = '';
        $params['reply_sent'] = 0;
        $params['now']      = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $GLOBALS['app']->Session->SetCookie('visitor_name',  $name,  60*24*150);
        $GLOBALS['app']->Session->SetCookie('visitor_email', $email, 60*24*150);
        $GLOBALS['app']->Session->SetCookie('visitor_url',   $url,   60*24*150);

        return true;
    }

}