<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class ListerTomoacBlockController extends BlockController {
	protected $btTable = 'btTomoacLister';
	protected $btInterfaceWidth = "600";
	protected $btInterfaceHeight = "500";
	

	public function getBlockTypeDescription() {
		return t('Database Lister by tomoac');
	}
	
	public function getBlockTypeName() {
		return t('Darabase Lister');
	}	 

	/*====================================================*
	 ***					save						***
	 *====================================================*/
	function save( $data ) {

		$db = Loader::db();

		$fid = $data['fID'];
		$cid = $this->get_cid_from_bid($fid);

		$editflag = $data['editFlag'.$fid];
		$regdateflag = $data['regdateFlag'.$fid];
		$reguserflag = $data['reguserFlag'.$fid];
		$pplines = $data['pplines'.$fid];
		$lcid = $data['LcID'];
		$itemc = $data['tID_'.$fid];
		$itmin = $data['iID_'.$fid];	// minimum msqID
		$itmax = $data['xID_'.$fid];	// maxmun msqID
//		if($itmin < 0)   $itmin = 0;
//		if($itmax > 100) $itmax = 100;
		$lbid = intval($this->bID);

		$vid = 0;
		$sql = "SELECT max(LbID) FROM btTomoacLister WHERE LcID=".$lcid;
		error_log($sql,0);
		$rows = $db->query($sql);
		$row = $rows->fetchrow();
 		foreach($row as $key=>$val) {
 			$vid = $val + 1;
 			break;
 		}
//		error_log('/itemc='.$itemc.'/itmin='.$itmin.'/itmax='.$itmax.'/',0);
		for($i=$itmin; $i<=$itmax; $i++) {
			if($data['bID_'.$fid.'_'.$i] == TRUE) {
				$position = $data['bID_'.$fid.'_'.$i];
				$vals = array( 0, intval($fid), intval($i), intval($position), intval($cid), intval($lcid), intval($lbid), 
								intval($editflag), intval($regdateflag), intval($reguserflag), intval($pplines) );
				$sql = "INSERT INTO btTomoacLister (mID, FbID, msqID, position, cID, LcID, LbID, 
													editFlag, regdateFlag, reguserFlag, pplines ) 
										values (?,?,?,?,?,?,?,?,?,?,?)";
				$db->query($sql, $vals);

				if(--$itemc == 0)
					break;
			}
		}
	}

	/*====================================================*
	 ***			get_lister_items (add & edit)		***
	 *====================================================*/
	function get_lister_items( $cid, $bid ) {

		// 表示対象のフィールド番号（msqID）を拾い出す。
		// 		結果は、$msqidar[] に入れる
		if($bid > 0) {
			$db = Loader::db();

			$sql = "SELECT * FROM btTomoacLister WHERE LcID=".$cid." AND LbID=".$bid;
//			error_log($sql,0);
			$rows = $db->query($sql);
			foreach($rows as $row) {
				foreach($row as $key=>$val) {
					if($key == 'FbID') {
						$setbid = $val;
						//error_log('setbid='.$setbid,0);
					} else if($key == 'cID')
						$formcid = $val;
					else if($key == 'editFlag')
						$editflag = $val;
					else if($key == 'regdateFlag')
						$regdateflag = $val;
					else if($key == 'reguserFlag')
						$reguserflag = $val;
					else if($key == 'pplines')
						$pplines = $val;
					else if($key == 'msqID') {
						$msqidar[] = $val;
						//error_log('msqidar='.$val,0);
					}
				}
			}
		}
		return array( $setbid, $formcid, $editflag, $regdateflag, $reguserflag, $pplines, $msqidar );
	}
	/*====================================================*
	 ***			get_form_list (add & edit)			***
	 *====================================================*/
	function get_form_list() {
		// 有効なフォームをリストアップ
		$db = Loader::db();
		$sql = "SELECT
					btFormTomoac.surveyName,
					btFormTomoac.bID,
					btFormTomoac.questionSetId,
					CollectionVersions.cvDateCreated
				FROM CollectionVersionBlocks 
					INNER JOIN CollectionVersions 
						ON CollectionVersionBlocks.cID=CollectionVersions.cID 
							AND CollectionVersionBlocks.cvID=CollectionVersions.cvID 
					INNER JOIN btFormTomoac 
						ON CollectionVersionBlocks.bID=btFormTomoac.bID 
				WHERE CollectionVersions.cvIsApproved=1
			";
		//error_log($sql,0);
		$rows = $db->Execute($sql);
		return $rows;
	}
	/*====================================================*
	 ***		 get_question_list (add & edit)			***
	 *====================================================*/
	function get_question_list( $bid ) {
		// 有効なフォームをリストアップ
		$db = Loader::db();
		$sql = "SELECT msqID,question FROM btFormTomoacQuestions WHERE bID=".$bid." ORDER BY position,msqID";
		//error_log($sql,0);
		$rows = $db->Execute($sql);
		return $rows;
	}
	/*====================================================*
	 ***		 		get_cid_from_bid				***
	 *====================================================*/
	function get_cid_from_bid( $bid ) {
		if($bid == 0)
			return 0;
		$db = Loader::db();
		$sql = "SELECT cID FROM CollectionVersionBlocks WHERE bID=".$bid." LIMIT 1";
		$rows = $db->Execute($sql);
		$row = $rows->fetchrow();
		return $row{'cID'};
	}

	/* ============ Format from database date ============*/
	function fmt_datetime( $datetimes ) {
		$dt = explode(' ',$datetimes);
		$d = explode('-',$dt[0]);
		$t = explode(':',$dt[1]);
		$timestamp = mktime($t[0],$t[1],$t[2],$d[1],$d[2],$d[0]);
		$created = $timestamp;
//		return date('Y年n月j日 H時i分s秒', $timestamp);		// japanese
		return date('j F, Y H:i:s', $timestamp);			// english
	}
	/* ============ Format from database date ============*/
	function get_username( $uid ) {
		$uo = UserInfo::getByID( $uid );
		return $uo->getUserName();
	}

	/*====================================================*
	 ***					view						***
	 *====================================================*/
	function view() {

		$debug = FALSE;	// no debug mode //
		$debug = TRUE;	// debug mode //

		$db = Loader::db();

		$lc = Page::getByID($linkID);
		$c = Page::getCurrentPage();
		$path = $lc->getCollectionPath();
		if(!empty($path)) {
			$path = "/index.php?cID=".$lc->cID; 
		}
		$cid = $c->getCollectionID();
		$lcid = $cid;

		$nowcid = $c->getCollectionID();
		$nowbid = $this->bID;

		$givenbid = $_POST['bID'];

		if($debug) {
			error_log('(now) cID='.$nowcid,0);
			error_log('(now) bID='.$nowbid,0);
			error_log('(given) bID='.$givenbid,0);
		}

		// Delete
		if($_POST['function'] == 'delete') {
			$sql = "DELETE FROM btFormTomoacAnswers WHERE asID=".$_POST['asID']."";
			$db->query($sql);
			$sql = "DELETE FROM btFormTomoacAnswerSet WHERE asID=".$_POST['asID']."";
			$db->query($sql);
		}

		if($nowbid > 0) {
			$sql = "SELECT * FROM btTomoacLister WHERE LcID=".$nowcid." AND LbID=".$nowbid;
			$rows = $db->query($sql);
			foreach($rows as $row) {
				foreach($row as $key=>$val) {
					if($key == 'FbID')
						$formbid = $val;
					if($key == 'cID')
						$formcid = $val;
					if($key == 'editFlag')
						$editflag = $val;
					if($key == 'regdateFlag')
						$regdateflag = $val;
					if($key == 'reguserFlag')
						$reguserflag = $val;
					if($key == 'pplines') {
						$pplines = $val;
						if($pplines <= 0)
							$pplines = 10;
					}
				}
			}
		}
/*
		$tc = Page::getByID($cID);
		$tc=Page::getCurrentPage();
    	$p = $tc->getCollectionPath();
		$s = $p->getCollectionPathFromID($formcid);
//		$s = Page::getCollectionPathFromID($formcid);
		error_log($s,0);
*/
//		if($formcid > 0 && $formbid > 0)
		if($formbid > 0)
			$bid = $formbid;
		else
			$bid = $givenbid;

		if($debug)
			$head = '<p align="center">'.$_POST['surveyName'].
					'This cID('.$nowcid.')bID('.$nowbid.')'.
					' Form cID('.$formcid.')bID:'.$formbid.')'.
					' (Given=function:'.$_POST['function'].
					'/lister:'.$_POST['lister'].
					'/bID:'.$givenbid.
					'/asID:'.$_POST['asID'].
				')'.'</p>';

		if($bid == 0) {

	/*====================Output Form Table========================*/

			$title = '';
			$html = '';

/*
			$sql = "SELECT btFormTomoac.bID,btFormTomoac.surveyName FROM CollectionVersionBlocks 
					INNER JOIN CollectionVersions ON CollectionVersionBlocks.cID=CollectionVersions.cID
						   AND CollectionVersionBlocks.cvID=CollectionVersions.cvID
					INNER JOIN btFormTomoac ON CollectionVersionBlocks.bID=btFormTomoac.bID
				WHERE CollectionVersions.cvIsApproved=1";
*/
			$sql = "SELECT CollectionVersions.cID,btFormTomoac.bID,btFormTomoac.surveyName,CollectionVersions.cvDatePublic FROM CollectionVersionBlocks 
						INNER JOIN CollectionVersions ON CollectionVersionBlocks.cID=CollectionVersions.cID
						   AND CollectionVersionBlocks.cvID=CollectionVersions.cvID
						INNER JOIN btFormTomoac ON CollectionVersionBlocks.bID=btFormTomoac.bID
					WHERE CollectionVersions.cvIsApproved=1
					ORDER BY CollectionVersions.cvDatePublic DESC
					";
			$rows = $db->query($sql);

			$title .= '<td>'.t('Page Title').'</td>';
			$title .= '<td>'.t('Form Name').'</td>';
			$title .= '<td>'.t('Created Date').'</td>';
			$title .= '<td>'.t('Operation').'</td>';

			foreach($rows as $row) {
				$html .= '<tr>';
				foreach($row as $key=>$val) {
/*
					if($key == 'bID')
						if($setbid != $val)
							break;
*/
					if($key == 'cID') {
						$html .= '<td>';
						$page = Page::getByID($val);
						$html .= $page->getCollectionName() . '&nbsp;(cID:'.$val.')';
						$html .= '</td>'."\n";
					} else if($key == 'bID') {
						$bid = $val;
					} else if($key == 'surveyName') {
						$html .= '<td>';
						$surveyname = $val;
						$html .= $val . '&nbsp;(bID:'.$bid.')';
						$html .= '</td>'."\n";
					} else if($key == 'cvDatePublic') {
						$cvdatepublic = $val;
						$html .= '<td>';
						$html .= $val;
						$html .= '</td>'."\n";
					}
				}
/*				if($key == 'bID' && $setbid != $val)
					continue;
*/				$html .= '<td>';
				$html .= '<form name="form'.$bid.'" method="post">';
				$html .= '<input type="hidden" name="bID" value="'.$bid.'">';
				$html .= '<input type="hidden" name="surveyName" value="'.$surveyname.'">';
				$html .= '<a onclick="document.form'.$bid.'.submit();">'.t('view').'</a>';
				$html .= '</form>';
				$html .= '</td>';
				$html .= '</tr>'."\n";
			}
		}
		else {

	/*============Output Record Table ( VIEW & LIST) ==============*/

	  // set $formcid by FORM page cID
/*
			$sql = "SELECT cID,msqID FROM btTomoacLister
	  				WHERE LcID=".$lcid." 
	  						AND FbID=".$bid." 
	  						AND LbID = (SELECT max(LbID) FROM btTomoacLister WHERE LcID=".$lcid.")
	  				ORDER BY position,msqID";
*/
			$sql = "SELECT DISTINCT cID,msqID FROM btTomoacLister
	  				WHERE cID=".$formcid." AND LcID=".$nowcid." AND LbID=".$nowbid." 
	  				ORDER BY position,msqID";
	  		if($debug) $head .= $sql;
	  		$rows = $db->query($sql);
	  		$formcid = 0;
			$msqidorder = array();
			foreach($rows as $row) {
				foreach($row as $key=>$val) {
					if($key == 'cID') {
						if($formcid == 0)
							$formcid = $val;	// Set Edit Form bID
					} else if($key == 'msqID') {
						$msqidorder[] = $val;
					}
				}
			}
			if($debug) {
				error_log('formcid='.$formcid,0);
				error_log('msqidorder[0]='.$msqidorder[0],0);
			}

			if($_POST['function'] == 'view') {

	/*=====================Output Record Table (VIEW: Single Record) ==============*/

				$html = '';
				$asid = $_POST['asID'];

				// アイテム名はbtFormTomoacQuestionsから得る
				$itemar = array();
				$sql = "SELECT question FROM btFormTomoacQuestions WHERE bID=".$bid." ORDER BY position,msqID";
				if($debug) $html .= $sql;
				$rows = $db->query($sql);
				foreach($rows as $row)
					$itemar[] .= $row{question};

				if(count($itemar) == 0) {	// フォームの編集等によって、bIDが変わってしまっている
					echo $head;
					echo '<div style="margin:0px; padding:0px; width:100%; height:auto" >';
					echo '<a onclick="back();">'.t('Changed Form bID').'</a>';
					echo '</div>';
					return;
				}
				if($bid != $_POST['bID'])	// This block not taget block
					return;

				$sql = "SELECT question,inputType,btFormTomoacQuestions.msqID,btFormTomoacAnswers.answer,btFormTomoacAnswers.answerLong,created,uID 
						FROM btFormTomoacQuestions 
							INNER JOIN btFormTomoacAnswers ON btFormTomoacQuestions.msqID = btFormTomoacAnswers.msqID 
							INNER JOIN btFormTomoacAnswerSet ON btFormTomoacAnswers.asID = btFormTomoacAnswerSet.asID 
						WHERE bID=".$bid." AND btFormTomoacAnswers.asID=".$asid."
						ORDER BY position ASC;";
				$rows = $db->query($sql);

				$html .= '<form enctype="multipart/form-data" name="form'.$asid.'" method="post" action="/index.php?cID='.$formcid.'">';
				$i = 0;
				$j = count($itemar);
				foreach($rows as $row) {
					$html .= '<tr>';
					if($itemar[$i] != $row{question}) {
						$html .= '<td>'.$row{question}.'</td>';		// 空item
					} else {
						$html .= '<td>'.$row{question}.'</td>';
						foreach($row as $key=>$val) {
							if($key == 'inputType') {
								$inputtype = $val;
							} else if($key == 'msqID') {
								$msqid = $val;
							} else if($key == 'answer') {
								$vals = $val;
							} else if($key == 'answerLong') {
								$vals .= $val;
								$html .= ListerTomoacBlockController::hidden_tag( $inputtype, $vals, $msqid, $debug );
							} else if($key == 'created') {
								$created = $this->fmt_datetime( $val );
							} else if($key == 'uID') {
								$uid = $this->get_username( $val );
							}
						}
					}
					$html .= '</tr>'."\n";
					$i++;
				}
				for(; $i<$j; $i++) {
					$html .= '<tr><td>'.$itemar[$i].'</td><td></td></tr>';	// 残りの空item
				}
				if($regdateflag > 0)
					$html .= '<tr><td>'.t('Registration Date').'</td><td>'.$created.'</td></tr>';
				if($reguserflag > 0)
					$html .= '<tr><td>'.t('UserName').'</td><td>'.$uid.'</td></tr>';
				// ボタン表示
//				$html .= '</tr>';
				$html .= '<tr align="center">';
				$html .= $this->button_tag($cid, $bid, $asid, $editflag, $_POST['surveyName'], 1, $debug);
				$html .= '<td></td>';
				$html .= '</tr>';
			}
			else {
				
	/*=====================Output Record Table	(LIST)===============*/

				// LIST up content 
				$sql = "DROP VIEW IF EXISTS view_ans";
				$rows = $db->query($sql);
				$sql = "CREATE VIEW view_ans AS ".
					"SELECT question,inputType,btFormTomoacQuestions.msqID,btFormTomoacAnswers.asID,answer,answerLong,created,uID ".
						"FROM btFormTomoacQuestions ".
							"INNER JOIN btFormTomoacAnswers ON btFormTomoacQuestions.msqID = btFormTomoacAnswers.msqID ".
							"INNER JOIN btFormTomoacAnswerSet ON btFormTomoacAnswers.asID = btFormTomoacAnswerSet.asID ".
						"WHERE bID=$bid ".
						"ORDER BY btFormTomoacAnswers.asID,position ASC";
				error_log($sql,0);
				$rows = $db->query($sql);

				// pickup msqID list and create view
				$sql = "SELECT DISTINCT msqID FROM view_ans";
				$rows = $db->query($sql);
				$msqidar = array();
				foreach($rows as $row) {
					foreach($row as $key=>$val) {
						$msqidar[] = $val;	// msqID
						$msqid = $val;
						$sql = "DROP VIEW IF EXISTS view_ans".$msqid;
						$rows = $db->query($sql);
						$sql = "CREATE VIEW view_ans".$msqid." AS SELECT * FROM view_ans WHERE msqID=".$msqid;
						if($msqid == 9)
							$sql .= " ORDER BY answer,answerLong";
						$rows = $db->query($sql);
					}
				}
				error_log('msqidar_count='.count($msqidar),0);
				// make SQL view of total
				for($i=0; $i<count($msqidar); $i++) {

					$m = $msqidar[$i];
					$r = "view_ans".$m;

					if($i == 0) {		// first
						$sql = "SELECT ";
						$f = "view_ans".$m;
					}
					$sql .=   $r.".asID as asID".$m.
							",".$r.".msqID as msqID".$m.
							",".$r.".question as question".$m.
							",".$r.".inputType as inputType".$m.
							",".$r.".answer as answer".$m.
							",".$r.".answerLong as answerLong".$m.
							",".$r.".created as created".$m.
							",".$r.".uID as uID".$m;

					if($i == 0) {		// first
						$from = " FROM ".$r;
						if($i == (count($msqidar)-1))	// last?
							$sql .= $from;
						else
							$sql .= ",";
					} else if($i == (count($msqidar)-1)) {	// last?
						$sql .= $from . " LEFT JOIN ".$r." ON ".$f.".asID=".$r.".asID";
					} else {
						$sql .= ",";
						$from .= " LEFT JOIN ".$r." ON ".$f.".asID=".$r.".asID";
					}
				}
//				error_log($sql,0);
//				if($debug) $html .= $sql.'<br>';
				$rows = $db->query($sql);

				// ------- Page control initial set START ------ //
				$listtotal = 0;
				foreach($rows as $row)
					$listtotal++;
				$listcount = $pplines;	// ページあたりの表示行数
				$listover = 11;			// ページ遷移インデックス表示数（11 以上, 13, 15 ...）
				$listbeginp = 0;
				if(isset($_POST['beginp']))
					$listbeginp = $_POST['beginp'];
				$listendp = $listbeginp + $listcount;
				$listcurr = 0;
//				error_log('1/curr='.$listcurr.'/begin='.$listbeginp.'/end='.$listendp.'/total='.$listtotal.'/count='.$listcount.'/',0);
				// ------- Page control initial set END -------- //

				// TITLE line pickup START -------
				$sql5 = "SELECT msqID,question FROM btFormTomoacQuestions WHERE bID=".$bid." ORDER BY position,msqID";
				// error_log($sql5,0);
				$rows5 = $db->query($sql5);

				$qas = array();
				foreach($rows5 as $row5) {
					foreach($row5 as $key=>$val) {
						if($key == 'msqID') {
							if(($u = array_search($val, $msqidorder)) === FALSE)
								$u = -1;	// no output
						} else if($key == 'question') {
							$qas[$u] = $val;
						}
					}
				}
				$html .= '<tr>';
				for($a=0; $a<count($msqidorder); $a++) {
					$html .= '<td>'.$qas[$a].'</td>';
				}
				if($regdateflag > 0)
					$html .= '<td>'.t('Registration Date').'</td>';
				if($reguserflag > 0)
					$html .= '<td>'.t('UserName').'</td>';
				$html .= '<td>'.t('Operation');
				if($debug) $html .= ' (Fcid:'.$formcid.'/FbID:'.$formbid.')';
				$html .= '</td>';
				$html .= '</tr>'."\n";
				// ------- TITLE line pickup END

				// content line
				$dc = 0;
				foreach($rows as $row) {

					// page skip until begin START ------ //
					if($listcurr < $listbeginp) {
						$listcurr++;
						continue;
					}
					// ------ page skip until begin END

					$first = 0;
					$htmlar = array();
					$ic = 0;
					foreach($row as $key=>$val) {

					//	if($dc == 0 && $ic == 0) error_log($key,0);
						$mc = count($msqidar);
						for($i=0; $i<$mc; $i++) {

							$m = $msqidar[$i];
							if(($u = array_search($m, $msqidorder)) === FALSE)
								$u = -1;	// no output
							if($key == 'asID'.$m) {
								if($i == 0)
									$asid = $val;
							} else if($key == 'msqID'.$m) {
								$msqid = $val;
							} else if($key == 'inputType'.$m) {
								$inputtype = $val;
							} else if($key == 'answer'.$m) {
								$vals = $val;
							} else if($key == 'answerLong'.$m) {
								$vals.= $val;
								if($first == 0) {
									$html .= '<tr>';
									$html .= $this->formopen_tag( $asid, $formcid, $debug );
									$first++;
								}
								if($u >= 0)
									$htmlar[$u] .= $this->hidden_tag( $inputtype, $vals, $msqid, $debug );
							} else if($key == 'created'.$m) {
								if($i == 0)
									$created = $this->fmt_datetime($val);
								if($i == $mc-1)
									if($regdateflag > 0)
										$html2 .= '<td>'.$created.'</td>';
							} else if($key == 'uID'.$m) {
								if($i == 0)
									$uid = $this->get_username( $val );
								if($i == $mc-1)
									if($reguserflag > 0)
										$html2 .= '<td>'.$uid.'</td>';
							}
						}
						$ic++;
					}
					// ボタン表示
					for($a=0; $a<count($msqidorder); $a++)
						$html .= $htmlar[$a];
					$html .= $html2; $html2 = '';
					$html .= $this->button_tag($cid, $bid, $asid, $editflag, $_POST['surveyName'], 0, $debug);
					$html .= '</tr>';

					// page skip over endp START -------
					$listcurr++;
					if($listcurr >= $listendp)
						break;
					// ------ page skip over endp END
					$dc++;
				}
				// ------------ Page Control START ----------- //
//				error_log('2/curr='.$listcurr.'/begin='.$listbeginp.'/end='.$listendp.'/total='.$listtotal.'/count='.$listcount.'/',0);
				if($listtotal > $listcount) {

					$pp = floor($listover/2) + 1;		// 6 <= 11/2 + 1
					$bp = $listbeginp/$listcount;		// 0,1,2,...
					$ep = round($listtotal/$listcount);	// <= (total data line)/(pplines)

					for($i=0; $i<$ep; $i++) {
						if($i == 0 && $bp != 0) {
							$n = ($listbeginp-$listcount)/$listcount+1;
							$bottom .= '[<a onclick="document.formp'.$n.'.submit();">'.t('Previous').'</a>] ';
						}
						if($bp < $pp) {
							// selected position from start to center
							if($i < ($pp+3)) {
								if($i == $bp)
									$bottom .= '[<b> '.($i+1).' </b>] ';
								else
									$bottom .= '[<a onclick="document.formp'.($i+1).'.submit();"> '.($i+1).' </a>] ';
							}
							else if(($i == ($ep-1)) && ($ep>$listover)) {
								$bottom .= ' ... [<a onclick="document.formp'.$ep.'.submit();"> '.$ep.' </a>]';
							}
						}
						else if(($bp>=$pp)&&($bp<($ep-$pp))) {
							// selected position center
							if($i == 0) {
								$bottom .= '[<a onclick="document.formp1.submit();"> 1 </a>] ... ';
							}
							else if(($i>=($bp-3))&&($i<=($bp+3))) {
								if($i == $bp)
									$bottom .= '[<b> '.($i+1).' </b>] ';
								else
									$bottom .= '[<a onclick="document.formp'.($i+1).'.submit();"> '.($i+1).' </a>] ';
							}
							else if($i == ($ep-1) && ($ep>$listover)) {
								$bottom .= ' ... [<a onclick="document.formp'.$ep.'.submit();"> '.$ep.' </a>]';
							}
						}
						else if($bp >= ($ep - $pp)) {
							// selected position from center to end
							if($i == 0 && ($ep>$listover)) {
								$bottom .= '[<a onclick="document.formp1.submit();"> 1 </a>] ... ';
							}
							else if($i > ($ep-$listover+1))
								if($i == $bp)
									$bottom .= '[<b> '.($i+1).' </b>] ';
								else
									$bottom .= '[<a onclick="document.formp'.($i+1).'.submit();"> '.($i+1).' </a>] ';
						}
						if($i == ($ep-1) && $bp != ($ep-1)) {
							$n = ($listbeginp+$listcount)/$listcount+1;
							$bottom .= '[<a onclick="document.formp'.$n.'.submit();">'.t('Next').'</a>] ';
						}
						$bottomform .= "\n".'<form name="formp'.($i+1).'" action="" method="POST"><input type="hidden" name="bID" value="'.$bid.'"><input type="hidden" name="beginp" value="'.($i*$listcount).'"></form>';
					}
				}
				// ------------ Page Control END-------- //
				$bottom .= $bottomform;
	  		}
		}
		$this->set('head',	$head);
		$this->set('kval',	$kval);
		$this->set('title',	$title);
		$this->set('html',	$html);
		$this->set('bottom',$bottom);
	}

	/*====================================================*
	 ***                make button tag (1)				***
	 *====================================================*/
	function formopen_tag( $asid, $formcid, $debug=0 ) {
//		error_log('/asID='.$asid.'/formcID='.$formcid.'/debug='.$debug,0);

		$tc = Page::getByID($formcid);
		$path = $tc->getCollectionPath();		// 	/<name>
//		$path = '/index.php?cID='.$formcid;		// 	/index.php?cID=xxx

		$html .= "\n";
		$html .= '<form enctype="multipart/form-data" name="form'.$asid.'" method="post" action="'.$path.'">';
		return $html;
	}
	/*====================================================*
	 ***                make button tag (2)				***
	 *====================================================*/
	function button_tag( $cid, $bid, $asid, $editflag, $surveyname, $bn, $debug=0 ) {
//		error_log('/cID='.$cid.'/bID='.$bid.'/asID='.$asid.'/formcID='.$formcid.'/surneyName='.$surveyname.'/bn='.$bn.'/debug='.$debug,0);

		$html .= '<td>';

		// 編集ボタン
		if($editflag != 0) {
			$html .= '<input type="hidden" name="function" value="edit">';
			$html .= '<input type="hidden" name="lister" value="'.$cid.'">';
			$html .= '<input type="hidden" name="asID" value="'.$asid.'">';
			$html .= '<input type="hidden" name="bID" value="'.$bid.'">';
			$html .= '<input type="hidden" name="surveyName" value="'.$surveyname.'">';
			$html .= '<a onclick="document.form'.$asid.'.submit();">'.t('Edit').'</a>';
			$html .= '&nbsp;';
			$html .= '<a onclick="if(confirm('."'".t('Delete OK?')."'".') == true) document.form'.$asid.'del.submit();">'.t('Delete').'</a>';
		}
		if($bn == 0) {
			$html .= '&nbsp;';
			$html .= '<a onclick="document.form'.$asid.'cnf.submit();">'.t('View').'</a>';
		} else {
			$html .= '&nbsp;';
			$html .= '<a onclick="javascript:back(-1);">'.t('Back').'</a>';
		}
		if($debug)
			$html .= '&nbsp;(asID:'.$asid.')';
		$html .= '</td>';
		$html .= '</form>';

		if($editflag != 0) {
			// 削除ボタン
			$html .= "\n";
			$html .= '<form name="form'.$asid.'del" method="post" action="">';
			$html .= '<input type="hidden" name="function" value="delete">';
			$html .= '<input type="hidden" name="lister" value="'.$cid.'">';
			$html .= '<input type="hidden" name="asID" value="'.$asid.'">';
			$html .= '<input type="hidden" name="bID" value="'.$bid.'">';
			$html .= '<input type="hidden" name="surveyName" value="'.$surveyname.'">';
			$html .= '</form>';
		}
		// 表示ボタン
		if($bn == 0) {
			$html .= "\n";
			$html .= '<form name="form'.$asid.'cnf" method="post" action="">';
			$html .= '<input type="hidden" name="function" value="view">';
			$html .= '<input type="hidden" name="lister" value="'.$cid.'">';
			$html .= '<input type="hidden" name="asID" value="'.$asid.'">';
			$html .= '<input type="hidden" name="bID" value="'.$bid.'">';
			$html .= '<input type="hidden" name="surveyName" value="'.$_POST['surveyName'].'">';
			$html .= '</form>'."\n";
		}
		return $html;
	}
	/*====================================================*
	 ***                  make hidden tag				***
	 *====================================================*/
	function hidden_tag( $inputtype, $vals, $msqid, $debug=0 ) {
		if($inputtype == 'jname') {
			$valar = explode('&&', $vals);
			$vals = $valar[0].' '.$valar[1].' ('.$valar[2].' '.$valar[3].')';
			$html .= '<td>'.$vals;
			$html .= '<input type="hidden" name="Question'.$msqid.'name1" value="'.$valar[0].'">';
			$html .= '<input type="hidden" name="Question'.$msqid.'name2" value="'.$valar[1].'">';
			$html .= '<input type="hidden" name="Question'.$msqid.'ruby1" value="'.$valar[2].'">';
			$html .= '<input type="hidden" name="Question'.$msqid.'ruby2" value="'.$valar[3].'">';
			$html .= '</td>';
		}
		else if($inputtype == 'postno') {
			$valar = explode('&&', $vals);
			$html .= '<td>'.'〒'.substr($vals,0,3).'-'.substr($vals,3,4).' '.$valar[1].' '.$valar[2].' '.$valar[3];
			$html .= '<input type="hidden" name="Question'.$msqid.'" value="'.$valar[0].'">';
			$html .= '<input type="hidden" name="Question'.$msqid.'a" value="'.$valar[1].'">';
			$html .= '<input type="hidden" name="Question'.$msqid.'b" value="'.$valar[2].'">';
			$html .= '<input type="hidden" name="Question'.$msqid.'c" value="'.$valar[3].'">';
			$html .= '</td>';
		}
		else {
			$html .= '<td>'.$vals;
			$html .= '<input type="hidden" name="Question'.$msqid.'" value="'.$vals.'">';
			$html .= '</td>';
		}
		return $html;
	}
}
