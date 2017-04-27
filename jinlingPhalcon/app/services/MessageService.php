<?php

/**
 * 短信服务公共类
 * author zhuangzhiwei
 */
namespace MyApp\Services;

use MyApp\Library\STD3Des;
use MyApp\Library\XHttp;
use MyApp\Models\Users;
use MyApp\Library\Utilities;
use MyApp\Services\BaseService;

class MessageService extends BaseService
{

    /**
     * @param int $type 短信类型
     * @param $userPhone 接收短信用户
     * @param array $data 短信数据
     * $data 格式：
     * ["hotelName"=>"酒店","userName"=>"zzw","inTime"=>"7月26日至27日","roomStyleName"=>"大床房","roomsNumber"=>2,"nightNumber"=>2,
     * "orderNum"=>"订单号","orderAmount"=>"退款金额/订单金额","hotelAddress"=>"广州市","weixinName"=>"微信公众号名称","hotelPhone"=>"12587496532"]
     * 字段名需对应，不同类型传对应需要的字段值
     * @param string $templateCode 短信模板，默认为DVB_ORDER，订单相关
     * @return bool
     */
    public function sendMessage($type=4,$userPhone,$data=[],$orderNo,$hotelId,$templateCode="DVB_ORDER"){
        $hotelId = $hotelId ? $hotelId:0;
        $message = $this->db->get("dvb_send_message", ["createTime", "msgCode"], ["AND" => ["telphone" => $userPhone, "msgCode" => $orderNo, "sendType" => $type, "sendStatus" => 1, "isActive" => 0]]);
        if(!$message){
            $userNetService = new UseNetService();
            $content = $this->getMsgContent($type,$data);
            $result = $userNetService->sendMessageByNet($userPhone,$content['content'],'',$userPhone,0,$content['title'],$templateCode);
            if($result){
                $this->db->insert("dvb_send_message", ["telphone"=>$userPhone,"hotelId"=>$hotelId,"sendType"=>$type,"sendStatus"=>1,"isActive" => 0,"msgCode"=>$orderNo,"sendContent"=>$content['content'],"createTime"=>time()]);
            }else{
                $this->db->insert("dvb_send_message", ["telphone"=>$userPhone,"hotelId"=>$hotelId,"sendType"=>$type,"sendStatus"=>2,"isActive" => 1,"msgCode"=>$orderNo,"sendContent"=>$content['content'],"createTime"=>time()]);
            }
            return $result;
        }else{
            return 2;
        }
    }

    /**
     * @param $type 短信类型 4：订单待确认通知 5：预订成功通知 6：预订失败通知
     *                      7：（已付款）取消订单成功通知 8：已退款通知 9：超时取消通知
     * @param $data
     * $data 格式：
     * ["hotelName"=>"酒店","userName"=>"zzw","inTime"=>"7月26日至27日","roomStyleName"=>"大床房","roomsNumber"=>2,"nightNumber"=>2,
     * orderNum=>"订单号","orderAmount"=>"退款金额/订单金额","hotelAddress"=>"广州市","weixinName"=>"微信公众号名称","hotelPhone"=>"12587496532"]
     * 字段名需对应，不同类型传对应需要的字段值
     * @return array
     */
    public function getMsgContent($type, $data)
    {
        $content = [];
        switch ($type) {
            case 4:
                //短信标题
                $content['title'] = "订单待确认通知";
                //短信内容
                $content['content'] = $this->getToBeConfirmContent($data);
                break;
            case 5:
                //短信标题
                $content['title'] = "预订成功通知";
                //短信内容
                $content['content'] = $this->getSuccessConfirmContent($data);
                break;
            case 6:
                //短信标题
                $content['title'] = "预订失败通知";
                //短信内容
                $content['content'] = $this->getErrorConfirmContent($data);
                break;
            case 7:
                //短信标题
                $content['title'] = "（已付款）取消订单成功通知";
                //短信内容
                $content['content'] = $this->getOrederCancelContent($data);
                break;
            case 8:
                //短信标题
                $content['title'] = "已退款通知";
                //短信内容
                $content['content'] = $this->getRefundContent($data);
                break;
            case 9:
                //短信标题
                $content['title'] = "超时取消订单";
                //短信内容
                $content['content'] = $this->getTimeOutCancelContent($data);
                break;
        }
        return $content;
    }


