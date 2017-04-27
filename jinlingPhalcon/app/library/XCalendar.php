<?php
namespace MyApp\Library;

/**
 * 日历类
 * @author zhaojianhui
 *
 */
class XCalendar
{
    /**
     * 获取开始日期和结束日期所有日期列表
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param string $formatTimeStamp 是否格式化为时间戳
     * @return string[]
     */
    public function getDateList($startDate, $endDate, $formatTimeStamp = true)
    {
        //开始时间
        $startTime = strtotime(date("Y-m-d", strtotime($startDate)));
        //结束时间戳
        $endTime = strtotime(date("Y-m-d", strtotime($endDate)));
        if ($startTime > $endTime){
            list($startTime, $endTime) = [$endTime, $startTime];
        }
        $dateList = [];
        while ($startTime <= $endTime){
            if ($formatTimeStamp){
                $dateList[] = $startTime;                
            }else{
                $dateList[] = date("Y-m-d", $startTime);
            }
            $startTime += 86400;
        }
        return $dateList;
    }
    /**
     * 获取星期中的第几天
     * @param number $timeStamp
     * @return string
     */
    public function getWeekNum($timeStamp){
        return (int)date('w',$timeStamp);
    }
    /**
     * 获取星期数的星期字符串
     * @param number $weekNum 行其中的第几天
     */
    public function getWeekStr($weekNum)
    {
        $weekCon = array('日','一','二','三','四','五','六');
        return isset($weekCon[$weekNum]) ? $weekCon[$weekNum] : '';
    }
    /**
     * 获取星期数列表的星期字符串列表
     * @param array $weekNumList
     */
    public function getWeekStrList($weekNumList = [])
    {
        $list = [];
        if ($weekNumList){
            foreach ($weekNumList as $v){
                $weekStr = $this->getWeekStr($v);
                $weekStr && $list[] = $weekStr;
            }
        }
        return $list;
    }
    /**
     * 获取时间戳的星期字符串
     * @param number $timeStamp
     */
    public function getTimeStampWeekStr($timeStamp){
        $weekNum = $this->getWeekNum($timeStamp);
        return $this->getWeekStr($weekNum);
    }
    /**
     * 获取日期列表中执行星期数的日期
     * @param array $dateList
     * @param array $weekNumList
     */
    public function getDateListByWeekNum($dateList = [], $weekNumList = [])
    {
        if (!$dateList || !$weekNumList){
            return [];
        }
        $list = [];
        foreach ($dateList as $date){
            //转换为时间戳
            $timeStamp = strtotime($date);
            $weekNum = $this->getWeekNum($timeStamp);
            if (in_array($weekNum, $weekNumList)){
                $list[] = $date;
            }
        }
        return $list;
    }
    
    
    
}