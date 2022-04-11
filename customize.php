<?php
/**
 * Plugin Name: SSA Customization - Limit Bookings
 * Plugin URI:  https://simplyscheduleappointments.com
 * Description: Limit bookings
 * Version:     1.0.0
 * Author:      Simply Schedule Appointments
 * Author URI:  https://simplyscheduleappointments.com
 * Donate link: https://simplyscheduleappointments.com
 * License:     GPLv2
 * Text Domain: simply-schedule-appointments
 * Domain Path: /languages
 *
 * @link    https://simplyscheduleappointments.com
 *
 * @package Simply_Schedule_Appointments
 * @version 1.0.0
 *
 */

/**
 * Copyright (c) 2019 Simply Schedule Appointments (email : support@simplyscheduleappointments.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! function_exists( 'ssa' ) ) {
     return; // prevent fatal error if main SSA plugin is not active
}

add_filter('ssa/appointment/before_insert', 'ssa_lb_maybe_prevent_booking', 5, 1);
function ssa_lb_maybe_prevent_booking( $new_appointment_data ) {
    if ( current_user_can( 'ssa_manage_appointments' ) ) {
        return $new_appointment_data; // an admin or team member can always book on behalf of a customer without limit
    }

    if ( empty( $new_appointment_data['customer_id'] ) ) {
        return $new_appointment_data; // the appointment isn't associated with a customer, so we can ignore it
    }

    $maximum_per_period = 1; // Limit customers to 1 appointment (across all appointment types)
    $period_date_format = 'n'; // per calendar month
    $period_label = 'month'; // label shown to customer in error messages
    $error_message = sprintf( __('You are limited to %d %s per %s', 'simply-schedule-appointments'), $maximum_per_period, (($maximum_per_period <= 1) ? 'appointment' : 'appointments'), $period_label );
    $separate_by_appoinment_type = false; // Should each appointment type count its limit separately? `true` counts each appointment type separately, `false` counts all appointment types together.

    // query appointments for this customer
    $customer_id = $new_appointment_data['customer_id'];
    $start_date_user_is_trying_to_book = ssa_datetime( $new_appointment_data['start_date'] );
    $upcoming_appointments_for_customer = ssa()->appointment_model->query( array(
        'customer_id' => $customer_id,
        'status' => SSA_Appointment_Model::get_unavailable_statuses(), 
    ) );

    // filter queried appointments
    $upcoming_appointments_for_customer = array_filter( $upcoming_appointments_for_customer, function( $appointment ) use ( $new_appointment_data, $maximum_per_period, $period_date_format, $period_label, $start_date_user_is_trying_to_book, $separate_by_appoinment_type ) {
        if ( $separate_by_appoinment_type  && $appointment['appointment_type_id'] != $new_appointment_data['appointment_type_id'] ) {
            return false; // if we are counting each appointment type separately, filter out any appointments this customer has belonging to other appointment types
        }

        // Insert your custom logic for advanced filtering here

        return true;
    } );

    // loop through appointments count up all the appointments in each period
    foreach ( $upcoming_appointments_for_customer as $this_appointment ) {
        $this_appointment_date = ssa_datetime( $this_appointment['start_date'] );
        $this_appointment_date_localized = ssa()->utils->get_datetime_as_local_datetime( $this_appointment_date, $new_appointment_data['appointment_type_id'] );
        $period = $this_appointment_date_localized->format( $period_date_format );
        $upcoming_appointments_for_customer_by_calendar_period[$period][] = $this_appointment;
    }

    // Rescheduled appointments insert a new "booked" appointment before setting the original to "canceled", so we need to allow for that.
    if ( ! empty($new_appointment_data['rescheduled_from_appointment_id'] ) ) {
        $original_appointment = new SSA_Appointment_Object( $new_appointment_data['rescheduled_from_appointment_id'] );
        $original_appointment_date = ssa_datetime( $original_appointment->start_date );
        $original_appointment_date_localized = ssa()->utils->get_datetime_as_local_datetime($original_appointment_date, $new_appointment_data['appointment_type_id']);
        if ($start_date_user_is_trying_to_book->format($period_date_format) == $original_appointment_date_localized->format($period_date_format) ) {
            // We only want to increase the maximum if the original appointment has the same period as the new appointment
            $maximum_per_period++; 
        }
    }

    // prevent booking on appointments if already over the limit
    $appointments_already_booked_in_period_user_is_trying_to_book = empty($upcoming_appointments_for_customer_by_calendar_period[$start_date_user_is_trying_to_book->format($period_date_format)] ) ? 0 : count( $upcoming_appointments_for_customer_by_calendar_period[$start_date_user_is_trying_to_book->format( $period_date_format )] );
    if ( $appointments_already_booked_in_period_user_is_trying_to_book >= $maximum_per_period ) {
        $new_appointment_data = array_merge( $new_appointment_data, array(
            'error' => array(
                'code' => 'limit_bookings',
                'message' => $error_message,
                'data' => array(),
            ),
        ) );
    }

    return $new_appointment_data;
}
