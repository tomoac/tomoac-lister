<?php  defined('C5_EXECUTE') or die(_("Access Denied."));

	$debug = 'FALSE';
//	$debug = 'TRUE';

	$db = Loader::db();

	$c = Page::getCurrentPage();
	$lcid = $c->getCollectionID();
	$bid = $controller->bID;

	$form = Loader::helper('form');

	list(	$formbid, 
			$formcid, 
			$colsOrder,
			$rowsOrder, 
			$editflag, 
			$regdateflag, 
			$reguserflag, 
			$pplines, 
			$msqidar) = $controller->get_lister_items( $lcid, $bid );

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
		echo '<tr><td></td><td>Column Order<br />(0: no display)</td><td>Row Order<br />(0: Inapplicable)</td></tr>';

		$cls = json_decode( $colsOrder );
		$rws = json_decode( $rowsOrder );

//		error_log('colsOrder='.$colsOrder,0);
//		error_log('rowsOrder='.$rowsOrder,0);

		$i = 0;
		$itemc = 0;
		$min = -1;
		$max = -1;
		foreach($rows2 as $row2) {
			foreach($row2 as $key=>$val) {
			// １つのアイテム処理
				if($key == 'msqID')
					$msqid = $val;
				if($key == 'question') {
					echo '<tr><td>'.$val;
					if($debug)
						echo '(msqID:'.$msqid.')';
					echo '</td><td>';
					echo $form->text('bID_'.$bid.'_'.$msqid, $controller->get_colsOrderNo( $cls,$msqid ), array('size'=>3));
					echo '</td><td>';
					echo $form->text('sID_'.$bid.'_'.$msqid, $controller->get_rowsOrderNo( $rws,$msqid ), array('size'=>3));
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
		echo $form->hidden('LcID', $lcid);			// page cID of Lister

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
