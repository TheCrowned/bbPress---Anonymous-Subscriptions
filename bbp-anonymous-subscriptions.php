<?php
/*
Plugin Name: bbPress - Anonymous Subscriptions
Plugin URL: http://www.thecrowned.org/bbpress-anonymous-subscriptions
Description: Allows anonymous users to subscribe to bbPress topics and receive emails notifications when new replies are posted.
Version: 1.3.8
Author: Stefano Ottolenghi
Author URI: http://www.thecrowned.org
Text Domain: bbp-anonymous-subscriptions
Domain Path: lang
*/

class BBP_Anonymous_Subscriptions {

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		//Load the plugin translation files
		add_action( 'init', array( __CLASS__, 'textdomain' ) );

		//Show the "Notify me" checkbox
		add_action( 'bbp_theme_before_reply_form_submit_wrapper', array( __CLASS__, 'checkbox' ) );
		add_action( 'bbp_theme_before_topic_form_submit_wrapper', array( __CLASS__, 'checkbox' ) );

		//Save the subscription state
		add_action( 'bbp_new_reply',  array( __CLASS__, 'update_reply' ), 0, 6 ); //hook to send email custom
		add_action( 'bbp_new_topic',  array( __CLASS__, 'update_topic' ), 0, 4 ); //hook to send email custom
		add_action( 'bbp_edit_reply',  array( __CLASS__, 'update_reply' ), 0, 6 );

		//Email notifications on new replies
		//It's needed to hook to the title filter because otherwise, if there are no registered users subscribed, emails wouldn't be sent
		add_filter( 'bbp_topic_subscription_user_ids', array( __CLASS__, 'notify_anonymous_subscriptions' ), 10, 3 );

