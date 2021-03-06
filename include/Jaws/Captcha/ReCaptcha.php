<?php
/**
 * reCaptcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_ReCaptcha extends Jaws_Captcha
{
    /**
     * Install captcha driver
     *
     * @access  public
     */
    function install()
    {
        if (is_null($GLOBALS['app']->Registry->fetch('reCAPTCHA_public_key', 'Policy'))) {
            $GLOBALS['app']->Registry->insert('reCAPTCHA_public_key', '', false, 'Policy');
            $GLOBALS['app']->Registry->insert('reCAPTCHA_private_key', '', false, 'Policy');
        }

        return true;
    }

    /**
     * Returns an array with the captcha text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the text entry) and entry (the input)
     */
    function get()
    {
        $res = array();
        $objReCaptcha = new LibReCaptcha();
        $publickey = $GLOBALS['app']->Registry->fetch('reCAPTCHA_public_key', 'Policy');
        $reCAPTCHA = $objReCaptcha->recaptcha_get_html($publickey);

        $res = array();
        $res['key']   = 0;
        $res['text']  = $reCAPTCHA;
        $res['label'] = _t($this->_label);
        $res['title'] = _t($this->_label);
        $res['description'] = _t($this->_description);
        return $res;
    }

    /**
     * Check if a captcha key is valid
     *
     * @access  public
     * @param   bool     Valid/Not Valid
     */
    function check()
    {
        $recaptcha = jaws()->request->fetch(array('recaptcha_challenge_field', 'recaptcha_response_field'), 'post');
        if ($recaptcha['recaptcha_response_field']) {
            $privatekey = $GLOBALS['app']->Registry->fetch('reCAPTCHA_private_key', 'Policy');
            $objReCaptcha = new LibReCaptcha();
            $objReCaptcha->recaptcha_check_answer(
                $privatekey,
                $_SERVER["REMOTE_ADDR"],
                $recaptcha['recaptcha_challenge_field'],
                $recaptcha['recaptcha_response_field']
            );

            return $objReCaptcha->is_valid;
        }

        return false;
    }

}

