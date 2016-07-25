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
        if(!is_array($msgtpl)){
            $msgtpl = [$msgtpl];
        }
        $msgtpls = $this->msgtplModel->whereIn('name',$msgtpl)->get();
        $msgtplModel = $this->msgtplModel->where('id','<',0);
        //获取所有子节点
        foreach($msgtpls as $item){
            $msgtplModel = $msgtplModel->orWhere(function($query) use ($item){
                $query->where('left_margin','>=',$item->left_margin)
                    ->where('right_margin','<=',$item->right_margin);
            });
        }
        $msgtpls = $msgtplModel->get();
        $msgtplsArr = collect($msgtpls->toArray())->keyBy('id');

        //获取未读消息
        $messages = collect($this->messageModel->where('user_id','=',$user_id)
            ->where('read','=',0)
            ->whereIn('msgtpl_id',$msgtplsArr->pluck('id'))
            ->orderBy('created_at','desc')
            ->get()
            ->toArray())
            ->map(function($item){
                $item['format_time'] = Carbon::createFromFormat('Y-m-d H:i:s',$item['created_at'])->diffForHumans();
                return $item;
            });

        $msgtplsArr = $msgtplsArr->toArray();
        $result = [];
        foreach($msgtpls as $row){
            if(in_array($row->name,$msgtpl)){
                $row_messages = $messages->filter(function($item) use ($row,$msgtplsArr){
                    return $msgtplsArr[$item['msgtpl_id']]['left_margin']>=$row->left_margin &&
                    $msgtplsArr[$item['msgtpl_id']]['right_margin']<=$row->right_margin;
                });
                $row->msg_count = $row_messages->count();
                $row->messages  = $row_messages->splice(0,$limit)->toArray();
                $result[] = $row;
            }
        }
        return collect($result);
    }

    /**
     * 统计分类组的未读消息条数
     * param $msgtpl
     * 返回: array
     */
    public function getCountNotReadByMsgtpl($user_id,$msgtpl){
        if(!is_array($msgtpl)){
            $msgtpl = [$msgtpl];
        }

        return array();
    }

}