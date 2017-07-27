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

if (!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class TeamOnlineInstaller
{

    public static function install()
    {
        global $db, $mybb, $lang;
        self::uninstall();
        $lang->load('config_teamOnline');
        $setting_group = [
            'name' => 'teamOnline_groupSettings',
            'title' => 'Team Online',
            'description' => $lang->settingsGroupDescription,
            'disporder' => 5, 
            'isdefault' => 0
        ];

        $gid = $db->insert_query("settinggroups", $setting_group);
        
        $setting_array = [
            'teamOnline_groups' => [
                'title'         => $lang->titleSetting1,
                'description'   => $lang->descriptionSetting1,
                'optionscode'   => 'groupselect',
                'value'         => 1, 
                'disporder'     => 1
            ],
            'teamOnline_order' => [
                'title'         => $lang->titleSetting2,
                'description'   => $lang->descriptionSetting2,
                'optionscode'   => "text",
                'value'         => "4,3,6",
                'disporder'     => 2
            ],
            'teamOnline_maxUsers' => [
                'title'         => $lang->titleSetting3,
                'description'   => $lang->descriptionSetting3,
                'optionscode'   => 'numeric',
                'value'         => 0,
                'disporder'     => 3
            ],
            'teamOnline_showStats' => [
                'title'         => $lang->titleSetting4,
                'description'   => $lang->descriptionSetting4,
                'optionscode'   => 'yesno',
                'value'         => 1,
                'disporder'     => 4
            ],
            'teamOnline_collapse' => [
                'title'         => $lang->titleSetting5,
                'description'   => $lang->descriptionSetting5,
                'optionscode'   => 'yesno',
                'value'         => 1,
                'disporder'     => 5
            ]
        ];

        foreach($setting_array as $name => $setting)
        {
            $setting['name'] = $name;
            $setting['gid'] = $gid;

            $db->insert_query('settings', $setting);
        }

        rebuild_settings();

    }

    public static function uninstall()
    {
        global $db;

        $db->delete_query('settings', "name LIKE 'teamOnline_%'");
        $db->delete_query('settinggroups', "name = 'teamOnline_groupSettings'");

        rebuild_settings();
    }

    public static function activate()
    {
        global $db;

        $mainTemplateHTML = '<table border="0" cellspacing="' . $theme['borderwidth'] . '" cellpadding="' . $theme['tablespace'] . '" class="tborder">
    <tr>
        <td class="thead{$expthead}" colspan="2">
            {$collapseThead}
            <strong>{$lang->title}</strong>
        </td>
	</tr>
	<tbody {$collapseTbody}>
		{$teamOnline_row}
		{$teamOnline_noOnline}
	</tbody>
	{$teamOnline_stats}
</table>
<br />';

        $mainTemplate = [
            'title' => 'teamOnline_main',
            'template' => $db->escape_string($mainTemplateHTML),
            'sid' => '-1',
            'version' => '',
            'dateline' => time()
        ];

        $rowTemplateHTML = '<tr>
        <td class="{$trowbg}">
            <img src="{$online[\'avatar_image\'][\'image\']}" style="max-width: 35px; max-height: 35px; text-align:center;" />
        </td>
		<td class="{$trowbg}" style="width: 100%;">
            {$online[\'profilelink\']}
            <br />
            <span>{$online[\'groupname\']}</span>
        </td>
</tr>';

        $rowTemplate = [
            'title' => 'teamOnline_row',
            'template' => $db->escape_string($rowTemplateHTML),
            'sid' => '-1',
            'version' => '',
            'dateline' => time()
        ];
        
        $db->insert_query('templates', $rowTemplate);
        $db->insert_query('templates', $mainTemplate);

    }
    
    public static function deactivate()
    {
        global $db;
        $db->delete_query("templates", "title LIKE 'teamOnline_%'");
    }
}