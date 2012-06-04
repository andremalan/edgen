<?php if ( bp_has_forum_topic_posts() ) : ?>

	<form action="<?php bp_forum_topic_action() ?>" method="post" id="forum-topic-form" class="standard-form">

		<div id="topic-meta">

			<h1 class="entry-title"><?php bp_the_topic_title() ?> (<?php bp_the_topic_total_post_count() ?>)</h1>
			
			<?php if ( bp_group_is_admin() || bp_group_is_mod() || bp_get_the_topic_is_mine() ) : 
				//<div class="item-list-tabs no-ajax" id="subnav">  ?>
				<div class="item-list-tabs no-ajax">
					<div class="sub-tabs admin-links">
						<?php bp_the_topic_admin_links() ?>
					</div>
				</div><!-- .item-list-tabs -->
			<?php endif; ?>
			
		</div>

		<div class="bp-pagination no-ajax">
			<div id="post-count" class="pag-count">
				<?php bp_the_topic_pagination_count() ?>
			</div>
			<div class="pagination-links" id="topic-pag">
				<?php bp_the_topic_pagination() ?>
			</div>
		</div>

		<ul id="topic-post-list" class="item-list">
			<?php while ( bp_forum_topic_posts() ) : bp_the_forum_topic_post(); ?>

				<li id="post-<?php bp_the_topic_post_id() ?>" class="item-li">
					<div class="item-container">
						<div class="item-content">
						
							<article>
								<div class="poster-avatar">
									<a href="<?php bp_the_topic_post_poster_link() ?>">
										<?php 
										$size = 35;
										// get the avatar image, get the default, resize default, replace URL of default with resized, insert new URL.
										$avatarURL = bp_theme_avatar_url(
											$size,$size,
											'', 
											bp_core_fetch_avatar(array( 'item_id' => $GLOBALS['topic_template']->post->poster_id, 'type' => 'full', 'html' => 'false', 'width' => $size, 'height' => $size ))
										);
										echo '<div class="avatar" style="background-image: url(\''.$avatarURL.'\'); width:'.$size.'px; height:'.$size.'px; "></div>';  //bp_the_topic_post_poster_avatar( 'width=40&height=40' ) ?>
									</a>
								</div>
								
								<div class="item-content-container">
									
									<header class="comment-header item-header">
										<div class="topic-post-title">
											<h4 class="poster-name"><?php bp_the_topic_post_poster_name() ?></h4> <span class="said"><?php _e( 'said', 'buddypress' ) ?></span>
										</div>
									</header>
				
									<div class="post-content">
										<?php bp_the_topic_post_content() ?>
									</div>
				
									<footer class="post-footer item-footer clearfix">
										<div class="date"><?php bp_the_topic_post_time_since() ?></div>
										<div class="item-actions admin-links">
											<?php if ( bp_group_is_admin() || bp_group_is_mod() || bp_get_the_topic_post_is_mine() ) : ?>
												<?php bp_the_topic_post_admin_links() ?> | 
											<?php endif; ?>
											<a href="#post-<?php bp_the_topic_post_id() ?>" title="<?php _e( 'Permanent link to this post.', 'buddypress' ) ?>"><?php _e( 'Permalink', 'buddypress' ) ?></a>
										</div>
									</footer>
									
								</div>
							</article>
						
						</div>
					</div>
				</li>

			<?php endwhile; ?>
		</ul>

		<div class="bp-pagination no-ajax">
			<div id="post-count" class="pag-count">
				<?php bp_the_topic_pagination_count() ?>
			</div>
			<div class="pagination-links" id="topic-pag">
				<?php bp_the_topic_pagination() ?>
			</div>
		</div>
		
		<?php if ( ( is_user_logged_in() && 'public' == bp_get_group_status() ) || bp_group_is_member() ) : ?>

			<?php if ( bp_get_the_topic_is_last_page() ) : ?>

				<?php if ( bp_get_the_topic_is_topic_open() ) : ?>

					<div id="post-topic-reply">
						<p id="post-reply"></p>

						<?php if ( !bp_group_is_member() ) : ?>
							<p><?php _e( 'You will auto join this group when you reply to this topic.', 'buddypress' ) ?></p>
						<?php endif; ?>

						<?php do_action( 'groups_forum_new_reply_before' ) ?>

						<h4><?php _e( 'Add a reply:', 'buddypress' ) ?></h4>

						<textarea name="reply_text" id="reply_text"></textarea>

						<div class="submit">
							<input type="submit" name="submit_reply" id="submit" value="<?php _e( 'Post Reply', 'buddypress' ) ?>" />
						</div>

						<?php do_action( 'groups_forum_new_reply_after' ) ?>

						<?php wp_nonce_field( 'bp_forums_new_reply' ) ?>
					</div>

				<?php else : ?>

					<div id="message" class="info">
						<p><?php _e( 'This topic is closed, replies are no longer accepted.', 'buddypress' ) ?></p>
					</div>

				<?php endif; ?>

			<?php endif; ?>

		<?php endif; ?>

	</form>
<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'There are no posts for this topic.', 'buddypress' ) ?></p>
	</div>

<?php endif;?>