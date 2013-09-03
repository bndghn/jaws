<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Model_Message extends Jaws_Gadget_Model
{
    /**
     * Get a message Info
     *
     * @access  public
     * @param   integer  $id   Message id
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetMessage($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.insert_time',
            'users.nickname as from_nickname', 'users.username as from_username', 'users.avatar', 'users.email',
            'pm_recipients.status:integer', 'from:integer'
        );
        $table->join('users', 'pm_messages.from', 'users.id');
        $table->join('pm_recipients', 'pm_messages.id', 'pm_recipients.message_id');
        $table->where('pm_messages.id', $id);

        $result = $table->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $result['attachments'] = $this->GetMessageAttachments($id);
        return $result;
    }

    /**
     * Get a message attachments Info
     *
     * @access  public
     * @param   integer  $id   Message id
     * @return  array    Array of message attachments or Empty Array
     */
    function GetMessageAttachments($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
        $table->select('id:integer', 'host_filename', 'user_filename', 'file_size', 'hints_count:integer');
        $result = $table->where('message_id', $id)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }
        return $result;
    }

    /**
     * Get a message attachments Info
     *
     * @access  public
     * @param   integer  $id   Attachment id
     * @return  array    Array of message attachments or Empty Array
     */
    function GetMessageAttachment($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
        $table->select('message_id:integer', 'host_filename', 'user_filename', 'file_size', 'hints_count:integer');
        $result = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return array();
        }
        return $result;
    }

    /**
     * Increment attachment download hits
     *
     * @access  public
     * @param   integer  $id   Attachment id
     * @return  mixed   True or Jaws_Error
     */
    function HitAttachmentDownload($id)
    {
        $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
        $res = $table->update(
            array(
                'hints_count' => $table->expr('hints_count + ?', 1)
            )
        )->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

    /**
     * Get a message
     *
     * @access  public
     * @param   integer  $id     Message id
     * @param   integer  $user   User id
     * @return  mixed    True or Jaws_Error on failure
     */
    function DeleteMessage($id, $user)
    {
        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $result = $table->delete()->where('message_id', $id)->and()->where('recipient', $user)->exec();
        return $result;
    }

    /**
     * Mark messages status
     *
     * @access  public
     * @param   array    $ids      Message id(s)
     * @param   integer  $status   New message status
     * @param   integer  $user     User id
     * @return  bool    True or False
     */
    function MarkMessages($ids, $status, $user)
    {
        if(!is_array($ids) && is_numeric($ids)) {
            $ids = array($ids);
        }

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $table->update(array('status' => $status))->where('message_id', $ids, 'in')->and()->where('recipient', $user);
        $res = $table->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return true;
    }

    /**
     * Reply message
     *
     * @access  public
     * @param   integer  $id     Message id
     * @param   integer  $user   User id
     * @param   string   $reply  Reply message
     * @return  mixed    True or Jaws_Error on failure
     */
    function ReplyMessage($id, $user, $reply)
    {
        $message = $this->GetMessage($id);

        $table = Jaws_ORM::getInstance()->table('pm_messages');
        //Start Transaction
        $table->beginTransaction();

        $data = array();
        $data['parent_id']   = $id;
        $data['from']        = $user;
        $data['subject']     = _t('PRIVATEMESSAGE_REPLY_ON', $message['subject']);
        $data['body']        = $reply;
        $data['insert_time'] = time();
        $message_id = $table->insert($data)->exec();

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
        $data = array();
        $data['message_id'] = $message_id;
        $data['recipient'] = $message['from'];

        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        //Commit Transaction
        $table->commit();
        return true;
    }


    /**
     * Send message
     *
     * @access  public
     * @param   integer $user           User id
     * @param   array   $message        Message data
     * @param   array   $attachments    File attachments data
     * @return  mixed    True or Jaws_Error on failure
     */
    function SendMessage($user, $message, $attachments)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        //Start Transaction
        $table->beginTransaction();

        $data = array();
        $data['from']        = $user;
        $data['subject']     = $message['subject'];
        $data['body']        = $message['body'];
        $data['insert_time'] = time();
        $message_id = $table->insert($data)->exec();
        if (Jaws_Error::IsError($message_id)) {
            return false;
        }

        if (!empty($attachments) && count($attachments) > 0) {
            $table = Jaws_ORM::getInstance()->table('pm_message_attachments');
//            $aData = array();
//            foreach ($attachments as $attachment) {
//                $attachment['message_id'] = $message_id;
//                $aData[] = $attachment;
//            }
//            $res = $table->insertAll($aData)->exec();
            foreach ($attachments as $attachment) {
                $aData = array();
                $aData = $attachment;
                $aData['message_id'] = $message_id;
                $res = $table->insert($aData)->exec();
                if (Jaws_Error::IsError($res)) {
                    return false;
                }
            }

        }

        $recipient_users = explode(",", $message['recipient_users']);
        if (!empty($recipient_groups)) {
            $recipient_groups = explode(",", $message['recipient_groups']);
            $table = Jaws_ORM::getInstance()->table('users_groups');
            $group_users = $table->select('user_id')->where('group_id', $recipient_groups, 'in')->fetchCol();
            $recipient_users = array_merge($recipient_users, $group_users);
        }

        $table = Jaws_ORM::getInstance()->table('pm_recipients');
//        $data = array();
//        foreach($recipient_users as $recipient_user) {
//            $data[] = array(
//                'message_id' =>$message_id,
//                'recipient' =>$recipient_user,
//            );
//        }
//        $res = $table->insertAll($data)->exec();

        foreach ($recipient_users as $recipient_user) {
            $data = array(
                'message_id' => $message_id,
                'recipient' => $recipient_user,
            );
            $res = $table->insert($data)->exec();
            if (Jaws_Error::IsError($res)) {
                return false;
            }
        }

        //Commit Transaction
        $table->commit();
        return true;
    }

 }