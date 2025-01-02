<?php echo $header; ?><?php echo $column_left; ?>
<?php if (!empty($upgrade)) { ?>
<div id="content">
  <div class="container-fluid">
    <div class="panel panel-default" style="margin-top:25px; min-height:700px">
      <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-upload" style="color:#86C440"></i> Complete SEO Package Upgrade Tool</h3></div>
      <div class="content panel-body">
        <?php if (!empty($upgrade_complete)) { ?>
          <h4>Upgrade process:</h4>
          <ul style="list-style-type:none; margin-bottom:50px">
            <?php foreach ($info as $msg) { ?>
            <li><i class="fa fa-check-circle" style="color:#60ba5b"></i> <?php echo $msg; ?></li> 
            <?php } ?>
          </ul>
          <div class="alert alert-success"><i class="fa fa-check-circle"></i> The upgrade has been successfully done, you can go back to module options to continue the configuration.</div>
          <div class="text-center" style="margin-top:50px;">
            <a href="<?php echo $cancel; ?>" class="btn btn-default btn-lg"><i class="fa fa-reply"></i> Back to module options</a>
          </div>
        <?php } else  { ?>
        <div class="alert alert-info">
          <p>Use this page to upgrade from your previous SEO extension, if you want to transfer your actual data (keywords, metas, titles, etc) to Complete SEO Package.</p>
        </div>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
          <div class="form-group">
            <label class="control-label"><span data-toggle="tooltip" title="What was your previous extension?">Upgrade from:</span></label>
            <select class="form-control" name="module">
              <option value="seo_pack_pro">SEO Pack Pro</option>
              <option value="paladin">Paladin</option>
              <option value="all_in_one">All In One SEO</option>
              <option value="mega_kit">SEO Mega Kit Plus</option>
              <option value="broken_link_manager">Broken Link Manager</option>
            </select>
          </div>
          <div class="instructions" style="min-height:200px;">
            <div class="seo_pack_pro well">
              <h3>Upgrade from SEO Pack Pro</h3>
              <ol>
                <li><span style="color:#ec5d5d">Important: Make full backup of your database</span></li>
                <li>Disable or delete these modification files from /vqmod/xml/ folder:
                  <ul>
                    <li>autolinks.xml</li>
                    <li>canonicals.xml</li>
                    <li>custom_title.xml</li>
                    <li>friendly_urls.xml</li>
                    <li>info_meta.xml</li>
                    <li>instant_seo.xml</li>
                    <li>product_seo_plus.xml</li>
                    <li>product_seo_plus_link_to_store.xml</li>
                    <li>product_seo_plus_title_with_category.xml</li>
                    <li>SEOEDITOR.xml</li>
                    <li>seopack.xml</li>
                    <li>store_keyword.xml</li>
                  </ul>
                </li>
                <li>If you had an url extension defined in your previous module (like .html), put it in the field below, if not just let it empty</li>
                <li>Click on proceed button</li>
                <li>If using ocmod do refresh in Extensions > Modifications</li>
              </ol>
            </div>
            <div class="paladin well" style="display:none">
              <h3>Upgrade from Paladin</h3>
                <ol>
                <li><span style="color:#ec5d5d">Important: Make full backup of your database</span></li>
                <li><b>Do not</b> uninstall Paladin from Extensions</li>
                <li>Remove the file /admin/controller/module/superseobox.php</li>
                <li>Disable or delete these modification files:
                  <ul>
                    <li>/vqmod/xml/ssb-admin-generate.xml</li>
                    <li>/vqmod/xml/ssb-catalog-generate.xml</li>
                  </ul>
                </li>
                <li>If you had an url extension defined in your previous module (like .html), put it in the field below, if not just let it empty</li>
                <li>Click on proceed button</li>
                <li>If using ocmod do refresh in Extensions > Modifications</li>
              </ol>
            </div>
            <div class="all_in_one well" style="display:none">
              <h3>Upgrade from All in One SEO (Nerdherd)</h3>
              <ol>
                <li><span style="color:#ec5d5d">Important: Make full backup of your database</span></li>
                <li><b>Do not</b> uninstall All in One SEO from Extensions</li>
                <li>Remove the file /admin/controller/extension/module/aioseo.php</li>
                <li>Disable or delete these modification files:
                  <ul>
                    <li>/system/aios1.ocmod.xml and do refresh in Extensions > Modifications</li>
                  </ul>
                </li>
                <li>If you had an url extension defined in your previous module (like .html), put it in the field below, if not just let it empty</li>
                <li>Click on proceed button</li>
                <li>Do refresh in Extensions > Modifications</li>
              </ol>
            </div>
            <div class="mega_kit well" style="display:none">
              <h3>Upgrade from SEO Mega Kit</h3>
              <ol>
                <li><span style="color:#ec5d5d">Important: Make full backup of your database</span></li>
                <li><b>Do not</b> uninstall SEO Mega Kit from Extensions</li>
                <li>Disable or delete all modification files starting by _seo_mega_pack: /vqmod/xml/_seo_mega_pack***.xml</li>
                <li>If you had an url extension defined in your previous module (like .html), put it in the field below, if not just let it empty</li>
                <li>Click on proceed button</li>
                <li>Delete the file /admin/controller/module/seo_mega_pack.php</li>
                <li>Do refresh in Extensions > Modifications</li>
              </ol>
            </div>
            <div class="broken_link_manager well" style="display:none">
              <h3>Broken Link Manager</h3>
              <p>Click on proceed to save all actual urls to Complete SEO Package.</p>
              <p>Then you can disable the Broken Link Manager modification files.</p>
            </div>
          </div>
          <div class="form-group hideExtension">
            <label class="control-label"><span data-toggle="tooltip" title="If your urls have an extension like .html put it here, the dot must be included">Extension</span></label>
            <input class="form-control" type="text" name="ext" value="" placeholder="example: .html"/>
          </div>
        </form>
        <div class="text-center" style="margin-top:50px;">
          <button type="submit" form="form" class="btn btn-primary btn-lg"><i class="fa fa-cog"></i> Proceed</button>
          <a href="<?php echo $cancel; ?>" class="btn btn-default btn-lg"><i class="fa fa-reply"></i> Back to module options</a>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$('select[name=module]').change(function(){
var mod = $(this).val();
    $('.instructions > div').hide(0);
    $('.instructions > .'+mod).fadeIn(700);
  
  if (mod == 'broken_link_manager') {
    $('.hideExtension').hide();
  } else {
    $('.hideExtension').show();
  }
});
</script>
<?php echo $footer; exit; ?>
<?php } ?>
<?php $gkhtab = $gkhdiv = 0; ?>
<div id="content">
<?php if(!empty($style_scoped)) { ?><style scoped><?php echo $style_scoped; ?></style><?php } ?>
<div id="modal-info" class="modal <?php if ($OC_V2) echo ' fade'; ?>" tabindex="-1" role="dialog" aria-hidden="true"></div>
    <ul class="breadcrumb">
      <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
      <?php } ?>
    </ul>
	
  <div class="<?php if ($OC_V2) echo 'container-fluid' ?>">
	<?php if (isset($success) && $success) { ?><div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><script type="text/javascript">setTimeout("$('.alert-success').slideUp();",5000);</script><?php } ?>
	<?php if (isset($info) && $info) { ?><div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $info; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><?php } ?>
	<?php if (isset($error) && $error) { ?><div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><?php } ?>
  <?php if (isset($error_warning) && $error_warning) { ?><div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div><?php } ?>
