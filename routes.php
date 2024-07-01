<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface		 as Response;

use \Lib\Middlewares\PostReturnIDMiddleware  as ReturnID;
use \Lib\Middlewares\RandomReturn422		 as Return422;

$app->post('/personal_cabinet/check-login', 'AuthCtrl:checkLoginPersonalCabinet');
$app->post('/personal_cabinet/signup', 'AuthCtrl:checkSignup');

$app->post('/check-login', 'AuthCtrl:checkLogin');
$app->post('/check-credentials', 'AuthCtrl:checkSignup');
$app->post('/signup', 'AuthCtrl:signup');
$app->post('/reset_password', 'AuthCtrl:resetPassword');
$app->post('/set_password', 'AuthCtrl:setPassword');
$app->get('/countries', 'AuthCtrl:countries');
$app->get('/business_types', 'AuthCtrl:getBusinessTypes');

### Appointments
$app->group('/appointments', function () use ($app) {
	$prefix = 'AppointmentsCtrl:';
	$app->get('', $prefix . 'getCalendar');
	$app->get('/{bid:\w+}/personal_cabinet', $prefix . 'getCalendarCabinet');
	// http://localhost:3000/appointments/available_slots?date=2020-01-01&worker_id=1&duration=120
	$app->get('/available_slots', $prefix . 'getAvailableSlots');

	$app->post('', $prefix . 'addAppointment'); // custom returned object

	$app->post('/meeting', $prefix . 'addMeeting');
	$app->post('/meeting/recurring-force', function (Request $request, Response $response):Response {
		return $response->withStatus(201);
	})->add(new ReturnID());
	$app->post('/meeting/recurring-skip-overlapping', function (Request $request, Response $response):Response {
		return $response->withStatus(201);
	})->add(new ReturnID());
	$app->post('/break', $prefix . 'addBreak');
	$app->post('/break/recurring-force', function (Request $request, Response $response):Response {
		return $response->withStatus(201);
	})->add(new ReturnID());
	$app->post('/break/recurring-skip-overlapping', function (Request $request, Response $response):Response {
		return $response->withStatus(201);
	})->add(new ReturnID());
	$app->post('/vacation', $prefix . 'addVacation');

	$app->get('/{appointment_id:\d+}/recurring/touched', $prefix . 'getRecurrentAppointments');
	$app->put ('/{appointment_id:\d+}', $prefix . 'editAppointment');
	$app->put ('/{appointment_id:\d+}/undelete', $prefix . 'undelete');
	$app->put ('/{appointment_id:\d+}/prepayment', $prefix . 'pay');
	$app->patch ('/{appointment_id:\d+}', $prefix . 'singleChange');
	$app->delete('/{appointment_id:\d+}', $prefix . 'delete');
	$app->delete('/{appointment_id:\d+}/recurring', $prefix . 'delete');

	$app->put('/{appointment_id:\d+}/confirmation', $prefix . 'confirm');

	$app->post('/{appointment_id:\d+}/delete_request', function (Request $request, Response $response):Response {
		return $response->withStatus(201);
	});

	$app->get('/settings', $prefix . 'getCalendarSettings');
	$app->get('/settings/holidays', $prefix . 'getHolidays');
});

### Workers
$app->group('/workers', function () use ($app) {
	$prefix = 'WorkersCtrl:';
	$app->get('', $prefix . 'getAllWorkersData');
	$app->get('/{worker_id:\d+}', $prefix . 'getData');
});

### Creating Appointment
$app->group('/creating-appointment', function () use ($app) {
	$prefix = 'CreatingAppointment\\ClientsCtrl:';
	$app->get('/', $prefix . 'index');
	$app->get('/clients', $prefix . 'getClients');
	$app->get('/clients/bi', $prefix . 'getBIClients');
	$app->get('/clients/{id:\d+}', $prefix . 'getClient');
});

