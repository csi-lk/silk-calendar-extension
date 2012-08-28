<?php 
/*
Plugin Name: * Silk Calendar Extensions [BETA]
Plugin URI: http://webanyti.me
Description: Creates functionality to list all events in current month - silk_this_month() and single event pages, these function piggybacks on the popular Calendar plugin by <a href="http://www.kieranoshea.com" target="_blank">Kieran O'Shea</a>
Version: 1.0
Author: Callum Silcock
Author URI: http://webanyti.me
Copyright (c) 2012 Callum Silcock (http://webanyti.me)
This is a WordPress plugin (http://wordpress.org).

     _______. __   __       __  ___    .___________. __    __   __       _______.   
    /       ||  | |  |     |  |/  /    |           ||  |  |  | |  |     /       |   
   |   (----`|  | |  |     |  '  /     `---|  |----`|  |__|  | |  |    |   (----`   
    \   \    |  | |  |     |    <          |  |     |   __   | |  |     \   \       
.----)   |   |  | |  `----.|  .  \         |  |     |  |  |  | |  | .----)   |      
|_______/    |__| |_______||__|\__\        |__|     |__|  |__| |__| |_______/       
			.___  ___.   ______   .__   __. .___________. __    __                              
			|   \/   |  /  __  \  |  \ |  | |           ||  |  |  |                             
			|  \  /  | |  |  |  | |   \|  | `---|  |----`|  |__|  |                             
			|  |\/|  | |  |  |  | |  . `  |     |  |     |   __   |                             
			|  |  |  | |  `--'  | |  |\   |     |  |     |  |  |  |                             
			|__|  |__|  \______/  |__| \__|     |__|     |__|  |__|          

function to check if active = calendar();  */

/* Create Function -----------------------------------------------------------------------[SILK_SINGLE()]------- */

add_filter('the_content','silk_single_content');
//add_filter('the_title','silk_single_title');

