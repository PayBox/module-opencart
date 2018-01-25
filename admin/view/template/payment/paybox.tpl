<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div class="box">
  <div class="left"></div>
  <div class="right"></div>
  <div class="heading">
          <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
    <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location='<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
  </div>
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
          <tr>
              <td width="25%"><span class="required">*</span> <?php echo $entry_payment_name; ?></td>
              <td><input type="text" name="paybox_payment_name" value="<?php echo $paybox_payment_name; ?>" />
                  <br />
                  <?php if ($error_payment_name) { ?>
                  <span class="error"><?php echo $error_payment_name; ?></span>
                  <?php } ?></td>
          </tr>
      	<tr>
        <td width="25%"><span class="required">*</span> <?php echo $entry_merchant_id; ?></td>
        <td><input type="text" name="paybox_merchant_id" value="<?php echo $paybox_merchant_id; ?>" />
          <br />
          <?php if ($error_merchant_id) { ?>
          <span class="error"><?php echo $error_merchant_id; ?></span>
          <?php } ?></td>
      	</tr>
      	<tr>
        <td><span class="required">*</span> <?php echo $entry_secret_word; ?></td>
        <td><input type="text" name="paybox_secret_word" value="<?php echo $paybox_secret_word; ?>" />
          <br />
          <?php if ($error_secret_word) { ?>
          <span class="error"><?php echo $error_secret_word; ?></span>
          <?php } ?></td>
      	</tr>
      	<tr>
       		<td><span class="required">*</span> Result URL:</td>
        	<td><?php echo $copy_result_url; ?></td>
      	</tr>
      	<tr>
        	<td><span class="required">*</span> Success URL:</td>
        	<td><?php echo $copy_success_url; ?></td>
      	</tr>
      	<tr>
        	<td><span class="required">*</span> Fail URL:</td>
        	<td><?php echo $copy_fail_url; ?></td>
      	</tr>
          <tr>
              <td><?php echo $entry_test; ?></td>
              <td>
                  <select name="paybox_test">
                      <?php if($paybox_test == 1) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                  </select>
              </td>
          </tr>
          <tr>
              <td width="25%"><?php echo $entry_lifetime; ?></td>
              <td><input type="text" name="paybox_lifetime" value="<?php echo $paybox_lifetime; ?>" /></td>
          </tr>
      	<tr>
        <td><?php echo $entry_order_status; ?></td>
        <td><select name="paybox_order_status_id">
            <?php foreach ($order_statuses as $order_status) { ?>
            <?php if ($order_status['order_status_id'] == $paybox_order_status_id) { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
            <?php } ?>
            <?php } ?>
          </select></td>
      	</tr>
      	<tr>
        <td><?php echo $entry_status; ?></td>
        <td><select name="paybox_status">
            <?php if ($paybox_status) { ?>
            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
            <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
            <option value="1"><?php echo $text_enabled; ?></option>
            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
            <?php } ?>
          </select></td>
      	</tr>
      	 <tr>
          <td><?php echo $entry_sort_order; ?></td>
          <td><input type="text" name="paybox_sort_order" value="<?php echo $paybox_sort_order; ?>" size="1" /></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>