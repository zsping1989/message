<?php

/**
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/7/15
 * 时间: 17:01
 */
namespace Message;
use Message\Facades\Message as Msg;

trait Message{
    //用户-用户消息
    public function messages(){
        return $this->hasMany('Message\Models\Message');
    }

    /**
     * 当前用户发送消息给其他用户
     * @param $to
     * @param $subject
     * @param $content
     */
    public function sendMessage($tpl,$data=[]){
        //消息对象组装
        $info =  new MessageInfo($this->id,$data['user_id'],$tpl);
        $info->setSubject($data['subject']);
        $info->setContent($data['content']);
        Msg::send($info);



    }



}