function silk_single_content($content) {

	if(isset($_GET['event_id'])) {

		$content = '';

		$event = silk_single($_GET['event_id']);


			$content .= '<div class="event_header clearfix"><h2 class="event_title clearfix">' . $event->event_title . '</h2>';

			$content .= '<div class="meta clearfix">
							<div class="social">
							<a href="mailto:?subject=Event: ' . $event->event_title . '&amp;body=I thought this event would be relevant to you.%0d' . $event->event_title . '%0d' . home_url() . $_SERVER["REQUEST_URI"] . '" class="social_icon" title="Share by Email" target="_blank">
							<img src="' . get_template_directory_uri() . '/library/images/small_email.png"></a>

							<a href="http://www.linkedin.com/cws/share?mini=true&amp;url=' . home_url() . $_SERVER["REQUEST_URI"] . '&amp;title=' . $event->event_title . '&amp;summary='. urlencode(strip_tags($event->event_desc)) .'" class="social_icon" title="Share on LinkedIn" target="_blank">
							<img src="' . get_template_directory_uri() . '/library/images/small_linkedin.png"></a>

							<a href="http://www.facebook.com/sharer.php?u=' . home_url() . $_SERVER["REQUEST_URI"] . '&amp;t=' . $event->event_title . '" class="social_icon" title="Share on Facebook" target="_blank">
							<img src="' . get_template_directory_uri() . '/library/images/small_facebook.png" target="_blank"></a>

							<a href="http://twitter.com/home?status=' . $event->event_title . ' starts: ' . $event->event_begin . ' - ' . home_url() . $_SERVER["REQUEST_URI"] . '" class="social_icon" title="Share on Twitter" target="_blank">
							<img src="' . get_template_directory_uri() . '/library/images/small_twitter.png"></a>
							</div>
						 </div></div>';

		if($event->event_end == $event->event_begin){

			$content .= '<p class="event_time clearfix">' . date("D jS F, Y",strtotime($event->event_begin));
			
			if ($event->event_time == '00:00:00') {
				$content .= ' - All Day';
			} else {
				$content .= ' '.__('at','calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time)));
			}
			
			$content .= '</p>';	

		} else {
			$content .= '<p class="event_time clearfix">' . date("D jS F, Y",strtotime($event->event_begin)) . ' - ' . date("D jS F, Y",strtotime($event->event_end));

			if ($event->event_time == '00:00:00') {
				$content .= ' - All Day';
			} else {
				$content .= ' '.__('at','calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time)));
			}

			$content .= '</p>';
			
		}

		$content .= '<p class="event_desc">' . stripslashes($event->event_desc) . '</p>';
		$content .= '<p class="event_link">View further details at the <a href="' . $event->event_link . '" target="_blank">'. $event->event_title . '</a> website.</p>';

	}
	return $content;	
}

/* Create Function ---------------------------------------------------------------------[SILK_SINGLE_TITLE()]--- */

function silk_single_title($title) {

	if(isset($_GET['event_id']) && is_page()) {

		$title = '';

		$event = silk_single($_GET['event_id']);

		$title .= $event->event_title;
	}

	return $title;
}

/* Create Function ------------------------------------------------------------------------[SILK_SINGLE()]------ */

function silk_single($event_id) {

	global $wpdb;

	$sql = "SELECT a.*,'Normal' AS type  FROM " . WP_CALENDAR_TABLE . " AS a WHERE a.event_id = '" . $event_id . "'";

	$event =$wpdb->get_results($sql);

    if (!empty($event)) {


			return $event[0];

	}

} 

/* Create Function ---------------------------------------------------------------------[SILK_THIS_MONTH()]----- */

function silk_this_month()
{

	function silk_create_excerpt($string)
	{
    $words = explode(" ",$string);
    $word_limit = 18;
    
    return implode(" ",array_splice($words,0,$word_limit));
	}

  	global $wpdb;

      // Get number of days left in month and store in $future_days to make things easy

      $future_days = date('t') - date('j');

      $day_count = 1;

      $output = '';

	  $event_ids = array();

	  if(isset($_GET['event_day'])) {

		while ($day_count < $future_days+1) {

		//list($y,$m,$d) = explode("-",date("Y-m-d",mktime($day_count*24,0,0,date("m",ctwo()),date("d",ctwo()),date("Y",ctwo()))));
		if(isset($_GET['event_year'])) {
			$y = $_GET['event_year'];
		} else {
			$y = date(Y);
		}
		if(isset($_GET['event_month'])){
			$m = $_GET['event_month'];
		} else {
			$m = date(n);
		}

		$d = $_GET['event_day'];

		$events = grab_events($y,$m,$d,'upcoming',$cat_list);

		usort($events, "time_cmp");

	    foreach($events as $event)
	    {

			if(!in_array($event->event_id, $event_ids)){ 

				$day_no = date(d, strtotime($event->event_begin));
				$day = date(D, strtotime($event->event_begin));

		     	if ($event->event_time == '00:00:00') {
					$time_string = '<h3>'.$day_no.'</h3>'.'<h4>'.$day.'</h4>'.'<h5>'.__('all day','calendar').'</h5>';
		     	} else {

					$time_string = ' '.__('at','calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time)));
		     	}

		     	//If silk single event is activated show below

	            $output .= '<a href="'.home_url().'/events/?event_id='.$event->event_id.'"><li><div class="silk_date">'.$time_string.'</div><h2>'.$event->event_title.'</h2><p>'.stripslashes(silk_create_excerpt($event->event_desc)).'...</p></li></a>';

	            array_push($event_ids, $event->event_id);
		    }

		}

		$day_count = $day_count+1;
		}

	  } elseif(isset($_GET['event_month'])) {

	  	if(isset($_GET['event_year'])){
	  		$the_year = $_GET['event_year'];
	  	} else {
	  		$the_year = date(Y);
	  	}

	  	$future_days = date(t,mktime(0,0,0,$_GET['event_month'],1,$the_year));

		while ($day_count < $future_days+1) {


			$events = grab_events($the_year,$_GET['event_month'],$day_count,'upcoming', '');

			foreach($events as $event) {

				if(!in_array($event->event_id, $event_ids)){ 

				$day_no = date(d, strtotime($event->event_begin));
				$day = date(D, strtotime($event->event_begin));

		     	if ($event->event_time == '00:00:00') {
					$time_string = '<h3>'.$day_no.'</h3>'.'<h4>'.$day.'</h4>'.'<h5>'.__('all day','calendar').'</h5>';
		     	} else {

					$time_string = ' '.__('at','calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time)));
		     	}

		     	//If silk single event is activated show below

	            $output .= '<a href="'.home_url().'/events/?event_id='.$event->event_id.'"><li><div class="silk_date">'.$time_string.'</div><h2>'.$event->event_title.'</h2><p>'.stripslashes(stripslashes(silk_create_excerpt($event->event_desc))).'...</p></li></a>';

	            array_push($event_ids, $event->event_id);
		    	}


	   		}

			$day_count = $day_count+1;
		}
	  	
	  } else {

      while ($day_count < $future_days+1)
	  {
		list($y,$m,$d) = explode("-",date("Y-m-d",mktime($day_count*24,0,0,date("m",ctwo()),date("d",ctwo()),date("Y",ctwo()))));
		$events = grab_events($y,$m,$d,'upcoming',$cat_list);

		usort($events, "time_cmp");

	    foreach($events as $event)
	    {

			if(!in_array($event->event_id, $event_ids)){ 

				$day_no = date(d, strtotime($event->event_begin));
				$day = date(D, strtotime($event->event_begin));

		     	if ($event->event_time == '00:00:00') {
					$time_string = '<h3>'.$day_no.'</h3>'.'<h4>'.$day.'</h4>'.'<h5>'.__('all day','calendar').'</h5>';
		     	} else {

					$time_string = ' '.__('at','calendar').' '.date(get_option('time_format'), strtotime(stripslashes($event->event_time)));
		     	}

		     	//If silk single event is activated show below

	            $output .= '<a href="'.home_url().'/events/?event_id='.$event->event_id.'"><li><div class="silk_date">'.$time_string.'</div><h2>'.$event->event_title.'</h2><p>'.silk_create_excerpt($event->event_desc,23).'...</p></li></a>';

	            array_push($event_ids, $event->event_id);
		    }

		}

		$day_count = $day_count+1;

	  }
	}

      if ($output != '')
	  {
		$visual = '<ul id="silk_event_list">';
		$visual .= $output;
		$visual .= '</ul>';

		return $visual;
	  } else {
	  	if(!is_page('events')){
	  		$visual = '<p class="noevent">No events found. Please pick a month / day on the mini calendar in the events page.</p>';
	  	} else {
	  		$visual = '<p>No events found. Please pick a month / day on the mini calendar on the right hand side of the page.</p>';
	  	}
	  	return $visual;
	  }
}

/* Create Function -------------------------------------------------------------------[SILK_MINI_CALENDAR()]----- */

function silk_mini_calendar()
{
	if(is_page('events')) :
	if(!isset($_GET['event_month'])) {
		$month = date(n);
	} else {
		$month = $_GET['event_month'];
	}

	if(!isset($_GET['event_year'])) {
		$year = date(Y);
	} else {
		$year = $_GET['event_year'];
	}

  /* draw table - this was fucking hard to do... confusing but should make sense if you bang your head on the desk */
  $calendar = '<div id="mini_calendar"><p><a href="?event_month=';
  if($month != 1){$calendar .= $month - 1;}else{$calendar .= '12&event_year=' . ($year - 1);}
  if(isset($_GET['event_year']) && $month != 1){ $calendar .= '&event_year=' . $_GET['event_year']; }
  $calendar .= '"><img src="' . get_template_directory_uri() . '/library/images/arrow_left.png" /></a>' . '<a href="?event_month=' . $month;
  if(isset($_GET['event_year'])){$calendar .= '&event_year=' . $_GET['event_year']; }
  $calendar .= '">' . strtoupper(date(F,mktime(0, 0, 0, $month, 1, 0))) . '</a>' . '<a href="?event_month=';
  if($month != 12){$calendar .= $month + 1;}else{$calendar .= '1&event_year=' . ($year + 1);}
  if(isset($_GET['event_year']) && $month != 12){ $calendar .= '&event_year=' . $_GET['event_year']; }  
  $calendar .= '"><img src="' . get_template_directory_uri() . '/library/images/arrow_right.png" /></a></p><table cellpadding="0" cellspacing="0" class="calendar">';

  /* table headings */
  $headings = array('S','M','T','W','T','F','S');
  $calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

  /* days and weeks vars now ... */
  $running_day = date('w',mktime(0,0,0,$month,1,$year));
  $days_in_month = date('t',mktime(0,0,0,$month,1,$year));
  $days_in_this_week = 1;
  $day_counter = 0;
  $dates_array = array();

  /* row for week one */
  $calendar.= '<tr class="calendar-row">';

  /* print "blank" days until the first of the current week */
  for($x = 0; $x < $running_day; $x++):
    $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
    $days_in_this_week++;
  endfor;

  /* keep going with days.... */
  for($list_day = 1; $list_day <= $days_in_month; $list_day++):
    $calendar.= '<td class="calendar-day">';
      /* add in the day number */


      $events = grab_events($year,$month,$list_day,'upcoming',$cat_list);

    if(!$events){
		$calendar.= '<div class="day-number">'.$list_day.'</div>';
		} else {
		$calendar .= '<div class="day-number-active"><a href="?event_day=' . $list_day;

		if(isset($_GET['event_year'])){// this mother fucker right here
			$calendar .= '&event_year=' . $_GET['event_year'];
		}
		if(isset($_GET['event_month'])){
			$calendar .= '&event_month=' . $_GET['event_month'];
		}
		
		$calendar .= '">' . $list_day . '</a></div>';
	}
      
    $calendar.= '</td>';
    if($running_day == 6):
      $calendar.= '</tr>';
      if(($day_counter+1) != $days_in_month):
        $calendar.= '<tr class="calendar-row">';
      endif;
      $running_day = -1;
      $days_in_this_week = 0;
    endif;
    $days_in_this_week++; $running_day++; $day_counter++;
  endfor;

  /* finish the rest of the days in the week */
  if($days_in_this_week < 8):
    for($x = 1; $x <= (8 - $days_in_this_week); $x++):
      $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
    endfor;
  endif;

  /* final row */
  $calendar.= '</tr>';

  /* end the table */
  $calendar.= '</table></div>';
  
  /* all done, return result */
  echo $calendar;

  endif;

}

wp_register_sidebar_widget( 
'silk_mini_calendar_1', // your unique widget id 
'Silk Mini Calendar', // widget name 
'silk_mini_calendar', // callback function 
array( // options 
'description' => 'Display mini calendar on event page' 
) 
);

/* Create Shortcode -------------------------------------------------------------[SILK_UPCOMING_EVENTS()]-------- */

function silk_upcoming_events()
{
	if(!is_page('events')){
		echo '<div id="silk_upcoming_sidebar">
		<div class="post_category Product">
		<a href="'.home_url().'/events">Events</a></div>';
		echo silk_this_month();
		echo '</div>';
	}
}

wp_register_sidebar_widget( 
'silk_upcoming_events_1', // your unique widget id 
'Silk Upcoming Events', // widget name 
'silk_upcoming_events', // callback function 
array( // options 
'description' => 'Display this months upcoming events in sidebar' 
) 
);

/* Create Shortcode -----------------------------------------------------------[SILK_THIS_MONTH_SHORTCODE()]----- */

function silk_this_month_shortcode(){

 return silk_this_month();
}
add_shortcode( 'events_this_month', 'silk_this_month_shortcode' );

?>