### Adding Clients
$app->group('/add-client', function () use ($app) {
	$prefix = 'AddClientCtrl:';
	$app->get('/', $prefix . 'index');
	$app->get('/clients',    $prefix . 'getClients');
	$app->get('/clients/{id:\d+}', $prefix . 'getClient');
	$app->post  ('/clients', $prefix . 'addClient');

	$app->get('/media', $prefix . 'getMedia');
	$app->get('/options/source', $prefix . 'getSourceVariants');
	$app->get('/options/tags', $prefix . 'getTagsVariants');
});

### Customers List
$app->group('/customers-list', function () use ($app) {
	$prefix = 'CustomersList:';
	$app->get   ('/', $prefix . 'index');
	$app->get   ('/clients', $prefix . 'getClients');
	$app->post  ('/clients/import', $prefix . 'importBulkClients');
	$app->post  ('/clients/import/skip', $prefix . 'skipImportBulkClients');
	$app->post  ('/clients/import/manual', $prefix . 'importClientsFile');
	$app->map   (['HEAD'], '/clients', $prefix . 'getCount');
	$app->delete('/clients', $prefix . 'deleteClients');
	// $app->delete('/clients/{client_ids:(?:\d+)(?:,\d+)*}', $prefix . 'deleteClients');

	$app->post('/clients/validate', $prefix . 'validatePhoneNumber');
	$app->get ('/clients/check-phone-number-exists/{number}', $prefix . 'checkPhoneNumberExists');
});

### Groups
$app->group('/groups', function () use ($app) {
	$prefix = 'GroupsCtrl:';
	$app->get('', $prefix . 'getGroups');
	$app->get('/{group_id:\d+}/clients', $prefix . 'getGroupClients');
	$app->post('', $prefix . 'add')->add(new ReturnID());
	$app->patch('/{group_id:\d+}', $prefix . 'rename');
	$app->delete('/{group_id:\d+}', $prefix . 'delete');
	$app->put('/{group_id:\d+}/clients', $prefix . 'addClients');
	$app->delete('/{group_id:\d+}/clients', $prefix . 'addClients');
});

