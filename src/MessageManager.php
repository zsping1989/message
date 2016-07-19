<?php

/**
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/7/15
 * 时间: 17:02
 */
namespace Message;

use Carbon\Carbon;
use Message\Contracts\Message as Msg;
use Message\Contracts\MessageInfo as MsgInfo;
use Message\Models\Message as MessageModel;
use Message\Models\Msgtpl;

class MessageManager implements Msg{
    protected  $messageModel;
    public function __construct(){
        $this->messageModel = new MessageModel();
        $this->msgtplModel = new Msgtpl();
    }

    /**
     * 消息发送
     * @param MessageInfo $info
     * @return mixed
     */
    public function send(MsgInfo $info){
        return $this->messageModel->create($info->toArray());
    }

    /**
     * 读取一条消息,并标记为已读
     * @param $id
     * 返回: mixed
     */
    public function readOne($id){
        $row = $this->messageModel->find($id);
        $this->messageModel->where('id','=',$row->id)->update(['read'=>1]);
        return $row;
    }

    /**
     * 获取最新的一条消息
     * @param $user_id
     * @param null $msgtpl_id
     * 返回: mixed
     */
    public function getLastMessage($user_id,$msgtpl_id=null){


    }

    /**
     * 获取所有未读消息
     * @param $user_id
     * @param null $msgtpl_id
     * 返回: mixed
     */
    public function getAllNotRead($user_id,$msgtpl_id=null){

    }

    public function getAllNotReadLimit($user_id,$msgtpl,$limit=3){
        $result = $this->msgtplModel->where('name','=',$msgtpl)->first();
        $result->messages = $this->messageModel->where('user_id','=',$user_id)
            ->where('read','=',0)
            ->where('msgtpl_id','=',$result->id)
            ->orderBy('created_at','desc')
            ->paginate($limit);
        $result->messages->each(function($item){
            $item->format_time = Carbon::createFromFormat('Y-m-d H:i:s',$item->created_at)->diffForHumans();
        });
        return $result;
    }

}