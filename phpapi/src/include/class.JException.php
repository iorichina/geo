<?php
/**
 * @name:	class.JException.php
 *  	TODO
 * @author:	zhys9(jingki) @ 2008-7-27
 *
 */
class JException extends Exception {
    
    public function JException($message, $code = 0) {
        parent::__construct ( $message, $code );
    }
    
    /**
     * @todo 输出错误的详细信息
     * @param int $exit 输出一条错误信息后是否退出程序
     * @return void
     */
    public function ShowErrorMessage($exit = 0) {
        // 		printf("Code: %d , Message: %s , In file: %s, On line: %d <br>\n",
        // 				$this->getCode(),
        // 				$this->getMessage(),
        // 				$this->getFile(),
        // 				$this->getLine()
        // 			);
        // 		printf("0|%d|%s|",
        // 				$this->getCode(),
        // 				$this->getMessage()
        // 			);
        $out = array (0, $this->getCode (), $this->getMessage () );
        $exit && (print json_encode ( $out ));
    }

}
