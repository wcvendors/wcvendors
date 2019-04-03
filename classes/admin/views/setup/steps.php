<?php
/**
 * Admin View: Setup Steps
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<ol class="wcv-setup-steps">
	<?php foreach ( $output_steps as $step_key => $step ) : ?>
		<li class="
			<?php
		if ( $step_key === $this->step ) {
			echo 'active';
		} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
			echo 'done';
		}
		?>
		"><?php echo esc_html( $step['name'] ); ?></li>
	<?php endforeach; ?>
</ol>
