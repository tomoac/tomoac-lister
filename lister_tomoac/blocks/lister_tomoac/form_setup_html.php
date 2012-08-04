<?php  defined('C5_EXECUTE') or die(_("Access Denied."));

define('COLS_ORDER', 1);
define('ROWS_ORDER', 2);
define('ITEM_FORMAT', 3);

	$debug = 'FALSE';
//	$debug = 'TRUE';

	$db = Loader::db();

	$c = Page::getCurrentPage();
	$LcID = $c->getCollectionID();
	$LbID = $controller->bID;
	$bid = $controller->bID;

	$form = Loader::helper('form');

	list(	$formbid, 
			$formcid, 
			$editflag, 
			$regdateflag, 
			$reguserflag, 
			$pplines, 
			$msqidar) = $controller->get_lister_items( $LcID, $bid );

	// 有効なフォームをリストアップ
	$rows = $controller->get_form_list();

	foreach($rows as $row)
		foreach($row as $key=>$val)
			if($key == 'bID')
				$FbIDar[] = $val;				// $FbIDar: List of Form bID
	$ctab = array_search($formbid, $FbIDar);	// $ctab: Current/Initial tab position

	$jss = '
	<script type="text/javascript">
		var ccm_fpActiveTab = "ccm-button-@@";	
		$("#ccm-button-tabs a").click(function() {
			$("li.ccm-nav-active").removeClass(\'ccm-nav-active\');
			$("#" + ccm_fpActiveTab + "-tab").hide();
			ccm_fpActiveTab = $(this).attr(\'id\');
			$(this).parent().addClass("ccm-nav-active");
			$("#" + ccm_fpActiveTab + "-tab").show();
		});
	</script>';
?>

<ul class="ccm-dialog-tabs" id="ccm-button-tabs">
<?php
	$bidas = array();
	$i = 0;
	// フォームのタブを表示
	foreach($rows as $row) {
		$bidas[] = $row['bID'];		// pooling Form bID
		if($i == $ctab)
			echo '<li class="ccm-nav-active">';
		else
			echo '<li>';
		echo '<a href="javascript:void(0)" id="ccm-button-'.$i.'">'.$row['surveyName'].' (bID:'.$FbIDar[$i].')</a></li>';
		$i++;
	}
?>
</ul>

<div style="text-align: left" >

<?php
	$active = 0;
	foreach($rows as $row) {
		$bid = $bidas[$active];		// pooling Form bID
		if($active == $ctab) {
			echo '<div id="ccm-button-'.$active.'-tab" style="">';
			$jss = str_replace('@@', $active, $jss);
		} else
			echo '<div id="ccm-button-'.$active.'-tab" style="display:none">';

		// フォーム選択のラジオボタン
		echo '<p>';
		if($bid == $formbid)
			$thisform = $bid;
		else
			$thisform = 0;
		echo $form->radio('fID', $bid, $thisform);
		echo t('This FORM');
		if($debug)
			echo '(bID:'.$thisform.')';

		// 編集可否のチェックボックス
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		if($bid == $formbid) {
			if($editflag == 0)
				$checked = 0;
			else
				$checked = 1;
		} else
			$checked = 0;
		echo $form->checkbox('editFlag'.$bid, 1, $checked);	// enable/disable edit
		echo t('Enable Edit');

		// 登録日付の表示可否のチェックボックス
		echo '<br />';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		if($bid == $formbid) {
			if($regdateflag == 0)
				$checked = 0;
			else
				$checked = 1;
		} else
			$checked = 0;
		echo $form->checkbox('regdateFlag'.$bid, 1, $checked);	// enable/disable registration date
		echo t('Show Registration Date');

		// 登録ユーザの表示可否のチェックボックス
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		if($bid == $formbid) {
			if($reguserflag == 0)
				$checked = 0;
			else
				$checked = 1;
		} else
			$checked = 0;
		echo $form->checkbox('reguserFlag'.$bid, 1, $checked);	// enable/disable registration user
		echo t('Show Registration User');

		// 一覧表の表示行数
		echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;';
		if($pplines == 0)
			$pplines = 10;
		echo t('Line par page: ');
		echo $form->text('pplines'.$bid, $pplines, array('size' => '2'));		// display line number

		echo '</p>';

		$rows2 = $controller->get_question_list( $bid );

		// １つのフォーム処理
		echo '<table>';
		// title lines
		echo '<tr>';
		echo '<td></td>';
		echo '<td>'.t('Column Order<br />(0: no display)').'</td>';
		echo '<td>'.t('Row Order<br />(0: Inapplicable)').'</td>';
		echo '<td>'.t('Display<br />&nbsp;&nbsp;format').'</td>';
		echo '</tr>';

		$i = 0;
		$itemc = 0;
		$min = -1;
		$max = -1;
		foreach($rows2 as $row2) {
			foreach($row2 as $key=>$val) {
			// １つのアイテム処理
				if($key == 'msqID')
					$msqid = $val;
				else if($key == 'inputType')
					$inputType = $val;
				else if($key == 'question') {
					echo '<tr><td>'.$val;
					if($debug)
						echo '(msqID:'.$msqid.')';
					echo '</td><td>';
					$orders = $controller->get_Number_by_msqID( COLS_ORDER, $LcID, $LbID, $msqid );
					if($orders < 0)	$orders = $itemc+1;
					echo $form->text('bID_'.$bid.'_'.$msqid, $orders, array('size'=>3));
					echo '</td><td>';
					$orders = $controller->get_Number_by_msqID( ROWS_ORDER, $LcID, $LbID, $msqid );
					if($orders < 0)	$orders = $itemc+1;
					echo $form->text('sID_'.$bid.'_'.$msqid, $orders, array('size'=>3));
					echo '</td><td>';
					$fmt = $controller->get_String_by_msqID( ITEM_FORMAT, $LcID, $LbID, $msqid );
					if(strlen($fmt) == 0) {
						switch($inputType) {
						case 'jname':
							$fmt = '%s %s (%s %s)';
							break;
						case 'postno':
							$fmt = '〒%03d-%04d %s %s %s';
							break;
						default:
							$fmt = '%s';
							break;
						}
					}
					echo $form->text('gID_'.$bid.'_'.$msqid, $fmt, array('size'=>16));
					echo '</td></tr>';
					if($min == -1)	$min = $msqid;
					if($max == -1)	$max = $msqid;
					if($min > $msqid)	$min = $msqid;
					if($max < $msqid)	$max = $msqid;
					$itemc++;
				}
			}
			$i++;
		}
		echo $form->hidden('tID_'.$bid, $itemc);	// item count
		echo $form->hidden('iID_'.$bid, $min);		// start number of items
		echo $form->hidden('xID_'.$bid, $max);		// end nubber of items
		echo $form->hidden('LcID', $LcID);			// page cID of Lister

		echo '</table>';
		echo '</div>';
		$active++;
	}
	// 選択したページのcIDのhiddenタグが作られる
	/*
	$form = Loader::helper('form/page_selector');
	if($formcid > 0)
		echo $form->selectPage('FormcID', $formcid);
	else
		echo $form->selectPage('FormcID');
	*/
	echo $jss;

?>
</div>
