<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class BusinessSettingsCtrl extends Controller {
	public function setBusinessName (Request $request, Response $response) {
		$body = $request->getParsedBody();
		if (isset($body['business_name']) && mb_strlen($body['business_name']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('business_name has to exist and be more than 1 symbol');
		}
	}
	public function setBusinessPhoneNumber (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['business_phone_number']) && preg_match('/^[\d()+\-*\/]+$/', $body['business_phone_number'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('business_phone_number must exist and be a correct phone number <br>');
		}
	}
	public function setBusinessLocation (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['business_location']) && mb_strlen($body['business_location']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('business_location has to exist and be more than 1 symbol');
		}
	}
	public function setIsMeetingAtClientLocation (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['is_meeting_at_client_location']) && in_array($body['is_meeting_at_client_location'], ['true', 'false'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('is_meeting_at_client_location value is incorrect <br>');
		}
	}
	public function setThankYouMessage (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['thank_you_message']) && mb_strlen($body['thank_you_message']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('thank_you_message has to exist and be more than 1 symbol');
		}
	}
	public function setWebsite (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['website']) && preg_match('/^https?:\/\/.{2,}$/', $body['website'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('website has to exist and not to be malformed');
		}
	}
	public function setFacebook (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['facebook']) && preg_match('/^https?:\/\/.{2,}$/', $body['facebook'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('facebook has to exist and not to be malformed');
		}
	}
	public function setAboutYou (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['about_you']) && mb_strlen($body['about_you']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('about_you has to exist and be more than 1 symbol');
		}
	}
	public function setBillingName (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['billing_name']) && mb_strlen($body['billing_name']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('billing_name has to exist and be more than 1 symbol');
		}
	}
	public function setBillingAddress (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['billing_address']) && mb_strlen($body['billing_address']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('billing_address has to exist and be more than 1 symbol');
		}
	}
	public function setBillingEmail (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['billing_email']) && preg_match('/^.*@.*\..{2,}$/', $body['billing_email'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('billing_email does\'nt match the pattern - /^.*@.*\..{2,}$/');
		}
	}
	public function setAdditionalBillingInfo (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['additional_billing_info']) && mb_strlen($body['additional_billing_info']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('additional_billing_info has to exist and be more than 1 symbol');
		}
	}
	public function setLoginEmail (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['login_email']) && preg_match('/^.*@.*\..{2,}$/', $body['login_email'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('login_email does\'nt match the pattern - /^.*@.*\..{2,}$/');
		}
	}
	public function setLoginPassword (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['login_password']) && mb_strlen($body['login_password']) > 1) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('login_password has to exist and be more than 7 symbol');
		}
	}
	public function setPermitAd (Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (isset($body['permit_ads']) && in_array($body['permit_ads'], ['true', 'false'])) {
			return $response->withStatus(204);
		} else {
			return $response->withStatus(400)->getBody()->write('permit_ads value is incorrect <br>');
		}
	}
	public function deleteAccount (Request $request, Response $response) {
		$body = $request->getParsedBody();
		return $response->withStatus(401);
	}
}