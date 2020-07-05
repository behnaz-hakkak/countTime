<?php

require_once "jdf.php";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST["hourPerDay"]) && isset($_POST["workHours"]) && !empty($_POST["hourPerDay"]) && !empty($_POST["workHours"])) {
        $workHours = addslashes(htmlentities($_POST['workHours']));
        $hourPerDay = addslashes(htmlentities($_POST['hourPerDay']));
        $selectedMonth = addslashes(htmlentities($_POST['selectedMonth']));
        $timeStamp = time();

        if ($selectedMonth != 13){
            $TimeStampOfaDayInChosenMonth = jmktime(23, 23 , 23, $selectedMonth, "", 1399, '', $timezone='Asia/Tehran');
            $lastDayOfChosenMonth = jdate("t", $TimeStampOfaDayInChosenMonth, '', 'Asia/Tehran', 'en');
            $timeStamp = jmktime(23, 23 , 23, $selectedMonth, $lastDayOfChosenMonth, 1399, '', $timezone='Asia/Tehran');
        }

        //Count OnLeave Days
        $numberOfDaysOff = 0;
        if (isset($_POST['numberOfDaysOff'])) {
            $numberOfDaysOff = addslashes(htmlentities($_POST['numberOfDaysOff']));
        }

        $currentDay = jdate("d", $timeStamp, '', 'Asia/Tehran', 'en');
        $lastDayOfMonth = jdate("t", $timeStamp, '', 'Asia/Tehran', 'en');
        $holidayDays = getHolidayDays($currentDay, $timeStamp);

        $result = array();
        $result["time"] = $workHours - (($currentDay - $holidayDays - $numberOfDaysOff) * $hourPerDay);

        $result = messagesManger($result);

    } else {
        $result["time"] = "empty";
        $result = messagesManger($result);
    }
}

function getHolidayDays($currentDay , $timeStamp)
{

    $startDay = $currentDay - 1;
    $holidayDays = array();

    for ($i = 1; $i <= $currentDay; $i++) {
        // Shows Name Of the Day
        $weekendDates = jdate("w", $timeStamp - ($startDay * 24) * 60 * 60, '', 'Asia/Tehran', 'en');

        if ($weekendDates == 5 || $weekendDates == 6) {
            $holidayDays[] = jdate("d", $timeStamp - ($startDay * 24) * 60 * 60, '', 'Asia/Tehran', 'en');
        }

        $startDay--;
    }

    $currentMonth = jdate("m", "", '', 'Asia/Tehran', 'en');
    $occasionalHolidays = array(
        "01" => array(2, 3, 4, 12, 13),
        "02" => array(),
        "03" => array(4, 5, 14, 28),
        "04" => array(),
        "05" => array(18),
        "06" => array(8, 9),
        "07" => array(26),
        "08" => array(4, 13),
        "09" => array(),
        "10" => array(28),
        "11" => array(22),
        "12" => array(),
    );

    $occasionalHolidays = $occasionalHolidays[$currentMonth];

    $holidayDays = array_merge($holidayDays, $occasionalHolidays);
    $holidayDays = count($holidayDays);

    return $holidayDays;
}

function messagesManger($result)
{

    if ($result["time"] == "0") {
        $result["alertType"] = "alert-primary";
        $result["message"] = "شما ساعت کاری خود را کامل کرده اید بنابراین حقوق کامل دریافت می کنید!";
    } elseif ($result["time"] == "empty") {
        $result["alertType"] = "alert-warning";
        $result["message"] = "فیلدهای ضروری جهت محاسبه زمان به درستی پر نشده اند!";
    } elseif ($result["time"] > 0) {
        $result["alertType"] = "alert-success";
        $result["message"] = "شما به اندازه " . $result["time"] . " ساعت " . " بیشتر حقوق دریافت می کنید!";
    } else {
        $result["alertType"] = "alert-warning";
        $result["time"] = $result["time"] * -1;
        $result["message"] = "شما به اندازه " . $result["time"] . " ساعت " . " کمتر حقوق دریافت می کنید!";
    }

    return $result;
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Document</title>
</head>
<body>

<div class="container">
    <div class="row mt-5">
        <div class="col-8 m-auto">
            <div class="card text-center">
                <div class="card-header">فرم محاسبه کارکردها</div>
                <div class="card-body">
                    <form class="col-12 m-auto text-right dir-rtl" method="post" name="countHours">
                        <div class="result">
                            <?php

                            if (isset($result))
                                echo "<div class='alert $result[alertType]' role='alert'>" . $result["message"] . "</div>";
                            ?>
                        </div>

                        <div class="form-group">
                            <select class="form-control text-right" name="selectedMonth">
                                <option value="13">ماه جاری</option>
                                <option value="01">فروردین</option>
                                <option value="02">اردیبهشت</option>
                                <option value="03">خرداد</option>
                                <option value="04">تیر</option>
                                <option value="05">مرداد</option>
                                <option value="06">شهریور</option>
                                <option value="07">مهر</option>
                                <option value="08">آبان</option>
                                <option value="09">آذر</option>
                                <option value="10">دی</option>
                                <option value="11">بهمن</option>
                                <option value="12">اسفند</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <input class="form-control text-right" name="workHours" placeholder="ساعت های کاری"/>
                        </div>

                        <div class="form-group">
                            <input class="form-control text-right" name="hourPerDay" placeholder="ساعت کار روزانه">
                        </div>

                        <div class="form-group">
                            <input class="form-control text-right" name="numberOfDaysOff" placeholder="تعداد روزهای تعطیل"/>
                        </div>

                        <button type="submit" name="Submit" class="btn btn-primary">ارسال</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>