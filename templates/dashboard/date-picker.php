<form method="post">
	<p>
		<label style="display:inline;" for="from"><?php _e( 'From:', 'wc-vendors' ); ?></label>
		<input class="date-pick" type="date" name="start_date" id="from"
			   value="<?php echo esc_attr( date( 'Y-m-d', $start_date ) ); ?>"/>

		<label style="display:inline;" for="to"><?php _e( 'To:', 'wc-vendors' ); ?></label>
		<input type="date" class="date-pick" name="end_date" id="to"
			   value="<?php echo esc_attr( date( 'Y-m-d', $end_date ) ); ?>"/>

		<input type="submit" class="btn btn-inverse btn-small" style="float:none;"
			   value="<?php _e( 'Show', 'wc-vendors' ); ?>"/>
	</p>
</form>
