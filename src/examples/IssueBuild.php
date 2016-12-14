<?php

/**
 * Created by PhpStorm.
 * User: shellus
 * Date: 2016/12/12
 * Time: 16:57
 */
class IssueBuild
{
    public $issueRole = [
        [
            'interval' => 60 * 10,
            'count' => 72,
            'start_at' => '10:10:00',
        ],
        [
            'interval' => 60 * 5,
            'count' => 24,
            'start_at' => '00:10:00',
            'offset' => 86400,
        ],
    ];
    /** @var array 每天的开奖偏移秒数 */
    const open_time_offsets = [
        36600,
        37200,
        37800,
        38400,
        39000,
        39600,
        40200,
        40800,
        41400,
        42000,
        42600,
        43200,
        43800,
        44400,
        45000,
        45600,
        46200,
        46800,
        47400,
        48000,
        48600,
        49200,
        49800,
        50400,
        51000,
        51600,
        52200,
        52800,
        53400,
        54000,
        54600,
        55200,
        55800,
        56400,
        57000,
        57600,
        58200,
        58800,
        59400,
        60000,
        60600,
        61200,
        61800,
        62400,
        63000,
        63600,
        64200,
        64800,
        65400,
        66000,
        66600,
        67200,
        67800,
        68400,
        69000,
        69600,
        70200,
        70800,
        71400,
        72000,
        72600,
        73200,
        73800,
        74400,
        75000,
        75600,
        76200,
        76800,
        77400,
        78000,
        78600,
        79200,
        87000,
        87300,
        87600,
        87900,
        88200,
        88500,
        88800,
        89100,
        89400,
        89700,
        90000,
        90300,
        90600,
        90900,
        91200,
        91500,
        91800,
        92100,
        92400,
        92700,
        93000,
        93300,
        93600,
        93900,

    ];

    /**
     * 获取当前期数，如果上期已经开奖，这期还没到开奖时间，则返回false
     * @param $timestamp int
     * @return false|string
     */
    public function getCurrentIssue($timestamp = null)
    {
        if (!$timestamp) $timestamp = time();


    }

    /**
     * 代码生成用的
     * @return array
     */
    public function renderTimesOffset()
    {
        $timestamp = time();
        $now_day = date('Y-m-d', $timestamp);
        $now_day_timestamp = strtotime($now_day);

        $issue_count = 0;
        $open_times = [];
        foreach ($this->issueRole as $rule) {
            for ($i = 0; $i < $rule['count']; $i++) {
                $issue_count++;
                $start_at = strtotime($now_day . " " . $rule['start_at']);
                $opentime = ($start_at + $rule['interval'] * $i);

                // 偏移一天补偿，万恶的新疆时时彩
                if (isset($rule['offset'])) $opentime = $opentime + $rule['offset'];
                $open_times[] = $opentime;
            }
        }


        foreach ($open_times as $open_time) {
            echo $open_time - $now_day_timestamp . PHP_EOL;
        }

        die();
        return $open_times;
    }

    /**
     * 计算当前时间处于哪一期的投注期，如果为非开奖时段，则为第二天的第一期
     * 返回值为期数 + 这期开奖时间戳
     * @param $timestamp
     * @return string
     */
    public function calcCurrentIssue($timestamp)
    {
        $date = date('Ymd', $timestamp);
        $now_day_timestamp = strtotime($date);
        $offset = $timestamp - $now_day_timestamp;

        if($timestamp > $this::open_time_offsets[count($this::open_time_offsets) -1] + $now_day_timestamp){
            // 已经过了最后一期开奖时间，应返回明天的第一期
            $issue_offset = 0;
            $date = date('Ymd', strtotime("$date +1 day"));
        }else{
            foreach ($this::open_time_offsets as $key => $open_time_offset) {
                if ($open_time_offset > $offset) {
                    $issue_offset = $key;
                    break;
                }
            }
        }

        return [$date . str_pad($issue_offset + 1 ,2,'0'), $now_day_timestamp + $this::open_time_offsets[$issue_offset]];
    }

    public function next()
    {

    }
    public function previous($issue)
    {
        $date = substr($issue, 0, 8);

        // 转换到从0开始
        $issue_offset = (int)substr($issue, 8) - 1;
        if(!key_exists($issue_offset, $this::open_time_offsets))
        {
            throw new Exception("issue: {$issue} invalid");
        }

        if($issue_offset === 0){
            // 要拿上一天的最后一期
            $date = date('Ymd', strtotime("$date -1 day"));
            $issue_offset = count($this::open_time_offsets) - 1;

        }else{
            $issue_offset = $issue_offset - 1;
        }


        return [$date . str_pad($issue_offset + 1 ,2,'0'), strtotime($date) + $this::open_time_offsets[$issue_offset]];
    }
    /**
     * 已开奖返回true
     * @param $issue
     * @return bool
     */
    public function checkOpenStatus($issue)
    {
        return file_exists(__DIR__ . '/issue/' . $issue . '.lock');
    }

    /**
     * 记录开奖号码
     * @param $issue
     * @param $code
     */
    public function setOpenStatus($issue, $code)
    {
        file_put_contents(__DIR__ . '/issue/' . $issue . '.lock', $code);
    }
}