<?php

require_once get_home_path() . "vendor/autoload.php";
function after_form_submission($response)
{

    $user_id = get_current_user_id();
    $user_messages_left = get_user_meta($user_id, 'number_of_messages_left', true);
    update_user_meta($user_id, 'number_of_messages_left', $user_messages_left == 0 ? 0 : $user_messages_left - 1);

    if ($user_messages_left == 1) {
        $customer = rcp_get_customer_by_user_id($user_id);
        if ($customer) {
            foreach ($customer->get_memberships() as $membership) {
                rcp_update_membership($membership->get_id(), array('expiration_date' => current_time('mysql')));
            }

        }
    }

}

add_action('jet-form-builder/form-handler/after-send', 'after_form_submission');

function get_no_of_messages_left_func()
{
    $user_id = get_current_user_id();
    $user_messages_left = get_user_meta($user_id, 'number_of_messages_left', true);
    return sprintf('You have %d consultations left for this billing period.', $user_messages_left);
}

add_shortcode('get_no_of_messages_left', 'get_no_of_messages_left_func');

//change 1
