# Simply Schedule Appointments: ssa-limit-bookings

## :pencil: Mini-customization service
Need help adjusting this mini-code snippet? 

- If the project is not a good fit, we guarantee 100% of your money back
- Speedy project completion and exceptional documentation
- First-class support and rapid feedback

[![mini-customization service](https://simplyscheduleappointments.com/wp-content/uploads/2022/09/GET-STARTED-BUTTON.png)](https://simplyscheduleappointments.com/mini-customizations/?utm_source=github&utm_medium=promo-link&utm_campaign=mini-customizations&utm_content=ssa-limit-bookings)

---

**WordPress Filter Hook** : ssa_lb_maybe_prevent_booking

## Description

Filter that inspects the logged-in users upcoming appointments and determines whether they can book their newly submitted appointment given the budgeted amount per period.

### Parameters

$new_appointment_data: The newly submitted appointment information

### Return

If they pass the filter, allow the appointment to be booked.
If they fail the filter, display an error message and prompt them to book a different time.

### (Optional) Add Your Own Logic
Within the $upcoming_appointments_for_customer array filter you have the opportunity to insert custom logic for advanced filtering. For example, you could add logic to limit submissions depending on a subset of Appointment Types instead of "All or Individual".

## Adjust the following variables

$maximum_per_period: 
	The maximum number of appointments a logged-in user can book in a given period.
	Accepts a number value.

$period_date_format: 
	The period you'd like to limit the customer on.
	Accepts PHP format character with a numeric representation.
	For example "n" is month, "Y" is year, and "w" is week.

$period_label:
	The verbal label shown to customer in the error messages.
	Accepts a string.

$separate_by_appointment_type:
  Choose whether to limit submissions for the individual appointment type or for all of them together.
  Accepts true or false.

## Pseudocode

1. Filter the appointment information just before the booking is created.
2. Check if the admin is booking. 
3. Check if the user is logged-in.
4. Collect all the booked appointments associated with this particular customer in an array.
5. Parse the array and remove any that you don't want to be counted towards the "limiting" count.
6. Loop through the array of appointments and count/organize them into periods.
7. Handle rescheduled appointments.
8. Prevent appointment from being booked if the limit is reached for the period the user is trying to book in.
9. Otherwise, let the user book.

---

## :pencil: Mini-customization service 
If you aren't a developer or don't have time to write custom code - We can help!

[![mini-customization service](https://simplyscheduleappointments.com/wp-content/uploads/2022/09/GET-STARTED-BUTTON.png)](https://simplyscheduleappointments.com/mini-customizations/?utm_source=github&utm_medium=promo-link&utm_campaign=mini-customizations&utm_content=ssa-limit-bookings)
