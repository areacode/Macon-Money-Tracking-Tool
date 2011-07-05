<?php

class DateDDLGenerator{

    var $intYear;
    var $intMonth;
    var $intDay;
    var $bolSetToCurrentDay;

    function DateDDLGenerator(){
        $this->bolSetToday = false;
        $this->intYear  = @date("Y");
        $this->intMonth = @date("m");
        $this->intDay   = @date("d");
    }

    function setToCurrentDay(){
        $this->bolSetToCurrentDay = true;
    }

    #Generate Year range
    function genYearDDL($selName = 'Year', $yearCount = 2, $year = '2012', $selYear = null){
    	/*
            Check if the year passed in is the same as current year.
            If the year got is not given or same as current year, the list 
            will select the current year by default.  Otherwise, $yearSelect
            will be set to what user entered.
        */
        $yearSelect = $year == '' ? @date("Y") : $year;
        
        /*
            $yearCount: it is the length of your drop down list, i.e. how many 
            years do you want to show.  It is 50 by default, which shows 50 years
            from now.
        */	

        $str = "<select name='$selName'>\n";
        for($i = $yearSelect; $i >= ($yearSelect - $yearCount); $i--){
			$selected = '';
        	if($i == $selYear || ($this->bolSetToCurrentDay == true && !isset($selYear) && $this->intYear == $i)) {
				$selected = 'selected="selected"';		
			}
            $str .= "\t<option value='$i' $selected>$i</option>\n";
        }
        $str .= "</select>\n";
        print $str;
    }

    #Generate month range from 1 to 12
    function genMonthDDL($selName = 'Month', $date_format = 'short', $selMonth = null){
    	$shortM = array(1 => "Jan", "Feb", "Mar",
                             "Apr", "May", "Jun",
                             "Jul", "Aug", "Sep",
                             "Oct", "Nov", "Dec");
        
        $longM  = array(1 => "January", "February", "March",
                             "April"  , "May"       , "June" ,
                             "July"      , "Aug"       , "September",
                             "October", "November", "December");
        $str = "<select name='$selName'>\n";
        for($i = 1; $i <= 12; $i++){
     		$selected = '';
           	if($selMonth == sprintf("%02d", $i) || ($this->bolSetToCurrentDay == true && empty($selMonth) && $this->intMonth == $i)) 
           		$selected = 'selected="selected"';
            $str .= "\t<option value='$i' $selected>". ($date_format == 'short' ? $shortM[$i] : $longM[$i]) ."</option>\n";
        }
        
        $str .= "</select>\n";

        print $str;
    }

    #Generate day range from 1 to max days of relevant month
    function genDayDDL($selName = 'Day', $selDay = null){
        $str = "<select name='$selName'>\n";

/*
        //Thanks to Peter K on this improvement and now this method support leap year
        if ($this->intMonth == 2) {                                            // February ?
            $leap_day = 0;
            
            if ($this->intYear >= 4 && $this->intYear % 4 == 0) {            // Leap year ?
                if ($this->intYear >= 1800 && $this->intYear % 100 == 0) {    // No accurate leap centuries before that
                    if (($this->intYear / 100) % 4 == 0)
                        $leap_day = 1;
                } else
                    $leap_day = 1;
            }
            
            $max_days = 28 + $leap_day;
        } else if ($this->intMonth == 4 || $this->intMonth == 6 ||
                   $this->intMonth == 9 || $this->intMonth == 11)
            $max_days = 30;
        else */
            $max_days = 31;
            
        for($i = 1; $i <= $max_days; $i++){
        	$selected = '';	
        	if($selDay == sprintf("%02d", $i) || ($this->bolSetToCurrentDay == true && empty($selDay) && $this->intDay == $i))
        		$selected = 'selected="selected"';
            $str .= "\t<option value='$i' $selected>$i</option>\n";
        }
        $str .= "</select>\n";
        print $str;
    }
}

$ddlFrom = new DateDDLGenerator();
$ddlTo = new DateDDLGenerator();
$ddlBlank = new DateDDLGenerator();

$ddlFrom->setToCurrentDay();
$ddlTo->setToCurrentDay();

if (isset($_POST["date_from"])  || isset($_POST["date_to"]) ) {
	if(isset($_POST['date_from'])) {
		$ddlFrom->intYear = intval($_POST["year_from"]);
		$ddlFrom->intMonth = intval($_POST["month_from"]);
		$ddlFrom->intDay = intval($_POST["date_from"]);
		$date_from = $_POST["year_from"] . "-" . sprintf("%02d", $_POST["month_from"]) . "-" . sprintf("%02d", $_POST["date_from"]);
		if(!checkdate($ddlFrom->intMonth, $ddlFrom->intDay, $ddlFrom->intYear))
			$errors[] = "The " . (isset($_POST["date_to"]) ? "start " : " ") . "date specified is not a valid date.";
	}
	if(isset($_POST['date_to'])) {	
		$ddlTo->intYear = intval($_POST["year_to"]);
		$ddlTo->intMonth = intval($_POST["month_to"]);
		$ddlTo->intDay = intval($_POST["date_to"]);
		$date_to = $_POST["year_to"] . "-" . sprintf("%02d", $_POST["month_to"]) . "-" . sprintf("%02d", $_POST["date_to"]);
		if(!checkdate($ddlTo->intMonth, $ddlTo->intDay, $ddlTo->intYear))
			$errors[] = "The end date specified is not a valid date.";
	}
}
?>