/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class LibReCaptcha
{
    const RECAPTCHA_API_SERVER        = "http://www.google.com/recaptcha/api";
    const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
    const RECAPTCHA_VERIFY_SERVER     = "www.google.com";

    var $is_valid;
    var $error;

    /**
     * Encodes the given data into a query string format
     * @param $data - array of string elements to be encoded
     * @return  string - encoded request
     */
    function _recaptcha_qsencode($data)
    {
        $req = "";
        foreach ($data as $key => $value) {
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     * @param   string $host
     * @param   string $path
     * @param   array $data
     * @param   int port
     * @return  array response
     */
    function _recaptcha_http_post($host, $path, $data, $port = 80)
    {
        $req = $this->_recaptcha_qsencode($data);

        $http_request = "POST $path HTTP/1.0\r\n";
        $http_request.= "Host: $host\r\n";
        $http_request.= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request.= "Content-Length: " . strlen($req) . "\r\n";
        $http_request.= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request.= "\r\n";
        $http_request.= $req;

        $response = '';
        if (false == ($fs = @fsockopen($host, $port, $errno, $errstr, 10))) {
            die ('Could not open socket');
        }

        fwrite($fs, $http_request);
        while (!feof($fs)) {
            $response.= fgets($fs, 1160); // One TCP-IP packet
        }
        fclose($fs);

        $response = explode("\r\n\r\n", $response, 2);
        return $response;
    }

    /**
     * Gets the challenge HTML (javascript and non-javascript version).
     * This is called from the browser, and the resulting reCAPTCHA HTML widget
     * is embedded within the HTML form it was called from.
     * @param   string $pubkey A public key for reCAPTCHA
     * @param   string $error The error given by reCAPTCHA (optional, default is null)
     * @param   bool    $use_ssl Should the request be made over ssl? (optional, default is false)
     * @return  string - The HTML to be embedded in the user's form.
     */
    function recaptcha_get_html($pubkey, $error = null, $use_ssl = false)
    {
        if ($pubkey == null || $pubkey == '') {
            return "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>";
        }

        $server = $use_ssl? self::RECAPTCHA_API_SECURE_SERVER : self::RECAPTCHA_API_SERVER;
        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }

        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
        <noscript>
              <iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br>
              <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
              <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
        </noscript>';
    }

    /**
      * Calls an HTTP POST function to verify if the user's guess was correct
      * @param  string  $privkey
      * @param  string  $remoteip
      * @param  string  $challenge
      * @param  string  $response
      * @param  array   $extra_params an array of extra variables to post to the server
      * @return void
      */
    function recaptcha_check_answer($privkey, $remoteip, $challenge, $response, $extra_params = array())
    {
        if ($privkey == null || $privkey == '') {
            $this->is_valid = false;
            $this->error = "To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>";
            return;
        }

        if ($remoteip == null || $remoteip == '') {
            $this->is_valid = false;
            $this->error = 'For security reasons, you must pass the remote ip to reCAPTCHA';
            return;
        }

        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
            $this->is_valid = false;
            $this->error = 'incorrect-captcha-sol';
            return;
        }

        $response = $this->_recaptcha_http_post(
            self::RECAPTCHA_VERIFY_SERVER,
            "/recaptcha/api/verify",
            array(
                'privatekey' => $privkey,
                'remoteip' => $remoteip,
                'challenge' => $challenge,
                'response' => $response
            ) + $extra_params
        );

        $answers = explode("\n", $response [1]);
        if (trim($answers [0]) == 'true') {
            $this->is_valid = true;
        } else {
            $this->is_valid = false;
            $this->error = $answers [1];
        }

        return;
    }

    /**
     * gets a URL where the user can sign up for reCAPTCHA. If your application
     * has a configuration page where you enter a key, you should provide a link
     * using this function.
     * @param   string $domain The domain where the page is hosted
     * @param   string $appname The name of your application
     */
    function recaptcha_get_signup_url($domain = null, $appname = null)
    {
        return "https://www.google.com/recaptcha/admin/create?".
               $this->_recaptcha_qsencode(array('domains' => $domain, 'app' => $appname));
    }

    function _recaptcha_aes_pad($val)
    {
        $block_size = 16;
        $numpad = $block_size - (strlen($val) % $block_size);
        return str_pad($val, strlen($val) + $numpad, chr($numpad));
    }

    /* Mailhide related code */
    function _recaptcha_aes_encrypt($val,$ky)
    {
        if (! function_exists ("mcrypt_encrypt")) {
            die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
        }
        $mode=MCRYPT_MODE_CBC;   
        $enc=MCRYPT_RIJNDAEL_128;
        $val=$this->_recaptcha_aes_pad($val);
        return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
    }


    function _recaptcha_mailhide_urlbase64($x)
    {
        return strtr(base64_encode($x), '+/', '-_');
    }

    /* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
    function recaptcha_mailhide_url($pubkey, $privkey, $email)
    {
        if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
            die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
                 "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>");
        }

        $ky = pack('H*', $privkey);
        $cryptmail = $this->_recaptcha_aes_encrypt($email, $ky);
        return "http://www.google.com/recaptcha/mailhide/d?k=" . $pubkey . "&c=" . $this->_recaptcha_mailhide_urlbase64($cryptmail);
    }

    /**
     * gets the parts of the email to expose to the user.
     * eg, given johndoe@example,com return ["john", "example.com"].
     * the email is then displayed as john...@example.com
     */
    function _recaptcha_mailhide_email_parts($email)
    {
        $arr = preg_split("/@/", $email);

        if (strlen($arr[0]) <= 4) {
            $arr[0] = substr($arr[0], 0, 1);
        } else if (strlen($arr[0]) <= 6) {
            $arr[0] = substr($arr[0], 0, 3);
        } else {
            $arr[0] = substr($arr[0], 0, 4);
        }
        return $arr;
    }

    /**
     * Gets html to display an email address given a public an private key.
     * to get a key, go to:
     *
     * http://www.google.com/recaptcha/mailhide/apikey
     */
    function recaptcha_mailhide_html($pubkey, $privkey, $email)
    {
        $emailparts = $this->_recaptcha_mailhide_email_parts($email);
        $url = $this->recaptcha_mailhide_url($pubkey, $privkey, $email);
        
        return htmlentities($emailparts[0]) . "<a href='" . htmlentities($url) .
            "' onclick=\"window.open('" . htmlentities($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities($emailparts [1]);
    }

}