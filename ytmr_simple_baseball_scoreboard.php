<?php
/*
Plugin Name: Simple Baseball Score Board
Plugin URI: https://php.dogrow.net/wordpressplugin/simple-baseball-scoreboard/
Description: Generate baseball scoreboard from shortcode
Version: 1.3
Author: DOGROW.NET
Author https://php.dogrow.net/
License: GPL2
*/
////////////////////////////////////////////////////////////////////////
if(class_exists('YTMRBBScoreBoard')){
  $obj = new YTMRBBScoreBoard();
}
////////////////////////////////////////////////////////////////////////
class YTMRBBScoreBoard {
  private $m_setting_group;
  private $m_option_name;
  private $m_options;
  //////////////////////////////////////////////////////////////////////
  public function __construct(){
    $this->m_setting_group = 'YTMRBBScoreBoard-setting-group';
    $this->m_option_name   = 'YTMRBBScoreBoard';
    $this->m_options = array('border_line_color'  =>array('t'=>'Border line color','v'=>'#bbbb00')
                            ,'border_line_width'  =>array('t'=>'Border line width','v'=>'3px')
                            ,'background_color'   =>array('t'=>'Background color', 'v'=>'#3f7d39')
                            ,'box_color'          =>array('t'=>'Input box color',  'v'=>'#285b2b')
                            ,'text_color'         =>array('t'=>'Text color',       'v'=>'#ffffff')
                  );
    //------------------------------------------------------------------
    add_action('wp_head',    array($this,'proc_output_css'), 9999);
    add_action('admin_head', array($this,'proc_output_css'), 9999);
    //------------------------------------------------------------------
    add_shortcode('ytmr_bb_scoreboard', array($this, 'proc_shortcode'));
    add_filter('widget_text', 'do_shortcode');
    //------------------------------------------------------------------
    add_action('admin_menu', array($this, 'proc_create_menu'));
    //------------------------------------------------------------------
    add_action('admin_init', array($this,'proc_register_settings'));
    //------------------------------------------------------------------
    add_action('admin_enqueue_scripts', array($this, 'proc_add_script'));
    //------------------------------------------------------------------
    register_activation_hook(  __FILE__, array($this, 'proc_plugin_activate'));
    register_deactivation_hook(__FILE__, array($this, 'proc_plugin_deactivate'));
  }
  //////////////////////////////////////////////////////////////////////
  function proc_add_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('ytmr_simple_baseball_scoreboard_script', plugins_url('js/ytmr_simple_baseball_scoreboard.js', __FILE__), array('jquery'), '1.0.0', TRUE);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_plugin_activate(){
    add_option($this->m_option_name, $this->m_options);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_plugin_deactivate(){
    delete_option($this->m_option_name);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_output_css(){
    //------------------------------------------------------------------
    $ary_set = get_option($this->m_option_name);
    foreach($ary_set as &$ary_tv){
      $ary_tv['v'] = esc_attr($ary_tv['v']);
    }
    //------------------------------------------------------------------
echo <<< EOM

<style type="text/css">
div#YTMRBBScoreBoard{
  background-color: {$ary_set['background_color']['v']};
  border-color: {$ary_set['border_line_color']['v']};
  border-width: {$ary_set['border_line_width']['v']};
  border-style: solid;
  margin: 3px; padding: 3px;
}
div#YTMRBBScoreBoard table{
  background: transparent !important;
  border: none !important;
  margin:0 !important;
  padding:0 !important;
}
div#YTMRBBScoreBoard tr,
div#YTMRBBScoreBoard td{
  background: transparent !important;
  border: none !important;
}
div#YTMRBBScoreBoard td{
  text-align: center;
  line-height: 1.5;
  padding: 4px;
  color: {$ary_set['text_color']['v']};
}
div#YTMRBBScoreBoard div.inner{
  padding: 4px 2px;
  background-color: {$ary_set['box_color']['v']};
}
</style>

