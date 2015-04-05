<?php
/*
Plugin Name: Douban Say for WordPress
Plugin URI: http://icek.me/doubansay-plugin-for-wordpress/
Description: Display the information of your douban say(能显示自己豆瓣说的小插件，启用后在把“我的豆瓣说”小工具放到想要的位置,user位置填入自己的豆瓣id即可，实际效果请见我的blog的主页右上角小工具DOUBANSAY内容。)
Version: 0.1.91
Author: icek
Author URI: http://www.icek.me/
License: GPL
*/

/*  Copyright 2012-2013 icek  (email : zhuxi910511@163.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
 */


if ( ! defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
if ( ! defined( 'DOUBANSAY_APIKEY' ) )
    define('DOUBANSAY_APIKEY', '00821e37becbbb0901865aa73c63311b' );
$content = json_decode(file_get_contents('http://www.icek.me/douban_token/access_token'), true);
if ( ! defined( 'ACCESS_TOKEN' ) )
    define('ACCESS_TOKEN', $content['access_token'] );

if ( !class_exists('DoubanSay'))
{
	class DoubanSay
	{
		function DoubanSay()
		{
			$this -> plugin_url = WP_PLUGIN_URL . '/douban-say-for-wordpress';
		}
		function douban_say()
		{
			echo('<!-- DoubanSay is here -->');
		}

		private function get_douban_user($user)
		{
			$url = 'https://api.douban.com/v2/user/' . $user . '?apikey=' . DOUBANSAY_APIKEY;
			$contents=json_decode(file_get_contents($url), true);  

			// we need the big user icon instead of the default small one, if exists
            $big_icon_url = preg_replace('/u(\d+)/i', 'ul$1', $contents['avatar']);
            $headers = get_headers($big_icon_url);
            if(strpos($headers[0], '404') === false){
                $contents['avatar'] = $big_icon_url;
            }

			return $contents;
		}

		private function get_douban_say($user, $max_results=5)
		{
            $url = 'https://api.douban.com/shuo/v2/statuses/user_timeline/' . $user . '?' . 'apikey=' . DOUBANSAY_APIKEY ;
            $opts = array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Authorization: Bearer ' . ACCESS_TOKEN
                ));

			$contents=json_decode(file_get_contents($url, false, stream_context_create($opts)), true);
            for ($i = 0; $i < count($contents); ++$i)
            {
                if (!$contents[$i]['reshared_status'])
                {
                    $c[] = $contents[$i];
                }
            }
            $contents = array_slice($c, 0, $max_results);

			return $contents;
		}

		function load_scripts()
		{  
			echo ('<!-- douban-say-script.js -->');
			//$this -> plugin_url = WP_PLUGIN_URL . '/douban-say';
			$script_url = $this->plugin_url . '/js/douban-say-script.js';
			//echo ($script_url);
			wp_register_script('douban_say_script', $script_url );
			wp_enqueue_script('douban_say_script');
		}

		function load_css()
		{
			echo ('<!-- douban-say-css.css -->');
			//$this -> plugin_url = WP_PLUGIN_URL . '/douban-say';
			$css_url = $this -> plugin_url . '/css/douban-say-css.css';

			wp_register_style('douban_say_css', $css_url);
			wp_enqueue_style('douban_say_css');
			$css_url = $this -> plugin_url . '/css/douban-say-content-css.css';

			wp_register_style('douban_say_content_css', $css_url);
			wp_enqueue_style('douban_say_content_css');
								
		}

		// 注册小工具
		function douban_say_register_widgets()
		{
			register_widget( 'douban_say_widget' );
		}

		function compose_html($settings)
		{
			$user = $settings['user'];
			$max_results = $settings['max_results'];
			//echo ('test'. $user);
			$douban_user = $this->get_douban_user($user);
			$douban_say = $this->get_douban_say($user, $max_results);

?>
<div id="doubansay">
<div id="profile">
<div class="infobox">
<div class="ex1">
<span></span>
</div>
<div class="bd">
<img src="<?php echo ($douban_user['avatar']); ?>" class="userface" alt="" />
<div class="sep-line"></div>
<div class="pl">hello, I'm <?php echo ($douban_user['name']); ?></div>
<div class="user-info">常居:&nbsp;<a href="http://www.douban.com/location/<?php echo ($douban_user['loc_id']); ?>/"><?php echo ($douban_user['loc_name']); ?></a><br />
</div>
<div class="sep-line"></div>
<div class="user-intro">
<div id="edit_intro"  class="j edtext pl">
<span id="intro_display" ><?php echo ($douban_user['desc']); ?></span>
</div>
</div>
</div>
<div class="ex2"><span></span></div>
</div>
</div>
<div id="shuo-widget">
	<div class="body">
		<ul>
<?php
			$counts = count($douban_say);
		for ($i = 0; $i < $counts; ++$i)
		{
			$now = $douban_say[$i];
			echo ('<li class="item">');
			echo ('<div class="text">');

			echo ('</div>');

			echo ('<div class="attachment">');
            if ($now['title'] != '说：')
            {
                $title = preg_replace('/\[score\](\d)\[\/score\]/', ' \1星 ', $now['title']);
				echo ($title);
                for ($j = 0; $j < count($now['attachments']); ++$j)
                {
                    $attachment = $now['attachments'][$j];
                    echo ('<a href="' . $attachment['expaned_href'] . '" target="_blank">' . $attachment['title'] . '</a>');
                }
                echo('<br>');
            }
				echo ($now['text']);
			echo ('</div>');
			echo ('<div class="ft">');
			sscanf($now['created_at'], "%*d-%d-%d %d:%d:%*s", $month, $day, $hour, $minute);
            $id = $now['id'];
?>
	<a href="http://www.douban.com/people/<?php echo ($user); ?>/status/<?php echo ($id); ?>" class="time-stamp" target="_blank"><?php printf("%02d", $month); ?>月<?php printf("%02d", $day); ?>日 <?php printf("%02d", $hour); ?>:<?php printf("%02d", $minute); ?></a>
<?php
			echo ('</div>');
		}
?>
		<li class="item">
			<div class="attachment" style="text-align:right">
			<a href="http://www.douban.com/people/<?php echo ($user); ?>">我的豆瓣>></a>
			</div>
		</li>
</ul>
	</div>
</div>

</div>


<?php
		}
	}
}




