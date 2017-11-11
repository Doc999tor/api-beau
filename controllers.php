<?php
# Clients
$container['AddClientCtrl'] = function () use ($container) {
	return new \Lib\Controllers\AddClientCtrl($container);
};

# CustomersList
$container['CustomersList'] = function () use ($container) {
	return new \Lib\Controllers\CustomersList($container);
};

# GroupsCtrl
$container['GroupsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\GroupsCtrl($container);
};

# CustomersDetails
$container['CustomersDetails\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\ClientsCtrl($container);
};
$container['CustomersDetails\DeptCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\DeptCtrl($container);
};
$container['CustomersDetails\NotesCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\NotesCtrl($container);
};
$container['CustomersDetails\MediaCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\MediaCtrl($container);
};
$container['CustomersDetails\SocialCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\SocialCtrl($container);
};
$container['CustomersDetails\SignatureCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\SignatureCtrl($container);
};

# CreatingAppointment
$container['CreatingAppointment\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CreatingAppointment\ClientsCtrl($container);
};
$container['ProceduresCtrl'] = function () use ($container) {
	return new \Lib\Controllers\ProceduresCtrl($container);
};
$container['CreatingAppointment\AppointmentsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CreatingAppointment\AppointmentsCtrl($container);
};

# Reminders
$container['RemindersCtrl'] = function () use ($container) {
	return new \Lib\Controllers\RemindersCtrl($container);
};

# Templates
$container['TemplatesCtrl'] = function () use ($container) {
	return new \Lib\Controllers\TemplatesCtrl($container);
};
