<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>


<?php
	$backupdir = "/dashboard/form_tomoac";
	$subname = t('Backup Form Items of \'Tomoac Form 5\'');
	$ver = substr(Config::get('SITE_APP_VERSION'),0,4);	// check current version

	if($ver == '5.4.') {
		// version 5.4.x
		echo '<h1><span>' . $subname . '</span></h1>';
		echo '<div class="ccm-dashboard-inner">';
		echo '<div class="ccm-addon-list-wrapper">';
	}
	if($ver == '5.5.') {
		// version 5.5.x
		$h = Loader::helper('concrete/dashboard');
		echo $h->getDashboardPaneHeaderWrapper( $subname );
	}

	$db = Loader::db();
	$js = Loader::helper('json');
	$fh = Loader::helper('file');
	$rows = $db->query("SELECT surveyName,bID,questionSetId FROM btFormTomoac");

	if($ver == '5.4.') {
		$title = '
			<div class="ccm-spacer">&nbsp;</div>
			<div style="margin:0px; padding:0px; width:100%; height:auto" >	
			<table class="grid-list" width="100%" cellspacing="1" cellpadding="0" border="0">
			<tr>
				<td class="subheader">'.t('Form Name').'</td>
				<td class="subheader">'.t('BlockID (bID)').'</td>
				<td class="subheader">'.t('ItemID (questionSetId)').'</td>
				<td class="subheader"></td>
			</tr>
		';
	}
	if($ver == '5.5.') {
		$title = '
			<div style="margin:0px; padding:0px; width:100%; height:auto" >
			<div class="ccm-ui">
			<table class="zebra-striped" border="1">
			<tr>
				<td class="header">'.t('Form Name').'</td>
				<td class="header">'.t('BlockID (bID)').'</td>
				<td class="header">'.t('ItemID (questionSetId)').'</td>
				<td class="header"></td>
			</tr>
		';
	}
	$html = '';
	foreach($rows as $row) {
		if($html == '')
			echo $title;
		foreach($row as $key=>$val) {
			switch($key) {
			case 'surveyName':
				$html.= '<tr>';
				$html.= '<td>'.$val.'</td>';
				$surveyName = $val;
				break;
			case 'bID':
				$html.= '<td>'.$val.'</td>';
				$bid = $val;
				break;
			case 'questionSetId':
				$html.= '<td>'.$val.'</td>';
				$html.= '<form action="'.View::url($backupdir.'/backup','backup_form').'" method="post">'."\n";
				$html.= '<td>&nbsp;';
				$html.= '<input type="hidden" name="function" value="backup">';
				$html.= '<input type="hidden" name="surveyName" value="'.$surveyName.'">'."\n";
				$html.= '<input type="hidden" name="bID" value="'.$bid.'">'."\n";
				$html.= '<input type="hidden" name="questionSetId" value="'.$val.'">'."\n";
				$html.= '<input type="submit" name="exec" value="'.t('Backup').'">'."\n";
				$html.= '</td>';
				$html.= '</form>';
				$html.= '</tr>';
				break;
			}
		}
	}
	if($html != '') {
		$html.= '</table>';
		$html.= '</div>';
		$html.= '</div>';
	} else {
		$html.= t('You have not created any forms by Tomoac Form 5.');
	}
	echo $html;

?>
		</table>

<?php
		if($ver == '5.4.') {
			echo '
				<div style="margin:0px; padding:0px; width:100%; height:auto" >	
				<br />
				<table class="grid-list" width="100%" cellspacing="1" cellpadding="0" border="0">
			';
		}
		if($ver == '5.5.') {
			echo '
				<div style="margin:0px; padding:0px; width:100%; height:auto" >
				<div class="ccm-ui">
				<table class="zebra-striped">
			';
		}
		$files = $fh->getDirectoryContents( DIR_BASE . "/files/tomoacform5" );
		rsort($files);
		foreach($files as $fn) {
			$html = '';
			$html.= '<tr>';
			$html.= '<td>'. $fn . '</td>';
			$html.= '<form action="'.View::url($backupdir.'/backup','backup_form').'" method="post">'."\n";
			$html.= '<td>';
			$html.= '<input type="hidden" name="function" value="download">';
			$html.= '<input type="hidden" name="filename" value="'.$fn.'">'."\n";
			$html.= '<input type="submit" name="exec" value="'.t('Download').'">'."\n";
			$html.= '</td>';
			$html.= '</form>';
			$html.= '<form action="'.View::url($backupdir.'/backup','backup_form').'" method="post">'."\n";
			$html.= '<td>';
			$html.= '<input type="hidden" name="function" value="delete">';
			$html.= '<input type="hidden" name="filename" value="'.$fn.'">'."\n";
			$html.= '<input type="submit" name="exec" value="'.t('Delete').'">'."\n";
			$html.= '</td>';
			$html.= '</form>';
			$html.= '</tr>';
			echo $html;
		}
?>
		</table>
		</div>
		</div>
	</div>
</div>
