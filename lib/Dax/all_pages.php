<?php
	###  Constants
	require_once(dirname(__FILE__) .'/LAUNCH.inc.php');

	#########################
	###  DAX Global Functions

	if ( ! isset( $_REQUEST['content_id'] ) ) return trigger_error("You must pass a content_id", E_USER_ERROR);
	if ( ! Dax::load()->edit_mode )           return trigger_error("Not Logged In", E_USER_ERROR);


	$sql = "SELECT
				SUBSTRING(dcs.content_id from 20) as url,
				SUBSTRING(SUBSTRING(dcs.content,19,LOCATE('\"}',dcs.content)-19), LOCATE('\/',SUBSTRING(dcs.content,19,LOCATE('\"}',dcs.content)-19))+1 ) as type
			FROM `dax_content_section` dcs
			LEFT JOIN `dax_content_publish` dcp using( `daxpub_id` )
			WHERE 1
			AND dcp.`not_deleted` = 1
			AND dcp.`is_published` = 1
			AND dcp.`start_date` <= CURDATE()
			AND (
				dcp.`end_date` is NULL
				OR
				dcp.end_date >= CURDATE()
			)
			AND dcs.content_id LIKE \"||CUSTOM_PAGE_URL||%\"
			AND dcs.content != \"||deleted||\"
			AND LOCATE('{\"page_template\"',dcs.content) != 0
			ORDER BY type;";

	$dbh = Dax::load()->get_dbh();
	$sth = $dbh->prepare($sql);
	$sth->execute();
	$pages_list = $sth->fetchAll();


	// Group by Page Type
	$pages_grouped = array();
	foreach($pages_list as &$p) {
		$meta_data_key = $p['url']."||meta-data";
		$meta_data = json_decode(\App::getStringOnly($meta_data_key));
		$p['title'] = $meta_data->title;

		$pages_grouped[$p['type']][] = $p;
	}

	// Sort pages by title within each group
	function cmp($a, $b) {
		return strcmp($a['title'], $b['title']);
	}

	foreach($pages_grouped as $k => &$group) {
		usort($group, "cmp");
	}

?>
<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
	<div class="container" id="all-pages" style="padding-top: 50px; width: 98%">
		<div class="panel panel-info">
			<div class="panel-heading" style="padding-bottom: 0; border-bottom: none;">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav active"><a href="#SpecialPages" data-toggle="tab">Special Pages</a></li>
					<?php $loop_counter = 0;
						foreach($pages_grouped as $k => $g) { ?>
							<li class="nav  <?= ''//($loop_counter==0 ? 'active' : '') ?>"><a href="#<?=$k?>" data-toggle="tab"><?=$k?></a></li>
					<? $loop_counter++; } ?>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 50px">
				<!-- Tab panes -->
				<div class="tab-content">
					<div class="tab-pane active" id="SpecialPages">
						<div class="col-md-4">
							<ul>
								<li style="overflow: hidden;white-space: nowrap"><a href="https://<?=\App::getPrimaryDomain(); ?>" target="_top">Home</a></li>
								<li style="overflow: hidden;white-space: nowrap"><a href="https://<?=\App::getPrimaryDomain() . '/checkout/receipt/cms-edit'; ?>" target="_top">Reciept Page</a></li>
							</ul>
						</div>
					</div>
					<?php $loop_counter = 0;
						foreach($pages_grouped as $k => $g) {
							// Set counter for place within the group
							$j = 0;

							// Evenly distribute the pages of current type into 3 columns
							$cols = 3;
							// Minimun number in every column
							$col_base = floor(count($g) / $cols);

							// Get individual column counts filling un-even numbers across columns beginning with the first
							$mod = count($g) % $cols;
							$col_counts = array();
							foreach(range(1,$cols) as $i){
								$col_counts[$i] = $col_base + ($mod >= $i ? 1 : 0);
							}
						?>

							<div class="tab-pane <?= ''//($loop_counter == 0 ? 'active' : '')?>" id="<?=$k?>">

								<?php foreach($col_counts as $count) { ?>
										<div class="col-md-4">
											<ul>
												<?php for($i = 0; $i < $count ; $i++) { ?>
														<li style="overflow: hidden;white-space: nowrap"><a href="https://<?=\App::getPrimaryDomain().$g[$j]['url']?>" target="_top"><?=($g[$j]['title'] != '' ? $g[$j]['title'] : 'Page has no title')?></a></li>
												<? $j++; } ?>
											</ul>
										</div>
								<? } ?>

							</div>
					<? $loop_counter++; } ?>
				</div>
			</div>
		</div>
	</div>
</body>