### Customers Details
$app->group('/customers-details', function () use ($app) {
	$prefix = 'CustomersDetails\\';
	$cl_prefix = $prefix . 'ClientsCtrl:';

	$app->get('/', $cl_prefix . 'index');

	$app->get('/clients', $cl_prefix . 'getClients');

	$app->group('/clients/{client_id:\d+}', function () use ($app, $prefix, $cl_prefix) {
		$app->get('', $cl_prefix . 'getPersonalData');
		$app->put('', $cl_prefix . 'setPersonalData');
		$app->patch('', $cl_prefix . 'setPersonalData');
		$app->patch('/rt', $cl_prefix . 'setRTPersonalData');
		$app->delete('', $cl_prefix . 'removeUser');
		$app->post('/profile_image', $cl_prefix . 'setProfileImage');

		# recent-appointments
		$app->get('/recent-appointments', 'AppointmentsCtrl:getRecentAppointments');

		# Debt
		$app->group('/debt', function () use ($app, $prefix) {
			$debt_prefix = $prefix . 'DebtCtrl';
			$app->post('', $debt_prefix . ':addDebt')->add(new ReturnID());

			$app->group('/{debt_id:\d+}', function () use ($app, $debt_prefix) {
				$app->put   ('', $debt_prefix . ':updateDebt');
				$app->delete('', $debt_prefix . ':deleteDebt');
			});
		});

		# Note
		$app->group('/{notes:notes|inventory}', function () use ($app, $prefix) {
			$note_prefix = $prefix . 'NotesCtrl';
			$app->post('', $note_prefix . ':addNote')->add(new ReturnID());

			$app->group('/{note_id:\d+}', function () use ($app, $note_prefix) {
				$app->put ('', $note_prefix . ':updateNote');
				$app->delete('', $note_prefix . ':deleteNote');
			});
		});

		# Map
		$app->get('/map', $cl_prefix . 'getMap');

		# Media
		$app->group('/media', function () use ($app, $prefix) {
			$app->post('', 'CustomersDetails\MediaCtrl:addMedia');
			$app->patch ('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:editMediaNote');
			$app->delete('/{media_id:(?:\d+)(?:,\d+)*}', 'CustomersDetails\MediaCtrl:removeMedia');
		});

		# Social
		$app->group('/social', function () use ($app, $prefix) {
			$app->post('', $prefix . 'SocialCtrl:addSocial')->add(new ReturnID());
			$app->delete('/{media_id:\d+}', $prefix . 'SocialCtrl:deleteSocial');
		});

		# Signature
		$app->group('/signature', function () use ($app, $prefix) {
			$app->post('', $prefix . 'SignatureCtrl:addSignature');
			$app->delete('', $prefix . 'SignatureCtrl:deleteSignature');
		});

		$app->put('/send-filling-up', $cl_prefix . 'sendFillingUpLink');

		# Timeline
		$app->group('/timeline', function () use ($app, $prefix) {
			$prefix = 'CustomersDetails\TimelineCtrl:';
			$app->get('/appointments', $prefix . 'getAppointments');
			$app->get('/gallery', $prefix . 'getGallery');
			$app->get('/debts', $prefix . 'getDebts');
			$app->get('/inventory', $prefix . 'getDebts');
			$app->get('/notes', $prefix . 'getNotes');
			$app->get('/sms', $prefix . 'getSms');
			$app->get('/punch_cards', $prefix . 'getPunchCards');
		});
		# Punch_cards
		$app->group('/punch_cards', function () use ($app) {
			$prefix = 'CustomersDetails\PunchCardsCtrl:';
			$app->get('', $prefix . 'get');
			$app->get('/{punch_card_id:\d+}', $prefix . 'getOne');
			$app->post('', $prefix . 'add')->add(new ReturnID());
			$app->delete('/{punch_card_id:\d+}', $prefix . 'deletePunchCard')->add(new Return422());

			$app->put('/{punch_card_id:\d+}/note', $prefix . 'editNote');

			$app->post('/{punch_card_id:\d+}/use', $prefix . 'use')->add(new ReturnID());
			$app->delete('/{punch_card_id:\d+}/use/{use_id:\d+}', $prefix . 'unuse');
		});
		# Colors beautech
		$app->get('/colors_beautech', $prefix . 'ColorsCtrl:getColorsBeautechOld');
	});
});

### Reminders
$app->group('/reminders', function () use ($app) {
	$prefix = 'RemindersCtrl:';
	$app->get   ('', $prefix . 'getReminders');
	$app->get   ('/clients', $prefix . 'getClients');
	$app->post  ('', $prefix . 'add')->add(new ReturnID());
	$app->put   ('/{reminder_id:\d+}', $prefix . 'update');
	$app->patch ('/{reminder_id:\d+}', $prefix . 'isDone');
	$app->delete('/{reminder_id:\d+}', $prefix . 'delete');
});