EOM;
    //------------------------------------------------------------------
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_shortcode( $args ){
    return $this->sub_display_table($args);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_create_menu() {
    add_submenu_page('options-general.php', 'Simple BBScoreboard', 'Simple BBScoreboard', 'administrator', __FILE__, array($this, 'proc_display_settings_page'));
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_register_settings() {
    register_setting($this->m_setting_group, $this->m_option_name, array($this, 'proc_handle_sanitization'));
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_handle_sanitization($ary_set) {
    $ary_options = $this->m_options;
    foreach($ary_set as $key => &$ary_tv){
      $ary_options[$key]['v'] = esc_attr($ary_tv['v']);
    }
    return $ary_options;
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_display_settings_page() {
    //------------------------------------------------------------------
    $ary_set = get_option($this->m_option_name);
    foreach($ary_set as &$ary_tv){
      $ary_tv['v'] = esc_attr($ary_tv['v']);
    }
    //------------------------------------------------------------------
    $ary_bw_sel = array('1px'=>'', '2px'=>'', '3px'=>'');
    $ary_bw_sel[$ary_set['border_line_width']['v']] = 'selected';
    //------------------------------------------------------------------
    $args = array('fsize'=>'1.2', 'width'=>'600px', 'tm1'=>'GreenSox', 'tm2'=>'Monkeys', 'scr1'=>'0/0/1/1/0/3/0', 'scr2'=>'1/0/0/2/2/1/X');
    $html_scrboard = $this->sub_display_table($args);
    //------------------------------------------------------------------
echo <<< EOM
<div class="wrap">
<h2>Simple Baseball Scoreboard</h2>
<h2>1. Usage</h2>
<p>Short code : <span style="background:#fff;color:#00f;padding:3px 5px;font-size:1.2rem">[ytmr_bb_scoreboard]</span></p>
<p>Parameters : <br />
- fsize : font size [rem]<br />
- tm1 : team name of the bat first<br />
- tm2 : team name of the field first<br />
- scr1, scr2 : run of the inning (separator is "/")<br />
</p>
<p>sample : <span style="background:#fff;color:#00f;padding:3px 5px;font-size:1.2rem">[ytmr_bb_scoreboard fsize="1.2" width="600px" tm1="GreenSox" tm2="Monkeys" scr1="0/0/1/1/0/3/0" scr2="1/0/0/2/2/1/X"]</span></p>
{$html_scrboard}
<h2 style="margin-top:2.5rem">2. Settings</h2>
<form id="YTMRBBScoreBoard_form" method="post" action="options.php">
EOM;
    settings_fields($this->m_setting_group);
    do_settings_sections($this->m_setting_group);
echo <<< EOM
  <table class="form-table">
    <tr>
      <th>{$ary_set['border_line_width']['t']}</th>
      <td>
        <select name="{$this->m_option_name}[border_line_width][v]">
          <option value="1px" {$ary_bw_sel['1px']}>thin</option>
          <option value="2px" {$ary_bw_sel['2px']}>middle</option>
          <option value="3px" {$ary_bw_sel['3px']}>thick</option>
        </select>
      </td>
    </tr>
    <tr>
      <th>{$ary_set['border_line_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[border_line_color][v]" value="{$ary_set['border_line_color']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['background_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[background_color][v]" value="{$ary_set['background_color']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['box_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[box_color][v]" value="{$ary_set['box_color']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['text_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[text_color][v]" value="{$ary_set['text_color']['v']}">
      </td>
    </tr>
  </table>
EOM;
  submit_button();
echo <<< EOM
</form>
</div>
EOM;
  }
  //////////////////////////////////////////////////////////////////////
  // args['fsize'] : text size [rem]
  // args['width'] : whole width
  // args['tm1']  : team name #1
  // args['tm2']  : team name #2
  // args['scr1'] : score #1 , separator='/'  ex) 0/0/1/0/0/1
  // args['scr2'] : score #2
  public function sub_display_table($args){
    $fsize = (isset($args['fsize']))? $args['fsize'] : '1';
    $tm1   = (isset($args['tm1']))? $args['tm1'] : 'team1';
    $tm2   = (isset($args['tm2']))? $args['tm2'] : 'team2';
    $width = (isset($args['width']))? $args['width'] : '100%';
    //------------------------------------------------------------------
    $html = "";
    $size = 'font-size:'.$fsize.'rem !important;';
    $table_class = 'tc_'.str_replace('.','_',$args['fsize']);
    //------------------------------------------------------------------
    $ary_tm = array();
    $ary_tm[] = $tm1;
    $ary_tm[] = $tm2;
    //------------------------------------------------------------------
    $ary_scr = array();
    $ary_scr[] = explode('/', $args['scr1']);
    $ary_scr[] = explode('/', $args['scr2']);
    $nScr = max(count($ary_scr[0]), count($ary_scr[1]));
    //------------------------------------------------------------------
    // display innings
    $html .= '<tr><td></td>';
    for($i=1 ; $i <= $nScr ; $i++){
      $html .= '<td>'.$i.'</td>';
    }
    $html .= '<td>R</td></tr>';
    //------------------------------------------------------------------
    // display score
    foreach($ary_scr as $idx => $scr){
      $html .= '<tr><td><div class="inner">'.$ary_tm[$idx].'</div></td>';
      for($i=0 ; $i < $nScr ; $i++){
        $html .= '<td><div class="inner">'.$scr[$i].'</div></td>';
      }
      $html .= '<td><div class="inner">'.array_sum($scr).'</div></td></tr>';
    }
    //------------------------------------------------------------------
return <<< EOM
<style type="text/css">
div#YTMRBBScoreBoard{
  max-width: 100% !important;
}
div#YTMRBBScoreBoard .{$table_class} td{
  {$size}
}
</style>
<div id="YTMRBBScoreBoard" style="width: {$width} !important"><table class="{$table_class}" style="width:100%">{$html}</table></div>
EOM;
  }
}     // end of class
?>
