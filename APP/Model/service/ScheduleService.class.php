<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-19
 */

class ScheduleService {

    private $_scheduleORM;

    private static $_instance;

    public static function getInstance() {
        if(self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->_scheduleORM = new ORM('Schedule');
    }

    //获取当前一周线下预订情况
    public function getOfflineBookSchedule($roomId) {
        $roomId = intval($roomId);

        if($roomId < 1)
            return false;

        $beginTime = strtotime(date('Y-m-d', time()));
        $endTime = strtotime(date('Y-m-d', strtotime('+1 week')));

        $sql = "select checkin_date, count(*) as total from pa_schedule "
            ."where room_id=$roomId and is_offline=1 and checkin_date between $beginTime and $endTime "
            ."group by checkin_date";
        $records = $this->_scheduleORM->sql($sql)->queryAll(false, true);

        $bookSchedules = array();
        for($i = 0; $i < 7; $i++) {
            $date = strtotime(date('Y-m-d',strtotime("+$i day")));

            $exist = false;
            foreach($records as $record) {
                if($record['checkin_date'] == $date) {
                    $exist = true;
                    $count =  intval($record['total']);
                    $bookSchedules[$date] = $count;
                    break;
                }
            }

            if(!$exist)
                $bookSchedules[$date] = 0;

        }

        return $bookSchedules;
    }

    public function getOneBookSchedule($roomId, $date) {
        $roomId = intval($roomId);
        $date = intval($date);

        if($date < 1 || $roomId < 1)
            return false;

        $sql = "select is_offline, count(*) as total from pa_schedule "
            ."where room_id=$roomId and checkin_date=$date "
            ."group by is_offline";

        $records = $this->_scheduleORM->sql($sql)->queryAll(false, true);

        $count = array();
        foreach($records as $record) {
            if($record['is_offline'] == 1)
                $count['offline'] = $record['total'];
            else
                $count['online'] = $record['total'];
        }

        return $count;
    }

    public function addOneOffline($roomId, $date) {
        $roomId = intval($roomId);
        $date = intval($date);

        if($date < 1 || $roomId < 1)
            return false;

        $s = new Schedule();
        $s->orderId(0);
        $s->roomId($roomId);
        $s->checkinDate($date);
        $s->isOffline(1);

        return $s->save() > 0;
    }

    public function subOneOffline($roomId, $date) {
        $roomId = intval($roomId);
        $date = intval($date);

        if($date < 1 || $roomId < 1)
            return false;

        $params = array(
            'roomId'=>$roomId,
            'checkinDate'=>$date,
            'isOffline'=>1
        );

        $status = $this->_scheduleORM->delete()
                    ->where($params)
                    ->limit(1)
                    ->execute();

        return $status;
    }

    public function deleteSchedulesByOrderId($orderId) {
        $orderId = intval($orderId);

        if($orderId < 1)
            return false;

        $status = $this->_scheduleORM->delete()
                    ->where(array('orderId'=>$orderId))
                    ->execute();

        return $status;
    }
}