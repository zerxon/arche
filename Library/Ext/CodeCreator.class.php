<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-23
 */

class CodeCreator {

    public static function produce() {
        return substr(uniqid('', true), 15).substr(microtime(), 2, 4);
    }
}