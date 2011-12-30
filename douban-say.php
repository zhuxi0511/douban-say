<?php
/*
Plugin Name: Douban Say for WordPress
Plugin URI: http://icek.me/doubansay-plugin-for-wordpress/
Description: Display the information of your douban say(能显示自己豆瓣说的小插件，启用后在把“我的豆瓣说”小工具放到想要的位置即可，实际效果请见我的blog的主页右上角小工具DOUBANSAY内容。)
Version: 0.1.1
Author: icek
Author URI: http://www.icek.me/
License: GPL
*/

/*  Copyright YEAR  icek  (email : zhuxi910511@163.com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
	define( 'DOUBANSAY_APIKEY', '047cf5811ff899010d76e0835fe9cb03' );

if ( !class_exists('DoubanSay'))
{
	class DoubanSay
	{
		function DoubanSay()
		{
			$this -> plugin_url = WP_PLUGIN_URL . '/douban-say';
		}
		function douban_say()
		{
			echo('<!-- DoubanSay is here -->');
		}

		private function get_douban_user($user)
		{
			$url = 'http://api.douban.com/people/' . $user . '?alt=json&apikey=' . DOUBANSAY_APIKEY;
			$contents=json_decode(file_get_contents($url), true);  

			// we need the big user icon instead of the default small one, if exists
            $big_icon_url = preg_replace('/u(\d+)/i', 'ul$1', $contents['link'][2]['@href']);
            $headers = get_headers($big_icon_url);
            if(strpos($headers[0], '404') === false){
                $contents['link'][2]['@href'] = $big_icon_url;
            }

			return $contents;
		}

		private function get_douban_say($user)
		{
			$max_results = 5;
			$max_results++;
			$url = 'http://api.douban.com/people/' . $user . '/miniblog?alt=json&max-results=' . $max_results . '&apikey=' . DOUBANSAY_APIKEY ;
			//$url = 'http://localhost/index.tmp';

			$contents=json_decode(file_get_contents($url), true);

			//print_r($contents);
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
			//echo ('test'. $user);
			$douban_user = $this->get_douban_user($user);
			$douban_say = $this->get_douban_say($user);

?>
<div id="doubansay">
<div id="profile">
<div class="infobox">
<div class="ex1">
<span></span>
</div>
<div class="bd">
<img src="<?php echo ($douban_user['link'][2]['@href']); ?>" class="userface" alt="" />
<div class="sep-line"></div>
<div class="pl">hello, I'm <?php echo ($douban_user['title']['$t']); ?></div>
<div class="user-info">常居:&nbsp;<a href="http://www.douban.com/location/<?php echo ($douban_user['db:location']['@id']); ?>/"><?php echo ($douban_user['db:location']['$t']); ?></a><br />
</div>
<div class="sep-line"></div>
<div class="user-intro">
<div id="edit_intro"  class="j edtext pl">
<span id="intro_display" ><?php echo ($douban_user['content']['$t']); ?></span>
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
			$counts = count($douban_say['entry']);
			//echo ($counts);
		for ($i = 0; $i < $counts; ++$i)
		{
			$now = $douban_say['entry'][$i];
			echo ('<li class="item">');
			echo ('<div class="text">');

			echo ('</div>');

			echo ('<div class="attachment">');
				echo ($douban_say['entry'][$i]['content']['$t']);
			echo ('</div>');
			echo ('<div class="ft">');
			sscanf($now['published']['$t'], "%*d-%d-%dT%d:%d:%*s", $month, $day, $hour, $minute);
			sscanf($now['id']['$t'], "http://api.douban.com/miniblog/%d", $id);
			//var_dump($month, $day, $hour, $minute);
			//printf("%02d", $minute);
			//var_dump($id);
?>
	<a href="http://shuo.douban.com/#!/<?php echo ($user); ?>/status/<?php echo ($id); ?>" class="time-stamp" target="_blank"><?php printf("%02d", $month); ?>月<?php printf("%02d", $day); ?>日 <?php printf("%02d", $hour); ?>:<?php printf("%02d", $minute); ?></a>
<?php
			echo ('</div>');
		}
?>
		<li class="item">
			<div class="attachment" style="text-align:right">
			<a href="http://www.douban.com/people/<?php echo ($user); ?>">我的豆瓣>></a>
			</div>
		</li>
<?php
/*<li class="item">  
	   <div class="text">             看过这部电影 ★★★★         </div>   
	   <div class="attachment">             <a href="http://dou.bz/30F6tj?uid=51146110" target="_blank" title="">南京！南京！ (2009)</a>      </div>         
		<div class="ft">             <a href="http://shuo.douban.com/#!/zhuxi0511/status/829087456" class="time-stamp" target="_blank">12月27日 21:55</a>             ·             <a href="http://shuo.douban.com/#!/zhuxi0511/status/829087456" target="_blank">回复</a>         </div>    
</li>
 */
?>
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
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$user = esc_attr( $instance[ 'user' ] );
		}
		else {
			$title = __( 'Douban Say', 'text_domain' );
			$user = 'zhuxi0511';
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
<?php 
	}

} // class Foo_Widget

?>
