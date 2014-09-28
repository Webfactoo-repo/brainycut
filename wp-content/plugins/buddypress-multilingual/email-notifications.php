<?php
/*
 * BP email messages
 * 
 * Fixes issue with String Translation 2.0.6
 * https://onthegosystems.myjetbrains.com/youtrack/issue/wpml-117
 * String 'name' md5() is wrongly set and checked when text has line breaks.
 * String names are updated once to match WPML gettext filter.
 */

add_action( 'init', 'bpml_update_email_notifications_strings' );

//if ( isset( $_GET['bpml_test_email'] ) ) {
//    add_action( 'init', 'bpml_email_notifications_test', 11 );
//}

/**
 * Updates string names once.
 * 
 * @return type
 */
function bpml_update_email_notifications_strings() {
    if ( function_exists( 'icl_st_is_registered_string' )
            && function_exists( 'icl_rename_string' ) ){
        $messages = bpml_email_notifications_get_messages();
        foreach ( $messages as $hook => $message ) {
            $name = md5( str_replace( "\n", '\n', $message ) );
            $registered = icl_st_is_registered_string( 'plugin buddypress', $name );
            if ( $registered ) {
                icl_rename_string( 'plugin buddypress', $name, md5( $message ) );
            }
        }
    }
}

/**
 * Problematic messages copied from BP.
 * 
 * @return array
 */
function bpml_email_notifications_get_messages() {
    return array(
        'bp_activity_at_message_notification_message' => '%1$s mentioned you in an update:

"%2$s"

To view and respond to the message, log in and visit: %3$s

---------------------
',
        'bp_activity_new_comment_notification_message' => '%1$s replied to one of your updates:

"%2$s"

To view your original update and all comments, log in and visit: %3$s

---------------------
',
        'bp_activity_new_comment_notification_comment_author_message' => '%1$s replied to one of your comments:

"%2$s"

To view the original activity, your comment and all replies, log in and visit: %3$s

---------------------
',
        'bp_core_activation_signup_blog_notification_message' => "Thanks for registering! To complete the activation of your account and blog, please click the following link:\n\n%1\$s\n\n\n\nAfter you activate, you can visit your blog here:\n\n%2\$s",
        'bp_core_activation_signup_user_notification_message' => "Thanks for registering! To complete the activation of your account please click the following link:\n\n%1\$s\n\n",
        'groups_at_message_notification_message' => '%1$s mentioned you in the group "%2$s":

"%3$s"

To view and respond to the message, log in and visit: %4$s

---------------------
',
        'friends_notification_new_request_message' => '%1$s wants to add you as a friend.

To view all of your pending friendship requests: %2$s

To view %3$s\'s profile: %4$s

---------------------
',
        'friends_notification_accepted_request_message' => '%1$s accepted your friend request.

To view %2$s\'s profile: %3$s

---------------------
',
        'groups_notification_group_updated_message' => 'Group details for the group "%1$s" were updated:

To view the group: %2$s

---------------------
',
        'groups_notification_new_membership_request_message' => '%1$s wants to join the group "%2$s".

Because you are the administrator of this group, you must either accept or reject the membership request.

To view all pending membership requests for this group, please visit:
%3$s

To view %4$s\'s profile: %5$s

---------------------
',
        'groups_notification_membership_request_completed_message' => 'Your membership request for the group "%1$s" has been rejected.

To submit another request please log in and visit: %2$s

---------------------
',
        'groups_notification_promoted_member_message' => 'You have been promoted to %1$s for the group: "%2$s".

To view the group please visit: %3$s

---------------------
',
        'groups_notification_group_invites_message' => 'One of your friends %1$s has invited you to the group: "%2$s".

To view your group invites visit: %3$s

To view the group visit: %4$s

To view %5$s\'s profile visit: %6$s

---------------------
',
        'bp_core_signup_send_validation_email_message' => "Thanks for registering! To complete the activation of your account please click the following link:\n\n%1\$s\n\n",
        'messages_notification_new_message_message' => '%1$s sent you a new message:

Subject: %2$s

"%3$s"

To view and read your messages please log in and visit: %4$s

---------------------
');
}

/**
 * Test function.
 */
function bpml_email_notifications_test() {
    $user = get_userdata( 1 );
    $user->user_id = $user->ID;
    messages_notification_new_message( $raw_args = array(
        'subject' => 'test notification',
        'message' => 'Hello!',
        'recipients' => array($user)
            )
    );
}