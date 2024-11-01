<?php
/*
 Plugin Name: WP-DailyBurn
 Plugin URI: http://ahealthydad.com/wp-dailyburn
 Description: <a href="http://dailyburn.com">DailyBurn</a> widget for your blog.
 Author: Brandon Wood
 Version: 1.0.2
 Author URI: http://ahealthydad.com/
*/

require_once("DailyBurn.php");

function displayUserProfile() 
{
	$username = get_option("widget_wpDailyBurn_username");
	$password = getPassword();
	
	if ($username == "" || $password == "")
	{
		echo "<p>Please enter your DailyBurn username and password in this widget's options.</p>";
		return;
	}
	
	wp_enqueue_style('wp-dailyburn-style', plugins_url('wp-dailyburn/wp-dailyburn.css'));
	
	$db = new DailyBurn($username, $password);
	$profile = $db->getUserProfile();
	
	if ($profile == null)
	{
		echo "<p>There was an error getting your profile from DailyBurn. Please make sure your
				 username and password are entered correctly.</p>";
		return;
	}
	
	if ($profile->uses_metric_weights === true)
		$units = 'kg';
	else
		$units = 'lbs';
	
	echo '	
		<table border="0">
			<tr>
				<td class="wp-dailyburn-label">Current Weight:</td> 
				<td>' . $profile->body_weight . ' ' . $units . '</td>
			</tr>
			<tr>
				<td class="wp-dailyburn-label">Goal Weight:</td> 
				<td>' . $profile->body_weight_goal . ' ' . $units . '</td>
			</tr>
			<tr>
				<td class="wp-dailyburn-label">Calories Eaten:</td> 
				<td>' . $profile->calories_consumed . '</td>
			</tr>
			<tr>
				<td class="wp-dailyburn-label">Calories Burned:</td> 
				<td>' . $profile->calories_burned . '</td>
			</tr>
	';
	
	echo '	<tr>
				<td class="wp-dailyburn-label">Exercise Status:</td>
			</tr>
			<tr>
				<td class="wp-dailyburn-img" colspan="2">';
	for ($i=1; $i<=7; $i++)
	{
		if ($i <= $profile->days_exercised_in_past_week)
			$img = 'flame';
		else
			$img = 'flame-faded';
			
		$url = plugins_url('wp-dailyburn/images/'.$img.'.gif');
		echo '<img src="'.$url.'" alt="'.$img.'" />';
	}
	echo '		</td>
			</tr>';
	
	echo '	<tr>
				<td class="wp-dailyburn-label">Nutrition Status:</td>
			</tr>
			<tr>
				<td class="wp-dailyburn-img" colspan="2">';
	for ($i=1; $i<=7; $i++)
	{
		if ($i <= $profile->cal_goals_met_in_past_week)
			$img = 'apple';
		else
			$img = 'apple-faded';
			
		$url = plugins_url('wp-dailyburn/images/'.$img.'.gif');
		echo '<img src="'.$url.'" alt="'.$img.'" />';
	}
	echo '		</td>
			</tr>
		</table>
		
		<p><a href="http://dailyburn.com'.$profile->url.'" title="View my DailyBurn profile">View my complete profile</a></p>';
}

function widget_wpDailyBurn($args) 
{
	$title = get_option("widget_wpDailyBurn_title");
	
	extract($args);
	echo $before_widget;
	echo $before_title . $title . $after_title;
	displayUserProfile();
	echo $after_widget;
 }
 
 function wpDailyBurn_control() 
 {
	if (isset($_POST['wpDailyBurn-Title']))
	{
		$title = htmlspecialchars($_POST['wpDailyBurn-Title']);
		$username = htmlspecialchars($_POST['wpDailyBurn-Username']);
		$password = htmlspecialchars($_POST['wpDailyBurn-Password']);
		
		update_option("widget_wpDailyBurn_title", $title);
		update_option("widget_wpDailyBurn_username", $username);
		setPassword($password);
	}
	else
	{
		$title = get_option("widget_wpDailyBurn_title");
		if ($title == "")
			$title = "My DailyBurn";
		
		$username = get_option("widget_wpDailyBurn_username");
		$password = getPassword();
	}
 ?>
   <p>
     <label for="wpDailyBurn-Title">Title:</label>
     <input type="text" class="widefat" id="wpDailyBurn-Title" name="wpDailyBurn-Title" value="<?php echo $title; ?>" />
   </p>
   <p>
     <label for="wpDailyBurn-Username">DailyBurn Username:</label>
     <input type="text" class="widefat" id="wpDailyBurn-Username" name="wpDailyBurn-Username" value="<?php echo $username; ?>" />
   </p>
   <p>
     <label for="wpDailyBurn-Password">DailyBurn Password:</label>
     <input type="password" class="widefat" id="wpDailyBurn-Password" name="wpDailyBurn-Password" value="<?php echo $password; ?>" />
   </p>
 <?php
 }
 
 function wpDailyBurn_init()
 {
	wp_enqueue_style('wp-dailyburn-style', plugins_url('wp-dailyburn/wp-dailyburn.css'));
	
	register_sidebar_widget(__('DailyBurn'), 'widget_wpDailyBurn');
	register_widget_control('DailyBurn', 'wpDailyBurn_control');
 }
 
 function getPassword()
 {
	$encrypted = get_option("widget_wpDailyBurn_password");
	return base64_decode($encrypted);
 }
 
 function setPassword($password)
 {
	$encrypted = base64_encode($password);
	update_option("widget_wpDailyBurn_password", $encrypted);
 }
 
 add_action("plugins_loaded", "wpDailyBurn_init");
?>
