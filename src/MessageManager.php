<?php

/**
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/7/15
 * 时间: 17:02
 */
namespace Message;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Message\Contracts\Message as Msg;
use Message\Contracts\MessageInfo as MsgInfo;
use Message\Models\Message as MessageModel;
use Message\Models\Msgtpl;

class MessageManager implements Msg
{
    protected $messageModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->msgtplModel = new Msgtpl();
    }

    /**
     * 消息发送
     * param MessageInfo $info
     * return mixed
     */
    public function send(MsgInfo $info)
    {
        return $this->messageModel->create($info->toArray());
    }

    /**
     * 读取一条消息,并标记为已读
     * @param $id
     * 返回: mixed
     */
    public function readOne($id)
    {
        $row = $this->messageModel->find($id);
        $this->messageModel->where('id', '=', $row->id)->where('read','=',0)->update(['read' => 1]);
        return $row;
    }

    /**
     * 获取最新的一条消息
     * @param $user_id
     * @param null $msgtpl_id
     * 返回: mixed
     */
    public function getLastMessage($user_id, $msgtpl_id = null)
    {


    }

    /**
     * 获取所有未读消息
     * param $user_id
     * param null $msgtpl_id
     * 返回: mixed
     */
    public function getAllNotRead($user_id, $msgtpl = null)
    {
        $msgtpls = $msgtpl ? $this->getMsgtplGroup($msgtpl) : collect([]);
        $msgtplsArr = collect($msgtpls->toArray())->pluck('id');
        return $this->selectNotRead($user_id, $msgtpl, $msgtplsArr);
    }

    /**
     * 查询未读消息
     * param $user_id
     * param $msgtpl
     * param array $msgtplsArr
     * 返回: mixed
     */
    public function selectNotRead($user_id, $msgtpl, $msgtplsArr = [])
    {
        if ($msgtpl) {
            $obj = $this->messageModel->whereIn('msgtpl_id', $msgtplsArr);
        }
        return $obj->where('user_id', '=', $user_id)
            ->where('read', '=', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($item) {
                $item->format_time = Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->diffForHumans();
                return $item;
            });
    }


    public function getAllNotReadLimit($user_id, $msgtpl, $limit = 3)
    {
        if (!is_array($msgtpl)) {
            $msgtpl = [$msgtpl];
        }
        $msgtpls = $this->getMsgtplGroup($msgtpl);
        $msgtplsArr = collect($msgtpls->toArray())->keyBy('id');

        //获取未读消息
        $messages = collect($this->selectNotRead($user_id, true, collect($msgtpls->toArray())->pluck('id'))->toArray());
        $msgtplsArr = $msgtplsArr->toArray();
        $result = [];
        foreach ($msgtpls as $row) {
            if (in_array($row->name, $msgtpl)) {
                $row_messages = $messages->filter(function ($item) use ($row, $msgtplsArr) {
                    return $msgtplsArr[$item['msgtpl_id']]['left_margin'] >= $row->left_margin &&
                    $msgtplsArr[$item['msgtpl_id']]['right_margin'] <= $row->right_margin;
                });
                $row->msg_count = $row_messages->count();
                $row->messages = $row_messages->splice(0, $limit)->toArray();
                $result[] = $row;
            }
        }
        return collect($result);
    }

    /**
     * 获取需查询的模板组
     */
    public function getMsgtplGroup($msgtpl)
    {
        if (!is_array($msgtpl)) {
            $msgtpl = [$msgtpl];
        }
        $msgtpls = $this->msgtplModel->whereIn('name', $msgtpl)->get();
        $msgtplModel = $this->msgtplModel->where('id', '<', 0);
        //获取所有子节点
        foreach ($msgtpls as $item) {
            $msgtplModel = $msgtplModel->orWhere(function ($query) use ($item) {
                $query->where('left_margin', '>=', $item->left_margin)
                    ->where('right_margin', '<=', $item->right_margin);
            });
        }
        return $msgtplModel->orderBy('left_margin')->get();
    }

    /**
     * 统计分类组的未读消息条数
     * param $msgtpl
     * 返回: array
     */
    public function getCountNotReadByMsgtpl($user_id, $msgtpl = null)
    {
        $msgtpls = $this->getMsgtplGroup($msgtpl);
        if (!is_array($msgtpl)) {
            $msgtpl = [$msgtpl];
        }
        $msgtplsArr = collect($msgtpls->toArray())->keyBy('id');

        //统计未读消息
        $msgtplCount = $this->messageModel
            ->select(DB::raw('msgtpl_id,count(*) as msg_count'))
            ->whereIn('msgtpl_id', $msgtplsArr->pluck('id'))
            ->where('user_id','=',$user_id)
            ->where('read','=',0)
            ->groupBy('msgtpl_id')
            ->get()->toArray();
        return $msgtpls->filter(function($item) use ($msgtpl){
            return in_array($item->name, $msgtpl);
        })->each(function($item) use ($msgtplCount,$msgtplsArr){
            $count = 0;
            foreach($msgtplCount as $row){
                if($item->left_margin<=$msgtplsArr[$row['msgtpl_id']]['left_margin'] && $item->right_margin>=$msgtplsArr[$row['msgtpl_id']]['right_margin']){
                    $count += $row['msg_count'];
                }
            }
            $item->childs = $item->childs(1)->implode('id',',');
            $item->msg_count = $count;
        });

    }

    /**
     * 修改成已读状态
     * @param $ids
     * 返回: mixed
     */
    public function updateReadByIds($ids){
        return $this->messageModel->whereIn('id',$ids)->where('read','=',0)->update(['read' => 1]);
    }

}