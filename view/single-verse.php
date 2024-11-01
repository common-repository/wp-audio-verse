<?php

get_header();


if(have_posts()) : while(have_posts()) : the_post(); ?> 

<div class="wrapper">

	<div class="verse-main-content mCustomScrollbar">
		<h2><?php the_title(); ?></h2>
		<?php the_content(); ?>
	</div>

	<div class="translated-verse mCustomScrollbar">
		<?php 
		$translate = get_post_meta($post->ID, 'verse_settings_verse_translate', true);
		echo wp_kses_post( $translate ); ?>
	</div>

	<?php 
	$dfmr_url = esc_attr(get_post_meta($post->ID, 'verse_settings_upload_mp3', true));

	if(!empty($dfmr_url)) {
	 ?>
	<div class="dfm-radio">

		<div id="dfmr_jquery_jplayer" class="jp-jplayer"></div>
		<div id="jp_container_1" class="jp-audio">


			<div id="dfmr-player" class="container">

				<div class="jp-type-single">
					<div class="dfmr-player-control">
						<div class="jp-gui jp-interface">
							<div class="volume-control">
								<div class="dfmr-volume-control">
									<div class="dfmr-mute-unmute">
										<button class="jp-mute" tabindex="0" title="mute"></button>
										<button class="jp-unmute" tabindex="0" title="unmute"></button>

										<li class="jp-volume-bar dfmr-volume">
											<div class="jp-volume-bar-value"></div>
										</li>
										<li class="dfmr-increase-vol">
											<button class="jp-volume-max dfmr-volume" tabindex="0" title="max volume"></button>
										</li>

									</div>
								</div>

							</div>
							<div class="jp-controls">
								<button class="jp-play" tabindex="0"></button>
								<button class="jp-pause" tabindex="0"></button>

							</div>
							<div class="dfmr-alert">
								<div class="dfmr-title"><?php echo get_the_title(); ?></div>
								<div id="waiting"></div>
								<div id="playing"></div>
								<div id="pause"></div>

							</div>
							<div class="jp-progress">
								<div class="jp-seek-bar">
									<div class="jp-play-bar"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<?php endwhile;endif; 

get_footer();
?>