<?php

use \Carbon\Carbon;


class Auth extends App\Models\Model {
    public function explodeToken($token) {
        $exploded_token = explode(':', $token);

        return $exploded_token;
    }

    public function validateToken($token) {
        $AuthToken = new AuthToken($this);

        $exploded_token = self::explodeToken($token);

        $selector = $exploded_token[0];
        $validator = $exploded_token[1];

        if (!$selector || !$validator) {
            $this->logger->debug('Selector or validator empty.', ['selector' => $selector, 'validator' => $validator]);

            return false;
        }

        $auth_token = $AuthToken->getToken($selector, $validator);

        $hashed_validator = hash('sha256', $validator);

        if (function_exists('hash_equals')) {
            $result = hash_equals($hashed_validator, $auth_token->validator);
        } else {
            $result = ($hashed_validator === $auth_token->validator);
        }

        if (!$result) {
            $this->logger->debug('Hashed validator does not match database validator.', ['hashed_validator' => $hashed_validator, 'token_validator' => $auth_token->validator]);

            return false;
        }

        return $auth_token;
    }

    public function createSession($user_id, $remember=0) {
        $AuthToken = new AuthToken($this);

        /**
         * Save the AuthToken for one year if the user chooses to remember the
         * current session.
         */
        if ($remember == '1') {
            $expiry_for_database = Carbon::now()->addYears(1);
            $expiry_for_cookie = time() + (60 * 60 * 24 * 365);
        } else {
            $expiry_for_database = Carbon::now()->addDays(1);
            $expiry_for_cookie = 0;
        }

        $this->logger->debug('Preparing session settings.', ['user_id' => $user_id, 'remember' => $remember]);

        $selector = md5(time());
        $validator = StringHandler::getToken(20);

        if (!$AuthToken->createToken($user_id, $selector, $validator, $expiry_for_database)) {
            $this->logger->error('Could not insert AuthToken.', ['user_id' => $user_id]);
            $this->flash->addMessage('danger', 'Internal error.');

            return false;
        }

        /**
         * Instead of only storing a random token in a cookie, we store
         * selector:validator.
         */
        if (setcookie('auth_token', $selector.':'.$validator, $expiry_for_cookie)) {
            $this->logger->debug('Cookie auth_token set.', ['user_id' => $user_id, 'selector' => $selector, 'validator' => $validator]);
        }

        return true;
    }

    public function destroySession($user_id) {
        $AuthToken = new AuthToken($this);

        if (!$AuthToken->purgeToken($user_id)) {
            $this->logger->error('Could not purge AuthToken.', ['user_id' => $user_id]);
        }

        setcookie('auth_token', '', time() - 3600);

        return true;
    }
}
