<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

$fm = Loader::helper('form');
$ph = Loader::helper('form/page_selector');
$ca = Loader::helper('concrete/asset_library');

$successMessage = t('Your XML import successfully completed! Click here to import more <a href="/index.php/dashboard/problog/site_importer/">Continue Importing</a>');
Loader::model("collection_types");
$ctArray = CollectionType::getList('');
$pageTypes = array();
foreach($ctArray as $ct) {
	$pageTypes[$ct->getCollectionTypeID()] = $ct->getCollectionTypeName();		
}
global $c;
?>
<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Import XML Content to ProBlog'), false, false, false);?>
	<div class="ccm-pane-body ccm-ui">
		<div id="success-message" class="ajax_success ccm-ui" style="display: none;">
			<div class="alert alert-success">
			  	<button type="button" class="close" data-dismiss="alert">&times;</button>
			  	<h4><?php echo t('Success!'); ?></h4>
				<?php
				echo $successMessage;
				?>
			</div>
		</div>
		<div id="install-progress-wrapper" style="display: none;">
			<div class="alert-message info">
				<div id="install-progress-summary">
					<?=t('Beginning Installation')?>
				</div>
			</div>
		</div>
		<div id="install-progress-bar" style="display: none;">
			<div class="progress progress-striped active">
				<div class="bar" style="width: 0%;"></div>
			</div>
		</div>

		<div id="install-progress-errors"></div>
		
		<div id="import_form">

			<div class="clearfix">
				<?php  echo $fm->label('source_type', t('Chose Source XML.'))?>
				<div class="input" style="width: 270px; float: left; padding-left: 22px;">
					<select name="source_type" id="source_type">
						<option value="wp"><?=t('Wordpress')?></option>
						<option value="feedburner"><?=t('Feedburner')?></option>
					</select>
				</div>
			</div>

			<div class="clearfix">
				<?php
				$args['selectedSearchField[]'] = 'extension'; 
				$args['extension'] = 'xml'; 
				?>
				<?php  echo $fm->label('importSchema', t('Please attach your exported .xml file'))?>
				<div class="input" style="width: 270px; float: left; padding-left: 22px;">
					<?php  echo $ca->file('importSchema', $importSchema,t('Select xml File'),null, $args)?>
				</div>
			</div>
			
			<div class="clearfix">
				<?php  echo $fm->label('importLocation', t('Please Chose a Location in your site to import to'))?>
				<div class="input" style="width: 270px; float: left; padding-left: 22px;">
					<?php  echo $ph->selectPage('importLocation', $importLocation, array('style' => 'width: 230px'))?>
				</div>
			</div>
			
			<div class="clearfix">
				<?php  echo $fm->label('selectedPageType', t('Please select a page_type to apply.'))?>
				<div class="input" style="width: 270px; float: left; padding-left: 22px;">
					<?php  echo $fm->select('selectedPageType', $pageTypes, $ctID)?>
				</div>
			</div>
		</div>
	</div>
    <div class="ccm-pane-footer">
    	<?php  $ih = Loader::helper('concrete/interface'); ?>
        <?php  print $ih->submit(t('Start Importer'), 'import-form', 'right', 'primary doInstaller'); ?>
    </div>
    
<script type="text/javascript">
$(function() {
	//$( "#install-progress-bar" ).hide();
	

	
	$('.doInstaller').click(function(){
	
		var importXml = $('div [ccm-file-manager-field="importSchema"]').attr('fID');
		var importType = $('#source_type').val();
		var importLocation = $('input[name="importLocation"]').val();
		var selectedPageType = $('#selectedPageType option:selected').val();
		
		if(!importXml || !importType || !importLocation || !selectedPageType){
			alert("<?=t('You must fill out all fields to continue!')?>");
			return false;
		}
		
		$('#install-progress-bar div.bar').css('width', '5%');
		$('#import_form').hide();
		$("#install-progress-wrapper").show();
		$("#install-progress-bar").show();


		$.ajax('<?php echo $this->action("get_post_count")?>', {
			dataType: 'json',
			data: {
				importXml: importXml
			},
			error: function(r) {
				$("#install-progress-wrapper").hide();
				$("#install-progress-bar").hide();
				$('#import_form').show();
				$("#install-progress-errors").empty();
				$("#install-progress-errors").append('<div class="alert-message error">' + r.responseText + '</div>');
				$("#install-progress-error-wrapper").fadeIn(300);
			},
			success: function(count) {
				var i = 0;
				//console.log(count);
				while(i < count){
					i++;
					var importXml = $('div [ccm-file-manager-field="importSchema"]').attr('fID');
					var importType = $('#source_type').val();
					var importLocation = $('input[name="importLocation"]').val();
					var selectedPageType = $('#selectedPageType option:selected').val();
					$.ajax('<?php echo $this->action("run_import_item")?>', {
						dataType: 'json',
						data: {
							importXml: importXml,
							importType: importType,
							importLocation: importLocation,
							selectedPageType: selectedPageType,
							i: i
						},
						error: function(r) {
							$("#install-progress-wrapper").hide();
							$("#install-progress-bar").hide();
							$('#import_form').show();
							$("#install-progress-errors").empty();
							$("#install-progress-errors").append('<div class="alert-message error">' + r.responseText + '</div>');
							$("#install-progress-error-wrapper").fadeIn(300);
						},
						success: function(r) {
							console.log(r);
							var p = (i/count) * 100;
							if (r.error) {
								$('#import_form').show();
								$("#install-progress-wrapper").hide();
								$("#install-progress-bar").hide();
								$("#install-progress-errors").append('<div class="alert-message error">' + r.message + '</div>');
								$("#install-progress-error-wrapper").fadeIn(300);
							} else {
								$('#install-progress-bar div.bar').css('width',p+'%');
								if(p == 100){
									//console.log('done');
									$("#install-progress-bar").hide();
									$("#install-progress-wrapper").fadeOut(300, function() {
										$("#success-message").fadeIn(300);
									});
									$("#install-progress-wrapper").hide();
								}
							}
					
						}
					});
				}
				/*
				if (items.error) {
					$('#import_form').show();
					$("#install-progress-wrapper").hide();
					$("#install-progress-bar").hide();
					$("#install-progress-errors").append('<div class="alert-message error">' + items.message + '</div>');
					$("#install-progress-error-wrapper").fadeIn(300);
				} else {
					$('#install-progress-bar div.bar').css('width',items.percent+'%');
					$("#install-progress-bar").hide();
					$("#install-progress-wrapper").fadeOut(300, function() {
						$("#success-message").fadeIn(300);
					});
					$("#install-progress-wrapper").hide();

				}
				*/
			}
		});

	
	return false;
	});

});
</script>