### Settings
$app->group('/settings', function () use ($app) {
	$app->get('/maps-api-key', 'SettingsCtrl:getMapsAPIKey');
	$app->get('/google-calendar-client-key', 'SettingsCtrl:getGoogleContactsAPIKey');
	$app->get('/business_data/new_appointments', 'SettingsCtrl:getBusinessDataAppointments');
	$app->get('/business_data/new_clients', 'SettingsCtrl:getBusinessDataClients');
	$app->get('/business_data/income', 'SettingsCtrl:getBusinessDataIncome');
	$app->get('/business_data/bonus_points', 'SettingsCtrl:getBusinessDataBonusPoints');
	$app->get('/business_data', 'SettingsCtrl:getBusinessData');
	$app->post('/sign/legal', 'SettingsCtrl:signLegal');

	$app->put('/account', 'SettingsCtrl:setAccountInfo');
	$app->put('/billing_details', 'SettingsCtrl:setBillingDetails');
	$app->put('/notifications', 'SettingsCtrl:setNotifications');
	$app->put('/calendar', 'SettingsCtrl:setCalendarSettings');

	$app->put('/calendar/calendar_view', 'CalendarSettingsCtrl:setCalendarView');
	$app->put('/calendar/view_starts_on', 'CalendarSettingsCtrl:setViewStartsOn');
	$app->put('/calendar/show_calendar_from', 'CalendarSettingsCtrl:setShowCalendarFrom');
	$app->put('/calendar/show_calendar_to', 'CalendarSettingsCtrl:setShowCalendarTo');
	$app->put('/calendar/slotDuration', 'CalendarSettingsCtrl:setSlotDuration');
	$app->put('/calendar/allow_multiple_events_on_the_same_time_slot', 'CalendarSettingsCtrl:setAllowMultipleEventsOnTheSameTimeSlot');
	$app->put('/calendar/allow_scheduling_outside_of_time_slots', 'CalendarSettingsCtrl:setAllowSchedulingOutsideOfTimeSlots');
	$app->put('/calendar/is_income_shown', 'CalendarSettingsCtrl:setIsIncomeShown');
	$app->post('/calendar/import', 'CalendarSettingsCtrl:importCalendar');

	$app->put('/business/business_name', 'BusinessSettingsCtrl:setBusinessName');
	$app->post('/business/business_logo', 'BusinessSettingsCtrl:uploadBusinessLogo');
	$app->put('/business/business_phone_number', 'BusinessSettingsCtrl:setBusinessPhoneNumber');
	$app->put('/business/business_location', 'BusinessSettingsCtrl:setBusinessLocation');
	$app->put('/business/is_meeting_at_client_location', 'BusinessSettingsCtrl:setIsMeetingAtClientLocation');
	$app->put('/business/thank_you_message', 'BusinessSettingsCtrl:setThankYouMessage');
	$app->put('/business/appointment_confirmation', 'BusinessSettingsCtrl:setAppointmentConfirmation');
	$app->put('/business/website', 'BusinessSettingsCtrl:setWebsite');
	$app->put('/business/facebook', 'BusinessSettingsCtrl:setFacebook');
	$app->put('/business/instagram', 'BusinessSettingsCtrl:setInstagram');
	$app->put('/business/about_you', 'BusinessSettingsCtrl:setAboutYou');
	$app->put('/business/billing_name', 'BusinessSettingsCtrl:setBillingName');
	$app->put('/business/billing_address', 'BusinessSettingsCtrl:setBillingAddress');
	$app->put('/business/billing_email', 'BusinessSettingsCtrl:setBillingEmail');
	$app->put('/business/additional_billing_info', 'BusinessSettingsCtrl:setAdditionalBillingInfo');

	$app->put('/user/login_email', 'BusinessSettingsCtrl:setLoginEmail');
	$app->put('/user/login_password', 'BusinessSettingsCtrl:setLoginPassword');
	$app->put('/user/permit_ads', 'BusinessSettingsCtrl:setPermitAd');
	$app->delete('/user', 'BusinessSettingsCtrl:deleteAccount');

	$app->put('/application/lang', 'ApplicationSettingsCtrl:setApplicationLang');
	$app->put('/application/start_page', 'ApplicationSettingsCtrl:setApplicationStartPage');
	$app->put('/application/currency', 'ApplicationSettingsCtrl:setApplicationCurrency');
	$app->put('/application/timezone', 'ApplicationSettingsCtrl:setApplicationTimezone');

	$app->get('/sms/credits', 'SmsSettingsCtrl:getCredits'); // credits_requested_count=100
	$app->get('/sms/sent', 'SmsSettingsCtrl:getSent'); // credits_requested_count=100
	$app->post('/sms/fill-credits', 'SmsSettingsCtrl:fillCredits'); // credits_requested_count=100
	$app->put('/sms/{setting_canonical_name:new_event|reschedule_event|delete_event|thank_for_visiting|reminders_before_event|thank_you_for_coming|greetings_before_birthdays|automatic_filling_up_sending}/text', 'SmsSettingsCtrl:editTemplate'); // text=template
	$app->post('/sms/{setting_canonical_name:new_event|reschedule_event|delete_event|thank_for_visiting|reminders_before_event|thank_you_for_coming|greetings_before_birthdays|automatic_filling_up_sending}/edit-manual', 'SmsSettingsCtrl:sendManualEdit'); // text=sms-text
	$app->put('/sms/new_event', 'SmsSettingsCtrl:shouldSend');
	$app->put('/sms/reschedule_event', 'SmsSettingsCtrl:shouldSend');
	$app->put('/sms/delete_event', 'SmsSettingsCtrl:shouldSend');
	$app->put('/sms/thank_for_visiting', 'SmsSettingsCtrl:shouldSend');
	$app->put('/sms/reminders_before_event', 'SmsSettingsCtrl:eventReminders'); // should_send=true&mins_before=30
	$app->put('/sms/thank_you_for_coming', 'SmsSettingsCtrl:eventThankYou'); // should_send=true&mins_before=30
	$app->put('/sms/greetings_before_birthdays', 'SmsSettingsCtrl:greetingsBeforeBirthdays'); // should_send=true&days_before=1&time_for_sending=16:30
	$app->put('/sms/automatic_filling_up_sending', 'SmsSettingsCtrl:automaticFillingUpSending'); // should_send=true&days_before=1&time_for_sending=16:30

	$app->post('/workers', 'WorkersCtrl:addWorker');
	$app->put('/workers/{worker_id:\d+}', 'WorkersCtrl:updateWorker');
	$app->patch('/workers/{worker_id:\d+}', 'WorkersCtrl:updateWorkerDetail');
	$app->delete('/workers/{worker_id:\d+}', 'WorkersCtrl:deleteWorker');

	$app->post('/digital-business-card', 'DigitalBusinessCardCtrl:addCard');
	$app->put('/digital-business-card/{card_id:\d+}', 'DigitalBusinessCardCtrl:updateCard');
	$app->delete('/digital-business-card/{card_id:\d+}', 'DigitalBusinessCardCtrl:deleteCard');
	$app->get('/digital-business-card/{card_id:\d+}/qr', 'DigitalBusinessCardCtrl:getQR');

	$app->put('/online-booking', 'DigitalBusinessCardCtrl:updateOnlineBooking');

	$app->post('/bonus_points/earn', 'BonusPointsCtrl:earn');
	$app->post('/bonus_points/redeem', 'BonusPointsCtrl:redeem');
});

