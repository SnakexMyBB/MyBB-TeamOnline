<?php
/**
    ===============================================================
    @author     : Mateusz 'Snake_' Ciećka;    
    @version    : 2.0 ;
    @mybb       : compatibility MyBB 1.8.x;
    @description: The Plugin displays the team forum at any given time. 
    @homepage   : http://polski-freeroam.pl 
    ===============================================================
 **/

if(!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

$plugins->add_hook('index_start', ['teamOnline', 'teamOnline_start']);

function teamOnline_info()
{
    global $lang;
    $lang->load('config_teamOnline');
    return [
		'name'			=> 'Team Online',
		'description'	=> $lang->settingsGroupDescription,
		'website'		=> 'http://mybboard.pl',
		'author'		=> 'Mateusz "Snake_" Ciećka',
		'authorsite'	=> 'http://polski-freeroam.pl',
		'version'		=> '2.0',
        'codename'      => 'teamonline',
		'compatibility' => '18*'
	];
}
function teamOnline_install()
{
    require_once('teamOnline.install.php');
    TeamOnlineInstaller::install();
}

function teamOnline_is_installed()
{
    global $mybb;
    return (isSet($mybb->settings['teamOnline_groups'])) ?  true : false;
}

function teamOnline_uninstall()
{
    require_once('teamOnline.install.php');
    TeamOnlineInstaller::uninstall();
}


function teamOnline_activate()
{
    require_once('teamOnline.install.php');
    TeamOnlineInstaller::activate();
}

function teamOnline_deactivate()
{
    require_once('teamOnline.install.php');
    TeamOnlineInstaller::deactivate();
}

class teamOnline
{
    public static function teamOnline_start()
    {
	
        global $db, $mybb, $teamOnline, $cache, $groupscache, $lang, $templates, $theme;
            $lang->load('teamOnline');
            $order = $mybb->settings['teamOnline_order'];
            if(empty($order))
            {
                $orderBy = "u.username ASC, s.time DESC";
            }
            else 
            {
                $orderBy = "FIELD(u.usergroup, {$order})";
            }
            if($mybb->settings['teamOnline_maxUsers'] != 0 && $mybb->settings['teamOnline_maxUsers'] > 0)    
            {
                $limit = "LIMIT 0, {$mybb->settings['teamOnline_maxUsers']}";
            }
            $timesearch = TIME_NOW - $mybb->settings['wolcutoffmins']*60;
            $trowbg = alt_trow();
            $sql = "SELECT s.sid, s.ip, s.uid, u.username, s.time, u.avatar, u.usergroup, u.displaygroup, u.invisible
                    FROM ".TABLE_PREFIX."sessions s
                    LEFT JOIN ".TABLE_PREFIX."users u ON (s.uid=u.uid)
                    WHERE u.usergroup IN({$mybb->settings['teamOnline_groups']}) AND time > " . $timesearch . "
                    ORDER BY {$orderBy}
                    {$limit}";
            $query = $db->query($sql);	
            if(!$db->num_rows($query))
            {
                $teamOnline_noOnline = "<tr><td colspan=\"2\">" . $lang->noOnline . "</td></tr>";
                $invisible = 0;
                $membercount = 0;
            }
            if(!is_array($groupscache))
            {
                $groupscache = $cache->read("usergroups");
            }
            if($mybb->settings['teamOnline_collapse'] == 1)
            {
                $sname = "teamOnline_c";
                if(isset($collapsed[$sname]) && $collapsed[$sname] == "display: show;")
				{
					$expcolimage = "collapse_collapsed.png";
					$expdisplay = "display: none;";
					$expthead = " thead_collapsed";                    
				}
				else
				{
					$expcolimage = "collapse.png";
					$expthead = "";
				}
                $collapseTbody = 'style="{$expdisplay}" id="teamOnline_e"';
                $collapseThead = ' <div class="expcolimage"><img src="' . $theme['imgdir'] . '/' . $expcolimage . '" id="teamOnline_img" class="expander" alt="{$expaltext}" title="{$expaltext}" /></div>';
            }
            while($online = $db->fetch_array($query))
            {
                $invisibleMark = '';
                if($online['invisible'] == 1)
                {
                    $invisibleMark = '*';
                }
                if($online['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1 || $online['uid'] == $mybb->user['uid'])
                {
                    $online['avatar_image'] = format_avatar($online['avatar']);
                    $online['username'] = format_name($online['username'], $online['usergroup'], $online['displaygroup']);
                    $online['profilelink'] = build_profile_link($online['username'], $online['uid']) . $invisibleMark;
                    $online['groupname'] = $groupscache[$online['usergroup']]['title'];
                    eval('$teamOnline_row .= "' . $templates->get('teamOnline_row') . '";');   
                }
                
                $invisible += $online['invisible'];                    
                $membercount++;
                if($mybb->settings['teamOnline_showStats'] == 1)
                {
                    $teamOnline_stats =  "<tr><td colspan=\"2\">" . $lang->invisible . " <strong>" . $invisible . "</strong></td></tr>
                    <tr><td colspan=\"2\">" . $lang->online . " <strong>" . $membercount . "</strong></td></tr>";
                }
             }
        eval('$teamOnline = "' . $templates->get('teamOnline_main') . '";');   
     }    
}