    /**
     * 订单等待确认模板
     * data格式
     * ["hotelName"=>"酒店","userName"=>"zzw","inTime"=>"2014","roomStyleName"=>"大床房","roomsNumber"=>2,"nightNumber"=>2,"orderAmount"=>100]
     * @param array $data
     * @return string
     */
    public function getToBeConfirmContent($data=[]){
        return  "【$data[hotelName]" . "】等待确认：已收到订单-$data[userName]，$data[inTime]，入住$data[roomStyleName]，$data[roomsNumber]间，$data[nightNumber]晚，已成功支付$data[orderAmount]元，请等待预订确认。";
    }

    /**
     * 订单成功确认模板，预订成功
     * data格式
     * ["hotelName"=>"酒店","userName"=>"zzw","inTime"=>"2014","roomStyleName"=>"大床房","roomsNumber"=>2,"nightNumber"=>2,"hotelAddress"=>"广州市","hotelPhone"=>"12587496532"]
     * @param array $data
     * @return string
     */
    public function getSuccessConfirmContent($data=[]){
        return "【$data[hotelName]"."】预订成功：$data[userName]，$data[inTime]，入住$data[roomStyleName]，$data[roomsNumber]间，$data[nightNumber]晚，房间整晚保留，酒店地址：$data[hotelAddress]，如有疑问请联系$data[hotelPhone]";
    }

    /**
     * 订单预订失败模板
     * data格式
     * ["hotelName"=>"酒店","inTime"=>"2014","roomStyleName"=>"大床房","roomsNumber"=>2,orderNum=>"订单号","orderAmount"=>"退款金额","weixinName"=>"微信公众号名称","hotelPhone"=>"12587496532"]
     * @param array $data
     * @return string
     */
    public function getErrorConfirmContent($data=[]){
        return "【$data[hotelName]"."】尊敬的客户，非常抱歉，您预订的$data[inTime]$data[roomsNumber]间$data[roomStyleName]已满房（订单号:$data[orderNum]），退款金额$data[orderAmount]元，工作人员将在3天内安排退款，如需重新预订或有任何疑问，请关注联系我们的微信号“$data[weixinName]”或拨打$data[hotelPhone]";
    }

    /**
     *（已付款）取消订单成功通知
     * data格式
     * ["hotelName"=>"酒店",orderNum=>"订单号","orderAmount"=>"退款金额","weixinName"=>"微信公众号名称","hotelPhone"=>"12587496532"]
     * @param array $data
     * @return string
     */
    public function getOrederCancelContent($data=[]){
        return "【$data[hotelName]"."】订单$data[orderNum]已取消，退款金额$data[orderAmount]元，工作人员将在3天内安排退款，如需重新预订或有任何疑问，请关注联系我们的微信号“$data[weixinName]”或拨打$data[hotelPhone]";
    }

    /**
     * 已退款通知
     * data格式
     * ["hotelName"=>"酒店",orderNum=>"订单号","orderAmount"=>"退款金额","weixinName"=>"微信公众号名称","hotelPhone"=>"12587496532"]
     * @param array $data
     * @return string
     */
    public function getRefundContent($data=[]){
        return "【$data[hotelName]】已退款：您的订单$data[orderNum]已原路退回$data[orderAmount]到您的支付账号，预计1-15个工作日到账，如有疑问，请关注联系我们的微信号“$data[weixinName]”或拨打$data[hotelPhone]";
    }

    /**
     * 超时取消通知
     * data格式
     * ["hotelName"=>"酒店",orderNum=>"订单号","weixinName"=>"微信公众号名称","hotelPhone"=>"12587496532"]
     * @param array $data
     * @return string
     */
    public function getTimeOutCancelContent($data=[]){
        return "【$data[hotelName]】尊敬的客户，因尚未收到订单$data[orderNum]的相应款项，系统已取消订单，如仍需入住，请重新预订，有疑问请关注联系我们的微信号“$data[weixinName]”或拨打$data[hotelPhone]";
    }

}
