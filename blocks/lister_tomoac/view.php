<?php  defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<?php echo $head; ?>

<style type="text/css">
table {
	width:100%;
	margin-bottom:18px;
	padding:0;
	font-size:13px;
	border:1px solid #ddd;
}
td {
	padding: 10px 10px 9px;
	line-height: 18px;
	text-align: left;
	vertical-align:top;
}
table td+td {
	border-left:1px solid #ddd;
}
table tr+tr td {
	border-top:1px solid #ddd;
}
tr:nth-child(odd) {
	background-color: #f9f9f9;
}
tr:nth-child(even) {
	background-color: #ffffff;
}
</style>

<table>
<?php echo $kval;	?>
<?php echo $title;	?>
<?php echo $html;	?>
</table>

<?php echo $bottom; ?>