if ( class_exists('DoubanSay') )
{
	$doubansay = new DoubanSay();
}

if (isset ( $doubansay ) )
{
	//add_action('wp_print_scripts', array( &$doubansay, 'load_scripts'));
	//add_action('wp_head', array( &$doubansay, 'douban_say'));
	//add_shortcode('douban_say_here', array( &$doubansay, 'douban_say'));
	add_action('wp_print_styles', array( &$doubansay, 'load_css') );
	// 使用 widgets_init 动作钩子来执行自定义的函数
	add_action( 'widgets_init', array( &$doubansay, 'douban_say_register_widgets') );

}

/**
 * Foo_Widget Class
 */
class douban_say_widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::WP_Widget( /* Base ID */'douban_say_widget', /* Name */'我的豆瓣说', array( 'description' => 'Display the user\'s Douban Say' ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$user = $instance['user'];
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title; ?>
		<?php //echo $user; ?>
<?php 
		if (class_exists('DoubanSay'))
			//if ( isset($doubansay) )
		{
			$doubansay = new DoubanSay();
			$doubansay->compose_html($instance);
		}
?>
<?php echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['user'] = strip_tags($new_instance['user']);
		$instance['max_results'] = strip_tags($new_instance['max_results']);
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$user = esc_attr( $instance[ 'user' ] );
			$max_results= esc_attr( $instance[ 'max_results' ]);
		}
		else {
			$title = __( 'Douban Say', 'text_domain' );
			$user = 'zhuxi0511';
			$max_results= 5;
		}
?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('user'); ?>"><?php _e('User:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('user'); ?>" name="<?php echo $this->get_field_name('user'); ?>" type="text" value="<?php echo $user; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('max_results'); ?>"><?php _e('Max number of says:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('max_results'); ?>" name="<?php echo $this->get_field_name('max_results'); ?>" type="text" value="<?php echo $max_results; ?>" />
		</p>
<?php 
	}

} // class Foo_Widget

?>
