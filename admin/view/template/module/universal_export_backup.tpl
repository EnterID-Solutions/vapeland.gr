  <?php include 'universal_export_functions.tpl'; ?>
  <fieldset class="filters"><legend><div class="pull-right" style="font-size:13px; color:#666"><?php echo $_language->get('total_export_items'); ?> <span class="export_number badge clearblue"></span></div><?php echo $_language->get('export_filters'); ?></legend>

    <!--div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <div class="pull-right intervalValue"></div>
          <?php fieldLabel('filter_interval', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-clock-o"></i></span>
          <input type="text" class="form-control" name="filter_interval" value="<?php echo isset($profile['filter_interval']) ? $profile['filter_interval'] : ''; ?>" placeholder="-1 day, -1 month, last monday, ..."/>
          </div>
        </div>
      </div>
    </div-->
    
    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <?php fieldLabel('entry_backup_tables', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-list"></i></span>
          <select class="selectize_category" name="backup_tables[]" multiple="multiple">
            <option value=""><?php echo $_language->get('export_all'); ?></option>
            <?php foreach ($tables as $field) { ?>
              <option value="<?php echo $field; ?>" <?php if (isset($profile['backup_tables']) && in_array($field, $profile['backup_tables'])) echo 'selected'; ?>><?php echo $field; ?></option>
            <?php } ?>
          </select>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  
  <fieldset><legend><?php echo $_language->get('export_options'); ?></legend>
  
    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('entry_sql_prefix', $_language); ?>
           <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-fw fa-terminal"></i></span>
            <input type="text" name="prefix" value="<?php echo isset($profile['prefix']) ? $profile['prefix'] : DB_PREFIX; ?>" placeholder="oc_" id="input-prefix" class="form-control" />
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-group">
          <?php fieldLabel('entry_truncate', $_language); ?>
          <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-fw fa-eraser"></i></span>
          <select class="form-control" name="truncate">
            <option value="1" <?php if (!empty($profile['truncate'])) echo 'selected'; ?>><?php echo $_language->get('text_yes'); ?></option>
            <option value="" <?php if (empty($profile['truncate'])) echo 'selected'; ?>><?php echo $_language->get('text_no'); ?></option>
          </select>
          </div>
        </div>
      </div>
    </div>
    
  </fieldset>

<div class="spacer"></div>

<?php include 'universal_export_common.tpl'; ?>

<script type="text/javascript">
var $selectize_category = $('.selectize_category').selectize({
  maxItems: null
});

getTotalExportCount();
</script>