### Catalog
$app->group('/catalog/services', function () use ($app) {
	$prefix = 'ServicesCtrl:';

	$app->get   ('',    $prefix . 'getAll');
	$app->get   ('/rt',    $prefix . 'getAllRT');
	$app->get   ('/bi', $prefix . 'getBI');
	$app->get   ('/{service_id:\d+}', $prefix . 'getService');
	$app->put   ('/{service_id:\d+}', $prefix . 'update');
	$app->patch ('/{service_id:\d+}', $prefix . 'singleUpdate');
	$app->delete('/{service_ids:(?:\d+)(?:,\d+)*}', $prefix . 'delete')->add(new Return422());
	$app->post  ('', $prefix . 'add')->add(new ReturnID());

	$app->post  ('/categories', $prefix . 'addCategory')->add(new ReturnID());
	$app->put   ('/categories/{category_id:\d+}', $prefix . 'renameCategory');
	$app->delete('/categories/{category_id:\d+}', $prefix . 'deleteCategory');
});

### SMS Templates
$app->group('/templates', function () use ($app) {
	$prefix = 'TemplatesCtrl:';

	$app->get ('',    $prefix . 'getAll');
	$app->get ('/{template_name:\w+}',    $prefix . 'getOne');
	$app->post('',    $prefix . 'add')->add(new ReturnID());
});
$app->post('/send-sms', 'TemplatesCtrl:sendSms');

$app->get('/error_page', function (Request $request, Response $response) {
	return $response->getBody()->write('<h1>Error Page</h1>');
});

