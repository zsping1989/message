<?php
/**
 * Created by PhpStorm.
 * User: zhangshiping
 * Date: 2016/7/17
 * Time: 20:48
 */

namespace Message\Contracts;


interface MessageInfo
{
    public function setFrom($from);

    public function setTo($to);

    public function setContent($content);

    public function setSubject($subject);
    public function toArray();
}