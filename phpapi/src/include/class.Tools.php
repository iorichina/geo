<?php
class Tools {
    /**
     * @desc 使用smtp发送邮件
     * @param string/array $to 收件人，可以是字符串/数组，如果是字符串同时是多个收件人，则收件人地址之间使用英文分号（;）分隔
     * @param string $subject 邮件标题
     * @param string $body 邮件正文
     * @param array $conf 
     * array( <br />
     * 'port' => 邮箱服务器端口，默认25, <br />
     * 'host'=>'邮箱服务器地址，默认smtp.163.com', <br />
     * 'username'=>'邮箱地址，默认 iorichina56@163.com', <br />
     * 'password'=>'密码，默认 iorichina56@163.com的密码', <br />
     * 'fromname'=>显示名称 ，默认是"56.com首页告警系统"<br />
     * )
     */
    public static function sendEmail($to, $subject, $body, $conf = array()) {
        try {
            $mail = new PHPMailer ( true ); //New instance, with exceptions enabled
            

            $mail->CharSet = 'utf-8';
            
            //$mail->IsSMTP (); // tell the class to use SMTP
            $mail->Mailer = 'smtp'; //$mailer
            

            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->Port = $conf ['port'] ? $conf ['port'] : 25; // set the SMTP server port
            $mail->Host = $conf ['host'] ? $conf ['host'] : "smtp.163.com"; // SMTP server
            $mail->Username = $conf ['username'] ? $conf ['username'] : "iorichina56@163.com"; // SMTP server username
            $mail->Password = $conf ['password'] ? $conf ['password'] : "0.123456"; // SMTP server password
            

            //$mail->IsSendmail (); // tell the class to use Sendmail
            

            //$mail->AddReplyTo ( "name@domain.com", "First Last" );
            

            $mail->From = $conf ['username'] ? $conf ['username'] : "iorichina56@163.com";
            $mail->FromName = Tools::convertToUtf8 ( $conf ['fromname'] ? $conf ['fromname'] : "56.com首页告警系统" );
            
            if (is_array ( $to )) {
                foreach ( $to as $addr ) {
                    $addr = trim ( $addr );
                    $mail->AddAddress ( $addr );
                }
            } elseif (strpos ( $to, ';' )) {
                preg_match_all ( '/[^;]+/i', $to, $matches, PREG_SET_ORDER );
                if (is_array ( $matches )) {
                    foreach ( $matches as $addr ) {
                        $addr = trim ( $addr );
                        $mail->AddAddress ( $addr );
                    }
                }
            } elseif (! empty ( $to )) {
                $mail->AddAddress ( $to );
            } else {
                return false;
            }
            
            //$body = preg_replace ( '/\\\\/', '', $body ); //Strip backslashes
            //$body = Tools::convertToUtf8 ( $body );
            $subject = Tools::convertToUtf8 ( $subject );
            
            $mail->Subject = $subject;
            
            $mail->AltBody = "56.com"; // optional, comment out and test
            $mail->WordWrap = $conf ['wordwrap'] ? $conf ['wordwrap'] : 80; // set word wrap
            

            if (! isset ( $conf ['html'] ) || $conf ['html']) {
                $mail->MsgHTML ( $body );
            
     //$mail->Body = $body;
            } else {
                $body = htmlspecialchars ( $body );
                $mail->Body = $body;
            }
            
            $return = $mail->Send ();
            return $return;
        } catch ( phpmailerException $e ) {
            return $e->errorMessage ();
        }
    }
    /**
     * 转换为json字符串
     * @param mixed $obj 可以为对象、数组、字符串
     * @return json string 
     */
    public static function jsonEncode($obj) {
        return json_encode ( self::convertToUtf8 ( $obj ) );
    }
    /**
     * 将json字符串转换成数组
     * @param string $obj json string 
     * @param $charset string 转换对应的字符串格式
     */
    public static function jsonDecode($obj, $charset = 'utf-8') {
        $data = json_decode ( $obj, true );
        switch (strtolower ( $charset )) {
            case 'gbk' :
                $data = self::convertToGbk ( $data );
                break;
        }
        return $data;
    }
    /**
     * Returns true if $string is valid UTF-8 and false otherwise.
     * @param string $string
     */
    public static function is_utf8($string) {
        if (! is_string ( $string )) {
            return false;
        }
        return preg_match ( '%^(?:
		[\x09\x0A\x0D\x20-\x7E] # ASCII
		| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
		| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
		| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
		)*$%xs', $string );
    
    }
    /**
     * 将数组或字符串或StrClass对象内容转换为utf-8字符集格式
     * @param mixed $_data
     * @return mixed $_data in utf8 
     */
    public static function convertToUtf8($_data) {
        if (is_string ( $_data )) {
            if (! self::is_utf8 ( $_data )) {
                if (function_exists ( 'mb_convert_encoding' )) {
                    $_data = mb_convert_encoding ( $_data, 'UTF-8', 'GBK' );
                } else {
                    $_data = iconv ( 'gbk', 'utf-8', $_data );
                }
            }
        }
        if (is_object ( $_data )) {
            $_data_as_array = ( array ) $_data;
            $_data_as_array = self::convertToUtf8 ( $_data_as_array );
            foreach ( $_data_as_array as $k => $v ) {
                $_data->$k = $v;
            }
        }
        if (is_array ( $_data )) {
            foreach ( $_data as $k => $v ) {
                $_data [$k] = self::convertToUtf8 ( $v );
            }
        }
        return $_data;
    }
    
    /**
     * 转换数组或者字符串为GBK格式的并返回
     * @param mixed $_data
     */
    public static function convertToGbk($_data) {
        if (is_string ( $_data )) {
            if (self::is_utf8 ( $_data )) {
                if (function_exists ( 'mb_convert_encoding' )) {
                    $_data = mb_convert_encoding ( $_data, 'GBK', 'UTF-8' );
                } else {
                    $_data = iconv ( 'utf-8', 'gbk', $_data );
                }
            }
        }
        if (is_object ( $_data )) {
            $_data_as_array = ( array ) $_data;
            $_data_as_array = self::convertToGbk ( $_data_as_array );
            foreach ( $_data_as_array as $k => $v ) {
                $_data->$k = $v;
            }
        }
        if (is_array ( $_data ))
            foreach ( $_data as $k => $v ) {
                $_data [$k] = self::convertToGbk ( $v );
            }
        return $_data;
    }
}