$app->any('/503', function (Request $request, Response $response):Response {
	return $response->withHeader('Retry-After', 2)->withStatus(503);
});
$app->any('/502', function (Request $request, Response $response):Response {
	return $response->withStatus(502);
});
$app->any('/500', function (Request $request, Response $response):Response {
	return $response->withStatus(500);
});
$app->any('/422', function (Request $request, Response $response):Response {
	return $response->withStatus(422);
});
$app->any('/409', function (Request $request, Response $response):Response {
	return $response->withStatus(409);
});
$app->any('/402', function (Request $request, Response $response):Response {
	return $response->withStatus(402);
});
$app->get('/helpers/empty-array', function (Request $request, Response $response):Response {
	return $response->withJson([]);
});
$app->get('/helpers/empty-object', function (Request $request, Response $response):Response {
	return $response->withJson((object) null);
});

$app->group('/metrics', function () use ($app) {
	$prefix = 'MetricsCtrl:';
	// https://api.bewebmaster.co.il/metrics/installation_popup_calendar
	$app->post  ('/installation_popup_calendar', $prefix . 'addCalendarInstallationMetrics');
	$app->post  ('/installation_popup_clients', $prefix . 'addCalendarInstallationMetrics');
	$app->post  ('/tracking_events', $prefix . 'addCustomMetrics');
	$app->post  ('/messaging_sms', $prefix . 'addCustomMetrics');
	$app->post  ('/messaging_whatsapp', $prefix . 'addCustomMetrics');
});

$app->group('/filling-up', function () use ($app) {
	$prefix = 'CustomersDetails\\ClientsCtrl';
	$app->patch('', $prefix . ':setPersonalDataFromClient');
	$app->post ('/photo', $prefix . ':setProfileImageFromClient');
	$app->post ('/notes', $prefix . ':addNoteFromClient');

	$app->post('/files', 'CustomersDetails\MediaCtrl:addMedia');
});
$app->post ('/unsubscribe', 'CustomersDetails\\ClientsCtrl:unsubscribe');

$app->group('/home', function () use ($app) {
	$app->post('/contact_us/leads', 'HomeController:contact_us_leads')/*->add(new ReturnID())*/;
	$app->post('/contact_us', 'HomeController:contact_us')/*->add(new ReturnID())*/;
});
$app->group('/aqua_plants', function () use ($app) {
	$app->post('/order-sdYoqg', 'AquaPlantsCtrl:order')/*->add(new ReturnID())*/;
});
// $app->group('/shadeecat/', function () use ($app) {
// 	$hn = 'Content-Type'; $hv = 'application/json';
// 	$sh = 'public/shadeecat/'; $j = '.json';
// 	$app->get('cv/data', function ($_, $r) use ($sh, $j, $hn, $hv) { return $r->withHeader($hn, $hv)->write(file_get_contents("{$sh}cv/data{$j}")); });
// 	$app->get('dt/data', function ($_, $r) use ($sh, $j, $hn, $hv) { return $r->withHeader($hn, $hv)->write(file_get_contents("{$sh}dt/data{$j}")); });
// 	$app->get('dt/data-pics', function ($_, $r) use ($sh, $j, $hn, $hv) { return $r->withHeader($hn, $hv)->write(file_get_contents("{$sh}dt/data-pics{$j}")); });
// 	$app->get('ms/mockStocks', function ($_, $r) use ($sh, $j, $hn, $hv) { return $r->withHeader($hn, $hv)->write(file_get_contents("{$sh}ms/mockStocks{$j}")); });
// 	$app->get('se/data', function ($_, $r) use ($sh, $j, $hn, $hv) { return $r->withHeader($hn, $hv)->write(file_get_contents("{$sh}se/data{$j}")); });
// 	$app->get('via/data', function ($_, $r) use ($sh, $j, $hn, $hv) { return $r->withHeader($hn, $hv)->write(file_get_contents("{$sh}via/data{$j}")); });
// });

$app->options('/{routes:.+}', 'cors');
function cors (Request $request, Response $response) { return $response; }
