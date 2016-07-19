<?php
/**
 * Created by PhpStorm.
 * User: zhangshiping
 * Date: 2016/7/17
 * Time: 20:15
 */

namespace Message;

use Message\Models\Msgtpl;

class MessageInfo implements \Message\Contracts\MessageInfo{
    /**
     * 消息主题
     * @var string
     */
    public $subject = '';


    /**
     * 消息内容
     * @var string
     */
    public $content = '';


    /**
     * 消息来自用户ID
     * @var int
     */
    public $from = 0;


    /**
     * 消息到达用户ID
     * @var int
     */
    public $to = 0;

    public $url = '';

    protected $tpl;

    /**
     * MessageInfo constructor.
     * @param $from
     * @param $to
     */
    public function __construct($from,$to,$tpl){
        $this->from = $from;
        $this->to = $to;
        $this->tpl = $tpl;
    }

    public function setFrom($from){
        $this->from = $from;
    }

    public function setTo($to){
        $this->to = $to;
    }

    public function setContent($content){
        $this->content = $content;
    }

    public function setSubject($subject){
        $this->subject = $subject;
    }

    public function setUrl($url){
        $this->url = $url;
    }

    public function toArray(){
        $tpl = $this->tpl ? Msgtpl::where('name','=',$this->tpl)->first() : Msgtpl::find(1);
        $subject = $this->subject ? $this->subject : $tpl->title;
        return [
            'user_id'=>$this->to,
            'from_id'=>$this->from,
            'msgtpl_id'=> $tpl->id ? $tpl->id : 0,
            'subject'=>$subject,
            'url'=>$this->url,
            'content'=>$this->content
        ];
    }
}