		//Manage unsubscriptions
		add_action( 'plugins_loaded', array( __CLASS__, 'unsubscribe_email' ) );
	}

	/**
	 * Load the plugin's text domain
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function textdomain() {
		load_plugin_textdomain( 'bbp-anonymous-subscriptions', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Manages unsubscriptions.
	 *
	 * Users get here through a link in the notification email. It's all tied to GET parameters.
	 *
	 * @since	1.0
	 *
	 * @return void
	 */
	public static function unsubscribe_email() {
		if( ! isset( $_GET['bbp_anonymous_unsubscribe'] ) OR ! isset( $_GET['user_email'] ) OR ! isset( $_GET['topic_id'] ) )
			return;

		$unsubscribe_email = trim( $_GET['user_email'] );
		if( ! is_email( $unsubscribe_email ) )
			wp_die( __( 'Invalid email.', 'bbp-anonymous-subscriptions' ) );

		$topic_id = (int) $_GET['topic_id'];

		$subscribed_emails = get_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails', true );
		if( empty( $subscribed_emails ) )
			wp_die( __( 'You do not seem subscribed to this topic, not with this email at least!', 'bbp-anonymous-subscriptions' ) );

		//Look for email to be unsubscribed
		$index_delete = array_search( $unsubscribe_email, $subscribed_emails );
		if( $index_delete !== false ) {
			unset( $subscribed_emails[$index_delete] );

			//Delete post_meta if there are no more subscribed emails
			if( ! empty( $subscribed_emails ) ) {
				if( ! update_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails', $subscribed_emails ) )
					wp_die( __( 'There was an error while unsubscribing!', 'bbp-anonymous-subscriptions' ) );
			} else {
				if( ! delete_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails' ) )
					wp_die( __( 'There was an error while unsubscribing!', 'bbp-anonymous-subscriptions' ) );
			}

			wp_die( __( 'Successfully unsubscribed!', 'bbp-anonymous-subscriptions' ), __( 'Successfully unsubscribed!', 'bbp-anonymous-subscriptions' ) );

		} else {
			wp_die( __( 'You do not seem subscribed to this topic, not with this email at least!', 'bbp-anonymous-subscriptions' ) );
		}

	}

	/**
	 * Outputs the "Notify me of follow-up replies via email" checkbox.
	 * Only if user is not logged in.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function checkbox() {

		if( ! is_user_logged_in() AND ! bbp_is_reply_edit() ) {
?>
		<p>
			<input name="bbp_anonymous_subscribe" id="bbp_anonymous_subscribe" type="checkbox" value="1" tabindex="<?php bbp_tab_index(); ?>" checked="checked" />
			<label for="bbp_anonymous_subscribe"><?php _e( 'Notify me of follow-up replies via email', 'bbp-anonymous-subscriptions' ); ?></label>
		</p>

			<?php } /*else {

			if( bbp_is_reply_edit() ) {

				<p>
				<input name="bbp_anonymous_subscribe" id="bbp_anonymous_subscribe" type="checkbox" <?php echo self::is_user_subscribed( get_the_author_meta( 'ID' ) ); ?> tabindex="<?php bbp_tab_index(); ?>" />
				<label for="bbp_anonymous_subscribe"><?php _e( 'Notify author of follow-up replies via email.', 'bbp-anonymous-subscriptions' ); ?></label>
				</p>

			<?php } ?>

<?php
		}*/
	}

	/*public static function is_user_subscribed( $user_id ) {
		return 'checked="checked"';
	}*/

	/**
	 * Stores the subscribed state on reply creation and edit
	 *
	 * @since 1.0
	 *
	 * @param $reply_id int The ID of the reply
	 * @param $topic_id int The ID of the topic the reply belongs to
	 * @param $forum_id int The ID of the forum the topic belongs to
	 * @param $anonymous_data bool Are we posting as an anonymous user?
	 * @param $author_id int The ID of user creating the reply, or the ID of the replie's author during edit
	 * @param $is_edit bool Are we editing a reply?
	 *
	 * @return void
	 */
	public static function update_reply( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {
		if( isset( $_POST['bbp_anonymous_subscribe'] ) ) {
			$subscribed_emails = get_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails', true );
			if( empty( $subscribed_emails ) )
				$subscribed_emails = array();

			if( ! in_array( $anonymous_data['bbp_anonymous_email'], $subscribed_emails ) ) {
				$subscribed_emails[] = $anonymous_data['bbp_anonymous_email'];

				update_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails', $subscribed_emails );
			}

		}
	}

	/**
	 * Stores the subscribed state on topic creation
	 *
	 * @since 1.3
	 *
	 * @param $topic_id int The ID of the topic the reply belongs to
	 * @param $forum_id int The ID of the forum the topic belongs to
	 * @param $anonymous_data bool Are we posting as an anonymous user?
	 * @param $topic_author int The ID of user who created the topic
	 *
	 * @return void
	 */
	public static function update_topic( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {
		if( isset( $_POST['bbp_anonymous_subscribe'] ) ) {
			$subscribed_emails = array();
			$subscribed_emails[] = $anonymous_data['bbp_anonymous_email'];
			update_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails', $subscribed_emails );
		}
	}

	/**
	 * Sends notification emails for new replies to subscribed topics to anonymous users.
	 *
	 * Derived from bbpress/includes/common/functions.php (bbp_notify_topic_subscribers()).
	 *
	 * @since 1.0
	 *
	 * @param string $subject subject of the newly made reply
	 * @param int $reply_id ID of the newly made reply
	 * @param int $topic_id ID of the topic of the reply
	 *
	 * @return bool True on success, false on failure
	 */
	public static function notify_anonymous_subscriptions( $user_ids, $reply_id, $topic_id ) {

		/** Validation ************************************************************/

		$reply_id = bbp_get_reply_id( $reply_id );
		$topic_id = bbp_get_topic_id( $topic_id );

		/** Topic *****************************************************************/

		// Bail if topic is not published
		if ( !bbp_is_topic_published( $topic_id ) ) {
			return $user_ids;
		}

		/** Reply *****************************************************************/

		// Bail if reply is not published
		if ( !bbp_is_reply_published( $reply_id ) ) {
			return $user_ids;
		}

		// Poster name
		$reply_author_name = bbp_get_reply_author_display_name( $reply_id );
		$reply_author_email = bbp_get_reply_author_email( $reply_id );

		/** Mail ******************************************************************/

		// Remove filters from reply content and topic title to prevent content
		// from being encoded with HTML entities, wrapped in paragraph tags, etc...
		bbp_remove_all_filters( 'bbp_get_reply_content' );
		bbp_remove_all_filters( 'bbp_get_topic_title' );
		bbp_remove_all_filters( 'the_title' );

		// Strip tags from text and setup mail data
		$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
		$reply_content = strip_tags( bbp_get_reply_content( $reply_id ) );
		$reply_url     = bbp_get_reply_url( $reply_id );
		$blog_name     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		/** Users *****************************************************************/

		// Get the noreply@ address
		$no_reply   = bbp_get_do_not_reply_address();

		// Setup "From" email address
		$from_email = apply_filters( 'bbp_subscription_from_email', $no_reply );

		// Setup the From header
		$headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <' . $from_email . '>' );

		// Get topic anonymous subscribers and bail if empty
		$user_emails = get_post_meta( $topic_id, '_bbp_anonymous_subscribed_emails', true );
		if ( empty( $user_emails ) )
			return $user_ids;

		// Loop through users
		foreach ( (array) $user_emails as $user_email ) {

			// Don't send notifications to the person who made the post
			if ( ! empty( $reply_author_email ) && $user_email === $reply_author_email )
				continue;

			//Custom unsubscribe link
			$topic_url = bbp_get_topic_permalink( $topic_id );
			$unsubscribe_link = add_query_arg( array(
				'bbp_anonymous_unsubscribe' => '',
				'user_email' => urlencode($user_email),
				'topic_id' => (int) $topic_id
			), $topic_url );

			// For plugins to filter messages per reply/topic/user
			$message = sprintf( __( 'DO NOT REPLY to this email, this is an automated notification. Instead, use the link at the bottom to go to the topic.

%1$s wrote:

%2$s

Post Link: %3$s

-----------

You are receiving this email because you subscribed to a forum topic.

To unsubscribe from notifications for this topic, click on the following link.%4$s', 'bbp-anonymous-subscriptions' ),

				$reply_author_name,
				$reply_content,
				$reply_url,
				'
'.$unsubscribe_link
			);

			$message = apply_filters( 'bbp_subscription_mail_message', $message, $reply_id, $topic_id );
			if ( empty( $message ) ) {
				return $user_ids;
			}

			$subject = apply_filters( 'bbp_subscription_mail_title', '[' . $blog_name . '] ' . $topic_title, $reply_id, $topic_id );
			if ( empty( $subject ) ) {
				return $user_ids;
			}

			// Get email address of subscribed user
			//$headers[] = 'Bcc: ' . $user_email;

			/** Send it ***************************************************************/

			// Custom headers
			$headers  = apply_filters( 'bbp_subscription_mail_headers', $headers  );
			$to_email = apply_filters( 'bbp_subscription_to_email',     $no_reply );

			// Send notification email
			wp_mail( $user_email, $subject, $message, $headers );
		}

		return $user_ids;
	}
}

//Instantiate plugin's class
$GLOBALS['bbp_anonymous_subscriptions'] = new BBP_Anonymous_Subscriptions();