<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
<div class="panel panel-default">
  <div class="panel-heading">
    <div class="pull-right">
      <button type="submit" form="form" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
      <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $button_cancel; ?></a>
    </div>
    <h3 class="panel-title"><img style="vertical-align:top;padding-right:4px" src="view/seo_package/img/icon-big.png"/> <?php echo $heading_title; ?></h3>
  </div>
  <div class="content panel-body">
  <?php if (count($stores)) { ?>
    <div id="stores" class="form-inline">
      <?php echo $_language->get('text_store_select'); ?>
      <select name="store" class="form-control input-sm">
        <?php foreach ($stores as $store) { ?>
        <?php if ($store_id == $store['store_id']) { ?>
        <option value="<?php echo $store['store_id']; ?>" selected="selected"><?php echo $store['name']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  <?php } ?>
		<ul class="nav nav-tabs">
      <?php if (!$store_id) { ?>
			<li class="active"><a href="#tab-dashboard" data-toggle="tab"><i class="fa fa-dashboard"></i><?php echo $_language->get('tab_seo_dashboard'); ?></a></li>
			<li><a href="#tab-editor" data-toggle="tab"><i class="fa fa-edit"></i><?php echo $_language->get('tab_seo_editor'); ?></a></li>
			<li><a href="#tab-update" data-toggle="tab"><i class="fa fa-bolt"></i><?php echo $_language->get('tab_seo_update'); ?></a></li>
			<li><a href="#tab-general" data-toggle="tab"><i class="fa fa-wrench"></i><?php echo $_language->get('tab_seo_options'); ?></a></li>
			<li><a href="#tab-store" data-toggle="tab"><i class="fa fa-area-chart"></i><?php echo $_language->get('tab_seo_store'); ?></a></li>
			<li><a href="#tab-fpp" data-toggle="tab"><i class="fa fa-location-arrow"></i><?php echo $_language->get('tab_seo_fpp'); ?></a></li>
			<li><a href="#tab-snippet" data-toggle="tab"><i class="fa fa-thumbs-o-up"></i><?php echo $_language->get('tab_seo_snippets'); ?></a></li>
			<li><a href="#tab-cron" data-toggle="tab"><i class="fa fa-terminal"></i><?php echo $_language->get('tab_seo_cron'); ?></a></li>
			<li><a href="#tab-about" data-toggle="tab"><i class="fa fa-info"></i><?php echo $_language->get('tab_seo_about'); ?></a></li>
      <?php } else { ?>
        <li class="active"><a href="#tab-multistore" data-toggle="tab"><i class="fa fa-sitemap"></i><?php echo $_language->get('tab_seo_multistore'); ?></a></li>
        <?php /* <li><a href="#tab-editor" data-toggle="tab"><i class="fa fa-edit"></i><?php echo $_language->get('tab_seo_editor'); ?></a></li> */ ?>
        <?php if ($mlseo_multistore) { ?>
        <li><a href="#tab-update" data-toggle="tab"><i class="fa fa-bolt"></i><?php echo $_language->get('tab_seo_update'); ?></a></li>
        <?php } ?>
        <li><a href="#tab-general" data-toggle="tab"><i class="fa fa-wrench"></i><?php echo $_language->get('tab_seo_options'); ?></a></li>
        <li><a href="#tab-store" data-toggle="tab"><i class="fa fa-area-chart"></i><?php echo $_language->get('tab_seo_store'); ?></a></li>
      <li><a href="#tab-snippet" data-toggle="tab"><i class="fa fa-thumbs-o-up"></i><?php echo $_language->get('tab_seo_snippets'); ?></a></li>
      <?php } ?>
		</ul>
    
		<div class="tab-content">
    <?php if (!$store_id) { ?>
		<div class="tab-pane active" id="tab-dashboard" style="position:relative;">
      <div class="col-md-4">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> <span><?php echo $_language->get('text_seo_score'); ?></span></h3>
          </div>
          <div class="panel-body">
            <div class="radial-progress" data-progress="0">
              <div class="circle">
                <div class="mask full">
                  <div class="fill"></div>
                </div>
                <div class="mask half">
                  <div class="fill"></div>
                  <div class="fill fix"></div>
                </div>
                <div class="shadow"></div>
              </div>
              <div class="inset">
                <div class="percentage">
                  <div class="numbers" style="color:transparent"><span>-</span><span>0%</span><span>1%</span><span>2%</span><span>3%</span><span>4%</span><span>5%</span><span>6%</span><span>7%</span><span>8%</span><span>9%</span><span>10%</span><span>11%</span><span>12%</span><span>13%</span><span>14%</span><span>15%</span><span>16%</span><span>17%</span><span>18%</span><span>19%</span><span>20%</span><span>21%</span><span>22%</span><span>23%</span><span>24%</span><span>25%</span><span>26%</span><span>27%</span><span>28%</span><span>29%</span><span>30%</span><span>31%</span><span>32%</span><span>33%</span><span>34%</span><span>35%</span><span>36%</span><span>37%</span><span>38%</span><span>39%</span><span>40%</span><span>41%</span><span>42%</span><span>43%</span><span>44%</span><span>45%</span><span>46%</span><span>47%</span><span>48%</span><span>49%</span><span>50%</span><span>51%</span><span>52%</span><span>53%</span><span>54%</span><span>55%</span><span>56%</span><span>57%</span><span>58%</span><span>59%</span><span>60%</span><span>61%</span><span>62%</span><span>63%</span><span>64%</span><span>65%</span><span>66%</span><span>67%</span><span>68%</span><span>69%</span><span>70%</span><span>71%</span><span>72%</span><span>73%</span><span>74%</span><span>75%</span><span>76%</span><span>77%</span><span>78%</span><span>79%</span><span>80%</span><span>81%</span><span>82%</span><span>83%</span><span>84%</span><span>85%</span><span>86%</span><span>87%</span><span>88%</span><span>89%</span><span>90%</span><span>91%</span><span>92%</span><span>93%</span><span>94%</span><span>95%</span><span>96%</span><span>97%</span><span>98%</span><span>99%</span><span>100%</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <style type="text/less"><?php echo $style_radial_meter; ?></style>
        <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/1.6.1/less.min.js"></script>
      </div>
      <div class="col-md-8">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-cog"></i> <span><?php echo $_language->get('text_dashboard_config'); ?></span></h3>
          </div>
          <div class="panel-body form-horizontal">
            <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo $_language->get('text_seo_package_status'); ?></label>
              <div class="col-sm-9">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-power-off"></i></span>
                  <input type="text" name="mlseo_enabled" value="<?php echo $mlseo_enabled; ?>" class="toggler" data-mode="background" data-on-text="<?php echo strip_tags($_language->get('text_enabled')); ?>" data-off-text="<?php echo strip_tags($_language->get('text_disabled')); ?>" data-icons="" />
                </div>
              </div>
            </div>
          </div>
        </div>

        
      </div>
      <div class="clearfix">&nbsp;</div>
    </div>
    <?php } ?>
		<div class="tab-pane" id="tab-editor" style="position:relative;">
			<ul id="tabs_editor_lang" class="nav nav-tabs" style="position:absolute; top:0; margin:0; <?php echo ($_language->get('direction')) == 'rtl' ? 'left:0;' : 'right:0;'; ?>">
				<?php if (count($languages) > 3) { ?>
				<li class="active pull-right dropdown">
         <?php foreach ($languages as $language) { ?>
          <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><img src="<?php echo $language['image']; ?>" alt=""/> <span class="caret"></span></a>
          <?php break; } ?>
          <ul class="dropdown-menu">
            <?php $f=0; foreach ($languages as $language) { ?>
            <li class="<?php if(!$f) echo 'active'; $f = 1; ?>"><a href="#editorlang-<?php echo $language['language_id']; ?>" editor-lang="<?php echo $language['language_id']; ?>" editor-lang-code="<?php echo $language['code']; ?>" data-toggle="tab"><img src="<?php echo $language['image']; ?>" alt=""/>&nbsp;&nbsp;<?php echo $language['name']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
				<?php } else { ?>
				<?php $f=0; foreach ($languages as $language) { ?>
				<li class="pull-right <?php if(!$f) echo 'active'; $f = 1; ?>"><a href="#editorlang-<?php echo $language['language_id']; ?>" editor-lang="<?php echo $language['language_id']; ?>" editor-lang-code="<?php echo $language['code']; ?>" data-toggle="tab"><img src="<?php echo $language['image']; ?>" alt=""/></a></li>
				<?php } ?>
				<?php } ?>
			</ul>
			<ul id="tabs_editor" class="nav nav-tabs">
				<?php
          $editor_types = array('product', 'category', 'information', 'manufacturer', 'image', 'absolute', 'common', 'special', '404', 'redirect');
          if ($store_id) {
            $editor_types = array('product', 'category', 'information', 'manufacturer', 'image');
          }
        ?>
				<?php $f = 0; foreach ($editor_types as $type) { ?>
					<li <?php if(!$f) echo 'class="active"'; $f = 1; ?>><a class="<?php echo in_array($type, array('manufacturer', 'redirect', '404')) ? 'ml-hide' : 'ml-show'; ?>" href="#tab-editor-<?php echo $type; ?>" editor-type="<?php echo $type; ?>" data-toggle="tab"><?php echo $_language->get('tab_seo_editor_'.$type); ?></a></li>
				<?php } ?>
			</ul>
			<div class="tab-content">
			  <?php $f=0; foreach ($editor_types as $type) { ?>
				<div id="tab-editor-<?php echo $type; ?>" class="tab-pane<?php if(!$f) {echo ' active'; $f=1;} ?>">
          <?php if (empty($mlseo_friendly) && in_array($type, array('common', 'special'))) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo $_language->get('error_friendly_disabled'); ?></div>
          <?php } else if (empty($mlseo_url_absolute) && $type == 'absolute') { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo $_language->get('error_absolute_disabled'); ?></div>
          <?php } else if (empty($mlseo_404) && $type == '404') { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo $_language->get('error_404_disabled'); ?></div>
          <?php } else if (empty($mlseo_redirect) && $type == 'redirect') { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo $_language->get('error_redirect_disabled'); ?></div>
          <?php } ?>
					<table id="seo_editor_<?php echo $type; ?>" class="seo_editor table table-condensed table-hover table-striped table-curved" width="100%" cellspacing="0">
						<thead>
							<tr>
                <?php if (in_array($type, array('redirect', 'absolute'))) { ?>
                  <th data-column="query"><i class="fa fa-link gkd-badge"></i> <?php echo $_language->get('text_editor_query_'.$type); ?></th>
									<th data-column="redirect"><i class="fa fa-link gkd-badge purple"></i> <?php echo $_language->get('text_editor_url_'.$type); ?></th>
									<th data-column="delete"></th>
								<?php } else if($type == '404') { ?>
									<th data-column="query"><i class="fa fa-link gkd-badge"></i> <?php echo $_language->get('text_editor_query'); ?></th>
									<th data-column="count"><?php echo $_language->get('text_editor_count'); ?></th>
									<th data-column="delete"></th>
								<?php } else if($type == 'image') { ?>
                  <th style="width:1px"></th>
									<th data-column="name"><i class="fa fa-italic gkd-badge"></i> <?php echo $_language->get('text_editor_item_name'); ?></th>
									<th data-column="image"><i class="fa fa fa-picture-o gkd-badge orange"></i> <?php echo $_language->get('text_editor_image_name'); ?></th>
									<th data-column="image_alt"><i class="fa fa fa-picture-o gkd-badge orange"></i> <?php echo $_language->get('text_editor_image_alt'); ?></th>
									<th data-column="image_title"><i class="fa fa fa-picture-o gkd-badge orange"></i> <?php echo $_language->get('text_editor_image_title'); ?></th>
								<?php } else if(in_array($type, array('common', 'special'))) { ?>
									<th data-column="query"><i class="fa fa-link gkd-badge"></i> <?php echo $_language->get('text_editor_query_'.$type); ?></th>
									<th data-column="keyword"><i class="fa fa-link gkd-badge purple"></i> <?php echo $_language->get('text_editor_url'); ?></th>
									<th data-column="delete"></th>
								<?php } else { ?>
									<?php if(in_array($type, array('product', 'category', 'manufacturer'))) { ?>
										<th style="width:1px"></th>
										<th data-column="name"><i class="fa fa-italic gkd-badge"></i> <?php echo $_language->get('text_editor_name'); ?></th>
									<?php } ?>
									<?php if(in_array($type, array('information'))) { ?>
										<th data-column="title"><i class="fa fa-italic gkd-badge"></i> <?php echo $_language->get('text_editor_title'); ?></th>
									<?php } ?>
									<th data-column="seo_keyword" style="width:25%"><i class="fa fa-link gkd-badge purple"></i> <?php echo $_language->get('text_editor_url'); ?></th>
									<?php if(!in_array($type, array('manufacturer'))) { ?>
										<th data-column="meta_title"><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('text_editor_meta_title'); ?></th>
										<th data-column="meta_keyword"><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('text_editor_meta_keyword'); ?></th>
										<th data-column="meta_description"><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('text_editor_meta_description'); ?></th>
									<?php } ?>
									<?php if(in_array($type, array('product')) && version_compare(VERSION, '1.5.3', '>=') && !$store_id) { ?>
										<th data-column="tag"><i class="fa fa-tags gkd-badge cyan"></i> <?php echo $_language->get('text_editor_tag'); ?></th>
									<?php } ?>
                  <?php if(in_array($type, array('product'))) { ?>
										<th data-column="related"><i class="fa fa-code-fork gkd-badge darkblue"></i> <?php echo $_language->get('text_editor_related'); ?></th>
									<?php } ?>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr><td style="height:200px"></td></tr>
						</tbody>
					</table>
          <?php if($type == '404') { ?>
					<div class="btn-group more_actions">
					  <button type="button" class="btn btn-default deleteAliases redirOnly" data-toggle="modal" data-target="#close"><i class="fa fa-minus"></i> <?php echo $_language->get('text_seo_remove_redirected'); ?></button>
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
					  <ul class="dropdown-menu" role="menu">
						<li><a class="deleteAliases redirOnly"><i class="fa fa-minus"></i> <?php echo $_language->get('text_seo_remove_redirected'); ?></a></li>
						<li><a class="deleteAliases"><i class="fa fa-close"></i> <?php echo $_language->get('text_seo_remove_urls'); ?></a></li>
					  </ul>
					</div>
          <div>&nbsp;
            <div class="gkdwidget gkdwidget-color-blueDark">
              <header role="heading">
                <i class="icon fa fa-info-circle fa-2x pull-left"></i>
                <ul class="nav nav-tabs pull-left in">
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_seo_editor_404'); ?></span></a></li>
                </ul>
              </header>
              <div class="gkdwidget-container" style="display:none">
                <div class="tab-content">
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_404'); ?></div>
                </div>
              </div>
            </div>
          </div>
					<?php } else if($type == 'redirect') { ?>
					<div class="btn-group more_actions">
					  <button type="button" class="btn btn-default" data-toggle="modal" data-target="#newAliasModal" onclick="javascript:$('#newAliasModal input').val('')"><i class="fa fa-plus"></i> <?php echo $_language->get('text_seo_add_url'); ?></button>
					  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
					  <ul class="dropdown-menu" role="menu">
						<li><a data-toggle="modal" data-target="#newAliasModal" onclick="javascript:$('#newAliasModal input[name=query]').val('')"><i class="fa fa-plus"></i> <?php echo $_language->get('text_seo_add_url'); ?></a></li>
						<li><a class="deleteAliases"><i class="fa fa-close"></i> <?php echo $_language->get('text_seo_remove_urls'); ?></a></li>
					  </ul>
					</div>
          <div>&nbsp;
            <div class="gkdwidget gkdwidget-color-blueDark">
              <header role="heading">
                <i class="icon fa fa-info-circle fa-2x pull-left"></i>
                <ul class="nav nav-tabs pull-left in">
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_seo_editor_redirect'); ?></span></a></li>
                </ul>
              </header>
              <div class="gkdwidget-container" style="display:none">
                <div class="tab-content">
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_redirections'); ?></div>
                </div>
              </div>
            </div>
          </div>
					<?php } else if (in_array($type, array('absolute', 'common', 'special'))) { ?>
					<div class="btn-group more_actions">
					  <button type="button" class="btn btn-default" data-toggle="modal" data-target="#newAliasModal" onclick="javascript:$('#newAliasModal input[name=query]').val('')"><i class="fa fa-plus"></i> <?php echo $_language->get('text_seo_add_url'); ?></button>
					  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
					  <ul class="dropdown-menu" role="menu">
						<li><a data-toggle="modal" data-target="#newAliasModal" onclick="javascript:$('#newAliasModal input[name=query]').val('')"><i class="fa fa-plus"></i> <?php echo $_language->get('text_seo_add_url'); ?></a></li>
					  <?php if($type == 'common') { ?>
						<li data-toggle="tooltip" data-placement="right" title="<?php echo $_language->get('text_seo_reset_urls_tooltip'); ?>"><a class="restoreAliases"><i class="fa fa-magic"></i> <?php echo $_language->get('text_seo_reset_urls'); ?></a></li>
						<li data-toggle="tooltip" data-placement="right" title="<?php echo $_language->get('text_seo_export_urls_tooltip'); ?>"><a class="exportAliases"><i class="fa fa-save"></i> <?php echo $_language->get('text_seo_export_urls'); ?></a></li>
					  <?php } ?>
						<li><a class="deleteAliases"><i class="fa fa-close"></i> <?php echo $_language->get('text_seo_remove_urls'); ?></a></li>
					  </ul>
					</div>
          <div>&nbsp;
            <div class="gkdwidget gkdwidget-color-blueDark">
              <header role="heading">
                <i class="icon fa fa-info-circle fa-2x pull-left"></i>
                <ul class="nav nav-tabs pull-left in">
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('title_common_overview'); ?></span></a></li>
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_seo_editor_absolute'); ?></span></a></li>
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_seo_editor_common'); ?></span></a></li>
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('tab_seo_editor_special'); ?></span></a></li>
                </ul>
              </header>
              <div class="gkdwidget-container" style="display:none">
                <div class="tab-content">
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_common_overview'); ?></div>
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_absolute'); ?></div>
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_common'); ?></div>
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_special'); ?></div>
                </div>
              </div>
            </div>
          </div>
					<?php } ?>
          <?php if(in_array($type, array('product', 'category', 'information'))) { ?>
          <div>&nbsp;
            <div class="gkdwidget gkdwidget-color-blueDark">
              <header role="heading">
                <i class="icon fa fa-info-circle fa-2x pull-left"></i>
                <ul class="nav nav-tabs pull-left in">
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('text_info_seo_titles_tab'); ?></span></a></li>
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('text_info_limits_tab'); ?></span></a></li>
                </ul>
              </header>
              <div class="gkdwidget-container" style="display:none">
                <div class="tab-content">
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_seo_titles'); ?></div>
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_limits'); ?></div>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
				</div>
				<?php } ?>
			</div>
		</div>
    
		<div class="tab-pane" id="tab-general">
			<ul class="nav nav-pills nav-stacked col-md-2">
				<li class="active"><a href="#tab-general-1" data-toggle="pill"><i class="fa fa-cog"></i> <?php echo $_language->get('text_seo_tab_general_1'); ?></a></li>
				<li><a href="#tab-general-14" data-toggle="pill"><i class="fa fa-mail-forward"></i> <?php echo $_language->get('text_seo_tab_general_14'); ?></a></li>
				<li><a href="#tab-general-2" data-toggle="pill"><i class="fa fa-globe"></i> <?php echo $_language->get('text_seo_tab_general_2'); ?></a></li>
				<!--li><a href="#tab-general-9" data-toggle="pill"><i class="fa fa-cubes"></i> <?php echo $_language->get('text_seo_tab_general_9'); ?></a></li-->
				<li><a href="#tab-general-3" data-toggle="pill"><i class="fa fa-flag"></i> <?php echo $_language->get('text_seo_tab_general_3'); ?></a></li>
				<li><a href="#tab-general-6" data-toggle="pill"><i class="fa fa-file-o"></i> <?php echo $_language->get('text_seo_tab_general_6'); ?></a></li>
				<li><a href="#tab-general-4" data-toggle="pill"><i class="fa fa-edit"></i> <?php echo $_language->get('text_seo_tab_general_4'); ?></a></li>
				<li><a href="#tab-general-8" data-toggle="pill"><i class="fa fa-code-fork"></i> <?php echo $_language->get('text_seo_tab_general_8'); ?></a></li>
				<li><a href="#tab-general-10" data-toggle="pill"><i class="fa fa-comments-o"></i> <?php echo $_language->get('text_seo_tab_general_10'); ?></a></li>
				<li><a href="#tab-general-11" data-toggle="pill"><i class="fa fa-crosshairs"></i> <?php echo $_language->get('text_seo_tab_general_11'); ?></a></li>
				<li><a href="#tab-general-12" data-toggle="pill"><i class="fa fa-sitemap"></i> <?php echo $_language->get('text_seo_tab_general_12'); ?></a></li>
				<li><a href="#tab-general-13" data-toggle="pill"><i class="fa fa-android"></i> <?php echo $_language->get('text_seo_tab_general_13'); ?></a></li>
        <?php if (!$store_id) { ?>
				<li><a href="#tab-general-5" data-toggle="pill"><i class="fa fa-magic"></i> <?php echo $_language->get('text_seo_tab_general_5'); ?></a></li>
				<li><a href="#tab-general-15" data-toggle="pill"><i class="fa fa-bolt"></i> <?php echo $_language->get('tab_seo_update'); ?></a></li>
        <?php } ?>
				<li><a href="#tab-general-16" data-toggle="pill"><i class="fa fa-cubes"></i> <?php echo $_language->get('text_seo_tab_general_16'); ?></a></li>
				<!--li><a href="#tab-general-7" data-toggle="pill"><i class="fa fa-dashboard"></i> <?php echo $_language->get('text_seo_tab_general_7'); ?></a></li-->
			</ul>
			<div class="tab-content col-md-10">
				<div class="tab-pane active" id="tab-general-1">
          <table class="form">
          <?php /*
            <tr>
              <td><?php echo $_language->get('text_seo_package_status'); ?></td>
              <td colspan="2" style="padding-bottom:30px">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-power-off"></i></span>
                  <input type="text" name="mlseo_enabled" value="1" class="toggler" data-mode="background" data-on-text="<?php echo $_language->get('text_enabled'); ?>" data-off-text="<?php echo $_language->get('text_disabled'); ?>" data-icons="" />
                </div>
              </td>
            </tr>
            */ ?>
            <tr>
              <td><?php echo $_language->get('text_seo_components'); ?></td>
              <td colspan="2" style="padding-top:25px">
                <table class="table">
                  <thead>
                    <tr>
                      <th><?php echo $_language->get('text_enabled'); ?></th>
                      <th><?php echo $_language->get('text_description'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><input class="checkable" data-label="<?php echo $_language->get('tab_seo_editor_absolute'); ?>" type="checkbox" name="mlseo_url_absolute" value="1" <?php if(!empty($mlseo_url_absolute)) echo 'checked="checked"'; ?> /></td>
                      <td><?php echo $_language->get('info_seo_absolute'); ?></td>
                    </tr>
                    <tr>
                      <td><input class="checkable" data-label="<?php echo $_language->get('tab_seo_editor_common'); ?>" type="checkbox" name="mlseo_friendly" value="1" <?php if(!empty($mlseo_friendly)) echo 'checked="checked"'; ?> /></td>
                      <td><?php echo $_language->get('info_seo_friendly'); ?></td>
                    </tr>
                    <tr>
                      <td><input class="checkable" data-label="<?php echo $_language->get('tab_seo_editor_404'); ?>" type="checkbox" name="mlseo_404" value="1" <?php if(!empty($mlseo_404)) echo 'checked="checked"'; ?> /></td>
                      <td><?php echo $_language->get('info_seo_404'); ?></td>
                    </tr>
                    <tr>
                      <td><input class="checkable" data-label="<?php echo $_language->get('tab_seo_editor_redirect'); ?>" type="checkbox" name="mlseo_redirect" value="1" <?php if(!empty($mlseo_redirect)) echo 'checked="checked"'; ?> /></td>
                      <td><?php echo $_language->get('info_seo_redirect'); ?></td>
                    </tr>
                    <?php if (version_compare(VERSION, '2', '>=')) { ?>
                    <tr>
                      <td><input class="checkable" data-label="<?php echo $_language->get('text_multistore'); ?>" type="checkbox" name="mlseo_multistore" value="1" <?php if(!empty($mlseo_multistore)) echo 'checked="checked"'; ?> <?php if(count($stores) === 1) echo 'disabled="disabled"'; ?>/></td>
                      <td><?php echo $_language->get('info_multistore'); ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </td>
            </tr>
            <!--tr class="info">
              <td><i class='fa fa-info'></i></td>
              <td colspan="2" style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_general'); ?></td>
            </tr-->
          </table>
				</div>
        <div class="tab-pane" id="tab-general-16">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_banner'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_banners" name="mlseo_banners" value="1" <?php if($mlseo_banners) echo 'checked="checked"'; ?> /></td>
              <td><span class="help"><?php echo $_language->get('text_seo_banner_help'); ?></span></td>
            </tr>
          </table>
				</div>
        <div class="tab-pane" id="tab-general-14">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_redirect_http'); ?></td>
              <td colspan="2">
                <select class="form-control" name="mlseo_redirect_http">
                  <option value=""><?php echo $_language->get('text_disabled'); ?></option>
                  <optgroup label="<?php echo $_language->get('text_seo_redirect_ssl'); ?>">
                    <option value="5" <?php if($mlseo_redirect_http == '5') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_5'); ?></option>
                    <option value="6" <?php if($mlseo_redirect_http == '6') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_6'); ?></option>
                  </optgroup>
                  <optgroup label="<?php echo $_language->get('text_seo_redirect_www'); ?>">
                    <option value="7" <?php if($mlseo_redirect_http == '7') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_7'); ?></option>
                    <option value="8" <?php if($mlseo_redirect_http == '8') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_8'); ?></option>
                  </optgroup>
                  <optgroup label="<?php echo $_language->get('text_seo_redirect_sslwww'); ?>">
                    <option value="4" <?php if($mlseo_redirect_http == '4') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_4'); ?></option>
                    <option value="3" <?php if($mlseo_redirect_http == '3') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_3'); ?></option>
                    <option value="2" <?php if($mlseo_redirect_http == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_2'); ?></option>
                    <option value="1" <?php if($mlseo_redirect_http == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_http_1'); ?></option>
                  </optgroup>
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_redirect_canonical'); ?></td>
              <td colspan="2">
                <select class="form-control" name="mlseo_redirect_canonical">
                  <option value=""><?php echo $_language->get('text_disabled'); ?></option>
                  <option value="1" <?php if($mlseo_redirect_canonical == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_canonical_1'); ?></option>
                  <option value="2" <?php if($mlseo_redirect_canonical == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_redirect_canonical_2'); ?></option>
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_redirect_dynamic'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_redirect_dynamic" name="mlseo_redirect_dynamic" value="1" <?php if($mlseo_redirect_dynamic) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
          </table>
				</div>
        <?php if (!$store_id) { ?>
        <div class="tab-pane" id="tab-general-15">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_special_group'); ?></td>
              <td colspan="2">
                <select class="form-control" name="mlseo_special_group">
                  <option value=""><?php echo $_language->get('text_disabled'); ?></option>
                  <?php foreach ($customer_groups as $group) { ?>
                    <option value="<?php echo $group['customer_group_id']; ?>" <?php if($mlseo_special_group == $group['customer_group_id']) echo 'selected="selected"'; ?>><?php echo $group['name']; ?></option>
                  <?php } ?>
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_format_tag'); ?></td>
              <td colspan="2">
                <input class="switch" type="checkbox" id="mlseo_format_tag" name="mlseo_format_tag" value="1" <?php if($mlseo_flag_short) echo 'checked="checked"'; ?> />
              </td>
            </tr>
          </table>
        </div>
        <?php } ?>
				<div class="tab-pane" id="tab-general-2">
					<table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_flag'); ?></td>
              <td>
                <select class="form-control" name="mlseo_flag_mode">
                  <option value=""><?php echo $_language->get('text_disabled'); ?></option>
                  <option value="tag" <?php if($mlseo_flag) echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_flag_tag'); ?> (example.com/en)</option>
                  <option value="store" <?php if($mlseo_store_mode) echo 'selected="selected"'; ?>><?php echo $_language->get('text_seo_flag_store'); ?>  (en.example.com)</option>
                </select>
                <input type="hidden" name="mlseo_flag" value="1" <?php if($mlseo_flag) echo 'checked="checked"'; ?> />
                <input type="hidden" name="mlseo_store_mode" value="1" <?php if($mlseo_store_mode) echo 'checked="checked"'; ?> />
              </td>
            </tr>
            <table class="flagmode-tag form">
              <tr>
                <td><?php echo $_language->get('text_seo_flag_short'); ?></td>
                <td><input class="switch" type="checkbox" id="mlseo_flag_short" name="mlseo_flag_short" value="1" <?php if($mlseo_flag_short) echo 'checked="checked"'; ?> /></td>
              </tr>
              <tr>
                <td><?php echo $_language->get('text_seo_flag_default'); ?></td>
                <td><input class="switch" type="checkbox" id="mlseo_flag_default" name="mlseo_flag_default" value="1" <?php if($mlseo_flag_default) echo 'checked="checked"'; ?> /></td>
              </tr>
              <tr>
                <td><?php echo $_language->get('text_seo_flag_upper'); ?></td>
                <td><input class="switch" type="checkbox" id="mlseo_flag_upper" name="mlseo_flag_upper" value="1" <?php if($mlseo_flag_upper) echo 'checked="checked"'; ?> /></td>
              </tr>
            </table>
            <table class="flagmode-store form">
              <tr>
                <td><?php echo $_language->get('text_seo_substore_config'); ?></td>
                <td>
                  <?php foreach($languages as $language) { ?>
                    <p><img style="margin-top:-2px" src="<?php echo $language['image']; ?>" alt="<?php echo $language['code']; ?>"/>  <?php echo !empty($lang_to_store[$language['code']]['config_url']) ? $lang_to_store[$language['code']]['config_url'] : '<span class="text-danger">'.$_language->get('text_no_language_defined').'</span>'; ?></p>
                  <?php } ?>
                </td>
              </tr>
            </table>
          <div class="gkdwidget gkdwidget-color-blueDark">
            <header role="heading">
              <i class="icon fa fa-info-circle fa-2x pull-left"></i>
              <ul class="nav nav-tabs pull-left in">
                <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_seo_flag_tag'); ?></a></li>
                <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_seo_flag_store'); ?></a></li>
              </ul>
            </header>
            <div class="gkdwidget-container" style="display:none">
              <div class="tab-content">
                <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_flag_mode'); ?></div>
                <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_store_mode'); ?></div>
              </div>
            </div>
          </div>
				</div>
				<div class="tab-pane" id="tab-general-3">
					<table class="form">
            <tr>
							<td><?php echo $_language->get('text_seo_hreflang'); ?></td>
							<td><input class="switch" type="checkbox" id="mlseo_hreflang" name="mlseo_hreflang" value="1" <?php if($mlseo_hreflang) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
            <tr class="info">
            <td><i class='fa fa-info'></i></td>
							<td colspan="2" style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_general_3'); ?></td>
            </tr>
          </table>
        </div>
    <div class="tab-pane" id="tab-general-6">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-friendly-1" data-toggle="tab"><?php echo $_language->get('tab_friendly_pagination'); ?></a></li>
        <li><a href="#tab-friendly-2" data-toggle="tab"><?php echo $_language->get('tab_friendly_sorting'); ?></a></li>
        <li><a href="#tab-friendly-3" data-toggle="tab"><?php echo $_language->get('tab_friendly_tag'); ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="tab-friendly-1">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_pagination'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_pagination" name="mlseo_pagination" value="1" <?php if($mlseo_pagination) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
            <!--tr>
              <td><?php echo $_language->get('text_seo_pagination_keyword'); ?></td>
              <td colspan="2">
                <?php foreach ($languages as $language) { ?>
                <div class="input-group">
                  <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
                  <input type="text" class="form-control" placeholder="page" name="mlseo_pagination_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_pagination_'.$language['language_id']})) echo ${'mlseo_pagination_'.$language['language_id']}; ?>" />
                </div>
                <?php } ?>
              </td>
            </tr-->
            <?php if (version_compare(VERSION, '2.2', '>=')) { ?>
            <tr>
              <td><?php echo $_language->get('text_seo_pagination_fix'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_pagination_fix" name="mlseo_pagination_fix" value="1" <?php if($mlseo_pagination_fix) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
            <?php } ?>
            <tr>
              <td><?php echo $_language->get('text_seo_pagination_canonical'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_pagination_canonical" name="mlseo_pagination_canonical" value="1" <?php if($mlseo_pagination_canonical) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
            <tr class="info">
              <td><i class='fa fa-info'></i></td>
              <td colspan="2" style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_general_6'); ?></td>
            </tr>
          </table>
        </div>
        <div class="tab-pane" id="tab-friendly-2">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_sort'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_sort" name="mlseo_sort" value="1" <?php if($mlseo_sort) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_sorting_keyword'); ?></td>
              <td colspan="2">
                <?php foreach ($languages as $language) { ?>
                <div class="input-group">
                  <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
                  <input type="text" class="form-control" placeholder="sort" name="mlseo_sort_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_sort_'.$language['language_id']})) echo ${'mlseo_sort_'.$language['language_id']}; ?>" />
                </div>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_order_keyword'); ?></td>
              <td colspan="2">
                <?php foreach ($languages as $language) { ?>
                <div class="input-group">
                  <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
                  <input type="text" class="form-control" placeholder="asc|desc" name="mlseo_order_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_order_'.$language['language_id']})) echo ${'mlseo_order_'.$language['language_id']}; ?>" />
                </div>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_sortname_keyword'); ?></td>
              <td colspan="2">
                <?php foreach ($languages as $language) { ?>
                <div class="input-group">
                  <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
                  <input type="text" class="form-control" placeholder="name|price|rating|model" name="mlseo_sortname_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_sortname_'.$language['language_id']})) echo ${'mlseo_sortname_'.$language['language_id']}; ?>" />
                </div>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_limit_keyword'); ?></td>
              <td colspan="2">
                <?php foreach ($languages as $language) { ?>
                <div class="input-group">
                  <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
                  <input type="text" class="form-control" placeholder="limit" name="mlseo_limit_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_limit_'.$language['language_id']})) echo ${'mlseo_limit_'.$language['language_id']}; ?>" />
                </div>
                <?php } ?>
              </td>
            </tr>
          </table>
        </div>
        <div class="tab-pane" id="tab-friendly-3">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_tag'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_tag" name="mlseo_tag" value="1" <?php if($mlseo_tag) echo 'checked="checked"'; ?> /></td>
              <td></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_tag_keyword'); ?></td>
              <td colspan="2">
                <?php foreach ($languages as $language) { ?>
                <div class="input-group">
                  <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
                  <input type="text" class="form-control" placeholder="tag" name="mlseo_fpp_tag_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_fpp_tag_'.$language['language_id']})) echo ${'mlseo_fpp_tag_'.$language['language_id']}; ?>" />
                </div>
                <?php } ?>
              </td>
            </tr>
          </table>
        </div>
      </div>
		</div>
    
    <div class="tab-pane" id="tab-general-4">
      <table class="form">
        <?php if (!$store_id) { ?>
        <tr>
          <td style="width:250px;"><?php echo $_language->get('text_seo_whitespace'); ?></td>
          <td><input class="form-control" type="text" name="mlseo_whitespace" value="<?php echo $mlseo_whitespace; ?>" size="1" /></td>
        </tr>
        <?php } ?>
        <tr>
          <td><?php echo $_language->get('text_seo_extension'); ?></td>
          <td><input class="form-control" type="text" name="mlseo_extension" value="<?php echo $mlseo_extension; ?>" size="7" placeholder=".html"/></td>
        </tr>
        <?php if (!$store_id) { ?>
        <tr>
          <td><?php echo $_language->get('text_seo_remove'); ?></td>
          <td>
            <?php foreach ($languages as $language) { ?>
            <div class="input-group">
              <span class="input-group-addon"><img src="<?php echo $language['image']; ?>" alt=""/></span>
              <input type="text" class="form-control" name="mlseo_remove_<?php echo $language['language_id']; ?>" value="<?php if(!empty(${'mlseo_remove_'.$language['language_id']})) echo ${'mlseo_remove_'.$language['language_id']}; ?>" placeholder="a,an,for,the,in,to"/>
            </div>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <td><?php echo $_language->get('text_seo_lowercase'); ?></td>
          <td><input class="switch" type="checkbox" id="mlseo_lowercase" name="mlseo_lowercase" value="1" <?php if($mlseo_lowercase) echo 'checked="checked"'; ?> /></td>
        </tr>
        <tr>
          <td><?php echo $_language->get('text_seo_duplicate'); ?></td>
          <td><input class="switch" type="checkbox" id="mlseo_duplicate" name="mlseo_duplicate" value="1" <?php if($mlseo_duplicate) echo 'checked="checked"'; ?> /></td>
        </tr>
        <tr>
          <td><?php echo $_language->get('text_seo_ascii'); ?></td>
            <td class="imgCheckbox">
              <?php foreach ($languages as $language) { ?>
              <img style="position:relative; bottom:12px; left:50px;" src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <input type="checkbox" class="checkable" value="1" data-label="<?php echo $language['name']; ?>" id="mlseo_ascii_<?php echo $language['language_id']; ?>" name="mlseo_ascii_<?php echo $language['language_id']; ?>" <?php if ($_config->get('mlseo_ascii_'.$language['language_id'])) echo 'checked="checked"'; ?>/> <br />
              <?php } ?>
            </td>
        </tr>
        <tr class="info">
        <td><i class='fa fa-info'></i></td>
          <td style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_transform'); ?></td>
        </tr>
        <?php } ?>
      </table>
    </div>
    
    <?php if (!$store_id) { ?>
    <div class="tab-pane" id="tab-general-5">
      <table class="form">
        <tr>
          <td><?php echo $_language->get('text_seo_autofill_desc'); ?></td>
          <td>
            <table class="table">
              <thead>
                <tr>
                  <th></th>
                  <th><?php echo $_language->get('text_insert'); ?></th>
                  <th><?php echo $_language->get('text_edit'); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><i class="fa fa-link gkd-badge purple"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_url'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautourl" value="1" <?php if(!empty($mlseo_insertautourl)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautourl" value="1" <?php if(!empty($mlseo_editautourl)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_title'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoseotitle" value="1" <?php if(!empty($mlseo_insertautoseotitle)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoseotitle" value="1" <?php if(!empty($mlseo_editautoseotitle)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_keyword'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautometakeyword" value="1" <?php if(!empty($mlseo_insertautometakeyword)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautometakeyword" value="1" <?php if(!empty($mlseo_editautometakeyword)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_description'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautometadesc" value="1" <?php if(!empty($mlseo_insertautometadesc)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautometadesc" value="1" <?php if(!empty($mlseo_editautometadesc)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa-header gkd-badge blue"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_h1'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoh1" value="1" <?php if(!empty($mlseo_insertautoh1)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoh1" value="1" <?php if(!empty($mlseo_editautoh1)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa-header gkd-badge blue"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_h2'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoh2" value="1" <?php if(!empty($mlseo_insertautoh2)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoh2" value="1" <?php if(!empty($mlseo_editautoh2)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa-header gkd-badge blue"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_h3'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoh3" value="1" <?php if(!empty($mlseo_insertautoh3)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoh3" value="1" <?php if(!empty($mlseo_editautoh3)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa fa-picture-o gkd-badge orange"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_image_title'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoimgtitle" value="1" <?php if(!empty($mlseo_insertautoimgtitle)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoimgtitle" value="1" <?php if(!empty($mlseo_editautoimgtitle)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa fa-picture-o gkd-badge orange"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_image_alt'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoimgalt" value="1" <?php if(!empty($mlseo_insertautoimgalt)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoimgalt" value="1" <?php if(!empty($mlseo_editautoimgalt)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa fa-picture-o gkd-badge orange"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_image_name'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautoimgname" value="1" <?php if(!empty($mlseo_insertautoimgname)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautoimgname" value="1" <?php if(!empty($mlseo_editautoimgname)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa fa-tags gkd-badge cyan"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_tag'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautotags" value="1" <?php if(!empty($mlseo_insertautotags)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautotags" value="1" <?php if(!empty($mlseo_editautotags)) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><i class="fa fa fa-code-fork gkd-badge darkblue"></i> <?php echo $_language->get('text_seo_autofill'); ?> <?php echo $_language->get('text_seo_mode_related'); ?> <?php echo $_language->get('text_seo_autofill_on'); ?></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_insert'); ?>" type="checkbox" name="mlseo_insertautorelated" value="1" <?php if(!empty($mlseo_insertautorelated)) echo 'checked="checked"'; ?> /></td>
                  <td><input class="checkable" data-label="<?php echo $_language->get('text_edit'); ?>" type="checkbox" name="mlseo_editautorelated" value="1" <?php if(!empty($mlseo_editautorelated)) echo 'checked="checked"'; ?> /></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td><?php echo $_language->get('text_seo_autotitle'); ?></td>
          <td class="multiple_switch">
            <div>
              <span> <?php echo $_language->get('text_insert'); ?></span>
              <input class="switch" type="checkbox" id="mlseo_insertautotitle" name="mlseo_insertautotitle" value="1" <?php if($mlseo_insertautotitle) echo 'checked="checked"'; ?> />
            </div>
            <div>
              <span><?php echo $_language->get('text_edit'); ?></span>
              <input class="switch" type="checkbox" id="mlseo_editautotitle" name="mlseo_editautotitle" value="1" <?php if($mlseo_editautotitle) echo 'checked="checked"'; ?> />
            </div>
          </td>
        </tr>
			</table>
    </div>
    <?php } ?>
    <div class="tab-pane" id="tab-general-7">
      <table class="form">
        <tr>
          <td><?php echo $_language->get('text_seo_urlcache'); ?></td>
          <td><!--input class="switch" type="checkbox" id="mlseo_cache" name="mlseo_cache" value="1" <?php if($mlseo_cache) echo 'checked="checked"'; ?> /></td-->
          <td></td>
        </tr>
        <input type="hidden" id="mlseo_cache" name="mlseo_cache" value="" />
        <tr class="info">
          <td><i class='fa fa-info'></i></td>
          <td colspan="2" style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_general_7'); ?></td>
        </tr>
      </table>
    </div>
    <div class="tab-pane" id="tab-general-8">
      <table class="form">
        <tr>
          <td><?php echo $_language->get('text_seo_canonical'); ?></td>
          <td><input class="switch" type="checkbox" id="mlseo_canonical" name="mlseo_canonical" value="1" <?php if($mlseo_canonical) echo 'checked="checked"'; ?> /></td>
          <td></td>
        </tr>
        <tr class="info">
          <td><i class='fa fa-info'></i></td>
          <td colspan="2" style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_general_8'); ?></td>
        </tr>
      </table>
    </div>
      <div class="tab-pane" id="tab-general-10">
        <table class="form">
          <tr>
            <td><?php echo $_language->get('text_seo_reviews'); ?></td>
            <td>
              <select class="form-control" name="mlseo_reviews">
                <option value=""><?php echo $_language->get('text_disabled'); ?></option>
                <option value="5" <?php if($mlseo_reviews == '5') echo 'selected="selected"'; ?>>5</option>
                <option value="10" <?php if($mlseo_reviews == '10') echo 'selected="selected"'; ?>>10</option>
                <option value="25" <?php if($mlseo_reviews == '25') echo 'selected="selected"'; ?>>25</option>
                <option value="50" <?php if($mlseo_reviews == '50') echo 'selected="selected"'; ?>>50</option>
                <option value="999" <?php if($mlseo_reviews == '999') echo 'selected="selected"'; ?>><?php echo $_language->get('text_all'); ?></option>
              </select>
            <!--input class="switch" type="checkbox" id="mlseo_reviews" name="mlseo_reviews" value="1" <?php if($mlseo_reviews) echo 'checked="checked"'; ?> /-->
            </td>
            <td></td>
          </tr>
          <tr>
            <td><?php echo $_language->get('text_seo_redir_reviews'); ?></td>
            <td><input class="switch" type="checkbox" id="mlseo_redir_reviews" name="mlseo_redir_reviews" value="1" <?php if($mlseo_redir_reviews) echo 'checked="checked"'; ?> /></td>
            <td></td>
          </tr>
          <tr class="info">
            <td><i class='fa fa-info'></i></td>
            <td colspan="2" style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_general_10'); ?></td>
          </tr>
        </table>
      </div>
      <div class="tab-pane" id="tab-general-11">
        <table class="form">
          <tr>
            <td><?php echo $_language->get('text_headers_lastmod'); ?></td>
            <td>
              <?php foreach (array('product', 'category') as $type) { ?>
              <input type="checkbox" class="checkable" value="1" data-label="<?php echo $_language->get('tab_seo_editor_'.$type); ?>" id="mlseo_header_lm_<?php echo $type; ?>" name="mlseo_header_lm_<?php echo $type; ?>" <?php if ($_config->get('mlseo_header_lm_'.$type)) echo 'checked="checked"'; ?>/> <br />
              <?php } ?>
            </td>
          </tr>
        </table>
        <div class="gkdwidget gkdwidget-color-blueDark">
          <header role="heading">
            <i class="icon fa fa-info-circle fa-2x pull-left"></i>
            <ul class="nav nav-tabs pull-left in">
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('tab_info_request'); ?></a></li>
            </ul>
          </header>
          <div class="gkdwidget-container" style="display:none">
            <div class="tab-content">
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_request'); ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane" id="tab-general-12">
        <table class="form">
          <tr>
            <td><?php echo $_language->get('text_seopackage_sitemap'); ?></td>
            <td>
              <p><?php echo $_language->get('text_info_general_12'); ?></p>
              <p><a class="btn btn-primary" href="<?php echo $link_sitemap; ?>" target="_blank"><i class="fa fa-sitemap"></i> <?php echo $_language->get('text_seopackage_sitemap'); ?></a></p>
            </td>
          </tr>
        </table>
      </div>
      <div class="tab-pane" id="tab-general-13">
        <table class="form">
          <tr>
            <td><?php echo $_language->get('entry_robots'); ?>:</td>
            <td>
              <input type="checkbox" class="switch" value="1" id="mlseo_robots" name="mlseo_robots" <?php if ($mlseo_robots) echo 'checked="checked"'; ?>/> <br />
            </td>
          </tr>
          <tr>
            <td><?php echo $_language->get('entry_robots_default'); ?>:</td>
            <td>
              <select class="form-control" name="mlseo_robots_default">
                <option value="" <?php if($mlseo_robots == 'all') echo 'selected="selected"'; ?>>all</option>
                <option value="noindex" <?php if($mlseo_robots == 'noindex') echo 'selected="selected"'; ?>>noindex</option>
                <option value="nofollow" <?php if($mlseo_robots == 'nofollow') echo 'selected="selected"'; ?>>nofollow</option>
                <option value="none" <?php if($mlseo_robots == 'none') echo 'selected="selected"'; ?>>none</option>
                <option value="noimageindex" <?php if($mlseo_robots == 'noimageindex') echo 'selected="selected"'; ?>>noimageindex</option>
              </select>
            </td>
          </tr>
        </table>
        <div class="gkdwidget gkdwidget-color-blueDark">
          <header role="heading">
            <i class="icon fa fa-info-circle fa-2x pull-left"></i>
            <ul class="nav nav-tabs pull-left in">
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_seo_tab_general_13'); ?></a></li>
            </ul>
          </header>
          <div class="gkdwidget-container" style="display:none">
            <div class="tab-content">
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_robots'); ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div style="clear:both"></div>
  </div>
    
		<div class="tab-pane" id="tab-store">
        <table class="form">
          <tr>
            <td><i class="fa fa-google gkd-badge purple"></i> <?php echo $_language->get('entry_analytics_code'); ?></td>
            <td><input type="text" class="form-control store_seo_title" name="mlseo_store[<?php echo $store_id; ?>][analytics]" value="<?php echo isset($mlseo_store[$store_id]['analytics']) ? $mlseo_store[$store_id]['analytics'] : ''; ?>"/></td>
          </tr>
        </table>
			  <ul id="language-<?php echo $store_id; ?>" class="nav nav-tabs">
				<?php $first=0; foreach ($languages as $language) { ?>
				<li<?php if(!$first) { echo ' class="active"'; $first=1;} ?>><a href="#tab-language-<?php echo $store_id; ?>-<?php echo $language['language_id']; ?>" data-toggle="tab"><img src="<?php echo $language['image']; ?>" alt=""/> <?php echo $language['name']; ?></a></li>
				<?php } ?>
			  </ul>
				<div class="tab-content">
			  <?php $first=0; foreach ($languages as $language) { ?>
			  <div id="tab-language-<?php echo $store_id; ?>-<?php echo $language['language_id']; ?>" class="tab-pane<?php if(!$first) { echo ' active'; $first=1;} ?>">
        <fieldset>
          <legend><?php echo $_language->get('store_seo_global'); ?></legend>
          <table class="form">
            <tr>
              <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('entry_store_seo_title_extra'); ?></td>
              <td>
                <input type="text" class="form-control store_seo_title" name="mlseo_title_prefix[<?php echo $store_id . $language['language_id']; ?>]" value="<?php echo isset($mlseo_title_prefix[$store_id.$language['language_id']]) ? $mlseo_title_prefix[$store_id.$language['language_id']] : ''; ?>" placeholder="<?php echo $_language->get('seo_title_prefix'); ?>"/></td>
                <td class="text-center"><?php echo $_language->get('text_seo_mode_title'); ?></td>
                <td><input type="text" class="form-control store_seo_title" name="mlseo_title_suffix[<?php echo $store_id . $language['language_id']; ?>]" value="<?php echo isset($mlseo_title_suffix[$store_id.$language['language_id']]) ? $mlseo_title_suffix[$store_id.$language['language_id']] : ''; ?>" placeholder="<?php echo $_language->get('seo_title_suffix'); ?>"/>
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset style="margin-top:35px">
          <legend><?php echo $_language->get('store_seo_home'); ?></legend>
          <table class="form">
            <tr>
              <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('entry_store_seo_title'); ?></td>
              <td><input type="text" class="form-control store_seo_title" name="mlseo_store[<?php echo $store_id . $language['language_id']; ?>][seo_title]" value="<?php echo isset($mlseo_store[$store_id.$language['language_id']]['seo_title']) ? $mlseo_store[$store_id.$language['language_id']]['seo_title'] : ''; ?>"/></td>
            </tr>
            <tr>
              <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('entry_store_desc'); ?></td>
              <td><textarea class="form-control store_seo_desc" name="mlseo_store[<?php echo $store_id . $language['language_id']; ?>][description]" cols="60" rows="6"><?php echo isset($mlseo_store[$store_id.$language['language_id']]['description']) ? $mlseo_store[$store_id.$language['language_id']]['description'] : ''; ?></textarea></td>
            </tr>
            <tr>
              <td><i class="fa fa-code gkd-badge green"></i> <?php echo $_language->get('entry_store_keywords'); ?></td>
              <td><textarea class="form-control" name="mlseo_store[<?php echo $store_id . $language['language_id']; ?>][keywords]" cols="60" rows="6"><?php echo isset($mlseo_store[$store_id.$language['language_id']]['keywords']) ? $mlseo_store[$store_id.$language['language_id']]['keywords'] : ''; ?></textarea></td>
            </tr>
            <tr>
              <td><i class="fa fa-header gkd-badge blue"></i> <?php echo $_language->get('entry_store_title'); ?></td>
              <td><input type="text" class="form-control" name="mlseo_store[<?php echo $store_id . $language['language_id']; ?>][title]" value="<?php echo isset($mlseo_store[$store_id.$language['language_id']]['title']) ? $mlseo_store[$store_id.$language['language_id']]['title'] : ''; ?>" size="63" placeholder="<?php echo $_language->get('info_store_heading'); ?>"/></td>
            </tr>
            <tr>
              <td><i class="fa fa-header gkd-badge blue"></i> <?php echo $_language->get('entry_store_h2'); ?></td>
              <td><input type="text" class="form-control" name="mlseo_store[<?php echo $store_id . $language['language_id']; ?>][h2]" value="<?php echo isset($mlseo_store[$store_id.$language['language_id']]['h2']) ? $mlseo_store[$store_id.$language['language_id']]['h2'] : ''; ?>" size="63" placeholder="<?php echo $_language->get('info_store_heading'); ?>"/></td>
            </tr>
            <tr>
              <td><i class="fa fa-header gkd-badge blue"></i> <?php echo $_language->get('entry_store_h3'); ?></td>
              <td><input type="text" class="form-control" name="mlseo_store[<?php echo $store_id . $language['language_id']; ?>][h3]" value="<?php echo isset($mlseo_store[$store_id.$language['language_id']]['h3']) ? $mlseo_store[$store_id.$language['language_id']]['h3'] : ''; ?>" size="63" placeholder="<?php echo $_language->get('info_store_heading'); ?>"/></td>
            </tr>
          </table>
        </fieldset>
			  </div>
			  <?php } ?>
			</div>
      <div>&nbsp;
        <div class="gkdwidget gkdwidget-color-blueDark">
          <header role="heading">
            <i class="icon fa fa-info-circle fa-2x pull-left"></i>
            <ul class="nav nav-tabs pull-left in">
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('tab_seo_store'); ?></a></li>
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_info_seo_titles_tab'); ?></a></li>
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('entry_robots'); ?></a></li>
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_info_limits_tab'); ?></a></li>
              <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_info_analytics_tab'); ?></a></li>
            </ul>
          </header>
          <div class="gkdwidget-container" style="display:none">
            <div class="tab-content">
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_store'); ?></div>
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_seo_titles'); ?></div>
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_robots'); ?></div>
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_limits'); ?></div>
              <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('text_info_analytics'); ?></div>
            </div>
          </div>
        </div>
      </div>
		</div>
    <div class="tab-pane" id="tab-snippet">
      <ul class="nav nav-pills nav-stacked col-md-2">
				<li class="active"><a href="#tab-snippet-1" data-toggle="pill"><i class="fa fa-google"></i> <?php echo $_language->get('text_seo_tab_snippet_1'); ?></a></li>
				<li><a href="#tab-snippet-4" data-toggle="pill"><i class="fa fa-google"></i> <?php echo $_language->get('text_seo_tab_snippet_4'); ?></a></li>
				<li><a href="#tab-snippet-2" data-toggle="pill"><i class="fa fa-facebook-square"></i> <?php echo $_language->get('text_seo_tab_snippet_2'); ?></a></li>
				<li><a href="#tab-snippet-3" data-toggle="pill"><i class="fa fa-twitter"></i> <?php echo $_language->get('text_seo_tab_snippet_3'); ?></a></li>
			</ul>
			<div class="tab-content col-md-10">
				<div class="tab-pane active" id="tab-snippet-1">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('entry_enable_microdata'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_microdata" name="mlseo_microdata" value="1" <?php if($mlseo_microdata) echo 'checked="checked"'; ?> /></td>
            </tr>
          </table>
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-microdata-1" data-toggle="tab"><?php echo $_language->get('tab_microdata_1'); ?></a></li>
            <li><a href="#tab-microdata-2" data-toggle="tab"><?php echo $_language->get('tab_microdata_2'); ?></a></li>
            <li><a href="#tab-microdata-3" data-toggle="tab"><?php echo $_language->get('tab_microdata_3'); ?></a></li>
            <li><a href="#tab-microdata-4" data-toggle="tab"><?php echo $_language->get('tab_microdata_4'); ?></a></li>
            <li><a href="#tab-microdata-5" data-toggle="tab"><?php echo $_language->get('tab_microdata_5'); ?></a></li>
            <li><a href="#tab-microdata-6" data-toggle="tab"><?php echo $_language->get('tab_microdata_6'); ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-microdata-1">
              <table class="form">
                <tr>
                  <td><?php echo $_language->get('text_enabled'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_microdata_data_product" name="mlseo_microdata_data[product]" value="1" <?php if(!empty($mlseo_microdata_data['product'])) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_data'); ?></td>
                  <td class="checkboxinline">
                    <?php foreach(array('model','brand','reviews','upc','mpn','ean','isbn') as $rstype) { ?>
                    <input class="checkable" data-label="<?php echo $_language->get('entry_'.$rstype); ?>" type="checkbox" name="mlseo_microdata_data[<?php echo $rstype; ?>]" value="1" <?php if(!empty($mlseo_microdata_data[$rstype])) echo 'checked="checked"'; ?> />
                    <?php } ?>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tab-pane" id="tab-microdata-2">
              <table class="form">
                <tr>
                  <td><?php echo $_language->get('text_enabled'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_microdata_data_organization" name="mlseo_microdata_data[organization]" value="1" <?php if(!empty($mlseo_microdata_data['organization'])) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_data'); ?></td>
                  <td>
                    <input class="checkable" data-label="<?php echo $_language->get('entry_microdata_search'); ?>" type="checkbox" id="mlseo_microdata_data_org_search" name="mlseo_microdata_data[organization_search]" value="1" <?php if(!empty($mlseo_microdata_data['organization_search'])) echo 'checked="checked"'; ?> /><br />
                  </td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_contact'); ?></td>
                  <td>
                    <div class="input-group" style="width:100%">
                      <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                      <input style="width:60%" type="text" class="form-control" placeholder="<?php echo $_language->get('entry_phone'); ?>" name="mlseo_microdata_data[contact][0][phone]" value="<?php if(!empty($mlseo_microdata_data['contact'][0]['phone'])) echo $mlseo_microdata_data['contact'][0]['phone']; ?>" />
                      <select class="form-control" name="mlseo_microdata_data[contact][0][type]" style="width:40%">
                        <?php foreach (array('customer support', 'technical support', 'billing support', 'bill payment', 'sales', 'reservations', 'credit card support', 'emergency', 'baggage tracking', 'roadside assistance', 'package tracking') as $mdc_type) { ?>
                        <option value="<?php echo $mdc_type; ?>" <?php if(!empty($mlseo_microdata_data['contact'][0]['type']) && $mlseo_microdata_data['contact'][0]['type'] == $mdc_type) echo 'selected="selected"'; ?>><?php echo ucfirst($mdc_type); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="input-group" style="width:100%; margin-top:10px;">
                      <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                      <input style="width:60%" type="text" class="form-control" placeholder="<?php echo $_language->get('entry_phone'); ?>" name="mlseo_microdata_data[contact][1][phone]" value="<?php if(!empty($mlseo_microdata_data['contact'][1]['phone'])) echo $mlseo_microdata_data['contact'][1]['phone']; ?>" />
                      <select class="form-control" name="mlseo_microdata_data[contact][1][type]" style="width:40%">
                        <?php foreach (array('customer support', 'technical support', 'billing support', 'bill payment', 'sales', 'reservations', 'credit card support', 'emergency', 'baggage tracking', 'roadside assistance', 'package tracking') as $mdc_type) { ?>
                        <option value="<?php echo $mdc_type; ?>" <?php if(!empty($mlseo_microdata_data['contact'][1]['type']) && $mlseo_microdata_data['contact'][1]['type'] == $mdc_type) echo 'selected="selected"'; ?>><?php echo ucfirst($mdc_type); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="input-group" style="width:100%; margin-top:10px;">
                      <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                      <input style="width:60%" type="text" class="form-control" placeholder="<?php echo $_language->get('entry_phone'); ?>" name="mlseo_microdata_data[contact][2][phone]" value="<?php if(!empty($mlseo_microdata_data['contact'][2]['phone'])) echo $mlseo_microdata_data['contact'][2]['phone']; ?>" />
                      <select class="form-control" name="mlseo_microdata_data[contact][2][type]" style="width:40%">
                        <?php foreach (array('customer support', 'technical support', 'billing support', 'bill payment', 'sales', 'reservations', 'credit card support', 'emergency', 'baggage tracking', 'roadside assistance', 'package tracking') as $mdc_type) { ?>
                        <option value="<?php echo $mdc_type; ?>" <?php if(!empty($mlseo_microdata_data['contact'][2]['type']) && $mlseo_microdata_data['contact'][2]['type'] == $mdc_type) echo 'selected="selected"'; ?>><?php echo ucfirst($mdc_type); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tab-pane" id="tab-microdata-3">
              <table class="form">
                <tr>
                  <td><?php echo $_language->get('text_enabled'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_microdata_data_store" name="mlseo_microdata_data[store]" value="1" <?php if(!empty($mlseo_microdata_data['store'])) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_data'); ?></td>
                  <td>
                    <input class="checkable" data-label="<?php echo $_language->get('entry_microdata_logo'); ?>" type="checkbox" id="mlseo_microdata_data_store_logo" name="mlseo_microdata_data[store_logo]" value="1" <?php if(!empty($mlseo_microdata_data['store_logo'])) echo 'checked="checked"'; ?> /><br />
                    <input class="checkable" data-label="<?php echo $_language->get('entry_email'); ?>" type="checkbox" id="mlseo_microdata_data_store_mail" name="mlseo_microdata_data[store_mail]" value="1" <?php if(!empty($mlseo_microdata_data['store_mail'])) echo 'checked="checked"'; ?> /><br />
                    <input class="checkable" data-label="<?php echo $_language->get('entry_phone'); ?>" type="checkbox" id="mlseo_microdata_data_store_phone" name="mlseo_microdata_data[store_phone]" value="1" <?php if(!empty($mlseo_microdata_data['store_phone'])) echo 'checked="checked"'; ?> /><br />
                    <input class="checkable" data-label="<?php echo $_language->get('entry_microdata_address'); ?>" type="checkbox" id="mlseo_microdata_data_address" name="mlseo_microdata_data[address]" value="1" <?php if(!empty($mlseo_microdata_data['address'])) echo 'checked="checked"'; ?> /><br />
                  </td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_microdata_address'); ?></td>
                  <td>
                    <input type="text" class="form-control" placeholder="<?php echo $_language->get('entry_address_street'); ?>" name="mlseo_microdata_data[address_street]" value="<?php if(!empty($mlseo_microdata_data['address_street'])) echo $mlseo_microdata_data['address_street']; ?>" />
                    <input type="text" style="margin-top:10px;" class="form-control" placeholder="<?php echo $_language->get('entry_address_city'); ?>" name="mlseo_microdata_data[address_city]" value="<?php if(!empty($mlseo_microdata_data['address_city'])) echo $mlseo_microdata_data['address_city']; ?>" />
                    <input type="text" style="margin-top:10px;" class="form-control" placeholder="<?php echo $_language->get('entry_address_region'); ?>" name="mlseo_microdata_data[address_region]" value="<?php if(!empty($mlseo_microdata_data['address_region'])) echo $mlseo_microdata_data['address_region']; ?>" />
                    <input type="text" style="margin-top:10px;" class="form-control" placeholder="<?php echo $_language->get('entry_address_code'); ?>" name="mlseo_microdata_data[address_code]" value="<?php if(!empty($mlseo_microdata_data['address_code'])) echo $mlseo_microdata_data['address_code']; ?>" />
                    <input type="text" style="margin-top:10px;" class="form-control" placeholder="<?php echo $_language->get('entry_address_country'); ?>" name="mlseo_microdata_data[address_country]" value="<?php if(!empty($mlseo_microdata_data['address_country'])) echo $mlseo_microdata_data['address_country']; ?>" />
                  </td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_same_as'); ?></td>
                  <td>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-link"></i></span>
                      <input type="text" class="form-control" placeholder="https://twitter.com/MyStore" name="mlseo_microdata_data[same_as][0]" value="<?php if(!empty($mlseo_microdata_data['same_as'][0])) echo $mlseo_microdata_data['same_as'][0]; ?>" />
                    </div>
                    <div class="input-group" style="margin-top:10px">
                      <span class="input-group-addon"><i class="fa fa-link"></i></span>
                      <input type="text" class="form-control" placeholder="https://www.pinterest.com/MyStore" name="mlseo_microdata_data[same_as][1]" value="<?php if(!empty($mlseo_microdata_data['same_as'][1])) echo $mlseo_microdata_data['same_as'][1]; ?>" />
                    </div>
                    <div class="input-group" style="margin-top:10px">
                      <span class="input-group-addon"><i class="fa fa-link"></i></span>
                      <input type="text" class="form-control" placeholder="https://www.facebook.com/MyStore" name="mlseo_microdata_data[same_as][2]" value="<?php if(!empty($mlseo_microdata_data['same_as'][2])) echo $mlseo_microdata_data['same_as'][2]; ?>" />
                    </div>
                  </td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_pricerange'); ?></td>
                  <td>
                    <select class="form-control" name="mlseo_microdata_data[pricerange]">
                      <?php foreach (array('','$','$$','$$$','$$$$','$$$$$') as $pricerange) { ?>
                      <option value="<?php echo $pricerange; ?>" <?php if(isset($mlseo_microdata_data['pricerange']) && $mlseo_microdata_data['pricerange'] == $pricerange) echo 'selected="selected"'; ?>><?php echo $pricerange ? $pricerange : $_language->get('text_disabled'); ?></option>
                      <?php } ?>
                    </select>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tab-pane" id="tab-microdata-4">
              <table class="form">
                <tr>
                  <td><?php echo $_language->get('text_enabled'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_microdata_data_website" name="mlseo_microdata_data[website]" value="1" <?php if(!empty($mlseo_microdata_data['website'])) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_snippet_data'); ?></td>
                  <td>
                    <input class="checkable" data-label="<?php echo $_language->get('entry_microdata_search'); ?>" type="checkbox" id="mlseo_microdata_data_websearch" name="mlseo_microdata_data[website_search]" value="1" <?php if(!empty($mlseo_microdata_data['website_search'])) echo 'checked="checked"'; ?> /><br />
                  </td>
                </tr>
              </table>
            </div>
            <div class="tab-pane" id="tab-microdata-5">
              <table class="form">
                <tr>
                  <td><?php echo $_language->get('text_enabled'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_microdata_data_place" name="mlseo_microdata_data[place]" value="1" <?php if(!empty($mlseo_microdata_data['place'])) echo 'checked="checked"'; ?> /></td>
                </tr>
                <tr>
                  <td><?php echo $_language->get('entry_microdata_gps'); ?></td>
                  <td>
                    <div class="input-group" style="width:100%;">
                      <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                      <input style="width:50%" type="text" class="form-control" placeholder="<?php echo $_language->get('entry_gps_lat'); ?>" name="mlseo_microdata_data[gps_lat]" value="<?php if(!empty($mlseo_microdata_data['gps_lat'])) echo $mlseo_microdata_data['gps_lat']; ?>" />
                      <input style="width:50%" type="text" class="form-control" placeholder="<?php echo $_language->get('entry_gps_long'); ?>" name="mlseo_microdata_data[gps_long]" value="<?php if(!empty($mlseo_microdata_data['gps_long'])) echo $mlseo_microdata_data['gps_long']; ?>" />
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tab-pane" id="tab-microdata-6">
              <table class="form">
                <tr>
                  <td><?php echo $_language->get('text_enabled'); ?></td>
                  <td><input class="switch" type="checkbox" id="mlseo_microdata_data_breadcrumbs" name="mlseo_microdata_data[breadcrumbs]" value="1" <?php if(!empty($mlseo_microdata_data['breadcrumbs'])) echo 'checked="checked"'; ?> /></td>
                </tr>
              </table>
            </div>
          </div>
          <div>
            <div class="gkdwidget gkdwidget-color-blueDark" data-widget-fullscreenbutton="true">
              <header role="heading">
                <i class="icon fa fa-info-circle fa-2x pull-left"></i>
                <ul class="nav nav-tabs pull-left in">
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('text_seo_tab_snippet_1'); ?></a></li>
                </ul>
              </header>
              <div class="gkdwidget-container" style="display:none">
                <div class="tab-content">
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_microdata'); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
				<div class="tab-pane" id="tab-snippet-2">
					<table class="form">
						<tr>
              <td><?php echo $_language->get('entry_enable_opengraph'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_opengraph" name="mlseo_opengraph" value="1" <?php if($mlseo_opengraph) echo 'checked="checked"'; ?> /></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_opengraph_id'); ?></td>
              <td>
                <input type="text" class="form-control" placeholder="111122223333444" name="mlseo_opengraph_data[page_id]" value="<?php if(!empty($mlseo_opengraph_data['page_id'])) echo $mlseo_opengraph_data['page_id']; ?>" />
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_product_data'); ?></td>
              <td>
                <input class="checkable" data-label="<?php echo $_language->get('entry_description'); ?>" type="checkbox" id="mlseo_opengraph_data_desc" name="mlseo_opengraph_data[desc]" value="1" <?php if(!empty($mlseo_opengraph_data['desc'])) echo 'checked="checked"'; ?> />
              </td>
            </tr>
          </table>
          <div>
            <div class="gkdwidget gkdwidget-color-blueDark" data-widget-fullscreenbutton="true">
              <header role="heading">
                <!--div class="gkdwidget-ctrls"><a href="javascript:void(0);" class="button-icon gkdwidget-fullscreen-btn" rel="tooltip" title="" data-placement="bottom" data-original-title="Fullscreen"><i class="fa fa-expand"></i></a></div-->
                <i class="icon fa fa-info-circle fa-2x pull-left"></i>
                <ul class="nav nav-tabs pull-left in">
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('help_fb_appid_tab'); ?></a></li>
                  <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><?php echo $_language->get('help_fb_setup_tab'); ?></a></li>
                </ul>
              </header>
              <div class="gkdwidget-container" style="display:none">
                <div class="tab-content">
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_fb_appid'); ?></div>
                  <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_fb_setup'); ?></div>
                </div>
              </div>
            </div>
          </div>
				</div>
        <div class="tab-pane" id="tab-snippet-3">
					<table class="form">
						<tr>
              <td><?php echo $_language->get('entry_enable_tcard'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_tcard" name="mlseo_tcard" value="1" <?php if($mlseo_tcard) echo 'checked="checked"'; ?> /></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_twitter_nick'); ?></td>
              <td>
                <input type="text" class="form-control" placeholder="@nickname" name="mlseo_tcard_data[nick]" value="<?php if(!empty($mlseo_tcard_data['nick'])) echo $mlseo_tcard_data['nick']; ?>" />
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_twitter_home_type'); ?></td>
              <td>
                <select class="form-control" name="mlseo_tcard_data[home_type]">
                  <option value="summary" <?php if(!empty($mlseo_tcard_data['home_type']) && $mlseo_tcard_data['home_type'] == 'summary') echo 'selected="selected"'; ?>><?php echo $_language->get('entry_twitter_summary'); ?></option>
                  <option value="summary_large_image" <?php if(!empty($mlseo_tcard_data['home_type']) && $mlseo_tcard_data['home_type'] == 'summary_large_image') echo 'selected="selected"'; ?>><?php echo $_language->get('entry_twitter_summary_large'); ?></option>
                </select>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_product_data'); ?></td>
              <td>
                <input class="checkable" data-label="<?php echo $_language->get('entry_description'); ?>" type="checkbox" id="mlseo_tcard_data_desc" name="mlseo_tcard_data[desc]" value="1" <?php if(!empty($mlseo_tcard_data['desc'])) echo 'checked="checked"'; ?> />
              </td>
            </tr>
          </table>
				</div>
         <div class="tab-pane" id="tab-snippet-4">
					<table class="form">
						<tr>
              <td><?php echo $_language->get('entry_enable_gpublisher'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_gpublisher" name="mlseo_gpublisher" value="1" <?php if($mlseo_gpublisher) echo 'checked="checked"'; ?> /></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_gpublisher_url'); ?></td>
              <td>
                <input type="text" class="form-control" placeholder="https://plus.google.com/my_google_plus/" name="mlseo_gpublisher_data[url]" value="<?php if(!empty($mlseo_gpublisher_data['url'])) echo $mlseo_gpublisher_data['url']; ?>" />
              </td>
            </tr>
          </table>
				</div>
      </div>
      <div style="clear:both"></div>
		</div>
		
    <?php if (!$store_id) { ?>
    <div class="tab-pane" id="tab-fpp">
      <!-- start fpp -->
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-fpp-1" data-toggle="tab"><?php echo $_language->get('tab_fpp_product'); ?></a></li>
        <li><a href="#tab-fpp-2" data-toggle="tab"><?php echo $_language->get('tab_fpp_category'); ?></a></li>
        <li><a href="#tab-fpp-3" data-toggle="tab"><?php echo $_language->get('tab_fpp_manufacturer'); ?></a></li>
        <li><a href="#tab-fpp-4" data-toggle="tab"><?php echo $_language->get('tab_fpp_search'); ?></a></li>
        <li><a href="#tab-fpp-5" data-toggle="tab"><?php echo $_language->get('tab_fpp_common'); ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="tab-fpp-1">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_fpp_mode'); ?></td>
              <td>
                <select name="mlseo_fpp_mode" class="form-control">
                  <option value="0" <?php if($mlseo_fpp_mode == '0') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_0'); ?></option>
                  <option value="1" <?php if($mlseo_fpp_mode == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_1'); ?></option>
                  <option value="2" <?php if($mlseo_fpp_mode == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_2'); ?></option>
                  <option value="4" <?php if($mlseo_fpp_mode == '4') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_4'); ?></option>
                  <option value="3" <?php if($mlseo_fpp_mode == '3') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_3'); ?></option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_mode_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fpp_depth'); ?></td>
              <td>
                <select name="mlseo_fpp_depth" class="form-control">
                  <option value="0" <?php if($mlseo_fpp_depth == '0') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_unlimited'); ?></option>
                  <option value="1" <?php if($mlseo_fpp_depth == '1') echo 'selected="selected"'; ?>>1</option>
                  <option value="2" <?php if($mlseo_fpp_depth == '2') echo 'selected="selected"'; ?>>2</option>
                  <option value="3" <?php if($mlseo_fpp_depth == '3') echo 'selected="selected"'; ?>>3</option>
                  <option value="4" <?php if($mlseo_fpp_depth == '4') echo 'selected="selected"'; ?>>4</option>
                  <option value="5" <?php if($mlseo_fpp_depth == '5') echo 'selected="selected"'; ?>>5</option>
                  <option value="6" <?php if($mlseo_fpp_depth == '6') echo 'selected="selected"'; ?>>6</option>
                  <option value="7" <?php if($mlseo_fpp_depth == '7') echo 'selected="selected"'; ?>>7</option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_depth_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fpp_breadcrumbs_fix'); ?></td>
              <td>
                <select name="mlseo_fpp_breadcrumbs" class="form-control">
                  <option value="0" <?php if($mlseo_fpp_breadcrumbs == '0') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_breadcrumbs_0'); ?></option>
                  <option value="1" <?php if($mlseo_fpp_breadcrumbs == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_breadcrumbs_1'); ?></option>
                  <option value="2" <?php if($mlseo_fpp_breadcrumbs == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_breadcrumbs_2'); ?></option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_breadcrumbs_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fpp_bc_mode'); ?></td>
              <td>
                <select name="mlseo_fpp_bc_mode" class="form-control">
                  <option value="0" <?php if($mlseo_fpp_bc_mode == '0') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_0'); ?></option>
                  <option value="1" <?php if($mlseo_fpp_bc_mode == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_1'); ?></option>
                  <option value="2" <?php if($mlseo_fpp_bc_mode == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_2'); ?></option>
                  <option value="4" <?php if($mlseo_fpp_bc_mode == '4') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_4'); ?></option>
                  <option value="3" <?php if($mlseo_fpp_bc_mode == '3') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_mode_3'); ?></option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_mode_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fpp_noprodbreadcrumb'); ?></td>
              <td><input class="switch" type="checkbox" name="mlseo_fpp_noprodbreadcrumb" id="mlseo_fpp_noprodbreadcrumb" value="1" <?php if($mlseo_fpp_noprodbreadcrumb) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_noprodbreadcrumb_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fpp_bypasscat'); ?></td>
              <td><input class="switch" type="checkbox" name="mlseo_fpp_bypasscat" id="mlseo_fpp_bypasscat" value="1" <?php if($mlseo_fpp_bypasscat) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_bypasscat_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('entry_category'); ?></td>
              <td colspan="2">
                <input type="text" name="category" value="" placeholder="<?php echo $_language->get('placeholder_category'); ?>" id="input-category" class="form-control" />
                <div id="product-category" class="well well-sm" style="height: 150px; overflow: auto;">
                  <?php foreach ($categories as $product_category) { ?>
                    <?php if (in_array($product_category['category_id'],(array) $mlseo_fpp_categories)) { ?>
                    <div id="mlseo_fpp_categories<?php echo $product_category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $product_category['name']; ?>
                      <input type="hidden" name="mlseo_fpp_categories[]" value="<?php echo $product_category['category_id']; ?>" />
                    </div>
                    <?php } ?>
                  <?php } ?>
                </div>
              </td>
            </tr>
            <?php /* changed to autocomplete for performance
            <tr>
              <td><?php echo $_language->get('entry_category'); ?></td>
              <td colspan="2"><div class="scrollbox" style="width:90%;height:350px">
                  <?php $class = 'odd'; ?>
                  <?php foreach ($categories as $category) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <?php if (in_array($category['category_id'],(array) $mlseo_fpp_categories)) { ?>
                    <input type="checkbox" name="mlseo_fpp_categories[]" value="<?php echo $category['category_id']; ?>" checked="checked" />
                    <?php echo $category['name']; ?>
                    <?php } else { ?>
                    <input type="checkbox" name="mlseo_fpp_categories[]" value="<?php echo $category['category_id']; ?>" />
                    <?php echo $category['name']; ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div>
                <a onclick="$(this).parent().find(':checkbox').prop('checked', true);"><?php echo $_language->get('text_select_all'); ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $_language->get('text_unselect_all'); ?></a></td>
            </tr>
            */ ?>
          </table>
        </div>
        <div class="tab-pane" id="tab-fpp-2">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_fpp_directcat'); ?></td>
              <td style="width:220px">
                <select name="mlseo_fpp_directcat" class="form-control">
                  <option value="1" <?php if($mlseo_fpp_directcat == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_cat_mode_0'); ?></option>
                  <option value="" <?php if(!$mlseo_fpp_directcat) echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_cat_mode_1'); ?></option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_directcat_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fpp_cat_canonical'); ?></td>
              <td>
                <select name="mlseo_fpp_cat_canonical" class="form-control">
                  <option value="" <?php if(!$mlseo_fpp_cat_canonical) echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_cat_mode_0'); ?></option>
                  <option value="1" <?php if($mlseo_fpp_cat_canonical == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_cat_mode_1'); ?></option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_cat_canonical_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_absolute'); ?></td>
              <td><input class="switch" type="checkbox" id="mlseo_absolute" name="mlseo_absolute" value="1" <?php if($mlseo_absolute) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_seo_absolute_help'); ?></td>
            </tr>
          </table>
          <script type="text/javascript">
            $('select[name=mlseo_fpp_directcat]').change(function(){
              $('select[name=mlseo_fpp_cat_canonical]').prop('disabled', $(this).val());
              if ($(this).val()) {
                $('select[name=mlseo_fpp_cat_canonical]').val('');
              }
            });
            $('select[name=mlseo_fpp_directcat]').change();
            
            $('select[name=mlseo_fpp_mode]').change(function(){
              if ($(this).val() == 1 || $(this).val() == 2) {
                $('select[name=mlseo_fpp_depth]').prop('disabled', false);
              } else {
                $('select[name=mlseo_fpp_depth]').prop('disabled', true);
              }
            });
            $('select[name=mlseo_fpp_mode]').change();
          </script>
        </div>
        <div class="tab-pane" id="tab-fpp-3">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_fpp_brand_parent'); ?></td>
              <td><input class="switch" type="checkbox" name="mlseo_fpp_brand_parent" id="mlseo_fpp_brand_parent" value="1" <?php if($mlseo_fpp_brand_parent) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_brand_parent_help'); ?></td>
            </tr>
          </table>
        </div>
        <div class="tab-pane" id="tab-fpp-4">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_fpp_remove_search'); ?></td>
              <td><input class="switch" type="checkbox" name="mlseo_fpp_remove_search" id="mlseo_fpp_remove_search" value="1" <?php if($mlseo_fpp_remove_search) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_remove_search_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fix_search'); ?></td>
              <td><input class="switch" type="checkbox" name="mlseo_fix_search" id="mlseo_fix_search" value="1" <?php if(!empty($mlseo_fix_search)) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fix_search_help'); ?></td>
            </tr>
          </table>
        </div>
        <div class="tab-pane" id="tab-fpp-5">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_fpp_slash'); ?></td>
              <td style="width:220px">
                <select name="mlseo_fpp_slash" class="form-control">
                  <option value="" <?php if(!$mlseo_fpp_slash) echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_slash_mode_0'); ?></option>
                  <option value="1" <?php if($mlseo_fpp_slash == '1') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_slash_mode_1'); ?></option>
                  <option value="2" <?php if($mlseo_fpp_slash == '2') echo 'selected="selected"'; ?>><?php echo $_language->get('text_fpp_slash_mode_2'); ?></option>
                </select>
              </td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fpp_slash_help'); ?></td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_fix_cart'); ?></td>
              <td><input class="switch" type="checkbox" name="mlseo_fix_cart" id="mlseo_fix_cart" value="1" <?php if(!empty($mlseo_fix_cart)) echo 'checked="checked"'; ?> /></td>
              <td style="padding-left:50px"><?php echo $_language->get('text_fix_cart_help'); ?></td>
            </tr>
          </table>
        </div>
      </div>
      <!-- end fpp -->
		</div>
    <?php } ?>
    
    <?php if ($store_id) { ?>
		<div class="tab-pane<?php if ($store_id) echo ' active'; ?>" id="tab-multistore">
      <div class="well">
        <h4><?php echo $_language->get('tab_seo_multistore'); ?></h4>
        <p><?php echo $_language->get('info_multistore_dashboard'); ?></p>
      </div>
    </div>
    <?php } ?>
    
    <?php if (!$store_id  || ($store_id && $mlseo_multistore)) { ?>
		<div class="tab-pane" id="tab-update">
			 <table class="form">
       <tr>
        <td colspan="2">
          <div class="row-fluid clearfix jumbo">
            <label class="col-sm-2"><?php echo $_language->get('text_seo_simulate'); ?></label>
            <div class="col-sm-3">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-floppy-o"></i></span>
                <input type="text" name="simulate" value="1" class="toggler" data-mode="background" data-on-text="<?php echo $_language->get('text_simulation'); ?>" data-off-text="<?php echo $_language->get('text_write'); ?>" data-icons="" data-on-color="#f0a357" data-off-color="#5ca455"/>
              </div>
            </div>
            <div class="col-sm-1"></div>
            <label class="col-sm-2"><?php echo $_language->get('text_seo_empty_only'); ?></label>
            <div class="col-sm-3">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-magic"></i></span>
                <input type="text" name="empty_only" value="1" class="toggler" data-mode="background" data-on-text="<?php echo $_language->get('text_empty_only'); ?>" data-off-text="<?php echo $_language->get('text_all_values'); ?>" data-icons="" data-on-color="#f0a357" data-off-color="#5ca455"/>
              </div>
            </div>
            <!--
            <label class="col-sm-2"><?php echo $_language->get('text_seo_redirect_mode'); ?></label>
            <div class="col-sm-2">
              <input class="switch" type="checkbox" id="redirect_mode"  name="redirect_mode" value="1" checked="checked"/>
            </div>
            -->
          </div>
         </td>
        </tr>
				<tr>
					<td><?php echo $_language->get('text_seo_languages'); ?>:</td>
					<td class="imgCheckbox">
            <?php foreach ($languages as $language) { ?>
            <img style="position:relative; bottom:12px; left:50px;" src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <input type="checkbox" class="checkable" value="<?php echo $language['language_id']; ?>" data-label="<?php echo $language['name']; ?>" id="langs_selector_<?php echo $language['language_id']; ?>" name="langs[]" checked="checked"/> 
            <?php } ?>
          </td>
				</tr>
				<tr>
				  <td><?php echo $_language->get('text_seo_generator_product'); ?></td>
				  <td>
					<table class="generator">
						<tr>
							<td class="patterns" rowspan="14"><?php echo $_language->get('text_seo_generator_product_desc'); ?></td>
							<td>
                <!--
                <div class="input-group">
                  <input class="form-control" type="text" name="mlseo_product_url_pattern" value="<?php echo $mlseo_product_url_pattern; ?>" size="50" />
                  <div class="input-group-btn">
                    <button onclick="seo_update('product', 'url');" class="btn btn-success mubtn" type="submit"><i class="fa fa-link"></i> <?php echo $_language->get('text_seo_generator_url'); ?></button>
                  </div>
                  </div>
                -->
                  <input type="text" name="mlseo_product_url_pattern" value="<?php echo $mlseo_product_url_pattern; ?>" size="50" />
                 
                  </td>
							<td>
                <a onclick="seo_update('product', 'url');" class="button"><span><i class="fa fa-link"></i> <?php echo $_language->get('text_seo_generator_url'); ?></span></a>
              </td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_product_h1_pattern" value="<?php echo $mlseo_product_h1_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'h1');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h1'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_product_h2_pattern" value="<?php echo $mlseo_product_h2_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'h2');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h2'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_product_h3_pattern" value="<?php echo $mlseo_product_h3_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'h3');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h3'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_product_title_pattern" value="<?php echo $mlseo_product_title_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'title');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_title'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_product_keyword_pattern" value="<?php echo $mlseo_product_keyword_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'keyword');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_keywords'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_product_description_pattern" value="<?php echo $mlseo_product_description_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'description');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_desc'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_product_image_alt_pattern" value="<?php echo $mlseo_product_image_alt_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'image_alt');" class="button"><span><i class="fa fa-picture-o"></i> <?php echo $_language->get('text_seo_generator_imgalt'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_product_image_title_pattern" value="<?php echo $mlseo_product_image_title_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('product', 'image_title');" class="button"><span><i class="fa fa-picture-o"></i> <?php echo $_language->get('text_seo_generator_imgtitle'); ?></span></a></td>
						</tr>
						<?php if (!$store_id) { ?>
              <tr>
                <td><input type="text" name="mlseo_product_image_name_pattern" value="<?php echo $mlseo_product_image_name_pattern; ?>" size="50" /></td>
                <td><a onclick="seo_update('product', 'image_name');" class="button"><span><i class="fa fa-picture-o"></i> <?php echo $_language->get('text_seo_generator_imgname'); ?></span></a></td>
              </tr>
              <?php if (version_compare(VERSION, '1.5.3', '>=')) { ?>
              <tr>
                <td><input type="text" name="mlseo_product_tag_pattern" value="<?php echo $mlseo_product_tag_pattern; ?>" size="50" /></td>
                <td><a onclick="seo_update('product', 'tag');" class="button"><span><i class="fa fa-tags"></i> <?php echo $_language->get('text_seo_generator_tag'); ?></span></a></td>
              </tr>
              <?php } ?>
              <?php } ?>
              <tr>
                <td><textarea class="form-control" name="mlseo_product_full_desc_pattern" style="width:372px;height:70px"><?php echo $mlseo_product_full_desc_pattern; ?></textarea></td>
                <td><a onclick="seo_update('product', 'full_desc');" class="button"><span><i class="fa fa-align-left"></i> <?php echo $_language->get('text_seo_generator_full_desc'); ?></span></a></td>
              </tr>
              <?php if ($store_id) { ?>
              <tr>
                <td><?php echo $_language->get('text_seo_generator_store_copy_i'); ?></td>
                <td><a onclick="seo_update('product', 'store_copy');" class="button"><span><i class="fa fa-copy"></i> <?php echo $_language->get('text_seo_generator_store_copy'); ?></span></a></td>
              </tr>
              <?php } ?>
              <?php if (!$store_id) { ?>
              <tr>
                <td>
                  <input type="hidden" name="mlseo_product_related_samecat"/>
                  <?php echo $_language->get('text_seo_generator_related_no'); ?> <input style="margin-right:15px" type="text" name="mlseo_product_related_no" value="<?php echo $mlseo_product_related_no; ?>" size="2" />
                  <?php echo $_language->get('text_seo_generator_related_relevance'); ?> <input style="margin-right:15px" type="text" name="mlseo_product_related_relevance" value="<?php echo $mlseo_product_related_relevance; ?>" size="2" />
                   <span style="position:relative; top:10px;"><input type="checkbox" class="checkable" value="1" data-label="<?php echo $_language->get('text_seo_generator_related_samecat'); ?>" id="mlseo_product_related_samecat" name="mlseo_product_related_samecat" <?php echo $mlseo_product_related_samecat ? 'checked="checked"' : ''; ?>/></span>
                </td>
                <td><a onclick="seo_update('product', 'related');" class="button"><span><i class="fa fa-sitemap"></i> <?php echo $_language->get('text_seo_generator_related'); ?></span></a></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><a onclick="seo_update('redirect', 'product');" class="button"><span><i class="fa fa-share"></i> <?php echo $_language->get('text_seo_generator_redirect'); ?></span></a></td>
              </tr>
						<?php } ?>
						</table>
					</td>
				</tr>
				<tr>
				  <td><?php echo $_language->get('text_seo_generator_category'); ?></td>
				  <td>
					<table class="generator">
						<tr>
							<td class="patterns" rowspan="9"><?php echo $_language->get('text_seo_generator_category_desc'); ?></td>
							<td><input type="text" name="mlseo_category_url_pattern" value="<?php echo $mlseo_category_url_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'url');" class="button"><span><i class="fa fa-link"></i> <?php echo $_language->get('text_seo_generator_url'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_category_h1_pattern" value="<?php echo $mlseo_category_h1_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'h1');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h1'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_category_h2_pattern" value="<?php echo $mlseo_category_h2_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'h2');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h2'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_category_h3_pattern" value="<?php echo $mlseo_category_h3_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'h3');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h3'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_category_title_pattern" value="<?php echo $mlseo_category_title_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'title');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_title'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_category_keyword_pattern" value="<?php echo $mlseo_category_keyword_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'keyword');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_keywords'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_category_description_pattern" value="<?php echo $mlseo_category_description_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('category', 'description');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_desc'); ?></span></a></td>
						</tr>
            <tr>
							<td><textarea class="form-control" name="mlseo_category_full_desc_pattern" style="width:370px;height:70px"><?php echo $mlseo_category_full_desc_pattern; ?></textarea></td>
							<td><a onclick="seo_update('category', 'full_desc');" class="button"><span><i class="fa fa-align-left"></i> <?php echo $_language->get('text_seo_generator_full_desc'); ?></span></a></td>
						</tr>
            <?php if ($store_id) { ?>
              <tr>
                <td><?php echo $_language->get('text_seo_generator_store_copy_i'); ?></td>
                <td><a onclick="seo_update('category', 'store_copy');" class="button"><span><i class="fa fa-copy"></i> <?php echo $_language->get('text_seo_generator_store_copy'); ?></span></a></td>
              </tr>
              <?php } ?>
            <?php if (!$store_id) { ?>
            <tr>
							<td>&nbsp;</td>
							<td><a onclick="seo_update('redirect', 'category');" class="button"><span><i class="fa fa-share"></i> <?php echo $_language->get('text_seo_generator_redirect'); ?></span></a></td>
						</tr>
            <?php } ?>
						</table>
					</td>
				</tr>
				<tr>
				  <td><?php echo $_language->get('text_seo_generator_information'); ?></td>
				  <td>
					<table class="generator">
						<tr>
							<td class="patterns" rowspan="9"><?php echo $_language->get('text_seo_generator_information_desc'); ?></td>
							<td><input type="text" name="mlseo_information_url_pattern" value="<?php echo $mlseo_information_url_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'url');" class="button"><span><i class="fa fa-link"></i> <?php echo $_language->get('text_seo_generator_url'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_information_h1_pattern" value="<?php echo $mlseo_information_h1_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'h1');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h1'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_information_h2_pattern" value="<?php echo $mlseo_information_h2_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'h2');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h2'); ?></span></a></td>
						</tr>
            <tr>
							<td><input type="text" name="mlseo_information_h3_pattern" value="<?php echo $mlseo_information_h3_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'h3');" class="button"><span><i class="fa fa-header"></i> <?php echo $_language->get('text_seo_generator_h3'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_information_title_pattern" value="<?php echo $mlseo_information_title_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'title');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_title'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_information_keyword_pattern" value="<?php echo $mlseo_information_keyword_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'keyword');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_keywords'); ?></span></a></td>
						</tr>
						<tr>
							<td><input type="text" name="mlseo_information_description_pattern" value="<?php echo $mlseo_information_description_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('information', 'description');" class="button"><span><i class="fa fa-code"></i> <?php echo $_language->get('text_seo_generator_desc'); ?></span></a></td>
						</tr>
            <tr>
							<td><textarea class="form-control" name="mlseo_information_full_desc_pattern" style="width:370px;height:70px"><?php echo $mlseo_information_full_desc_pattern; ?></textarea></td>
							<td><a onclick="seo_update('information', 'full_desc');" class="button"><span><i class="fa fa-align-left"></i> <?php echo $_language->get('text_seo_generator_full_desc'); ?></span></a></td>
						</tr>
            <?php if ($store_id) { ?>
              <tr>
                <td><?php echo $_language->get('text_seo_generator_store_copy_i'); ?></td>
                <td><a onclick="seo_update('information', 'store_copy');" class="button"><span><i class="fa fa-copy"></i> <?php echo $_language->get('text_seo_generator_store_copy'); ?></span></a></td>
              </tr>
              <?php } ?>
            <?php if (!$store_id) { ?>
            <tr>
							<td>&nbsp;</td>
							<td><a onclick="seo_update('redirect', 'information');" class="button"><span><i class="fa fa-share"></i> <?php echo $_language->get('text_seo_generator_redirect'); ?></span></a></td>
						</tr>
            <?php } ?>
						</table>
					</td>
				</tr>
        <?php if (!$store_id  || ($store_id && $mlseo_multistore)) { ?>
				<tr>
				  <td><?php echo $_language->get('text_seo_generator_manufacturer'); ?></td>
				  <td>
					<table class="generator">
						<tr>
							<td class="patterns" rowspan="3"><?php echo $_language->get('text_seo_generator_manufacturer_desc'); ?></td>
							<td><input type="text" name="mlseo_manufacturer_url_pattern" value="<?php echo $mlseo_manufacturer_url_pattern; ?>" size="50" /></td>
							<td><a onclick="seo_update('manufacturer', 'url');" class="button"><span><i class="fa fa-link"></i> <?php echo $_language->get('text_seo_generator_url'); ?></span></a></td>
						</tr>
            <tr>
							<td>&nbsp;</td>
							<td><a onclick="seo_update('redirect', 'manufacturer');" class="button"><span><i class="fa fa-share"></i> <?php echo $_language->get('text_seo_generator_redirect'); ?></span></a></td>
						</tr>
					 </table>
					</td>
				</tr>
				<?php } ?>
				<tr>
          <td><?php echo $_language->get('text_seo_result'); ?></td>
			<td>
        <div id="update_result">
          <div>
            <div class="fullscreen" onclick="javascript:fullscreen();"><?php echo $_language->get('text_seo_fullscreen'); ?></div>
            <div class="simulation simu"><?php echo $_language->get('text_seo_simulation_mode'); ?></div>
            <div class="simulation write" style="display:none"><?php echo $_language->get('text_seo_write_mode'); ?></div>
            <table class="table table-bordered table-striped table-hover">
              <thead>
                <tr>
                  <th style="width:1%"></th>
                  <th><?php echo $_language->get('text_seo_item'); ?></th>
                  <th><?php echo $_language->get('text_seo_old_value'); ?></th>
                  <th><?php echo $_language->get('text_seo_new_value'); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr data-lang="0" style="display:none"><td colspan="4"></td></tr>
                <?php $f=0; foreach ($languages as $language) { ?>
                <tr data-lang="<?php echo $language['language_id']; ?>" style="display:none"><td colspan="4"><?php echo $language['name']; ?></td></tr>
                <?php } ?>
                <tr><td colspan="4" class="text-center"><?php echo $_language->get('text_seo_update_info'); ?></td></tr>
                <tr data-lang="loading" id="loading" style="display:none"><td colspan="4" class="text-center" style="padding:30px 0"><img src="view/seo_package/img/loading.gif" alt=""/></td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div id="generateProgress" class="progress" style="margin-top:15px;display:none">
          <div class="progress-bar progress-bar-striped progress-bar-success active"></div>
        </div>
      </td>
		</tr>
        <?php if (!$store_id) { ?>
        <?php /*
		<tr class="generator">
      <td><?php echo $_language->get('text_cache'); ?></td>
      <td>
				<a onclick="seo_update('cache', 'create');" class="button"><span><i class="fa fa-refresh"></i> <?php echo $_language->get('text_cache_create_btn'); ?></span></a>
				<a onclick="seo_update('cache', 'delete');" class="button"><span><i class="fa fa-eraser"></i> <?php echo $_language->get('text_cache_delete_btn'); ?></span></a>
			</td>
    </tr>
    */ ?>
		<tr class="generator">
      <td><?php echo $_language->get('text_report'); ?></td>
      <td>
				<a onclick="seo_update('report', 'url_alias');" class="button"><span><i class="fa fa-file-text-o"></i> <?php echo $_language->get('text_url_alias_report_btn'); ?></span></a>
			</td>
    </tr>
    <tr class="generator">
      <td><?php echo $_language->get('text_cleanup'); ?></td>
      <td>
				<a onclick="seo_update('cleanup', 'duplicate');" class="button"><span><i class="fa fa-copy"></i> <?php echo $_language->get('text_cleanup_duplicate_btn'); ?></span></a>
				<a onclick="seo_update('cleanup', 'url');" class="button"><span><i class="fa fa-repeat"></i> <?php echo $_language->get('text_cleanup_btn'); ?></span></a>
			</td>
    </tr>
				<tr class="info">
			  <td><i class='fa fa-info'></i></td>
          <td style="color:#555;padding:40px 100px 40px 0;"><?php echo $_language->get('text_info_update'); ?></td>
        </tr>
        <?php } ?>
			</table>
		</div>
    <?php } ?>
		
    <?php if (!$store_id) { ?>
    <div class="tab-pane" id="tab-cron">
      <ul class="nav nav-pills nav-stacked col-md-2">
				<li class="active"><a href="#tab-cron-1" data-toggle="pill"><i class="fa fa-cogs"></i> <?php echo $_language->get('text_tab_cron_1'); ?></a></li>
				<li><a href="#tab-cron-2" data-toggle="pill"><i class="fa fa-file-text-o"></i> <?php echo $_language->get('text_tab_cron_2'); ?></a></li>
			</ul>
			<div class="tab-content col-md-10">
				<div class="tab-pane active" id="tab-cron-1">
          <table class="form">
            <tr>
              <td><?php echo $_language->get('text_seo_simulate'); ?></td>
              <td>
                <input class="switch" type="checkbox" id="cron_simulate"  name="mlseo_cron[simulation]" value="1" <?php if(!empty($mlseo_cron['simulation'])) echo 'checked="checked"'; ?>/>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_empty_only'); ?></td>
              <td>
                <input class="switch" type="checkbox" id="cron_empty_only"  name="mlseo_cron[empty_only]" value="1" <?php if(!empty($mlseo_cron['empty_only'])) echo 'checked="checked"'; ?>/>
              </td>
            </tr>
            <tr>
              <td><?php echo $_language->get('text_seo_cron_update'); ?></td>
              <td>
              <table class="cron">
                <tr>
                  <td>
                    <b><?php echo $_language->get('text_seo_generator_product'); ?></b><br />
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_url'); ?>" type="checkbox" name="mlseo_cron[update][product][]" value="url" <?php if(isset($mlseo_cron['update']['product']) && in_array('url', $mlseo_cron['update']['product'])) echo 'checked="checked"'; ?>/> <br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_title'); ?>" type="checkbox" name="mlseo_cron[update][product][]" value="title" <?php if(isset($mlseo_cron['update']['product']) && in_array('title', $mlseo_cron['update']['product'])) echo 'checked="checked"'; ?>/> <br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_keyword'); ?>" type="checkbox" name="mlseo_cron[update][product][]" value="keyword" <?php if(isset($mlseo_cron['update']['product']) && in_array('keyword', $mlseo_cron['update']['product'])) echo 'checked="checked"'; ?>/> <br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_description'); ?>" type="checkbox" name="mlseo_cron[update][product][]" value="description" <?php if(isset($mlseo_cron['update']['product']) && in_array('description', $mlseo_cron['update']['product'])) echo 'checked="checked"'; ?>/> 
                  </td>
                  <td>
                    <b><?php echo $_language->get('text_seo_generator_category'); ?></b><br />
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_url'); ?>" type="checkbox" name="mlseo_cron[update][category][]" value="url" <?php if(isset($mlseo_cron['update']['category']) && in_array('url', $mlseo_cron['update']['category'])) echo 'checked="checked"'; ?>/><br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_title'); ?>" type="checkbox" name="mlseo_cron[update][category][]" value="title" <?php if(isset($mlseo_cron['update']['category']) && in_array('title', $mlseo_cron['update']['category'])) echo 'checked="checked"'; ?>/><br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_keyword'); ?>" type="checkbox" name="mlseo_cron[update][category][]" value="keyword" <?php if(isset($mlseo_cron['update']['category']) && in_array('keyword', $mlseo_cron['update']['category'])) echo 'checked="checked"'; ?>/> <br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_description'); ?>" type="checkbox" name="mlseo_cron[update][category][]" value="description" <?php if(isset($mlseo_cron['update']['category']) && in_array('description', $mlseo_cron['update']['category'])) echo 'checked="checked"'; ?>/> 
                  </td>
                  <td>
                    <b><?php echo $_language->get('text_seo_generator_information'); ?></b><br />
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_url'); ?>" type="checkbox" name="mlseo_cron[update][information][]" value="url" <?php if(isset($mlseo_cron['update']['information']) && in_array('url', $mlseo_cron['update']['information'])) echo 'checked="checked"'; ?>/><br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_title'); ?>" type="checkbox" name="mlseo_cron[update][information][]" value="title" <?php if(isset($mlseo_cron['update']['information']) && in_array('title', $mlseo_cron['update']['information'])) echo 'checked="checked"'; ?>/><br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_keyword'); ?>" type="checkbox" name="mlseo_cron[update][information][]" value="keyword" <?php if(isset($mlseo_cron['update']['information']) && in_array('keyword', $mlseo_cron['update']['information'])) echo 'checked="checked"'; ?>/> <br/>
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_description'); ?>" type="checkbox" name="mlseo_cron[update][information][]" value="description" <?php if(isset($mlseo_cron['update']['information']) && in_array('description', $mlseo_cron['update']['information'])) echo 'checked="checked"'; ?>/> 
                  </td>
                  <td>
                    <b><?php echo $_language->get('text_seo_generator_manufacturer'); ?></b><br />
                    <input class="checkable" data-label="<?php echo $_language->get('text_seo_mode_url'); ?>" type="checkbox" name="mlseo_cron[update][manufacturer][]" value="url" <?php if(isset($mlseo_cron['update']['manufacturer']) && in_array('url', $mlseo_cron['update']['manufacturer'])) echo 'checked="checked"'; ?>/><br/>
                  </td>
                </tr>
              </table>
              </td>
            </tr>
          </table>
          <div class="gkdwidget gkdwidget-color-blueDark">
            <header role="heading">
              <i class="icon fa fa-info-circle fa-2x pull-left"></i>
              <ul class="nav nav-tabs pull-left in">
                <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('help_cron_title'); ?></span></a></li>
                <li><a data-toggle="tab" href="#gkhelp<?php echo $gkhtab++; ?>"><span class="hidden-mobile hidden-tablet"><?php echo $_language->get('help_cron_setup_title'); ?></span></a></li>
              </ul>
            </header>
            <div class="gkdwidget-container" style="display:none">
              <div class="tab-content">
                <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_cron'); ?></div>
                <div class="tab-pane" id="gkhelp<?php echo $gkhdiv++; ?>"><?php echo $_language->get('help_cron_setup'); ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane" id="tab-cron-2">
          <textarea wrap="off" rows="30" readonly class="form-control"><?php echo $cli_log; ?></textarea>
          <div class="text-center" style="margin-top:20px">
            <a class="btn btn-success" <?php if(!$cli_log_link || !$cli_log) { echo 'disabled'; } else { echo 'href="'.$cli_log_link.'"';} ?>><i class="fa fa-download"></i> <?php echo $_language->get('text_cli_log_save'); ?></a>
            <a <?php if(!$cli_log_link || !$cli_log) { echo 'disabled not'; } ?>onclick="confirm('<?php echo $_language->get('text_confirm'); ?>') ? location.href='<?php echo $action.'&clear_cli_logs=1'; ?>' : false;" class="btn btn-danger"><i class="fa fa-eraser"></i> <?php echo $_language->get('text_clear_logs'); ?></a>
          </div>
        </div>
      </div>
		</div>
		<?php } ?>
    <?php if (!$store_id) { ?>
    <div class="tab-pane" id="tab-about">
			<table class="form about">
				<tr>
					<td colspan="2" style="text-align:center;padding:30px 0 50px"><img src="view/seo_package/img/logo<?php echo rand(1,1); ?>.gif"/></td>
				</tr>
				<tr>
					<td>Version</td>
					<td><?php echo $module_version; ?> - <?php echo $module_type; ?></td>
				</tr>
        <tr>
					<td>Upgrade Tool</td>
					<td>Easy upgrade tool for transferring data from older seo module: <a href="<?php echo $upgrade_url; ?>"><i class="fa fa-upload"></i> Upgrade Tool</a></td>
				</tr>
				<tr>
					<td>Free support</td>
					<td>Top quality module guaranteed.<br/>In case of bug, incompatibility, or if you want a new feature, just contact me on my mail.</td>
				</tr>
				<tr>
					<td>Contact</td>
					<td><a href="mailto:support@geekodev.com">support@geekodev.com</a></td>
				</tr>
				<tr>
					<td>Links</td>
					<td>
						If you like this module, please consider to make a star rating <span style="position:relative;top:3px;width:80px;height:17px;display:inline-block;background:url(data:data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAARCAYAAADUryzEAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8wNy8xMrG4sToAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAACr0lEQVQ4jX1US0+TURA98/Xri0KBYqG8BDYItBoIBhFBBdRNTTQx0Q0gujBiAkEXxoXxD6iJbRcaY1iQEDXqTgwQWkWDIBU3VqWQoEgECzUU+n5910VbHhacZHLvzD05c+fMzaVhgxYJIwIYi+8B8FJ5bzjob9ucB4DmLttGMGyoAGMsyc1G7bEvA91roz2NL7Y7TziHHSxFmWsorbuUFgn79BaTLnMn3LYEZqPukCKruFAUGEd54w1ekqK69x8CSkoqMnJv72noTmN+O9Q5KlE44GqxmHTS7Qho5MH+X8SJUuMhAIbM/CrS1tSnCYsmkOoUnO7SiP3dHV8Mw5AoKkRCfTwR96ei+ZZGVVDDJQhIWAVbfhjDe8eQnd/Aq8+/VAIsAcGbR8ejQiR8jcwGbYZEkTFVd7I9B4IXcL+GEPwdK4SN0XJSDaCoAvHZsA4/93hWHNVNnbZpjoG5gl7XvpFnxggxAZRaA0rokliIAIkaxMnwdWLE7XW77jd12qYBgCMiNHfZlhgTCkZfPfUDBAYGItoiL0lK8N0+51txzD1u7Ji8njTGpk6bg/iUhSiU4GT5YOtPL940AOfiDyHod9/dMsYEzmLS5bBoKE/ES8ECCyACSF4IFledAdhd2SIFUdtmAp7i92QM+uKqVg6RJXDKakCcjyjSwcldMUDgG7I0h8WKdI0ewM2kFuTpmlb1bp2UMYBJyjBjm/FYh57MjA/1+1wuESNZOfjoLPwe516zUSdLIgi6l+sl3CIW5leD7/v7HPNTE+cOtr8tDXhWy+zWAcvnDx/XoiEPiirPBomgXxd32KAFEWp3FR0YdP60pop4sfHI5cmr+MfMRl2tXKnqzS5pyFuaHRusu2A5EyeoAEAQS2Q94VDg4pY/YUOf9ZgxnBaJJSeOdny6AgB/AYEpKtpaTusRAAAAAElFTkSuQmCC)"></span> on the module page :]<br/><br/>
						<b>Module page :</b> <a target="new" href="https://www.opencart.com/index.php?route=extension/extension/info&extension_id=9486">Complete SEO Package</a><br/>
						<b>Other modules :</b> <a target="new" href="https://www.opencart.com/index.php?route=marketplace/extension&filter_member=GeekoDev">My modules on opencart</a><br/>
					</td>
				</tr>
			</table>
		</div>
    <?php } ?>
  	</div>
  </div>
</div>
	</form>
</div>
<div class="modal" id="newAliasModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo $_language->get('text_seo_new_alias_title'); ?></h4>
      </div>
      <div class="modal-body">
	<form class="form-stacked" role="form">
		   <div class="container-fluid">
			<div class="row clearfix modalinfo common"><div class="col-md-12 column"><?php echo $_language->get('text_seo_new_alias_info'); ?></div></div>
			<div class="row clearfix modalinfo special"><div class="col-md-12 column"><?php echo $_language->get('text_seo_new_spec_alias_info'); ?></div></div>
			<div class="row clearfix modalinfo absolute"><div class="col-md-12 column"><?php echo $_language->get('text_seo_new_absolute_alias_info'); ?></div></div>
			<div class="row clearfix modalinfo redirect 404"><div class="col-md-12 column"><?php echo $_language->get('text_seo_new_redirect'); ?></div></div>
			<hr />
			<div class="row clearfix">
				<div class="form-group">
          <label><?php echo $_language->get('text_editor_query'); ?></label>
					<input class="form-control" name="query" type="text" placeholder="<?php echo $_language->get('text_editor_query'); ?>"/>
				</div>
				<div class="form-group">
        <label class="modalinfo common special"><?php echo $_language->get('text_editor_url'); ?></label>
        <label class="modalinfo absolute"><?php echo $_language->get('text_editor_url'); ?></label>
        <label class="modalinfo redirect 404"><?php echo $_language->get('text_editor_url_redirect'); ?></label>
					<input class="form-control" name="keyword" type="text" placeholder="<?php echo $_language->get('text_editor_url'); ?>"/>
				</div>
			</div>
		</div>
	</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary addAlias">Save changes</button>
      </div>
    </div>
  </div>
</div>
</div>
<script type="text/javascript"><!--
$(function(){
  window.setGaugeMeter = function() {
    $('.radial-progress').attr('data-progress', <?php echo $seo_score; ?>);
  }
  setTimeout(window.setGaugeMeter, 400);
});

// Category
$('input[name=\'category\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/category/autocomplete&<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['category_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'category\']').val('');

		$('#product-category' + item['value']).remove();

		$('#product-category').append('<div id="product-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="mlseo_fpp_categories[]" value="' + item['value'] + '" /></div>');
	}
});
$('#product-category').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});

$('input[name=mlseo_enabled]').change(function(){
if ($(this).val() == 1) {
  $('.radial-progress').attr('data-progress', <?php echo $seo_score; ?>);
} else {
  $('.radial-progress').attr('data-progress', 0);
}
});
$('select[name=store]').change(function(){
	document.location = 'index.php?route=module/complete_seo&<?php echo $token; ?>&store_id='+jQuery(this).val();
});
$('body').on('click', '.gkdwidget .icon', function(){
  $(this).closest('.gkdwidget').find('.gkdwidget-container').slideUp(function(){
    $(this).closest('.gkdwidget').find('.active').removeClass('active');
  });
});
$('body').on('click', '.gkdwidget a[data-toggle="tab"]', function(){
  $(this).closest('.gkdwidget').find('.gkdwidget-container').fadeIn();
});
$('.toggler').toggler();
$('input.switch').iToggle({easing: 'swing',speed: 200});
jQuery.each( jQuery('input.checkable'), function(){ jQuery(this).prettyCheckable(); });

$('select[name="mlseo_flag_mode"]').on('change', function() {
  var flagmode = $(this).val();
  
  $('input[name="mlseo_flag"]').val(flagmode == 'tag' ? 1 : '');
  $('input[name="mlseo_store_mode"]').val(flagmode == 'store' ? 1 : '');
  
  $('[class^="flagmode-"]').fadeOut('fast', function(){
    $('.flagmode-'+flagmode).fadeIn();
  });
});

$('select[name="mlseo_flag_mode"]').trigger('change');

<?php if (false && !$OC_V2) { ?>
$('#tabs a').tabs();
$('#tabs_editor a').tabs();
$('#language-friendly a').tabs();
$('#tabs_editor_lang a').tabs();

$('#stores a').tabs();
<?php foreach ($stores as $store) { ?>
$('#language-<?php echo $store['store_id']; ?> a').tabs();
<?php } ?>
<?php }/*!OC_V2*/ ?>

<?php if (count($languages) > 3) { ?>
$('#tabs_editor_lang ul > li > a').on('click', function(){
$('#tabs_editor_lang > li > a > img').attr('src', $(this).find('img').attr('src'));
});
<?php } ?>

function reset_urls(type, lang){
	location = '<?php echo str_replace('&amp;', '&', $_url->link('module/complete_seo/reset_urls', $token, 'SSL')); ?>&type='+type+'&lang='+lang;
}
function seo_update(type, mode){
  $('html,body').animate({scrollTop:$('#update_result').offset().top}, 500);
	$('#loading').show();
  
  $('#generateProgress').show();
  $('#generateProgress .progress-bar').addClass('active');
  
  $('#generateProgress .progress-bar').css('width','0%').css('min-width', '2em').html('0 %');

  $('#update_result tbody tr:not([data-lang])').remove();
  
  processQueue(type, mode, 0);
  return '';
  
	//$('#update_result *').fadeOut();
	/*$('#update_result').html('<img style="padding-left:40%" src="view/seo_package/img/bigloader.gif"/>');
	$.ajax({
		url: 'index.php?route=module/complete_seo/generator&type='+type+'&mode='+mode+'&<?php echo $token; ?>',
		data: $('#tab-update :input').serialize(),
		dataType: 'text',
		success: function(data){
			$('#update_result').html(data);
			$('#loading').hide();
		}
	});
  */
}
function display_old(){
	$('.toggle').toggleClass('active');
	$('.res_table td:nth-child(3)').toggle();
}
function fullscreen(){
	if($('#update_result').hasClass('full')){
		$('.fullscreen').removeClass('active');
		$('#update_result').removeClass('full');
		$('html, body').css({'overflow': 'auto','height': 'auto'});
	} else {
		$('.fullscreen').addClass('active');
		$('#update_result').addClass('full');
		$('html, body').css({'overflow': 'auto', 'height': '100%'});
	}
}

function processQueue(type, mode, start) {
  $.ajax({
    url: 'index.php?route=module/complete_seo/generator&type='+type+'&mode='+mode+'&start='+start+'&store=<?php echo $store_id; ?>&<?php echo $token; ?>',
    type: 'POST',
		data: $('#tab-update :input').serialize(),
		dataType: 'json',
		success: function(data){
      if(data.success) {
        $('#generateProgress .progress-bar').css('width',data.progress + '%').html(data.progress + ' %');
        
        if (data.log.langs) {
          $.each(data.log.langs, function(lang_id, lang) {
            $.each(lang.rows, function(i, item) {
              var img = name = changed = '';
              if (lang.lang_img) {
                img = '<img src="'+lang.lang_img+'" alt=""/>';
              }
              
              if (item.link) {
                name = '<a href="'+item.link+'" target="_blank">'+item.name+'</a>';
              } else {
                name = item.name;
              }
              
              if (item.changed) {
                changed = ' class="c"';
              }
              
              $('#update_result tr[data-lang="'+lang_id+'"]').before('<tr><td>'+img+'</td><td>'+name+'</td><td>'+item.old_value+'</td><td'+changed+'>'+item.value+'</td></tr>');
            });
            
            if (lang.no_old) {
              $('#update_result table td:nth-child(3), #update_result table th:nth-child(3)').hide();
            } else {
              $('#update_result table td:nth-child(3), #update_result table th:nth-child(3)').show();
            }
          });
          
        }
        
        if (!data.finished) {
          processQueue(type, mode, data.processed);
        } else {
          $('#generateProgress .progress-bar').removeClass('active');
          $('#loading').hide();
          if ($('#update_result tbody tr:not([data-lang])').length == 0) {
             $('#update_result table tbody').append('<tr><td colspan="4" style="padding:30px 0" class="text-center"><?php echo $_language->get('text_nothing_changed'); ?></td></tr>');
          }
        }
      }
		},
    error: function(xhr) {
      $('#generateProgress .progress-bar').removeClass('active');
      $('#loading').hide();
      if (xhr.responseText.length > 1000) {
        alert('Your session has terminated, please log-in again');
      } else {
        alert(xhr.responseText);
      }
    }
	});
}
$(document).ready(function () { 
  $('input[name="simulate"]').on('change', function() {
    if ($(this).val() == 1) {
      $('#update_result .write').hide();
      $('#update_result .simu').show();
    } else {
      $('#update_result .simu').hide();
      $('#update_result .write').show();
    }
  });
});
--></script>
<script type="text/javascript"><!--
var editor_type, editor_lang;

var columnsArray = function(type){
	if (type == 'product') {
		return [
			{'width': '1%', 'orderable': false, 'searchable': false},
			{'width': '21%'},
			{'width': '13%'},
			{'width': '13%'},
			{'width': '13%'},
			<?php if (version_compare(VERSION, '1.5.3', '>=') && !$store_id) { ?>
			{'width': '13%'},
			<?php } ?>
			{'width': '13%'},
			{'width': '13%', 'orderable': false, 'searchable': false}
		];
	}
	else if (type == 'category') {
		return [
			{'width': '1%', 'orderable': false, 'searchable': false},
			null,
			{'width': '25%'},
			{'width': '15%'},
			{'width': '15%'},
			{'width': '15%'}
		];
	}
	else if (type == 'information') {
		return [
			null,
			{'width': '22%'},
			{'width': '19%'},
			{'width': '19%'},
			{'width': '19%'}
		];
	}
	else if (type == 'manufacturer') {
		return [
			{'width': '1%', 'orderable': false, 'searchable': false},
			null,
			{'width': '47%'}
		];
	}
  else if (type == 'image') {
		return [
			{'width': '1%', 'orderable': false, 'searchable': false},
			null,
			{'width': '25%'},
			{'width': '25%'},
			{'width': '25%'},
		];
	}
	else if (type == 'common' || type == 'special' || type == 'redirect' || type == 'absolute') {
		return [
			{'width': '47%'},
			null,
			{'width': '50px', 'orderable': false, 'class': 'center'}
		];
	}
  else if (type == '404') {
		return [
			null,
			{'width': '5%'},
			{'width': '50px', 'orderable': false, 'class': 'center'}
		];
	}
}
// save number of rows to display between each table
var lastPageLength = 10;
$('#tab-editor').on('change', '.dataTables_length select', function(e) {
	lastPageLength = this.value;
});

function reloadCurrentTable() {
	editor_type = $('#tabs_editor li.active > a').attr('editor-type');
	lang = $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang');
	
	$('#seo_editor_'+editor_type).dataTable().api().page.len(lastPageLength);
	$('#seo_editor_'+editor_type).dataTable().api().ajax.url('index.php?route=module/complete_seo/editor_data&type=' + editor_type + '&lang=' + lang + '&store=<?php echo $store_id; ?>&<?php echo $token; ?>').load();
}
	
$(document).ready(function() {

	<?php if (!$store_id  || ($store_id && $mlseo_multistore)) { ?>
	$('#tabs_editor a, #tabs_editor_lang a[editor-lang]').on('shown.bs.tab', function() {
    if ($(this).hasClass('ml-hide')) {
      $('#tabs_editor_lang').hide();
    } else {
      $('#tabs_editor_lang').show();
    }
    
		editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
		lang = $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang');
		is_instancied = $($('#tabs_editor > li.active > a').attr('href') + ' .dataTable').length;
		if(!is_instancied) {
			$('#seo_editor_'+editor_type).dataTable({
				"processing": true,
				"serverSide": true,
				"ajax": 'index.php?route=module/complete_seo/editor_data&type=' + editor_type + '&lang=' + lang + '&store=<?php echo $store_id; ?>&<?php echo $token; ?>',
				"order": [1, 'asc'],
				"lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
				"pageLength": lastPageLength,
				"language": {
					paginate: {
						first: '<<',
						previous: '<',
						next: '>',
						last: '>>'
					},
					"processing": '<img style="z-index:10;position:absolute;left:43%" src="view/seo_package/img/bigloader.gif"/>',
					"info": '_START_ - _END_ / _TOTAL_',
					"infoFiltered": ''
				},
				"columns": columnsArray(editor_type)
			});
      
      $('#seo_editor_'+editor_type).on( 'draw.dt', function () {
        var titleIdx = $('#seo_editor_'+editor_type+' th[data-column="meta_title"]').index();
        
        if (titleIdx > 0) {
          $.each($('#seo_editor_'+editor_type+' tbody tr'), function(){
            setLimitColor($(this).find('td:eq('+titleIdx+') a'));
          });
        }
        
        var descIdx = $('#seo_editor_'+editor_type+' th[data-column="meta_description"]').index();
        
        if (descIdx > 0) {
          $.each($('#seo_editor_'+editor_type+' tbody tr'), function(){
            setLimitColor($(this).find('td:eq('+descIdx+') a'));
          });
        }
        
        //$('#seo_editor_'+editor_type+' .ttip').tooltip();
      });
		}
		else {
			reloadCurrentTable();
		}
	});
	
	$('#tabs_editor a:first').trigger('shown.bs.tab');
  <?php } ?>
  
  function setLimitColor(el, len, type) {
    len = typeof len !== 'undefined' ? len : false;
    type = typeof type !== 'undefined' ? type : false;
    
    if (!type) {
      var type = $(el).closest('.seo_editor').find('th').eq($(el).closest('td').index()).attr('data-column');
    }
    
    if (type == 'meta_title') {
      var limit_low = 67;
      var limit_hi = 85;
    } else if (type == 'meta_description') {
      var limit_low = 300;
      var limit_hi = 320;
    } else {
      return;
    }
    
    if (len !== false) {
    } else if (el.tagName == 'TEXTAREA' || el.tagName == 'INPUT') {
      len = $(el).val().trim().length;
    } else {
      len = $(el).html().length;
    }
    
    if (len > limit_hi) {
      $(el).css('color', '#f23333');
    } else if (len > limit_low) {
      $(el).css('color', '#fc7402');
    } else {
      $(el).css('color', '#333');
    }
  }
  
  // title and desc length
  $('.seo_editor').on('focus keydown keyup', '.editable-input textarea, .editable-input input', function(e) {
    setLimitColor(e.target);
	});
  
  $('.store_seo_title').on('focus keydown keyup', function(e) {
    setLimitColor(e.target, false, 'meta_title');
	});
  $('.store_seo_desc').on('focus keydown keyup', function(e) {
    setLimitColor(e.target, false, 'meta_description');
	});
  $('.store_seo_title,.store_seo_desc').trigger('keyup');
  
	// editor
	$.fn.editable.defaults.mode = 'inline';
	$.fn.editable.defaults.emptytext = '';
	
  <?php /*
  var products = [<?php foreach ($products as $p) { ?>{id:'<?php echo $p['product_id']?>', text:'<?php echo $p['name']?>'},<?php } ?>];
  */ ?>

  var select2Active = false;
  
  $('.seo_editor').on('click', 'a.select2', function(e) {
    var select =  $(this).parent().find('select');
    if (!select.is(':visible')) {
      $('.select2-input').hide();
      $('a.select2').show();
      $(this).hide();
      $(this).prev().show();
      select.attr('data-pk');
      select.select2({
        minimumInputLength:1,
        width: '100%',
        placeholder: 'Choose product',
        ajax: {
          url: 'index.php?route=module/complete_seo/product_search&<?php echo $token; ?>',
          dataType: 'json',
          delay: 250,
        }
      });
    }
    select2Active = true;
  });

  $('.seo_editor').on('click', 'button.select2-submit', function(e) {
    e.preventDefault();
    
    $.ajax({
      url: 'index.php?route=module/complete_seo/editor_update&<?php echo $token; ?>',
      data: {
        'pk': $(this).parent().parent().find('select').attr('data-pk'),
        'col': $(this).parent().parent().find('select').attr('data-col'),
        'value': $(this).parent().parent().find('select').val()
      },
      dataType: 'json',
      method: 'post',
      success: function(data){
        $('a.select2[pk='+data.pk+']').html(data.msg);
        $('.select2-input').hide();
        $('a.select2').show();
        
        var $e = $('a.select2[pk='+data.pk+']').closest('td');
                    
        $e.css('background-color', '#9EC499');
        setTimeout(function(){
          $e.css('background-color', '');
          $e.addClass('editable-bg-transition');
          setTimeout(function(){
             $e.removeClass('editable-bg-transition');  
          }, 1700);
        }, 10);
      },
      error: function(xhr){
        if (xhr.responseText.length > 1000) {
          alert('Your session has terminated, please log-in again');
        } else {
          alert(xhr.responseText);
        }
      }
    });
  });
  
  // close selector on click elsewhere
  $('html').on('click', function(e) {
    if(!select2Active && !$('.select2-container').is('.select2-container--open')) {
      $('.select2-input').hide();
      $('a.select2').show();
    }
    select2Active = false;
  });
  
  // prevent closing selector
	$('.seo_editor').on('select2:unselect select2:close', '.select2-input', function(e) {
     select2Active = true;
	});
	$('.seo_editor').on('click', '.select2-container--open', function(e) {
      select2Active = true;
	});
  
	$('.seo_editor').editable({
		selector: 'a[data-pk]',
		highlight: false,
		ajaxOptions: {dataType: 'json'},
		success: function(response, newValue) {
			
			var $e = $(this).closest('td');
                    
			$e.css('background-color', '#9EC499');
			setTimeout(function(){
				$e.css('background-color', '');
				$e.addClass('editable-bg-transition');
				setTimeout(function(){
				   $e.removeClass('editable-bg-transition');  
				}, 1700);
			}, 10);
			
			if(!response) {
				return "Unknown error";
			} else if(response.status == 'error') {
				return response.msg;
			} else if(response.status == 'success') {
        setLimitColor($e.find('.editable'), response.msg.length);
				return {newValue: response.msg};
			}else if(response.status == 'success-related') {
        $e.html(response.msg);
        //$('a.related[data-pk="'+response.pk+'"]').text(response.msg);
				//return {newValue: response.val};
			}
			
		},
	    error: function(response, newValue) {
			return 'Request error (session terminated ?)';
		},
		url: 'index.php?route=module/complete_seo/editor_update&<?php echo $token; ?>'
	});
  
	$('.seo_editor').on('click', 'td', function(e) {
    $('.editable').editable('option', 'params', {
      'type': $('#tabs_editor > li.active > a').attr('editor-type'),
      'col': $(e.target).closest('.seo_editor').find('th').eq($(this).index()).attr('data-column'),
      'lang': $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang')
    });
    
    if ($(this).find('.select2-input').is(':visible')) {
      select2Active = true;
    }
    
		if ($(e.target).is('td') && $(this).find('> a').length) {
			$(this).find('> a').trigger('click');
			return false;
		} else if ($(e.target).is('td') && $(this).find('> a.modal-related').length) {
			$(this).find('> a.modal-related').trigger('click');
			return false;
		}
	});
  
  $('body').on('click', '.info-btn', function() {
    $('#modal-info').html('<div style="text-align:center"><img src="view/complete_seo/img/loader.gif" alt=""/></div>');
    $('#modal-info').load('index.php?route=module/complete_seo/modal_info&<?php echo $token; ?>', {'info': $(this).attr('data-info')});
  });
  
  $('body').on('click', '.modal-related', function() {
    $('#modal-info').html('<div style="text-align:center"><img src="view/complete_seo/img/loader.gif" alt=""/></div>');
    $('#modal-info').load('index.php?route=module/complete_seo/modal_related&<?php echo $token; ?>', {'id': $(this).attr('data-pk')});
  });
	
	$('.seo_editor').on('click', '.deleteAlias', function(e) {
		if (confirm('<?php echo $_language->get('text_seo_confirm'); ?>')) {
      editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
			$.ajax({
				url: 'index.php?route=module/complete_seo/editor_delete_alias&type='+ editor_type +'&pk='+$(this).attr('data-pk')+'&<?php echo $token; ?>',
				success: function(){ reloadCurrentTable(); }
			});
		}
	});
	$('#newAliasModal').on('show.bs.modal', function (e) {
	  editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
		$('#newAliasModal .modalinfo').hide();
    $('#newAliasModal .' + editor_type).show();
	})
	$('.modal').on('click', '.addAlias', function(e) {
		editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
		lang = $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang');
		$.ajax({
			url: 'index.php?route=module/complete_seo/editor_add_alias&type='+ editor_type +'&lang='+ lang +'&<?php echo $token; ?>',
			data: $('#newAliasModal form').serialize(),
			success: function(){ $('#newAliasModal').modal('hide'); reloadCurrentTable(); }
		});
	});
	$('.more_actions').on('click', '.restoreAliases', function(e) {
		if (confirm('<?php echo $_language->get('text_seo_confirm'); ?>')) {
			editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
			lang = $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang');
			lang_code = $('#tabs_editor_lang li.active > a[editor-lang-code]').attr('editor-lang-code');
			$.ajax({
				url: 'index.php?route=module/complete_seo/editor_restore_aliases&type='+ editor_type +'&lang='+ lang +'&lang_code='+ lang_code +'&<?php echo $token; ?>',
				success: function(){ reloadCurrentTable(); }
			});
		}
	});
	$('.more_actions').on('click', '.exportAliases', function(e) {
		editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
		lang = $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang');
		lang_code = $('#tabs_editor_lang li.active > a[editor-lang-code]').attr('editor-lang-code');
		url = 'index.php?route=module/complete_seo/editor_export_aliases&type='+ editor_type +'&lang='+ lang +'&lang_code='+ lang_code +'&<?php echo $token; ?>';
		window.open(url, '_blank');
	});
	$('.more_actions').on('click', '.deleteAliases', function(e) {
		if (confirm('<?php echo $_language->get('text_seo_confirm'); ?>')) {
      var redirOnly = '';
      if ($(this).hasClass('redirOnly')) {
        redirOnly = '&redir_only=1';
      }
			editor_type = $('#tabs_editor > li.active > a').attr('editor-type');
			lang = $('#tabs_editor_lang li.active > a[editor-lang]').attr('editor-lang');
			$.ajax({
				url: 'index.php?route=module/complete_seo/editor_delete_aliases&type='+ editor_type +'&lang='+ lang + redirOnly + '&<?php echo $token; ?>',
				success: function(){ reloadCurrentTable(); }
			});
		}
	});
});
//--></script>
<?php if ($OC_V2) { ?>
<style>
#update_result{margin-top:40px;}
</style>
<?php } ?>
<?php echo $footer; ?>