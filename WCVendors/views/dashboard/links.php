<center>
<p>
        <a href="<?php echo $shop_page; ?>" class="button">View Your Store</a>
        <a href="<?php echo $settings_page; ?>" class="button">Store Settings</a>

<?php if ( $can_submit ) { ?>
                <a target="_TOP" href="<?php echo $submit_link; ?>" class="button">Add New Product</a>
                <a target="_TOP" href="<?php echo $edit_link; ?>" class="button">Edit Products</a>
<?php } ?>
</center>

<hr>